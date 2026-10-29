/*
 * Workspace — tab Otomasi: aturan pekerjaan berulang (mentor/admin) dengan penyusun gaya Jira.
 * Rantai aturan: When (jadwal) → If (kondisi, opsional) → Then (aksi: buat tugas / kirim notifikasi).
 * Pemanggilan lewat WS.api.automations.* (mock: mock-automations.js; kontrak: docs/workspace/kontrak-frontend.md §7 #21).
 * Markup: workspace/partials/automations.blade.php.
 */
(function () {
    'use strict';

    const WS = window.WS;

    const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; // indeks 0..6 ↔ hari ke-1..7
    const NOTIFY_TO = { assignees: 'penerima tugas yang dibuat', all: 'semua anggota', leader: 'ketua sprint', mentor: 'mentor' };
    const COND = {
        workday: { label: 'Hanya hari kerja', hint: 'Aturan dilewati pada hari Sabtu dan Minggu.' },
        active_sprint: { label: 'Hanya bila ada sprint berjalan', hint: 'Aturan dilewati bila saat itu tidak ada sprint yang sedang berjalan.' },
        skip_if_open: { label: 'Lewati bila tugas sebelumnya belum selesai', hint: 'Mencegah tugas menumpuk: tidak membuat tugas baru selama tugas dengan judul yang sama masih terbuka.' },
    };

    const fmtTime = (t) => String(t || '08:00').replace(':', '.');
    const dayFmt = new Intl.DateTimeFormat('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
    const isoDow = (d) => (d.getDay() + 6) % 7 + 1; // 1 = Senin … 7 = Minggu

    function triggerText(t) {
        if (!t) return '';
        const at = ' pukul ' + fmtTime(t.time);
        if (t.frequency === 'daily') return 'Setiap hari' + at;
        if (t.frequency === 'weekly') {
            const names = (t.days || []).slice().sort((a, b) => a - b).map((d) => DAYS[d - 1]);
            if (!names.length) return 'Setiap minggu (pilih hari)' + at;
            return 'Setiap ' + (names.length > 1 ? names.slice(0, -1).join(', ') + ' dan ' + names[names.length - 1] : names[0]) + at;
        }
        return (t.month_day === 'last' ? 'Setiap akhir bulan' : 'Setiap tanggal ' + t.month_day) + at;
    }

    function assigneeText(a) {
        if (a.assignees === 'leader') return 'ketua sprint';
        if (a.assignees === 'selected') {
            const names = (window.Alpine ? window.Alpine.store('wsTasks').interns : []).filter((i) => (a.assignee_ids || []).includes(i.id)).map((i) => i.name.split(' ')[0]);
            return names.length ? names.join(', ') : 'anggota terpilih';
        }
        return 'semua anggota';
    }

    function actionText(a) {
        if (a.type === 'create_task') {
            const each = a.assignees !== 'leader' && a.per_member ? ' (satu tugas per anggota)' : '';
            return 'buat tugas "' + (a.title || '…') + '" untuk ' + assigneeText(a) + each + ', deadline ' + (Number(a.due_in_days) ? a.due_in_days + ' hari kemudian' : 'hari itu juga');
        }
        return 'kirim notifikasi ke ' + (NOTIFY_TO[a.to] || '…');
    }

    // "Setiap Jumat pukul 15.00, buat tugas "…" untuk semua anggota (hanya hari kerja)."
    function describe(rule) {
        const acts = (rule.actions || []).map(actionText).join(', lalu ');
        const conds = (rule.conditions || []).map((c) => (COND[c.type] ? COND[c.type].label.charAt(0).toLowerCase() + COND[c.type].label.slice(1) : ''));
        return triggerText(rule.trigger) + (acts ? ', ' + acts : '') + (conds.length ? ' (' + conds.join('; ') + ')' : '') + '.';
    }

    // Waktu jalan berikutnya (Date[]), mulai setelah `from`.
    function nextRuns(rule, count, from) {
        const t = rule.trigger;
        if (!t) return [];
        const start = from || new Date();
        const [hh, mm] = String(t.time || '08:00').split(':').map(Number);
        const workdayOnly = (rule.conditions || []).some((c) => c.type === 'workday');
        const out = [];
        for (let i = 0; i < 400 && out.length < (count || 3); i++) {
            const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i, hh || 0, mm || 0, 0, 0);
            if (d <= start) continue;
            const dow = isoDow(d);
            if (workdayOnly && dow > 5) continue;
            let ok = false;
            if (t.frequency === 'daily') ok = true;
            else if (t.frequency === 'weekly') ok = (t.days || []).includes(dow);
            else {
                const last = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
                ok = t.month_day === 'last' ? d.getDate() === last : d.getDate() === Math.min(Number(t.month_day) || 1, last);
            }
            if (ok) out.push(d);
        }
        return out;
    }

    const whenText = (d) => dayFmt.format(d) + ' · ' + fmtTime(String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'));

    function blankAction(type) {
        return type === 'notify'
            ? { type: 'notify', to: 'assignees', message: '' }
            : { type: 'create_task', title: '', priority: 'medium', assignees: 'all', assignee_ids: [], per_member: true, due_in_days: 3, destination: 'active_sprint' };
    }

    function blankRule() {
        return {
            id: null, name: '', enabled: true,
            trigger: { type: 'schedule', frequency: 'weekly', time: '08:00', days: [1], month_day: 1 },
            conditions: [], actions: [blankAction('create_task')],
        };
    }

    // Contoh aturan untuk memulai.
    const PRESETS = {
        report: {
            label: 'Laporan mingguan', icon: 'fa-file-lines', hint: 'Tiap Jumat, tugas laporan untuk semua anggota.',
            build: () => ({
                name: 'Laporan mingguan', enabled: true,
                trigger: { type: 'schedule', frequency: 'weekly', time: '15:00', days: [5], month_day: 1 },
                conditions: [{ type: 'active_sprint' }, { type: 'skip_if_open' }],
                actions: [
                    { type: 'create_task', title: 'Laporan kemajuan mingguan', priority: 'medium', assignees: 'all', assignee_ids: [], per_member: true, due_in_days: 3, destination: 'active_sprint' },
                    { type: 'notify', to: 'assignees', message: 'Tugas laporan mingguan sudah dibuat. Kumpulkan sebelum deadline.' },
                ],
            }),
        },
        logbook: {
            label: 'Pengingat logbook', icon: 'fa-bell', hint: 'Tiap hari kerja pukul 16.00, ingatkan mengisi logbook.',
            build: () => ({
                name: 'Pengingat logbook harian', enabled: true,
                trigger: { type: 'schedule', frequency: 'daily', time: '16:00', days: [1], month_day: 1 },
                conditions: [{ type: 'workday' }],
                actions: [{ type: 'notify', to: 'all', message: 'Jangan lupa mengisi logbook hari ini.' }],
            }),
        },
        monthly: {
            label: 'Rangkuman bulanan', icon: 'fa-calendar-check', hint: 'Akhir bulan, tugas rangkuman masuk Backlog.',
            build: () => ({
                name: 'Rangkuman bulanan', enabled: true,
                trigger: { type: 'schedule', frequency: 'monthly', time: '10:00', days: [1], month_day: 'last' },
                conditions: [],
                actions: [{ type: 'create_task', title: 'Rangkuman kegiatan bulan ini', priority: 'low', assignees: 'all', assignee_ids: [], per_member: true, due_in_days: 5, destination: 'backlog' }],
            }),
        },
    };

    WS.automations = { DAYS, COND, NOTIFY_TO, triggerText, actionText, describe, nextRuns, whenText, blankRule, blankAction, PRESETS };

    document.addEventListener('alpine:init', () => {
        // config: { workspaceId }
        window.Alpine.data('wsAutomations', (config) => ({
            view: 'list',          // list | edit
            loading: true,
            error: null,
            rules: [],
            draft: null,           // aturan yang sedang diedit (salinan)
            snapshot: '',
            selected: 'trigger',   // trigger | c:<type> | a:<index>
            errors: {},
            saving: false,
            running: {},
            confirming: null,      // id aturan yang menunggu konfirmasi hapus
            confirmTimer: null,
            addMenu: false,
            days: DAYS,
            conditionDefs: COND,
            conditionList: Object.keys(COND).map((type) => ({ type, label: COND[type].label })),
            presetList: Object.keys(PRESETS).map((key) => ({ key, label: PRESETS[key].label, icon: PRESETS[key].icon, hint: PRESETS[key].hint })),

            init() {
                this.load();
            },

            // ---------- Data ----------

            async load() {
                this.loading = true;
                this.error = null;
                try {
                    this.rules = (await WS.api.automations.list(config.workspaceId)).data;
                } catch (err) {
                    this.error = err.message;
                } finally {
                    this.loading = false;
                }
            },

            get interns() { return this.$store.wsTasks.interns; },
            get activeSprint() { return this.$store.wsTasks.activeSprint; },
            get isNew() { return !this.draft || this.draft.id === null; },
            get dirty() { return !!this.draft && JSON.stringify(this.draft) !== this.snapshot; },
            get summary() { return this.draft ? describe(this.draft) : ''; },
            get upcoming() { return this.draft ? nextRuns(this.draft, 4).map(whenText) : []; },

            describe,
            triggerText,
            actionText,
            ruleNext(rule) {
                if (!rule.enabled) return '';
                const d = rule.next_run_at ? new Date(rule.next_run_at) : nextRuns(rule, 1)[0];
                return d ? whenText(d) : '';
            },
            ago: (iso) => WS.time.relative(iso),
            err(path) {
                const e = this.errors[path];
                return Array.isArray(e) ? e[0] : (e || '');
            },
            hasError(prefix) {
                return Object.keys(this.errors).some((k) => k === prefix || k.startsWith(prefix + '.') || k.startsWith(prefix + '['));
            },

            // ---------- Daftar ----------

            startNew(preset) {
                this.open(preset ? PRESETS[preset].build() : blankRule());
            },

            edit(rule) {
                this.open(JSON.parse(JSON.stringify(rule)));
            },

            duplicate(rule) {
                const copy = JSON.parse(JSON.stringify(rule));
                copy.id = null;
                copy.name = (rule.name + ' (salinan)').slice(0, 60);
                this.open(copy);
            },

            open(draft) {
                this.draft = draft;
                this.snapshot = JSON.stringify(draft);
                this.errors = {};
                this.selected = 'trigger';
                this.addMenu = false;
                this.view = 'edit';
                this.$nextTick(() => window.scrollTo({ top: document.getElementById('ws-tabbar')?.offsetTop || 0, behavior: WS.scrollBehavior() }));
            },

            back() {
                if (this.dirty && !window.confirm('Buang perubahan yang belum disimpan?')) return;
                this.view = 'list';
                this.draft = null;
            },

            async toggle(rule) {
                const next = !rule.enabled;
                rule.enabled = next;
                try {
                    const res = await WS.api.automations.update(rule.id, { enabled: next });
                    Object.assign(rule, res.data);
                    WS.toast(next ? `"${rule.name}" diaktifkan.` : `"${rule.name}" dinonaktifkan.`, 'success');
                } catch (err) {
                    rule.enabled = !next;
                    WS.toast(err.message, 'error');
                }
            },

            async run(rule) {
                this.running[rule.id] = true;
                try {
                    const res = await WS.api.automations.run(rule.id);
                    (res.tasks || []).forEach((card) => this.$store.wsTasks.upsert(card));
                    Object.assign(rule, res.data);
                    WS.toast(res.run.summary, res.run.ok ? 'success' : 'info');
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    delete this.running[rule.id];
                }
            },

            // Hapus dua langkah: klik pertama meminta konfirmasi (hilang sendiri setelah 5 detik).
            askRemove(rule) {
                clearTimeout(this.confirmTimer);
                this.confirming = rule.id;
                this.confirmTimer = setTimeout(() => { this.confirming = null; }, 5000);
            },

            async remove(rule) {
                clearTimeout(this.confirmTimer);
                this.confirming = null;
                try {
                    await WS.api.automations.remove(rule.id);
                    this.rules = this.rules.filter((r) => r.id !== rule.id);
                    WS.toast(`"${rule.name}" dihapus.`, 'success');
                } catch (err) {
                    WS.toast(err.message, 'error');
                }
            },

            // ---------- Penyusun ----------

            select(key) {
                this.selected = this.selected === key ? '' : key;
                this.addMenu = false;
            },

            hasCondition(type) {
                return this.draft.conditions.some((c) => c.type === type);
            },

            addCondition(type) {
                if (!this.hasCondition(type)) this.draft.conditions.push({ type });
                this.selected = 'c:' + type;
                this.addMenu = false;
            },

            removeCondition(type) {
                this.draft.conditions = this.draft.conditions.filter((c) => c.type !== type);
                if (this.selected === 'c:' + type) this.selected = '';
            },

            addAction(type) {
                this.draft.actions.push(blankAction(type));
                this.selected = 'a:' + (this.draft.actions.length - 1);
                this.addMenu = false;
            },

            removeAction(i) {
                if (this.draft.actions.length <= 1) { WS.toast('Aturan butuh minimal satu aksi.', 'info'); return; }
                this.draft.actions.splice(i, 1);
                this.selected = '';
                this.errors = {};
            },

            moveAction(i, dir) {
                const j = i + dir;
                if (j < 0 || j >= this.draft.actions.length) return;
                const [a] = this.draft.actions.splice(i, 1);
                this.draft.actions.splice(j, 0, a);
                this.selected = 'a:' + j;
            },

            toggleDay(n) {
                const days = this.draft.trigger.days;
                const i = days.indexOf(n);
                if (i === -1) days.push(n);
                else days.splice(i, 1);
            },

            toggleAssignee(action, id) {
                const ids = action.assignee_ids;
                const i = ids.indexOf(id);
                if (i === -1) ids.push(id);
                else ids.splice(i, 1);
            },

            async save() {
                this.saving = true;
                this.errors = {};
                try {
                    const body = JSON.parse(JSON.stringify(this.draft));
                    const res = this.isNew
                        ? await WS.api.automations.create(config.workspaceId, body)
                        : await WS.api.automations.update(this.draft.id, body);
                    const i = this.rules.findIndex((r) => r.id === res.data.id);
                    if (i === -1) this.rules.push(res.data);
                    else this.rules.splice(i, 1, res.data);
                    WS.toast(`Aturan "${res.data.name}" disimpan.`, 'success');
                    this.snapshot = JSON.stringify(this.draft);
                    this.view = 'list';
                    this.draft = null;
                } catch (err) {
                    if (err.status === 422 && err.errors) {
                        this.errors = err.errors;
                        // Buka kartu pertama yang bermasalah.
                        const keys = Object.keys(err.errors);
                        const first = keys.find((k) => k.startsWith('actions.')) || keys.find((k) => k.startsWith('trigger')) || '';
                        if (first.startsWith('trigger')) this.selected = 'trigger';
                        else if (first.startsWith('actions.')) this.selected = 'a:' + first.split('.')[1];
                        WS.toast(err.message, 'error');
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
