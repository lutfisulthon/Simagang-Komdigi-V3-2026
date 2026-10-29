/*
 * Workspace — tab Laporan: grafik gaya Jira (burndown, selesai per sprint/minggu, sebaran status, ketepatan waktu, beban anggota).
 * Sumber data: store `wsTasks` (hanya kelompok yang sedang dibuka). Mentor/admin melihat seluruh kelompok;
 * anak magang hanya melihat data tugasnya sendiri.
 * Tanpa pustaka: bar & donat memakai HTML/CSS, hanya burndown yang berupa SVG (teks di-escape).
 * Markup: workspace/partials/reports.blade.php, report-bars.blade.php, report-donut.blade.php.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const DAY = 86400000;
    const STATUS_ORDER = ['todo', 'in_progress', 'review', 'revision', 'done'];
    const COLORS = { todo: '#94a3b8', in_progress: '#3b82f6', review: '#8b5cf6', revision: '#f97316', done: '#10b981', ontime: '#10b981', late: '#ef4444' };

    const dayFmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short' });
    const startOfDay = (d) => { const x = new Date(d); x.setHours(0, 0, 0, 0); return x; };
    const addDays = (d, n) => new Date(d.getFullYear(), d.getMonth(), d.getDate() + n);
    const mondayOf = (d) => addDays(startOfDay(d), -((startOfDay(d).getDay() + 6) % 7));
    const parseDate = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const f1 = (n) => Number(n).toFixed(1);

    // Skala sumbu Y bilangan bulat: 0, step, 2·step, … ≥ max.
    function niceTicks(max) {
        const m = Math.max(1, max);
        const step = m <= 5 ? 1 : Math.ceil(m / 4);
        const top = Math.ceil(m / step) * step;
        const ticks = [];
        for (let v = 0; v <= top; v += step) ticks.push(v);
        return { top, ticks };
    }

    // Diagram batang HTML/CSS: items [{label, value}] → kolom dengan tinggi persen.
    function bars(items) {
        const { top, ticks } = niceTicks(Math.max(0, ...items.map((i) => i.value)));
        return {
            cols: items.map((i) => ({ ...i, pct: i.value / top * 100 })),
            ticks: ticks.map((v) => ({ v, pct: v / top * 100 })),
            total: items.reduce((n, i) => n + i.value, 0),
        };
    }

    // Donat dari conic-gradient: segments [{key, label, value, color}].
    function donut(segments) {
        const total = segments.reduce((n, s) => n + s.value, 0);
        let acc = 0;
        const parts = segments.filter((s) => s.value > 0).map((s) => {
            const a = acc / total * 100;
            acc += s.value;
            return s.color + ' ' + a.toFixed(2) + '% ' + (acc / total * 100).toFixed(2) + '%';
        });
        return {
            total,
            gradient: total ? 'conic-gradient(' + parts.join(', ') + ')' : 'conic-gradient(#e2e8f0 0 100%)',
            legend: segments.map((s) => ({ ...s, pct: total ? Math.round(s.value / total * 100) : 0 })),
        };
    }

    document.addEventListener('alpine:init', () => {
        window.Alpine.data('wsReports', () => ({
            sprintId: null,
            w: 560,
            observer: null,

            init() {
                this.sprintId = this.sprint ? this.sprint.id : null;
                // Lebar area burndown mengikuti kartu, supaya teks sumbu tidak mengecil di HP.
                this.$nextTick(() => {
                    const el = this.$refs.burn;
                    if (!el) return;
                    const measure = () => { const nw = Math.floor(el.clientWidth); if (nw >= 200 && Math.abs(nw - this.w) > 1) this.w = nw; };
                    measure();
                    if ('ResizeObserver' in window) {
                        this.observer = new ResizeObserver(measure);
                        this.observer.observe(el);
                    }
                });
            },

            destroy() {
                if (this.observer) this.observer.disconnect();
            },

            get isIntern() {
                return !!(WS.config.user && WS.config.user.role === 'intern');
            },

            // Mentor/admin: semua tugas kelompok. Anak magang: hanya tugas yang di-assign kepadanya.
            get scope() {
                const uid = WS.config.user && WS.config.user.id;
                const tasks = this.$store.wsTasks.tasks;
                return this.isIntern ? tasks.filter((t) => t.assignees.some((a) => a.user_id === uid)) : tasks;
            },

            get sprintOptions() {
                return this.$store.wsTasks.sprints.filter((sp) => sp.start_date && sp.end_date && sp.status !== 'planned');
            },

            get sprint() {
                const list = this.sprintOptions;
                return list.find((s) => s.id === this.sprintId) || list.find((s) => s.status === 'active') || list[list.length - 1] || null;
            },

            // ---------- Burndown (SVG) ----------

            get burndown() {
                const sp = this.sprint;
                if (!sp) return { empty: true, reason: 'Belum ada sprint yang berjalan atau selesai.' };
                const tasks = this.scope.filter((t) => t.sprint_id === sp.id);
                if (!tasks.length) return { empty: true, reason: this.isIntern ? 'Anda belum punya tugas di ' + sp.name + '.' : 'Belum ada tugas di ' + sp.name + '.' };

                const total = tasks.length;
                const start = parseDate(sp.start_date);
                const end = parseDate(sp.end_date);
                const n = Math.max(1, Math.round((end - start) / DAY));
                const todayIdx = Math.round((startOfDay(new Date()) - start) / DAY);
                const upto = sp.status === 'completed' ? n : Math.min(n, Math.max(0, todayIdx));
                const doneBy = (d) => tasks.filter((t) => t.status === 'done' && t.completed_at && startOfDay(t.completed_at) <= addDays(start, d)).length;
                const pts = [];
                for (let d = 0; d <= upto; d++) pts.push({ d, v: total - doneBy(d) });
                const remaining = pts[pts.length - 1].v;
                const ideal = total * (1 - upto / n);
                const diff = remaining - ideal;
                const state = sp.status === 'completed' ? (remaining === 0 ? 'ahead' : 'behind') : (Math.abs(diff) < 0.5 ? 'on' : diff < 0 ? 'ahead' : 'behind');

                const W = this.w;
                const H = 250;
                const m = { l: 34, r: 16, t: 14, b: 34 };
                const iw = W - m.l - m.r;
                const ih = H - m.t - m.b;
                const { top, ticks } = niceTicks(total);
                const x = (d) => m.l + d / n * iw;
                const y = (v) => m.t + ih - v / top * ih;

                let svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + W + ' ' + H + '" width="' + W + '" height="' + H + '" role="img" aria-label="'
                    + esc('Burndown ' + sp.name + ': sisa ' + remaining + ' dari ' + total + ' tugas') + '" font-family="inherit">';
                ticks.forEach((v) => {
                    svg += '<line x1="' + m.l + '" x2="' + (W - m.r) + '" y1="' + f1(y(v)) + '" y2="' + f1(y(v)) + '" stroke="#e2e8f0"/>'
                        + '<text x="' + (m.l - 8) + '" y="' + f1(y(v) + 4) + '" text-anchor="end" font-size="11" fill="#5f6f86">' + v + '</text>';
                });
                const step = Math.max(1, Math.ceil(n / 6));
                const xs = [];
                for (let d = 0; d <= n; d += step) xs.push(d);
                if (n - xs[xs.length - 1] >= step / 2) xs.push(n);
                xs.forEach((d) => {
                    svg += '<text x="' + f1(x(d)) + '" y="' + (H - 10) + '" text-anchor="middle" font-size="11" fill="#5f6f86">' + esc(dayFmt.format(addDays(start, d))) + '</text>';
                });
                svg += '<line x1="' + f1(x(0)) + '" y1="' + f1(y(total)) + '" x2="' + f1(x(n)) + '" y2="' + f1(y(0)) + '" stroke="#94a3b8" stroke-width="2" stroke-dasharray="6 5"/>';
                if (sp.status === 'active' && todayIdx >= 0 && todayIdx <= n) {
                    svg += '<line x1="' + f1(x(todayIdx)) + '" x2="' + f1(x(todayIdx)) + '" y1="' + m.t + '" y2="' + (m.t + ih) + '" stroke="#f97316" stroke-width="1.5" stroke-dasharray="3 3"/>'
                        + '<text x="' + f1(x(todayIdx)) + '" y="' + (m.t - 3) + '" text-anchor="middle" font-size="10" font-weight="700" fill="#c2410c">Hari ini</text>';
                }
                svg += '<polyline fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" points="'
                    + pts.map((p) => f1(x(p.d)) + ',' + f1(y(p.v))).join(' ') + '"/>';
                if (pts.length <= 40) {
                    pts.forEach((p) => {
                        svg += '<circle cx="' + f1(x(p.d)) + '" cy="' + f1(y(p.v)) + '" r="3.5" fill="#fff" stroke="#4f46e5" stroke-width="2"><title>'
                            + esc(dayFmt.format(addDays(start, p.d)) + ': sisa ' + p.v + ' tugas') + '</title></circle>';
                    });
                }
                svg += '</svg>';

                const summary = sp.status === 'completed'
                    ? (remaining === 0 ? 'Semua tugas selesai' : remaining + ' tugas belum selesai saat sprint berakhir')
                    : { ahead: 'Lebih cepat dari rencana', on: 'Sesuai rencana', behind: 'Tertinggal dari rencana' }[state];

                return {
                    empty: false, svg, total, remaining, done: total - remaining, state, summary,
                    rows: pts.map((p) => ({ label: dayFmt.format(addDays(start, p.d)), value: p.v })),
                };
            },

            // ---------- Diagram batang ----------

            // Tugas selesai per sprint (sprint berjalan & selesai, urut kronologis).
            get velocity() {
                return bars(this.$store.wsTasks.sprints.filter((sp) => sp.status !== 'planned').map((sp) => ({
                    label: sp.name,
                    value: this.scope.filter((t) => t.sprint_id === sp.id && t.status === 'done').length,
                })));
            },

            // Tugas selesai per minggu, 8 minggu terakhir.
            get weekly() {
                const thisWeek = mondayOf(new Date());
                const items = [];
                for (let i = 7; i >= 0; i--) {
                    const from = addDays(thisWeek, -7 * i);
                    const to = addDays(from, 7);
                    items.push({
                        label: dayFmt.format(from),
                        value: this.scope.filter((t) => t.status === 'done' && t.completed_at && new Date(t.completed_at) >= from && new Date(t.completed_at) < to).length,
                    });
                }
                return bars(items);
            },

            // ---------- Donat ----------

            get statusDonut() {
                return donut(STATUS_ORDER.map((k) => ({ key: k, label: WS.STATUS[k], color: COLORS[k], value: this.scope.filter((t) => t.status === k).length })));
            },

            // Ketepatan waktu: tugas selesai yang rampung sebelum / sesudah deadline.
            get ontime() {
                const done = this.scope.filter((t) => t.status === 'done' && t.completed_at && t.due_at);
                const onTime = done.filter((t) => new Date(t.completed_at) <= new Date(t.due_at)).length;
                const d = donut([
                    { key: 'ontime', label: 'Tepat waktu', color: COLORS.ontime, value: onTime },
                    { key: 'late', label: 'Terlambat', color: COLORS.late, value: done.length - onTime },
                ]);
                return { ...d, pct: done.length ? Math.round(onTime / done.length * 100) : null };
            },

            // ---------- Beban anggota (mentor/admin) ----------

            get workload() {
                const store = this.$store.wsTasks;
                const make = (label, intern, tasks) => ({
                    label, intern, total: tasks.length,
                    segs: STATUS_ORDER.map((k) => ({ key: k, label: WS.STATUS[k], color: COLORS[k], count: tasks.filter((t) => t.status === k).length })),
                });
                const rows = store.interns.map((i) => make(i.name, i, store.tasks.filter((t) => t.assignees.some((a) => a.id === i.id))));
                const unassigned = store.tasks.filter((t) => !t.assignees.length);
                if (unassigned.length) rows.push(make('Belum dibagi', null, unassigned));
                const max = Math.max(1, ...rows.map((r) => r.total));
                rows.forEach((r) => r.segs.forEach((s) => { s.pct = s.count / max * 100; }));
                return { rows, legend: STATUS_ORDER.map((k) => ({ key: k, label: WS.STATUS[k], color: COLORS[k] })) };
            },
        }));
    });
})();
