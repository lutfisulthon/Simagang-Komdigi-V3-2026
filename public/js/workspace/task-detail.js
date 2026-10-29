/*
 * Workspace — detail tugas (slide-over & halaman penuh) beserta modal review, kirim hasil, dan hapus.
 * Komunikasi dengan board lewat event window (lihat tasks.js):
 *   keluar: ws-task-saved { card }, ws-task-deleted { id }, ws-open-review, ws-open-submit,
 *           ws-open-task-form { task }, ws-open-delete { task }
 */
(function () {
    'use strict';

    const WS = window.WS;
    const emit = (name, detail) => window.dispatchEvent(new CustomEvent(name, { detail }));

    const FILE_ICONS = [
        [/pdf/, 'fa-file-pdf', '#dc2626'],
        [/word|msword/, 'fa-file-word', '#2563eb'],
        [/sheet|excel/, 'fa-file-excel', '#059669'],
        [/zip/, 'fa-file-zipper', '#a16207'],
        [/^image\//, 'fa-file-image', '#7c3aed'],
    ];

    function fileIcon(mime) {
        const hit = FILE_ICONS.find(([re]) => re.test(mime || ''));
        return hit ? { icon: hit[1], color: hit[2] } : { icon: 'fa-file', color: '#64748b' };
    }

    function fileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
        return (bytes / 1024 / 1024).toFixed(1).replace('.', ',') + ' MB';
    }

    WS.files = { icon: fileIcon, size: fileSize };

    // Ambil field TaskCard dari TaskDetail (untuk memperbarui board).
    function cardOf(task) {
        const keys = ['id', 'workspace_id', 'code', 'title', 'status', 'priority', 'position', 'start_at', 'due_at',
            'completed_at', 'is_late', 'sprint_id', 'rank', 'labels', 'assignees', 'checklist', 'comments_count', 'attachments_count', 'url', 'can'];
        return Object.fromEntries(keys.filter((k) => k in task).map((k) => [k, task[k]]));
    }

    WS.tasks = Object.assign(WS.tasks || {}, { cardOf });

    // datetime-local ↔ ISO (zona waktu lokal browser)
    const toLocal = (d) => { const x = new Date(d); x.setMinutes(x.getMinutes() - x.getTimezoneOffset()); return x.toISOString().slice(0, 16); };

    document.addEventListener('alpine:init', () => {
        // ---------- Perpanjang deadline (mentor/admin) ----------
        // Dibuka lewat event window `ws-open-deadline` { task } dari detail tugas atau menu kartu board.
        window.Alpine.data('wsDeadline', () => ({
            open: false,
            task: null,
            days: 3,
            dueAt: '',
            reason: '',
            errors: {},
            saving: false,
            options: [{ days: 1, label: '+1 hari' }, { days: 3, label: '+3 hari' }, { days: 7, label: '+1 minggu' }],

            init() {
                window.addEventListener('ws-open-deadline', (e) => {
                    this.task = e.detail.task;
                    this.reason = '';
                    this.errors = {};
                    this.choose(3);
                    this.open = true;
                });
            },

            get current() {
                return this.task ? WS.time.dateTime(this.task.due_at) : '';
            },

            // Dihitung dari deadline lama, atau dari sekarang bila deadline sudah lewat.
            choose(days) {
                this.days = days;
                const base = Math.max(Date.now(), new Date(this.task.due_at).getTime());
                this.dueAt = toLocal(base + days * 86400000);
            },

            async submit() {
                if (!this.dueAt) { this.errors = { due_at: 'Deadline baru wajib diisi.' }; return; }
                this.saving = true;
                try {
                    const res = await WS.api.tasks.extendDeadline(this.task.id, { due_at: new Date(this.dueAt).toISOString(), reason: this.reason.trim() || null });
                    emit('ws-task-saved', { card: cardOf(res.data) });
                    WS.toast(`Deadline ${this.task.code} diperpanjang sampai ${WS.time.dateTime(res.data.due_at)}.`, 'success');
                    this.open = false;
                } catch (err) {
                    if (err.status === 422 && err.errors) this.errors = Object.fromEntries(Object.entries(err.errors).map(([k, v]) => [k, v[0]]));
                    else WS.toast(err.message, 'error');
                } finally {
                    this.saving = false;
                }
            },
        }));

        const Alpine = window.Alpine;

        // ---------- Detail tugas ----------
        // config: { mode: 'panel' | 'page', task?: TaskDetail }
        Alpine.data('wsTaskDetail', (config) => ({
            mode: config.mode,
            task: config.task || null,
            loading: false,
            error: null,
            comment: '',
            sending: false,
            commentError: '',
            moving: false,

            init() {
                if (this.mode === 'panel') {
                    this.$watch('$store.wsTasks.detailId', (id) => { if (id) this.load(id); });
                    this.$watch('$store.wsTasks.detailOpen', (open) => { if (!open) this.$store.wsTasks.closeDetail(); });
                    if (this.$store.wsTasks.detailId) this.load(this.$store.wsTasks.detailId);
                }
                window.addEventListener('ws-task-saved', (e) => {
                    if (this.task && e.detail.card.id === this.task.id && !e.detail.fromDetail) this.refresh();
                });
                window.addEventListener('ws-task-deleted', (e) => {
                    if (this.task && e.detail.id === this.task.id && this.mode === 'page') {
                        window.location.href = this.task.workspace ? this.task.workspace.url : '/workspace';
                    }
                });
            },

            async load(id) {
                if (this.task && this.task.id === id) { this.refresh(); return; }
                this.task = null;
                this.loading = true;
                this.error = null;
                this.comment = '';
                try {
                    const res = await WS.api.tasks.show(id);
                    this.task = res.data;
                } catch (err) {
                    this.error = err.message;
                } finally {
                    this.loading = false;
                }
            },

            async refresh() {
                if (!this.task) return;
                try {
                    const res = await WS.api.tasks.show(this.task.id);
                    this.task = res.data;
                } catch (_) { /* tetap tampilkan data lama */ }
            },

            // Simpan perubahan lokal & beri tahu board.
            commit(task) {
                this.task = task;
                emit('ws-task-saved', { card: cardOf(task), fromDetail: true });
            },

            // ---------- Aksi status ----------

            get isStaff() {
                return !!(this.task && this.task.can.review);
            },

            get primaryActions() {
                const t = this.task;
                if (!t || !t.can.move) return [];
                if (this.isStaff) {
                    if (t.status === 'review') {
                        return [
                            { target: 'done', label: 'Setujui & beri nilai', icon: 'fa-circle-check', style: 'primary' },
                            { target: 'revision', label: 'Minta revisi', icon: 'fa-rotate-left', style: 'soft' },
                        ];
                    }
                    return [];
                }
                if (WS.tasks.submitLocked(t) && t.status === 'in_progress') return [];
                return {
                    todo: [{ target: 'in_progress', label: 'Mulai kerjakan', icon: 'fa-play', style: 'primary' }],
                    in_progress: [{ target: 'review', label: 'Kirim untuk review', icon: 'fa-paper-plane', style: 'primary' }],
                    revision: [{ target: 'in_progress', label: 'Perbaiki sekarang', icon: 'fa-screwdriver-wrench', style: 'primary' }],
                }[t.status] || [];
            },

            get statusOptions() {
                return this.task ? WS.tasks.allowedTargets(this.task) : [];
            },

            get waitingNote() {
                const t = this.task;
                if (!t || this.isStaff) return '';
                if (t.status === 'review') return 'Menunggu review mentor. Anda akan mendapat notifikasi setelah direview.';
                if (t.status === 'done') return 'Tugas selesai dan sudah dinilai mentor.';
                if (!t.can.move) return 'Tugas ini tidak di-assign ke Anda.';
                if (WS.tasks.submitLocked(t)) return WS.tasks.LOCK_NOTE;
                return '';
            },

            async moveTo(target) {
                const t = this.task;
                const store = this.$store.wsTasks;
                // Di halaman board: pakai store supaya urutan kolom ikut terjaga.
                if (this.mode === 'panel' && store.booted && store.byId(t.id)) {
                    this.moving = true;
                    await store.requestMove(store.byId(t.id), target, 0);
                    this.moving = false;
                    // requestMove hanya memperbarui board; ambil ulang detail (status, aksi, riwayat).
                    await this.refresh();
                    return;
                }
                const dialog = WS.tasks.dialogFor(t, target);
                if (dialog) { emit(dialog.event, { task: t, decision: dialog.decision }); return; }
                this.moving = true;
                try {
                    const res = await WS.api.tasks.move(t.id, { status: target });
                    emit('ws-task-saved', { card: res.data });
                    WS.toast(`${t.code} dipindah ke ${WS.STATUS[target]}`, 'success');
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.moving = false;
                }
            },

            // ---------- Checklist ----------

            get canCheck() {
                return !!(this.task && (this.task.can.move || this.task.can.update));
            },

            get checklistPercent() {
                const c = this.task && this.task.checklist;
                return c && c.total ? Math.round((c.done / c.total) * 100) : 0;
            },

            async toggleItem(item) {
                if (!this.canCheck) return;
                const before = item.is_done;
                item.is_done = !before;
                const c = this.task.checklist;
                c.done += item.is_done ? 1 : -1;
                try {
                    const res = await WS.api.tasks.toggleChecklist(this.task.id, item.id, { is_done: item.is_done });
                    this.task.checklist = res.data;
                    emit('ws-task-saved', { card: { id: this.task.id, checklist: res.data }, fromDetail: true });
                } catch (err) {
                    item.is_done = before;
                    c.done += before ? 1 : -1;
                    WS.toast(err.message, 'error');
                }
            },

            // ---------- Komentar ----------

            async sendComment() {
                const body = this.comment.trim();
                this.commentError = '';
                if (!body) { this.commentError = 'Komentar tidak boleh kosong.'; return; }
                this.sending = true;
                try {
                    const res = await WS.api.tasks.comment(this.task.id, { body });
                    this.task.comments.push(res.data);
                    this.task.comments_count = this.task.comments.length;
                    this.comment = '';
                    emit('ws-task-saved', { card: { id: this.task.id, comments_count: this.task.comments_count }, fromDetail: true });
                    this.$nextTick(() => this.$refs.comments && this.$refs.comments.lastElementChild?.scrollIntoView({ block: 'nearest', behavior: WS.scrollBehavior() }));
                } catch (err) {
                    this.commentError = (err.errors && err.errors.body && err.errors.body[0]) || err.message;
                } finally {
                    this.sending = false;
                }
            },

            // ---------- Lain-lain ----------

            edit() {
                emit('ws-open-task-form', { task: JSON.parse(JSON.stringify(this.task)) });
            },

            remove() {
                emit('ws-open-delete', { task: this.task });
            },

            async copyLink() {
                const link = new URL(this.task.url, window.location.origin).href;
                try {
                    await navigator.clipboard.writeText(link);
                    WS.toast('Link tugas disalin.', 'success');
                } catch (_) {
                    WS.toast(link);
                }
            },

            due() {
                return WS.tasks.dueInfo(this.task);
            },

            describe: (item) => WS.activity.describe(item),
            relative: (iso) => WS.time.relative(iso),
            dateTime: (iso) => WS.time.dateTime(iso),
            fileIcon,
            fileSize,
        }));

        // ---------- Modal review (mentor/admin) ----------
        Alpine.data('wsReviewModal', () => ({
            open: false,
            task: null,
            decision: 'approve',
            score: '',
            feedback: '',
            errors: {},
            submitted: false,
            saving: false,

            init() {
                this.$watch('decision', () => { if (this.submitted) this.validate(); });
                this.$watch('feedback', () => { if (this.submitted) this.validate(); });
                this.$watch('score', () => { if (this.submitted) this.validate(); });
                window.addEventListener('ws-open-review', (e) => {
                    this.task = e.detail.task;
                    this.decision = e.detail.decision || 'approve';
                    this.score = '';
                    this.feedback = '';
                    this.errors = {};
                    this.submitted = false;
                    this.open = true;
                    this.$nextTick(() => this.$refs.first && this.$refs.first.focus());
                });
            },

            validate() {
                const errors = {};
                if (this.decision === 'revision' && !this.feedback.trim()) errors.feedback = 'Catatan revisi wajib diisi.';
                if (this.score !== '' && (Number(this.score) < 0 || Number(this.score) > 100)) errors.score = 'Nilai harus 0–100.';
                this.errors = errors;
                return !Object.keys(errors).length;
            },

            async submit() {
                this.submitted = true;
                if (!this.validate()) return;
                this.saving = true;
                try {
                    const res = await WS.api.tasks.review(this.task.id, {
                        decision: this.decision,
                        score: this.decision === 'approve' && this.score !== '' ? Number(this.score) : null,
                        feedback: this.feedback.trim() || null,
                    });
                    emit('ws-task-saved', { card: cardOf(res.data) });
                    WS.toast(this.decision === 'approve'
                        ? `${this.task.code} ditandai Selesai.`
                        : `${this.task.code} dikembalikan untuk revisi.`, 'success');
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

        // ---------- Modal kirim hasil (intern → Review) ----------
        Alpine.data('wsSubmitModal', () => ({
            open: false,
            task: null,
            links: [],
            files: [],
            note: '',
            errors: {},
            saving: false,

            init() {
                window.addEventListener('ws-open-submit', (e) => {
                    if (WS.tasks.submitLocked(e.detail.task)) { WS.toast(WS.tasks.LOCK_NOTE, 'error'); return; }
                    this.task = e.detail.task;
                    this.links = [{ url: '', label: '' }];
                    this.files = [];
                    this.note = '';
                    this.errors = {};
                    this.open = true;
                });
            },

            get uploading() {
                return this.files.some((f) => f.status === 'uploading');
            },

            async submit() {
                const links = this.links.filter((l) => l.url.trim());
                const errors = {};
                links.forEach((l, i) => { if (!/^https?:\/\/\S+$/.test(l.url.trim())) errors['link' + i] = 'URL harus diawali http:// atau https://'; });
                this.errors = errors;
                if (Object.keys(errors).length || this.uploading) return;

                this.saving = true;
                const store = window.Alpine.store('wsTasks');
                try {
                    const res = await WS.api.tasks.move(this.task.id, {
                        status: 'review',
                        result_links: links.map((l) => ({ url: l.url.trim(), label: l.label.trim() || null })),
                        attachment_ids: this.files.filter((f) => f.status === 'done').map((f) => f.attachment.id),
                        note: this.note.trim() || null,
                    });
                    const card = Object.assign({}, res.data, { position: -1 });
                    emit('ws-task-saved', { card });
                    if (store.booted) store.reorder('review', store.column('review').map((t) => t.id));
                    WS.toast(`${this.task.code} dikirim untuk review.`, 'success');
                    this.open = false;
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.saving = false;
                }
            },
        }));

        // ---------- Konfirmasi hapus ----------
        Alpine.data('wsDeleteModal', () => ({
            open: false,
            task: null,
            saving: false,

            init() {
                window.addEventListener('ws-open-delete', (e) => {
                    this.task = e.detail.task;
                    this.open = true;
                });
            },

            async confirm() {
                this.saving = true;
                try {
                    await WS.api.tasks.remove(this.task.id);
                    emit('ws-task-deleted', { id: this.task.id });
                    WS.toast(`${this.task.code} dihapus.`, 'success');
                    this.open = false;
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.saving = false;
                }
            },
        }));
    });
})();
