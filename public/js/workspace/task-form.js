/*
 * Workspace — form buat/edit tugas dan uploader lampiran.
 * Dibuka lewat event window `ws-open-task-form` { task?: TaskDetail, status?: string }.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const emit = (name, detail) => window.dispatchEvent(new CustomEvent(name, { detail }));

    const MAX_SIZE = 10 * 1024 * 1024;
    const ALLOWED_EXT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'zip'];

    // ISO → nilai <input type="datetime-local"> (waktu lokal) dan sebaliknya.
    function toLocalInput(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function fromLocalInput(value) {
        return value ? new Date(value).toISOString() : null;
    }

    // Dipakai bersama uploader form tugas dan komposer channel.
    WS.files = Object.assign(WS.files || {}, {
        ACCEPT: ALLOWED_EXT.map((e) => '.' + e).join(','),

        // Item uploader dari File; langsung berstatus 'error' bila tipe/ukuran tidak valid.
        item(file, key) {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const item = { key, name: file.name, size: file.size, progress: 0, status: 'uploading', error: '', attachment: null };
            if (!ALLOWED_EXT.includes(ext)) Object.assign(item, { status: 'error', error: 'Tipe file tidak didukung.' });
            else if (file.size > MAX_SIZE) Object.assign(item, { status: 'error', error: 'Ukuran melebihi 10 MB.' });
            return item;
        },

        // `item` harus versi reaktif (sudah dimasukkan ke array Alpine) supaya progres tampil.
        async upload(file, item) {
            const form = new FormData();
            form.append('file', file);
            try {
                const res = await WS.api.attachments.upload(form, (p) => { item.progress = p; });
                item.attachment = res.data;
                item.status = 'done';
                item.progress = 100;
            } catch (err) {
                item.status = 'error';
                item.error = (err.errors && err.errors.file && err.errors.file[0]) || err.message;
            }
        },
    });

    // Bentuk form kosong — template modal dirender sebelum form pertama kali dibuka.
    function blankForm() {
        return { title: '', description: '', priority: 'medium', assignee_ids: [], label_ids: [], start_at: '', due_at: '', checklist: [], result_links: [],
            template_id: '', separate_copies: false };
    }

    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        // ---------- Uploader (dipakai form tugas & modal kirim hasil) ----------
        // Pakai: <div x-data="wsUploader" x-modelable="items" x-model="files">
        Alpine.data('wsUploader', () => ({
            items: [],
            dragging: false,
            seq: 0,

            accept: WS.files.ACCEPT,

            pick() {
                this.$refs.input.click();
            },

            onInput(event) {
                this.add([...event.target.files]);
                event.target.value = '';
            },

            onDrop(event) {
                this.dragging = false;
                this.add([...event.dataTransfer.files]);
            },

            add(files) {
                files.forEach((file) => {
                    this.items.push(WS.files.item(file, ++this.seq));
                    const item = this.items[this.items.length - 1];
                    if (item.status === 'uploading') WS.files.upload(file, item);
                });
            },

            removeItem(key) {
                this.items = this.items.filter((i) => i.key !== key);
            },

            size: (bytes) => WS.files.size(bytes),
        }));

        // ---------- Form tugas ----------
        // config: { workspaceId, interns: InternMini[], labels, selfInternId?: number, templates?: Template[],
        //          teamId?: number, canManageLabels?: bool }
        Alpine.data('wsTaskForm', (config) => ({
            open: false,
            editing: null, // TaskDetail saat edit
            form: blankForm(),
            files: [],
            errors: {},
            submitted: false,
            saving: false,
            interns: config.interns,
            labels: config.labels,
            templates: config.templates || [],
            selfOnly: !!config.selfInternId,
            sprintId: null,       // sprint tujuan tugas baru (null = backlog)
            canManageLabels: !!config.canManageLabels,

            init() {
                window.addEventListener('ws-open-task-form', (e) => this.start(e.detail || {}));
                window.addEventListener('ws-labels-changed', (e) => {
                    this.labels = e.detail.labels;
                    const ids = new Set(e.detail.labels.map((l) => l.id));
                    this.form.label_ids = this.form.label_ids.filter((id) => ids.has(id));
                });
                // Template yang baru disimpan ("Simpan sebagai template") langsung bisa dipakai.
                window.addEventListener('ws-template-saved', (e) => {
                    const t = e.detail.template;
                    const i = this.templates.findIndex((x) => x.id === t.id);
                    if (i === -1) this.templates.push(t);
                    else this.templates.splice(i, 1, t);
                });
                // Setelah percobaan simpan pertama, error diperbarui langsung saat user mengetik.
                this.$watch('form', () => { if (this.submitted) this.validate(); });
                // Template + beberapa intern: default-nya salinan terpisah (seperti "Tugaskan" di halaman template).
                this.$watch('form.assignee_ids.length', (n, old) => {
                    if (!this.editing && this.form.template_id && n > 1 && old <= 1) this.form.separate_copies = true;
                });
            },

            start({ task, sprint_id }) {
                this.editing = task || null;
                // "+ Buat tugas" di bagian sprint/backlog: tugas baru langsung masuk ke sana.
                this.sprintId = task ? null : (sprint_id === undefined ? WS.tasks.defaultSprintId() : sprint_id);
                this.submitted = false;
                this.errors = {};
                this.files = [];
                const t = task || {};
                this.form = {
                    title: t.title || '',
                    description: t.description || '',
                    priority: t.priority || 'medium',
                    assignee_ids: t.assignees ? t.assignees.map((a) => a.id)
                        : (config.selfInternId ? [config.selfInternId] : (this.interns.length === 1 ? [this.interns[0].id] : [])),
                    label_ids: t.labels ? t.labels.map((l) => l.id) : [],
                    start_at: toLocalInput(t.start_at),
                    due_at: toLocalInput(t.due_at),
                    checklist: t.checklist_items ? t.checklist_items.map((c) => ({ id: c.id, content: c.content, is_done: c.is_done })) : [],
                    result_links: t.result_links ? t.result_links.map((l) => ({ url: l.url, label: l.label || '' })) : [],
                    template_id: '',
                    separate_copies: false,
                };
                this.open = true;
                this.$nextTick(() => this.$refs.title && this.$refs.title.focus());
            },

            manageLabels() {
                this.$dispatch('ws-open-labels', { teamId: config.teamId });
            },

            // "Pakai template": isi form dari template; assignee & lampiran tidak diubah.
            applyTemplate(id) {
                const tpl = this.templates.find((x) => x.id === Number(id));
                this.form.template_id = tpl ? tpl.id : '';
                if (!tpl) return;
                const labelIds = new Set(this.labels.map((l) => l.id));
                Object.assign(this.form, {
                    title: tpl.title,
                    description: tpl.description || '',
                    priority: tpl.priority,
                    label_ids: (tpl.labels || []).map((l) => l.id).filter((x) => labelIds.has(x)),
                    due_at: WS.templates ? WS.templates.dueFromDays(tpl.due_in_days) : this.form.due_at,
                    checklist: (tpl.checklist || []).map((c) => ({ id: null, content: c.content, is_done: false })),
                });
                // Template biasanya dipakai untuk beberapa intern sekaligus.
                if (this.form.assignee_ids.length > 1) this.form.separate_copies = true;
            },

            get uploading() {
                return this.files.some((f) => f.status === 'uploading');
            },

            toggle(list, id) {
                const arr = this.form[list];
                const i = arr.indexOf(id);
                if (i === -1) arr.push(id);
                else arr.splice(i, 1);
            },

            addChecklist() {
                this.form.checklist.push({ id: null, content: '', is_done: false });
                this.$nextTick(() => {
                    const inputs = this.$root.querySelectorAll('[data-checklist-input]');
                    inputs[inputs.length - 1]?.focus();
                });
            },

            // Toolbar markdown sederhana untuk deskripsi.
            format(kind) {
                const el = this.$refs.description;
                const { selectionStart: s, selectionEnd: e, value } = el;
                const selected = value.slice(s, e);
                const wrap = {
                    bold: ['**', '**', 'teks tebal'],
                    code: ['`', '`', 'kode'],
                    link: ['[', '](https://)', 'teks link'],
                    list: ['- ', '', 'item'],
                }[kind];
                const text = selected || wrap[2];
                const prefix = kind === 'list' && s > 0 && value[s - 1] !== '\n' ? '\n' : '';
                this.form.description = value.slice(0, s) + prefix + wrap[0] + text + wrap[1] + value.slice(e);
                this.$nextTick(() => {
                    el.focus();
                    const start = s + prefix.length + wrap[0].length;
                    el.setSelectionRange(start, start + text.length);
                });
            },

            validate() {
                const f = this.form;
                const errors = {};
                if (!f.title.trim()) errors.title = 'Judul wajib diisi.';
                else if (f.title.length > 150) errors.title = 'Judul maksimal 150 karakter.';
                if (!f.due_at) errors.due_at = 'Deadline wajib diisi.';
                else if (f.start_at && new Date(f.start_at) > new Date(f.due_at)) errors.due_at = 'Deadline harus setelah tanggal mulai.';
                f.result_links.forEach((l, i) => {
                    if (l.url.trim() && !/^https?:\/\/\S+$/.test(l.url.trim())) errors['result_links.' + i + '.url'] = 'URL harus diawali http:// atau https://';
                });
                this.errors = errors;
                return !Object.keys(errors).length;
            },

            payload() {
                const f = this.form;
                return {
                    title: f.title.trim(),
                    description: f.description,
                    priority: f.priority,
                    assignee_ids: f.assignee_ids,
                    label_ids: f.label_ids,
                    start_at: fromLocalInput(f.start_at),
                    due_at: fromLocalInput(f.due_at),
                    checklist_items: f.checklist.filter((c) => c.content.trim()),
                    result_links: f.result_links.filter((l) => l.url.trim()).map((l) => ({ url: l.url.trim(), label: l.label.trim() || null })),
                    attachment_ids: this.files.filter((x) => x.status === 'done').map((x) => x.attachment.id),
                    // Hanya saat membuat: satu salinan per assignee (respons tetap berupa array TaskCard).
                    ...(this.editing ? {} : {
                        sprint_id: this.sprintId,
                        template_id: f.template_id || null,
                        separate_copies: f.separate_copies && f.assignee_ids.length > 1,
                    }),
                };
            },

            async submit() {
                this.submitted = true;
                if (!this.validate()) {
                    this.$nextTick(() => this.$root.querySelector('.ws-input--error')?.focus());
                    return;
                }
                if (this.uploading) { WS.toast('Tunggu upload lampiran selesai.'); return; }
                this.saving = true;
                try {
                    if (this.editing) {
                        const res = await WS.api.tasks.update(this.editing.id, this.payload());
                        emit('ws-task-saved', { card: WS.tasks.cardOf(res.data) });
                        WS.toast(`${this.editing.code} diperbarui.`, 'success');
                    } else {
                        const res = await WS.api.tasks.create(config.workspaceId, this.payload());
                        res.data.forEach((card) => emit('ws-task-saved', { card }));
                        WS.toast(res.data.length > 1
                            ? `${res.data.length} tugas dibuat.`
                            : `Tugas ${res.data[0].code} dibuat.`, 'success');
                        const store = window.Alpine.store('wsTasks');
                        if (store.booted) store.reorder('todo', store.column('todo').map((t) => t.id));
                    }
                    this.open = false;
                } catch (err) {
                    if (err.status === 422) {
                        this.errors = Object.fromEntries(Object.entries(err.errors).map(([k, v]) => [k, v[0]]));
                    } else {
                        WS.toast(err.message, 'error');
                    }
                } finally {
                    this.saving = false;
                }
            },
        }));
    });
})();
