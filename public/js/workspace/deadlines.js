/*
 * Workspace — tab Deadline (agenda + kalender deadline per tugas).
 * Sumber data: store `wsTasks` (tugas kelompok yang sedang dibuka), jadi hanya kelompok ini yang tampil
 * dan perubahan tugas (geser status, perpanjang deadline, dll.) langsung ikut berubah.
 * Markup: workspace/partials/deadlines.blade.php dan deadline-row.blade.php.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const DAY = 86400000;
    const VIEW_KEY = 'ws-deadline-view';

    const pad = (n) => String(n).padStart(2, '0');
    const dayKey = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    const startOfDay = (d) => { const x = new Date(d); x.setHours(0, 0, 0, 0); return x; };
    const startOfMonth = (d) => new Date(d.getFullYear(), d.getMonth(), 1);

    const monthFmt = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
    const longDayFmt = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const shortDayFmt = new Intl.DateTimeFormat('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
    const timeFmt = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' });
    const rtf = new Intl.RelativeTimeFormat('id', { numeric: 'auto' });

    const OPEN = ['todo', 'in_progress', 'revision'];

    // Urutan tampil di agenda.
    const BUCKETS = [
        { key: 'late', label: 'Lewat deadline', hint: 'Belum dikirim' },
        { key: 'today', label: 'Hari ini' },
        { key: 'tomorrow', label: 'Besok' },
        { key: 'week', label: 'Minggu ini', hint: '2–7 hari lagi' },
        { key: 'later', label: 'Lebih dari seminggu' },
        { key: 'sent', label: 'Sudah dikirim', hint: 'Menunggu review' },
        { key: 'done', label: 'Selesai' },
    ];

    const FILTERS = [
        { key: 'all', label: 'Semua' },
        { key: 'late', label: 'Lewat deadline' },
        { key: 'today', label: 'Hari ini' },
        { key: 'week', label: '7 hari ke depan' },
    ];
    const WEEK_BUCKETS = ['today', 'tomorrow', 'week'];

    document.addEventListener('alpine:init', () => {
        window.Alpine.data('wsDeadlines', () => ({
            view: 'agenda',
            filter: 'all',
            mine: false,
            showDone: false,
            month: startOfMonth(new Date()),
            picked: dayKey(new Date()),
            now: Date.now(),
            timer: null,
            filters: FILTERS,
            weekdays: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],

            init() {
                try {
                    const v = window.localStorage.getItem(VIEW_KEY);
                    if (v === 'agenda' || v === 'calendar') this.view = v;
                } catch (e) { /* penyimpanan browser tidak tersedia: pakai default */ }
                // Segarkan pengelompokan (Hari ini / Lewat deadline) tiap menit.
                this.timer = setInterval(() => { this.now = Date.now(); }, 60000);
            },

            destroy() {
                clearInterval(this.timer);
            },

            setView(v) {
                this.view = v;
                try { window.localStorage.setItem(VIEW_KEY, v); } catch (e) { /* abaikan */ }
            },

            get isIntern() {
                return !!(WS.config.user && WS.config.user.role === 'intern');
            },

            get todayKey() {
                this.now;
                return dayKey(new Date());
            },

            // ---------- Data ----------

            bucketOf(card) {
                this.now;
                if (card.status === 'done') return 'done';
                if (WS.tasks.unfinished(card)) return 'late';
                const diff = Math.round((startOfDay(card.due_at) - startOfDay(new Date())) / DAY);
                if (card.status === 'review' && diff < 0) return 'sent';
                if (diff <= 0) return 'today';
                if (diff === 1) return 'tomorrow';
                return diff <= 7 ? 'week' : 'later';
            },

            matches(card, key) {
                if (key === 'all') return true;
                const b = this.bucketOf(card);
                return key === 'week' ? WEEK_BUCKETS.includes(b) : b === key;
            },

            // Tugas kelompok ini setelah saklar "tugas saya" dan "tampilkan selesai".
            get base() {
                const uid = WS.config.user && WS.config.user.id;
                return this.$store.wsTasks.tasks.filter((t) => {
                    if (t.status === 'done' && !this.showDone) return false;
                    if (this.mine && !t.assignees.some((a) => a.user_id === uid)) return false;
                    return true;
                });
            },

            get visible() {
                return this.base.filter((t) => this.matches(t, this.filter));
            },

            count(key) {
                return this.base.filter((t) => this.matches(t, key)).length;
            },

            get groups() {
                const byDue = (a, b) => new Date(a.due_at) - new Date(b.due_at);
                return BUCKETS.map((b) => ({
                    ...b,
                    items: this.visible.filter((t) => this.bucketOf(t) === b.key).sort(b.key === 'done' ? (x, y) => byDue(y, x) : byDue),
                })).filter((g) => g.items.length);
            },

            // ---------- Tampilan baris ----------

            tone(card) {
                const b = this.bucketOf(card);
                return b === 'late' ? 'late' : b === 'done' ? 'done' : b === 'sent' ? 'sent' : b === 'today' ? 'soon' : 'normal';
            },

            dueDateLine(card) {
                const d = new Date(card.due_at);
                return shortDayFmt.format(d) + ' · ' + timeFmt.format(d);
            },

            relText(card) {
                if (card.status === 'done') return card.completed_at ? 'Selesai ' + WS.time.relative(card.completed_at) : 'Selesai';
                const days = Math.round((startOfDay(card.due_at) - startOfDay(new Date())) / DAY);
                return Math.abs(days) > 7 ? rtf.format(days, 'day') : WS.time.relative(card.due_at);
            },

            // Konteks sprint: tampil hanya bila tugas tidak di sprint yang sedang berjalan.
            sprintLabel(card) {
                const store = this.$store.wsTasks;
                if (!store.sprints.length) return '';
                const sp = card.sprint_id ? store.sprintById(card.sprint_id) : null;
                if (!sp) return 'Backlog';
                return sp.status === 'active' ? '' : sp.name;
            },

            open(card) {
                this.$store.wsTasks.openDetail(card);
            },

            // ---------- Kalender ----------

            get monthLabel() {
                return monthFmt.format(this.month);
            },

            // Baris minggu (Senin–Minggu) yang menutupi bulan ini.
            get weeks() {
                const byDay = {};
                this.visible.forEach((t) => { (byDay[dayKey(new Date(t.due_at))] ||= []).push(t); });
                Object.values(byDay).forEach((list) => list.sort((a, b) => new Date(a.due_at) - new Date(b.due_at)));

                const y = this.month.getFullYear();
                const m = this.month.getMonth();
                const lead = (new Date(y, m, 1).getDay() + 6) % 7;
                const rows = Math.ceil((lead + new Date(y, m + 1, 0).getDate()) / 7);
                const today = this.todayKey;
                const weeks = [];
                for (let w = 0; w < rows; w++) {
                    const week = [];
                    for (let i = 0; i < 7; i++) {
                        const date = new Date(y, m, 1 - lead + w * 7 + i);
                        const key = dayKey(date);
                        const items = byDay[key] || [];
                        week.push({
                            key,
                            date,
                            day: date.getDate(),
                            inMonth: date.getMonth() === m,
                            today: key === today,
                            items,
                            label: longDayFmt.format(date) + (items.length ? ', ' + items.length + ' tugas' : ', tidak ada tugas'),
                        });
                    }
                    weeks.push(week);
                }
                return weeks;
            },

            get pickedItems() {
                return this.visible
                    .filter((t) => dayKey(new Date(t.due_at)) === this.picked)
                    .sort((a, b) => new Date(a.due_at) - new Date(b.due_at));
            },

            get pickedLabel() {
                const [y, m, d] = this.picked.split('-').map(Number);
                return longDayFmt.format(new Date(y, m - 1, d));
            },

            shift(step) {
                this.month = new Date(this.month.getFullYear(), this.month.getMonth() + step, 1);
                const now = new Date();
                this.picked = this.month.getFullYear() === now.getFullYear() && this.month.getMonth() === now.getMonth() ? dayKey(now) : dayKey(this.month);
            },

            goToday() {
                this.month = startOfMonth(new Date());
                this.picked = dayKey(new Date());
            },

            pick(day) {
                this.picked = day.key;
                if (!day.inMonth) this.month = startOfMonth(day.date);
            },
        }));
    });
})();
