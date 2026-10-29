/*
 * Workspace — Backlog & sprint (tab Backlog di halaman kelompok).
 * Data: store wsTasks (tasks + sprints). Markup: workspace/partials/backlog.blade.php.
 *   - Backlog = tempat merencanakan: mentor menyusun tugas ke sprint (seret / menu "Pindah ke").
 *   - Board   = sprint yang sedang berjalan; assign & status diatur di sana.
 * Intern: hanya membaca (tanpa seret, tanpa tombol).
 * Dialog sprint dibuka lewat event window:
 *   ws-sprint-form { sprint? }  buat / ubah sprint
 *   ws-sprint-start { sprint }  mulai sprint (nama, durasi, tanggal, tujuan)
 *   ws-sprint-complete { sprint } selesaikan; tugas belum selesai dipindah ke backlog / sprint lain / sprint baru
 *   ws-sprint-delete { sprint } hapus sprint yang belum dimulai; tugasnya kembali ke backlog
 */
(function () {
    'use strict';

    const WS = window.WS;
    const dayFmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short' });
    const DAY = 86400000;

    const parseDate = (d) => (d ? new Date(d + 'T00:00:00') : null);
    const toDateInput = (date) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    };

    function dateRange(sp) {
        if (!sp.start_date || !sp.end_date) return '';
        return dayFmt.format(parseDate(sp.start_date)) + ' – ' + dayFmt.format(parseDate(sp.end_date));
    }

    // "sisa 3 hari" / "berakhir hari ini" / "lewat 2 hari"
    function remaining(sp) {
        if (!sp.end_date) return '';
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const days = Math.round((parseDate(sp.end_date) - today) / DAY);
        if (days > 0) return 'sisa ' + days + ' hari';
        if (days === 0) return 'berakhir hari ini';
        return 'lewat ' + Math.abs(days) + ' hari';
    }

    function counts(tasks) {
        return {
            todo: tasks.filter((t) => t.status === 'todo').length,
            progress: tasks.filter((t) => ['in_progress', 'review', 'revision'].includes(t.status)).length,
            done: tasks.filter((t) => t.status === 'done').length,
            total: tasks.length,
        };
    }

    WS.sprints = { dateRange, remaining, counts };

    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        // ---------- Backlog ----------
        // config: { canManage: bool }
        Alpine.data('wsBacklog', (config) => ({
            canManage: !!config.canManage,
            collapsed: {},          // { [key]: true }
            showCompleted: false,
            sortables: [],
            draggingId: null,
            justDraggedAt: 0,

            init() {
                this.$nextTick(() => this.bind());
                // Sprint dibuat / dihapus / diselesaikan → daftar bagian berubah; pasang ulang Sortable.
                this.$watch(() => this.$store.wsTasks.sprints.map((s) => s.id + s.status).join(), () => this.$nextTick(() => this.bind()));
            },

            destroy() {
                this.sortables.forEach((s) => s.destroy());
            },

            bind() {
                this.sortables.forEach((s) => s.destroy());
                this.sortables = [];
                if (!this.canManage || !window.Sortable || this.$store.wsTasks.readOnly) return;
                const store = this.$store.wsTasks;
                const self = this;
                this.$root.querySelectorAll('[data-sprint-list]').forEach((el) => {
                    this.sortables.push(window.Sortable.create(el, {
                        group: 'ws-backlog',
                        draggable: '.ws-bl-row',
                        handle: '.ws-bl-row__handle',
                        filter: '[data-no-drag]',
                        preventOnFilter: false,
                        animation: 150,
                        delay: 150,
                        delayOnTouchOnly: true,
                        ghostClass: 'ws-bl-row--ghost',
                        onStart(evt) { self.draggingId = Number(evt.item.dataset.id); },
                        onEnd(evt) {
                            self.draggingId = null;
                            self.justDraggedAt = Date.now();
                            const card = store.byId(Number(evt.item.dataset.id));
                            const to = evt.to.dataset.sprintList === 'backlog' ? null : Number(evt.to.dataset.sprintList);
                            // Kembalikan DOM; Alpine merender ulang dari data.
                            const siblings = [...evt.from.querySelectorAll(':scope > .ws-bl-row')].filter((n) => n !== evt.item);
                            evt.from.insertBefore(evt.item, siblings[evt.oldDraggableIndex] || null);
                            if (!card || (evt.from === evt.to && evt.oldDraggableIndex === evt.newDraggableIndex)) return;
                            store.moveToSprint(card, to, evt.newDraggableIndex);
                        },
                    }));
                });
            },

            // Sprint berjalan, lalu sprint terencana (urutan dibuat = urutan array), lalu Backlog.
            get sections() {
                const sprints = this.$store.wsTasks.sprints;
                const list = sprints.filter((s) => s.status === 'active')
                    .concat(sprints.filter((s) => s.status === 'planned'))
                    .map((s) => ({ key: 's' + s.id, id: s.id, sprint: s }));
                return list.concat([{ key: 'backlog', id: null, sprint: null }]);
            },

            get completed() {
                return this.$store.wsTasks.sprints.filter((s) => s.status === 'completed').reverse();
            },

            tasks(id) {
                return this.$store.wsTasks.section(id);
            },

            counts(id) {
                return counts(this.tasks(id));
            },

            // Tujuan "Pindah ke" untuk satu tugas: semua bagian lain (kecuali sprint selesai).
            targets(card) {
                return this.sections.filter((s) => s.id !== (card.sprint_id || null));
            },

            toggle(key) {
                this.collapsed[key] = !this.collapsed[key];
            },

            open(card) {
                if (Date.now() - this.justDraggedAt < 300) return;
                this.$store.wsTasks.openDetail(card);
            },

            move(card, id) {
                this.$store.wsTasks.moveToSprint(card, id, this.tasks(id).length);
            },

            get hasActive() {
                return !!this.$store.wsTasks.activeSprint;
            },

            dateRange,
            remaining,
            due: WS.tasks.dueInfo,
            priorityIcon: WS.tasks.priorityIcon,
        }));

        // ---------- Dialog sprint ----------
        // config: { workspaceId }
        Alpine.data('wsSprintDialogs', (config) => ({
            formOpen: false,
            startOpen: false,
            completeOpen: false,
            deleteOpen: false,
            sprint: null,
            form: { name: '', goal: '', start_date: '', end_date: '', weeks: '2', leader_id: '', tasks: [] },
            newTitle: '',
            newPriority: 'medium',
            moveTo: 'backlog',
            errors: {},
            saving: false,

            init() {
                window.addEventListener('ws-sprint-form', (e) => this.openForm((e.detail || {}).sprint || null));
                window.addEventListener('ws-sprint-start', (e) => this.openStart(e.detail.sprint));
                window.addEventListener('ws-sprint-complete', (e) => this.openComplete(e.detail.sprint));
                window.addEventListener('ws-sprint-delete', (e) => { this.sprint = e.detail.sprint; this.errors = {}; this.deleteOpen = true; });
                this.$watch('form.weeks', () => this.syncEnd());
                this.$watch('form.start_date', () => this.syncEnd());
            },

            get store() {
                return this.$store.wsTasks;
            },

            // Durasi (minggu) → tanggal selesai otomatis. "custom" = isi sendiri.
            syncEnd() {
                if (this.form.weeks === 'custom' || !this.form.start_date) return;
                const end = new Date(parseDate(this.form.start_date).getTime() + Number(this.form.weeks) * 7 * DAY);
                this.form.end_date = toDateInput(end);
            },

            get members() {
                return this.store.interns;
            },

            // Kelompok dengan satu anggota: ketua otomatis anggota itu.
            defaultLeader(sprint) {
                if (sprint && sprint.leader) return String(sprint.leader.id);
                return this.members.length === 1 ? String(this.members[0].id) : '';
            },

            openForm(sprint) {
                this.sprint = sprint;
                this.errors = {};
                const twoWeeks = new Date(Date.now() + 14 * DAY);
                this.form = {
                    name: sprint ? sprint.name : '',
                    goal: sprint ? sprint.goal || '' : '',
                    start_date: sprint ? sprint.start_date || '' : '',
                    end_date: sprint ? sprint.end_date || '' : toDateInput(twoWeeks),
                    weeks: 'custom',
                    leader_id: this.defaultLeader(sprint),
                    tasks: [],
                };
                this.newTitle = '';
                this.newPriority = 'medium';
                this.formOpen = true;
            },

            // Baris tugas cepat: judul + prioritas, Enter untuk menambah.
            addTask() {
                const title = this.newTitle.trim();
                if (!title) return;
                this.form.tasks.push({ key: Date.now() + Math.random(), title: title.slice(0, 150), priority: this.newPriority });
                this.newTitle = '';
            },

            removeTask(i) {
                this.form.tasks.splice(i, 1);
            },

            openStart(sprint) {
                this.sprint = sprint;
                this.errors = {};
                const start = sprint.start_date || toDateInput(new Date());
                this.form = { name: sprint.name, goal: sprint.goal || '', start_date: start, end_date: sprint.end_date || '', weeks: sprint.end_date ? 'custom' : '2', leader_id: this.defaultLeader(sprint), tasks: [] };
                this.syncEnd();
                this.startOpen = true;
            },

            get completeStats() {
                return this.sprint ? counts(this.store.section(this.sprint.id)) : counts([]);
            },

            get plannedSprints() {
                return this.store.sprints.filter((s) => s.status === 'planned');
            },

            openComplete(sprint) {
                this.sprint = sprint;
                this.errors = {};
                this.moveTo = this.plannedSprints.length ? String(this.plannedSprints[0].id) : 'backlog';
                this.completeOpen = true;
            },

            validate(required, opts = {}) {
                const e = {};
                const f = this.form;
                if (opts.nameOptional ? f.name.trim().length > 60 : !f.name.trim()) e.name = f.name.trim() ? 'Nama sprint maksimal 60 karakter.' : 'Nama sprint wajib diisi.';
                else if (f.name.trim().length > 60) e.name = 'Nama sprint maksimal 60 karakter.';
                if (opts.deadline && !f.end_date) e.end_date = 'Deadline sprint wajib diisi.';
                else if (opts.deadline && !this.sprint && f.end_date < toDateInput(new Date())) e.end_date = 'Deadline sprint tidak boleh di masa lalu.';
                if (opts.leader && !f.leader_id) e.leader_id = 'Pilih ketua tim sebelum memulai sprint.';
                if (required && !f.start_date) e.start_date = 'Tanggal mulai wajib diisi.';
                if (required && !f.end_date) e.end_date = 'Tanggal selesai wajib diisi.';
                if (f.start_date && f.end_date && f.end_date <= f.start_date) e.end_date = 'Tanggal selesai harus setelah tanggal mulai.';
                this.errors = e;
                return !Object.keys(e).length;
            },

            payload() {
                const f = this.form;
                return {
                    name: f.name.trim(), goal: f.goal.trim(), start_date: f.start_date || null, end_date: f.end_date || null,
                    leader_id: f.leader_id ? Number(f.leader_id) : null,
                };
            },

            async saveForm() {
                const editing = !!this.sprint;
                this.addTask(); // judul yang masih diketik ikut disimpan
                if (!this.validate(editing && this.sprint.status === 'active', { nameOptional: !editing, deadline: true })) return;
                await this.run(async () => {
                    let res;
                    if (editing) {
                        res = await WS.api.sprints.update(this.sprint.id, this.payload());
                    } else {
                        const p = this.payload();
                        if (!p.name) delete p.name;
                        delete p.start_date;
                        p.tasks = this.form.tasks.map((t) => ({ title: t.title, priority: t.priority }));
                        res = await WS.api.sprints.create(config.workspaceId, p);
                        (res.tasks || []).forEach((card) => this.store.upsert(card));
                    }
                    this.store.upsertSprint(res.data);
                    const n = (res.tasks || []).length;
                    WS.toast(editing ? 'Sprint disimpan.' : `${res.data.name} dibuat` + (n ? ` dengan ${n} tugas.` : '. Tambah tugas atau seret dari Backlog.'), 'success');
                    this.formOpen = false;
                });
            },

            async start() {
                if (!this.validate(true, { leader: true })) return;
                await this.run(async () => {
                    const res = await WS.api.sprints.start(this.sprint.id, this.payload());
                    this.store.upsertSprint(res.data);
                    WS.toast(`${res.data.name} dimulai. Board kini menampilkan tugas sprint ini.`, 'success');
                    this.startOpen = false;
                });
            },

            async complete() {
                await this.run(async () => {
                    const res = await WS.api.sprints.complete(this.sprint.id, { move_to: this.moveTo === 'backlog' || this.moveTo === 'new' ? this.moveTo : Number(this.moveTo) });
                    this.store.upsertSprint(res.data);
                    if (res.created_sprint) this.store.upsertSprint(res.created_sprint);
                    res.moved.task_ids.forEach((id) => { const t = this.store.byId(id); if (t) t.sprint_id = res.moved.sprint_id; });
                    const target = res.moved.sprint_id ? (this.store.sprintById(res.moved.sprint_id) || {}).name : 'Backlog';
                    WS.toast(`${res.data.name} selesai.` + (res.moved.task_ids.length ? ` ${res.moved.task_ids.length} tugas belum selesai dipindah ke ${target}.` : ''), 'success');
                    this.completeOpen = false;
                });
            },

            async remove() {
                await this.run(async () => {
                    await WS.api.sprints.remove(this.sprint.id);
                    this.store.tasks.forEach((t) => { if (t.sprint_id === this.sprint.id) t.sprint_id = null; });
                    this.store.sprints = this.store.sprints.filter((s) => s.id !== this.sprint.id);
                    WS.toast(`${this.sprint.name} dihapus. Tugasnya kembali ke Backlog.`, 'success');
                    this.deleteOpen = false;
                });
            },

            async run(fn) {
                this.saving = true;
                try {
                    await fn();
                } catch (err) {
                    if (err.status === 422 && err.errors) this.errors = Object.fromEntries(Object.entries(err.errors).map(([k, v]) => [k, v[0]]));
                    else WS.toast(err.message, 'error');
                } finally {
                    this.saving = false;
                }
            },

            dateRange,
        }));
    });
})();
