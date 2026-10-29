/*
 * Workspace — tab Timeline (roadmap tugas gaya Jira): jalur sprint, tugas per sprint sebagai batang tanggal.
 * Sumber data: store `wsTasks` (hanya kelompok yang sedang dibuka). Hanya baca; perpanjang deadline lewat dialog yang ada.
 * Markup: workspace/partials/timeline.blade.php.
 *
 * Tanggal mulai batang: `start_at`; bila kosong → awal sprint tugas (perkiraan); bila tidak ada → 3 hari sebelum deadline.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const DAY = 86400000;
    const ZOOM_KEY = 'ws-timeline-zoom';
    const ZOOMS = { weeks: 34, months: 12, quarters: 4 }; // piksel per hari
    const MIN_DAYS = { weeks: 42, months: 126, quarters: 270 };
    const WEEKDAY = ['S', 'S', 'R', 'K', 'J', 'S', 'M']; // Senin … Minggu

    const monthFmt = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
    const monthShort = new Intl.DateTimeFormat('id-ID', { month: 'short' });
    const dayFmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short' });

    const startOfDay = (d) => { const x = new Date(d); x.setHours(0, 0, 0, 0); return x; };
    const addDays = (d, n) => new Date(d.getFullYear(), d.getMonth(), d.getDate() + n);
    const mondayOf = (d) => addDays(startOfDay(d), -((startOfDay(d).getDay() + 6) % 7));
    const parseDate = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };

    document.addEventListener('alpine:init', () => {
        window.Alpine.data('wsTimeline', () => ({
            zoom: 'weeks',
            q: '',
            who: [],
            status: '',
            mine: false,
            showDone: true,
            open: {},
            now: Date.now(),
            timer: null,
            zooms: [{ key: 'weeks', label: 'Minggu' }, { key: 'months', label: 'Bulan' }, { key: 'quarters', label: 'Kuartal' }],
            statuses: [['todo', 'To Do'], ['in_progress', 'In Progress'], ['review', 'Review'], ['revision', 'Revisi'], ['done', 'Selesai']],

            init() {
                try {
                    const z = window.localStorage.getItem(ZOOM_KEY);
                    if (z && ZOOMS[z]) this.zoom = z;
                } catch (e) { /* penyimpanan browser tidak tersedia */ }
                this.timer = setInterval(() => { this.now = Date.now(); }, 60000);
                // Panel tersembunyi saat halaman dibuka: geser ke "hari ini" ketika tab ditampilkan.
                window.addEventListener('ws-tab-changed', (e) => {
                    if (e.detail === 'timeline') this.$nextTick(() => this.scrollToToday());
                });
                this.$nextTick(() => setTimeout(() => this.scrollToToday(), 60));
            },

            destroy() {
                clearInterval(this.timer);
            },

            get isIntern() {
                return !!(WS.config.user && WS.config.user.role === 'intern');
            },

            get interns() {
                return this.$store.wsTasks.interns;
            },

            get ppd() {
                return ZOOMS[this.zoom];
            },

            setZoom(z) {
                this.zoom = z;
                try { window.localStorage.setItem(ZOOM_KEY, z); } catch (e) { /* abaikan */ }
                this.$nextTick(() => this.scrollToToday());
            },

            toggleWho(id) {
                const i = this.who.indexOf(id);
                if (i === -1) this.who.push(id);
                else this.who.splice(i, 1);
            },

            // ---------- Data ----------

            tone(card) {
                if (card.status === 'done') return 'done';
                return WS.tasks.unfinished(card) ? 'late' : card.status;
            },

            isMine(card) {
                const uid = WS.config.user && WS.config.user.id;
                return card.assignees.some((a) => a.user_id === uid);
            },

            // Rentang batang satu tugas (ms). est = tanggal mulai diperkirakan.
            span(card) {
                const store = this.$store.wsTasks;
                const end = new Date(card.due_at).getTime();
                let start;
                let est = false;
                if (card.start_at) {
                    start = new Date(card.start_at).getTime();
                } else {
                    est = true;
                    const sp = card.sprint_id ? store.sprintById(card.sprint_id) : null;
                    start = sp && sp.start_date ? parseDate(sp.start_date).getTime() : end - 3 * DAY;
                }
                if (start >= end) start = end - DAY;
                return { start, end, est };
            },

            get filtered() {
                const q = this.q.trim().toLowerCase();
                return this.$store.wsTasks.tasks.filter((t) => {
                    if (!this.showDone && t.status === 'done') return false;
                    if (this.status && t.status !== this.status) return false;
                    if (this.mine && !this.isMine(t)) return false;
                    if (this.who.length && !t.assignees.some((a) => this.who.includes(a.id))) return false;
                    if (q && !(t.title.toLowerCase().includes(q) || t.code.toLowerCase().includes(q))) return false;
                    return true;
                });
            },

            get filtering() {
                return !!(this.q.trim() || this.status || this.mine || this.who.length);
            },

            // ---------- Skala waktu ----------

            // Mulai hari Senin sebelum tanggal paling awal; panjang dibulatkan ke minggu penuh.
            get range() {
                const store = this.$store.wsTasks;
                const stamps = [startOfDay(new Date()).getTime()];
                store.tasks.forEach((t) => { const s = this.span(t); stamps.push(s.start, s.end); });
                store.sprints.forEach((sp) => {
                    if (sp.start_date) stamps.push(parseDate(sp.start_date).getTime());
                    if (sp.end_date) stamps.push(parseDate(sp.end_date).getTime());
                });
                const start = mondayOf(addDays(new Date(Math.min(...stamps)), -3));
                const last = addDays(new Date(Math.max(...stamps)), 10);
                const days = Math.ceil(Math.max(Math.ceil((last - start) / DAY), MIN_DAYS[this.zoom]) / 7) * 7;
                return { start, days };
            },

            get totalW() {
                return this.range.days * this.ppd;
            },

            get todayX() {
                return Math.round((this.now - this.range.start.getTime()) / DAY * this.ppd);
            },

            // Pita header dua tingkat: atas (bulan / kuartal), bawah (hari / minggu / bulan).
            get bands() {
                const { start, days } = this.range;
                const ppd = this.ppd;
                const end = addDays(start, days);
                const x = (d) => Math.round((d - start) / DAY * ppd);
                const today = startOfDay(new Date()).getTime();
                const segments = (next, label) => {
                    const out = [];
                    let c = new Date(start);
                    while (c < end) {
                        const n = next(c);
                        const e = n < end ? n : end;
                        out.push({ key: c.getTime(), label: label(c), left: x(c), width: x(e) - x(c), strong: true });
                        c = n;
                    }
                    return out;
                };
                const nextMonth = (c) => new Date(c.getFullYear(), c.getMonth() + 1, 1);
                const nextQuarter = (c) => new Date(c.getFullYear(), Math.floor(c.getMonth() / 3) * 3 + 3, 1);

                if (this.zoom === 'weeks') {
                    const bottom = [];
                    for (let i = 0; i < days; i++) {
                        const d = addDays(start, i);
                        bottom.push({
                            key: d.getTime(), label: d.getDate(), sub: WEEKDAY[(d.getDay() + 6) % 7], left: i * ppd, width: ppd,
                            weekend: d.getDay() === 0 || d.getDay() === 6, today: d.getTime() === today, strong: d.getDay() === 1,
                        });
                    }
                    return { top: segments(nextMonth, (c) => monthFmt.format(c)), bottom };
                }
                if (this.zoom === 'months') {
                    const bottom = [];
                    for (let i = 0; i < days / 7; i++) {
                        const d = addDays(start, i * 7);
                        bottom.push({ key: d.getTime(), label: d.getDate(), left: i * 7 * ppd, width: 7 * ppd, today: today >= d.getTime() && today < addDays(d, 7).getTime(), strong: true });
                    }
                    return { top: segments(nextMonth, (c) => monthFmt.format(c)), bottom };
                }
                const bottom = segments(nextMonth, (c) => monthShort.format(c)).map((b) => ({ ...b, today: today >= b.key && today < nextMonth(new Date(b.key)).getTime() }));
                return { top: segments(nextQuarter, (c) => 'K' + (Math.floor(c.getMonth() / 3) + 1) + ' ' + c.getFullYear()), bottom };
            },

            // Jalur sprint: batang tiap sprint. Sprint terencana tanpa tanggal mulai dimulai saat sprint sebelumnya berakhir.
            get lane() {
                const start0 = this.range.start.getTime();
                const ppd = this.ppd;
                let cursor = startOfDay(new Date());
                return this.$store.wsTasks.sprints.filter((sp) => sp.end_date).map((sp) => {
                    const end = addDays(parseDate(sp.end_date), 1);
                    let st = sp.start_date ? parseDate(sp.start_date) : cursor;
                    if (st >= end) st = addDays(end, -7);
                    cursor = end;
                    return {
                        id: sp.id, name: sp.name, status: sp.status, planned: !sp.start_date,
                        left: Math.round((st - start0) / DAY * ppd), width: Math.max(28, Math.round((end - st) / DAY * ppd)),
                        title: sp.name + ' · ' + dayFmt.format(st) + ' – ' + dayFmt.format(addDays(end, -1)),
                    };
                });
            },

            // ---------- Bagian & baris ----------

            row(card, start0) {
                const sp = this.span(card);
                const left = Math.round((sp.start - start0) / DAY * this.ppd);
                const width = Math.max(10, Math.round((sp.end - sp.start) / DAY * this.ppd));
                const dates = dayFmt.format(new Date(sp.start)) + ' – ' + dayFmt.format(new Date(sp.end));
                return {
                    card, left, width, tone: this.tone(card), est: sp.est, mine: this.isMine(card),
                    progress: card.status === 'done' ? 100 : (card.checklist.total ? Math.round(card.checklist.done / card.checklist.total * 100) : 0),
                    title: card.code + ' ' + card.title + ' · ' + WS.STATUS[card.status] + ' · ' + dates + (sp.est ? ' (awal perkiraan)' : ''),
                    aria: card.code + ', ' + card.title + ', ' + WS.STATUS[card.status] + ', ' + dates,
                };
            },

            // Urutan seperti tab Backlog: sprint berjalan, terencana, Backlog, lalu sprint selesai.
            get sections() {
                const store = this.$store.wsTasks;
                const start0 = this.range.start.getTime();
                const visible = this.filtered;
                const sprints = store.sprints;
                const defs = [
                    ...sprints.filter((s) => s.status === 'active'),
                    ...sprints.filter((s) => s.status === 'planned'),
                    null,
                    ...sprints.filter((s) => s.status === 'completed').reverse(),
                ];
                return defs.map((sp) => {
                    const id = sp ? sp.id : null;
                    const inSection = (t) => (t.sprint_id || null) === id;
                    const all = store.tasks.filter(inSection);
                    const rows = visible.filter(inSection).sort((a, b) => (a.rank || 0) - (b.rank || 0)).map((t) => this.row(t, start0));
                    return {
                        key: sp ? 's' + sp.id : 'backlog', sprint: sp, status: sp ? sp.status : 'backlog',
                        name: sp ? sp.name : (sprints.length ? 'Backlog' : 'Semua tugas'),
                        range: sp ? WS.sprints.dateRange(sp) : '', leader: sp && sp.leader ? sp.leader.name.split(' ')[0] : '',
                        done: all.filter((t) => t.status === 'done').length, total: all.length, rows,
                    };
                }).filter((s) => s.rows.length || (!this.filtering && s.total) || (!this.filtering && s.sprint && s.status !== 'completed'));
            },

            isOpen(sec) {
                return this.open[sec.key] === undefined ? sec.status !== 'completed' : this.open[sec.key];
            },

            toggle(sec) {
                this.open[sec.key] = !this.isOpen(sec);
            },

            get empty() {
                return this.sections.every((s) => !s.rows.length);
            },

            // ---------- Aksi ----------

            openTask(card) {
                this.$store.wsTasks.openDetail(card);
            },

            scrollToToday() {
                const el = this.$refs.scroll;
                if (!el || !el.clientWidth) return;
                const left = parseFloat(getComputedStyle(this.$root).getPropertyValue('--tl-left')) || 0;
                el.scrollTo({ left: Math.max(0, this.todayX - (el.clientWidth - left) * 0.3), behavior: 'auto' });
            },

            reset() {
                Object.assign(this, { q: '', who: [], status: '', mine: false });
            },

            statusLabel: (s) => WS.STATUS[s] || s,
            dateRange: WS.sprints.dateRange,
        }));
    });
})();
