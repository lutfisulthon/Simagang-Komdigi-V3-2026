/*
 * Workspace — widget di dashboard mentor & intern (Fase 2).
 * Dashboard dirender controller yang sudah ada, jadi widget mengambil datanya sendiri lewat
 * WS.api.dashboard.summary() (GET /workspace/summary, lihat kontrak "Celah PRD" #8).
 * Dimuat oleh components/workspace/dashboard-widgets.blade.php.
 * Juga: store wsProgress untuk Admin → Monitoring (components/workspace/intern-progress & team-recap),
 * wsLogbookTask untuk dropdown "Tugas terkait" di form logbook (components/workspace/logbook-task-select),
 * wsInternTasks untuk panel tugas di detail intern (components/workspace/intern-tasks),
 * dan wsTaskRecap untuk rekap tugas di laporan akhir & form sertifikat (Fase 3).
 */
(function () {
    'use strict';

    const WS = window.WS;
    const me = WS.config.user || { id: 0 };

    document.addEventListener('alpine:init', () => {
        // Progres tugas per intern + rekap per tim. Satu request untuk semua sel [data-ws-progress] di halaman.
        window.Alpine.store('wsProgress', {
            loaded: false,
            error: null,
            interns: {},
            teams: [],
            ids: [],

            init() {
                this.ids = [...document.querySelectorAll('[data-ws-progress]')].map((el) => Number(el.dataset.wsProgress));
                if (this.ids.length || document.querySelector('[data-ws-team-recap]')) this.load();
            },

            async load() {
                this.error = null;
                try {
                    const res = await WS.api.monitoring.progress({ intern_ids: this.ids });
                    this.interns = res.interns || {};
                    this.teams = res.teams || [];
                    this.loaded = true;
                } catch (err) {
                    this.error = err.message;
                }
            },

            retry() {
                this.loaded = false;
                this.load();
            },
        });

        // Rekap tugas selesai. config: { internId?: number } (kosong = intern yang login)
        window.Alpine.data('wsTaskRecap', (config) => ({
            loading: true,
            error: null,
            recap: null,
            showAll: false,

            init() {
                this.load();
            },

            async load() {
                this.loading = true;
                this.error = null;
                try {
                    this.recap = (await WS.api.recap.get(config.internId ? { intern_id: config.internId } : {})).data;
                } catch (err) {
                    this.error = err.message;
                } finally {
                    this.loading = false;
                }
            },

            get onTimePct() {
                const s = this.recap && this.recap.summary;
                return s && s.done ? Math.round(s.on_time / s.done * 100) : null;
            },

            get shown() {
                if (!this.recap) return [];
                return this.showAll ? this.recap.tasks : this.recap.tasks.slice(-5).reverse();
            },

            // Teks ringkas untuk kolom "Kegiatan Selama Magang" di form laporan akhir.
            text() {
                const s = this.recap.summary;
                const head = `Rekap tugas Workspace: ${s.done} tugas selesai` + (s.avg_score !== null ? `, rata-rata nilai ${s.avg_score}` : '')
                    + (this.onTimePct !== null ? `, ${this.onTimePct}% tepat waktu` : '') + '.';
                const lines = this.recap.tasks.map((t) => `- ${t.code} ${t.title} (${t.workspace.name})` + (t.score !== null ? ` — nilai ${t.score}` : ''));
                return [head, ...lines].join('\n');
            },

            fillActivities() {
                const area = this.$root.closest('form')?.querySelector('textarea[name="activities[0][description]"]');
                if (!area) return;
                if (area.value.includes('Rekap tugas Workspace')) {
                    WS.toast('Rekap tugas sudah ada di kolom Kegiatan.', 'info');
                } else {
                    area.value = area.value.trim() ? area.value.trimEnd() + '\n\n' + this.text() : this.text();
                    area.dispatchEvent(new Event('input', { bubbles: true }));
                    WS.toast('Rekap tugas ditambahkan ke kolom Kegiatan. Silakan sesuaikan bila perlu.', 'success');
                }
                area.focus();
                area.scrollIntoView({ block: 'center', behavior: WS.scrollBehavior() });
            },

            date: (iso) => WS.time.date(iso),
        }));

        // Panel tugas di detail intern (admin/mentor).
        window.Alpine.data('wsInternTasks', (internId) => ({
            loading: true,
            error: null,
            data: null,
            tab: 'active',
            tabs: [{ key: 'active', label: 'Aktif' }, { key: 'done', label: 'Selesai' }, { key: 'all', label: 'Semua' }],

            init() {
                this.load();
            },

            async load() {
                this.loading = true;
                this.error = null;
                try {
                    this.data = await WS.api.interns.tasks(internId);
                } catch (err) {
                    this.error = err.message;
                } finally {
                    this.loading = false;
                }
            },

            match(t, tab) {
                if (tab === 'active') return t.status !== 'done';
                if (tab === 'done') return t.status === 'done';
                return true;
            },

            count(tab) {
                return this.data ? this.data.data.filter((t) => this.match(t, tab)).length : 0;
            },

            get visible() {
                return this.data ? this.data.data.filter((t) => this.match(t, this.tab)) : [];
            },

            due(card) {
                const state = card.is_late ? 'overdue' : WS.time.dueState(card.due_at, card.status);
                if (state === 'overdue') return { state, text: 'Terlambat · ' + WS.time.relative(card.due_at) };
                if (state === 'soon') return { state, text: 'Deadline ' + WS.time.relative(card.due_at) };
                return { state, text: 'Deadline ' + WS.time.date(card.due_at) };
            },

            boardUrl: (t) => t.workspace.url + '?task=' + encodeURIComponent(t.code),
            statusLabel: (s) => WS.STATUS[s] || s,
            dateTime: (iso) => WS.time.dateTime(iso),
        }));

        // config: { selected: taskId | null }
        window.Alpine.data('wsLogbookTask', (config) => ({
            value: config.selected ? String(config.selected) : '',
            tasks: [],
            loading: true,
            loaded: false,
            error: null,

            init() {
                this.load();
            },

            async load() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await WS.api.tasks.mine();
                    this.tasks = res.data;
                    this.loaded = true;
                    // Pilih ulang nilai lama setelah <option> dirender.
                    const keep = this.value;
                    this.$nextTick(() => { this.value = keep; });
                } catch (err) {
                    this.error = err.message;
                } finally {
                    this.loading = false;
                }
            },

            // Dikelompokkan per workspace: tugas aktif dulu, lalu yang sudah selesai.
            get groups() {
                const groups = [];
                const order = (t) => (t.status === 'done' ? 1 : 0);
                this.tasks.slice().sort((a, b) => order(a) - order(b)).forEach((t) => {
                    let g = groups.find((x) => x.name === t.workspace.name);
                    if (!g) groups.push(g = { name: t.workspace.name, tasks: [] });
                    g.tasks.push(t);
                });
                return groups;
            },

            get hint() {
                if (this.loading) return 'Memuat daftar tugas…';
                if (this.error) return 'Daftar tugas gagal dimuat. Logbook tetap bisa disimpan tanpa tugas terkait.';
                if (!this.tasks.length) return 'Belum ada tugas yang di-assign ke Anda.';
                return 'Hubungkan kegiatan hari ini dengan tugas di Workspace. Mentor melihatnya saat mereview logbook.';
            },
        }));

        window.Alpine.data('wsDashboard', () => ({
            loading: true,
            error: null,
            data: null,

            init() {
                this.load();
                // Badge pesan belum dibaca ikut polling lonceng (usulan kontrak #3).
                window.addEventListener('ws-unread-messages', (e) => this.applyUnread((e.detail && e.detail.by_channel) || {}));
            },

            async load() {
                this.loading = true;
                this.error = null;
                try {
                    this.data = await WS.api.dashboard.summary();
                } catch (err) {
                    this.error = err.message;
                } finally {
                    this.loading = false;
                }
            },

            applyUnread(byChannel) {
                if (!this.data || !this.data.unread_channels) return;
                this.data.unread_channels.forEach((u) => { u.unread_count = Number(byChannel[u.channel.id] || 0); });
                this.data.unread_channels = this.data.unread_channels.filter((u) => u.unread_count > 0);
            },

            get unreadTotal() {
                return this.data && this.data.unread_channels
                    ? this.data.unread_channels.reduce((n, u) => n + u.unread_count, 0)
                    : 0;
            },

            // { state: 'overdue'|'soon'|'normal', text }
            due(card) {
                const state = card.is_late ? 'overdue' : WS.time.dueState(card.due_at, card.status);
                if (state === 'overdue' && WS.tasks.unfinished(card)) return { state, text: 'Deadline ' + WS.time.relative(card.due_at) };
                if (state === 'overdue') return { state, text: 'Terlambat · ' + WS.time.relative(card.due_at) };
                if (state === 'soon') return { state, text: 'Deadline ' + WS.time.relative(card.due_at) };
                return { state, text: 'Deadline ' + WS.time.date(card.due_at) };
            },

            // Grafik mini mentor: progres kelompok (persen selesai) & beban tugas aktif per anak bimbingan.
            pct: (g) => (g.total ? Math.round(g.done / g.total * 100) : 0),
            get workloadRows() {
                const rows = (this.data && this.data.workload) || [];
                const sum = (r) => Object.values(r.counts).reduce((n, v) => n + v, 0);
                const max = Math.max(1, ...rows.map(sum));
                const colors = { todo: '#94a3b8', in_progress: '#3b82f6', review: '#8b5cf6', revision: '#f97316' };
                return rows.map((r) => ({
                    ...r, total: sum(r),
                    segs: Object.keys(colors).map((k) => ({ key: k, label: WS.STATUS[k], count: r.counts[k] || 0, pct: (r.counts[k] || 0) / max * 100, color: colors[k] })),
                }));
            },

            channelTitle(c) {
                if (c.type !== 'direct') return '#' + c.name;
                const members = c.members || [];
                const others = members.filter((m) => m.id !== me.id);
                return (others.length ? others : members).map((m) => m.name).join(' & ') || c.name;
            },

            // Membuka slide-over detail di board (tombol Mulai / Kirim / Setujui / Minta revisi ada di sana).
            boardUrl: (t) => t.workspace.url + '?task=' + encodeURIComponent(t.code),
            statusLabel: (s) => WS.STATUS[s] || s,
            relative: (iso) => WS.time.relative(iso),
            dateTime: (iso) => WS.time.dateTime(iso),
        }));
    });
})();
