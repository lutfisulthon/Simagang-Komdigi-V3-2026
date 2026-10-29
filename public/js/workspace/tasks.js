/*
 * Workspace — tugas (Fase 1B): store bersama, board kanban, dan list.
 *
 * Alur data:
 *   - Store `wsTasks` memegang TaskCard[] workspace yang sedang dibuka (dari $initialTasks).
 *   - Komponen lain (detail, form, modal) TIDAK mengubah store langsung; mereka memancarkan event
 *     window `ws-task-saved` { card } / `ws-task-deleted` { id }, lalu store menyesuaikan.
 *     Dengan begitu detail tugas juga bisa dipakai di halaman penuh tanpa board.
 *   - Perpindahan status yang butuh dialog (review / kirim hasil) diminta lewat
 *     `ws-open-review` / `ws-open-submit` { task, decision? }.
 */
(function () {
    'use strict';

    const WS = window.WS;

    const STATUSES = ['todo', 'in_progress', 'review', 'revision', 'done'];
    const PRIORITY_RANK = { urgent: 0, high: 1, medium: 2, low: 3 };

    /*
     * Status tujuan yang boleh dipilih user untuk kartu ini.
     * Server tetap memvalidasi; ini hanya agar UI tidak menawarkan aksi yang pasti ditolak.
     */
    function allowedTargets(card) {
        if (!card.can || !card.can.move) return [];
        if (card.can.review) return STATUSES.filter((s) => s !== card.status);
        // Intern: hanya sampai Review; tugas di Review/Selesai menunggu mentor.
        if (card.status === 'review' || card.status === 'done') return [];
        const targets = WS.tasks.submitLocked(card) ? ['todo', 'in_progress'] : ['todo', 'in_progress', 'review'];
        return targets.filter((s) => s !== card.status);
    }

    // Status yang tidak langsung dipindah, melainkan lewat dialog.
    function dialogFor(card, target) {
        if (target === 'done') return { event: 'ws-open-review', decision: 'approve' };
        if (target === 'revision') return { event: 'ws-open-review', decision: 'revision' };
        if (target === 'review' && !card.can.review) return { event: 'ws-open-submit' };
        return null;
    }

    function dueInfo(card) {
        const state = card.is_late ? 'overdue' : WS.time.dueState(card.due_at, card.status);
        return { state, label: WS.time.dateTime(card.due_at), relative: WS.time.relative(card.due_at) };
    }

    // Sama dengan ikon di components/workspace/priority-badge.blade.php
    const PRIORITY_ICON = { low: 'fa-arrow-down', medium: 'fa-equals', high: 'fa-arrow-up', urgent: 'fa-fire' };
    const priorityIcon = (p) => PRIORITY_ICON[p] || 'fa-flag';

    // Sprint tujuan default tugas baru: sprint berjalan (supaya langsung tampil di board), selain itu backlog.
    const defaultSprintId = () => {
        const store = window.Alpine && window.Alpine.store('wsTasks');
        return store && store.activeSprint ? store.activeSprint.id : null;
    };

    WS.tasks = Object.assign(WS.tasks || {}, { STATUSES, allowedTargets, dialogFor, dueInfo, priorityIcon, defaultSprintId });

    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        // ---------- Store ----------
        Alpine.store('wsTasks', {
            booted: false,
            workspace: null,
            tasks: [],
            sprints: [],       // Sprint[] kelompok ini (planned, active, completed)
            interns: [],       // anggota yang bisa di-assign (assign di board)
            initialDone: 0,
            readOnly: false,
            busy: {},          // { [taskId]: true } saat request berjalan
            detailId: null,    // tugas yang dibuka di slide-over
            detailOpen: false,

            boot(config) {
                this.workspace = config.workspace;
                this.tasks = config.tasks;
                this.sprints = config.sprints || [];
                this.interns = config.interns || [];
                this.readOnly = !!config.workspace.is_read_only;
                this.initialDone = config.tasks.filter((t) => t.status === 'done').length;
                this.booted = true;
                if (WS.config.mock && WS.mock) WS.mock.seed('tasks', config.tasks);

                window.addEventListener('ws-task-saved', (e) => this.upsert(e.detail.card));
                window.addEventListener('ws-task-deleted', (e) => this.remove(e.detail.id));
                // Realtime: perubahan tugas oleh orang lain langsung tampil di board & list.
                if (WS.realtime) {
                    const ch = 'workspace.' + config.workspace.id;
                    WS.realtime.subscribe(ch, 'task.changed', (e) => { if (e && e.card && !this.busy[e.card.id]) this.upsert(e.card); });
                    WS.realtime.subscribe(ch, 'task.deleted', (e) => { if (e && e.id) this.remove(e.id); });
                }
                // Label tim berubah (nama/warna/hapus): perbarui chip label di semua kartu.
                window.addEventListener('ws-labels-changed', (e) => {
                    this.tasks.forEach((t) => { t.labels = WS.labels.apply(t.labels, e.detail.labels); });
                });
                window.addEventListener('popstate', () => this.syncFromUrl());
                this.syncFromUrl();
            },

            byId(id) {
                return this.tasks.find((t) => t.id === id);
            },

            // ---------- Sprint ----------

            get activeSprint() {
                return this.sprints.find((sp) => sp.status === 'active') || null;
            },

            // Board hanya menampilkan sprint berjalan. Tanpa sprint aktif: semua tugas (kelompok tanpa sprint).
            onBoard(t) {
                const active = this.activeSprint;
                return active ? t.sprint_id === active.id : true;
            },

            // Isi satu bagian backlog (sprintId null = Backlog), urut rank.
            section(sprintId) {
                return this.tasks
                    .filter((t) => (t.sprint_id || null) === sprintId)
                    .sort((a, b) => (a.rank || 0) - (b.rank || 0));
            },

            sprintById(id) {
                return this.sprints.find((sp) => sp.id === id) || null;
            },

            // User yang login adalah ketua sprint ini?
            leads(sprint) {
                return !!(sprint && sprint.leader && sprint.leader.user_id === (WS.config.user && WS.config.user.id));
            },

            // Tugas belum dibagi (tanpa assignee, belum selesai) di satu sprint/backlog.
            undivided(sprintId) {
                return this.section(sprintId).filter((t) => !t.assignees.length && t.status !== 'done').length;
            },

            upsertSprint(sprint) {
                const i = this.sprints.findIndex((sp) => sp.id === sprint.id);
                if (i === -1) this.sprints.push(sprint);
                else this.sprints.splice(i, 1, Object.assign({}, this.sprints[i], sprint));
            },

            // Pindahkan tugas ke sprint/backlog pada indeks tertentu (optimistic, seperti requestMove).
            async moveToSprint(card, sprintId, index) {
                const snapshot = this.tasks.map((t) => ({ id: t.id, sprint_id: t.sprint_id, rank: t.rank }));
                const ids = this.section(sprintId).map((t) => t.id).filter((id) => id !== card.id);
                ids.splice(Math.max(0, Math.min(index, ids.length)), 0, card.id);
                const from = card.sprint_id || null;
                card.sprint_id = sprintId;
                ids.forEach((id, i) => { const t = this.byId(id); if (t) t.rank = i + 1; });
                this.busy[card.id] = true;
                try {
                    await WS.api.tasks.plan(card.id, { sprint_id: sprintId, rank_order: ids });
                    if (from !== sprintId) {
                        const sp = this.sprintById(sprintId);
                        WS.toast(`${card.code} dipindah ke ${sp ? sp.name : 'Backlog'}.`, 'success');
                    }
                } catch (err) {
                    snapshot.forEach((x) => Object.assign(this.byId(x.id) || {}, { sprint_id: x.sprint_id, rank: x.rank }));
                    WS.toast(err.message + ' Posisi tugas dikembalikan.', 'error');
                } finally {
                    delete this.busy[card.id];
                }
            },

            // Bagi tugas di board (optimistic): mentor/admin atau ketua sprint. ids = [internId, ...]; kosong = belum dibagi.
            async assign(card, ids) {
                const before = card.assignees;
                card.assignees = ids.map((id) => this.interns.find((i) => i.id === id)).filter(Boolean);
                this.busy[card.id] = true;
                try {
                    const res = await WS.api.tasks.assign(card.id, { assignee_ids: ids });
                    this.upsert(WS.tasks.cardOf(res.data));
                    WS.toast(ids.length ? `${card.code} dibagi ke ${card.assignees.map((a) => a.name.split(' ')[0]).join(', ')}.` : `${card.code} belum dibagi ke siapa pun.`, 'success');
                } catch (err) {
                    card.assignees = before;
                    WS.toast(err.message, 'error');
                } finally {
                    delete this.busy[card.id];
                }
            },

            // ---------- Board ----------

            column(status) {
                return this.tasks
                    .filter((t) => t.status === status && this.onBoard(t))
                    .sort((a, b) => a.position - b.position);
            },

            count(status) {
                return this.tasks.filter((t) => t.status === status && this.onBoard(t)).length;
            },

            // Statistik hidup (seluruh kelompok, bukan hanya sprint): angka server + perubahan di halaman ini.
            stat(status, serverValue) {
                const all = (st) => this.tasks.filter((t) => t.status === st).length;
                if (status === 'late') return this.tasks.filter((t) => t.is_late && t.status !== 'done').length;
                if (status === 'done') return serverValue + (all('done') - this.initialDone);
                return all(status);
            },

            get activeCount() {
                return this.tasks.filter((t) => t.status !== 'done' && this.onBoard(t)).length;
            },

            // ---------- Deadline (seluruh kelompok, termasuk backlog) ----------
            // Dihitung dari tugas yang belum dikirim (To Do / In Progress / Revisi), sama dengan kartu di daftar kelompok.

            // Lewat deadline dan belum dikirim ("Belum diselesaikan").
            get overdueCount() {
                return this.tasks.filter((t) => WS.tasks.unfinished(t)).length;
            },

            // Tugas terdekat yang belum lewat deadline.
            get nextDue() {
                const open = this.tasks.filter((t) => ['todo', 'in_progress', 'revision'].includes(t.status) && !WS.tasks.unfinished(t));
                return open.sort((a, b) => new Date(a.due_at) - new Date(b.due_at))[0] || null;
            },

            get deadlineLine() {
                const late = this.overdueCount;
                if (late) return late + ' tugas lewat deadline';
                const next = this.nextDue;
                return next ? 'Deadline terdekat ' + next.code + ' · ' + WS.time.relative(next.due_at) : '';
            },

            upsert(card) {
                const i = this.tasks.findIndex((t) => t.id === card.id);
                if (i === -1) this.tasks.push(card);
                else this.tasks.splice(i, 1, Object.assign({}, this.tasks[i], card));
            },

            remove(id) {
                this.tasks = this.tasks.filter((t) => t.id !== id);
                if (this.detailId === id) this.closeDetail();
            },

            // Susun ulang posisi satu kolom sesuai urutan id.
            reorder(status, orderedIds) {
                orderedIds.forEach((id, index) => {
                    const t = this.byId(id);
                    if (t) { t.status = status; t.position = index; }
                });
            },

            /*
             * Pindahkan kartu ke `target` pada indeks `index` (optimistic).
             * Dipanggil oleh drag & drop dan menu "Pindah status".
             */
            async requestMove(card, target, index) {
                if (!allowedTargets(card).includes(target) && !(target === card.status && card.can.move)) {
                    WS.toast(target === 'review' && WS.tasks.submitLocked(card) ? WS.tasks.LOCK_NOTE : 'Anda tidak dapat memindahkan tugas ke kolom ini.', 'error');
                    return;
                }
                const dialog = target === card.status ? null : dialogFor(card, target);
                if (dialog) {
                    window.dispatchEvent(new CustomEvent(dialog.event, { detail: { task: card, decision: dialog.decision } }));
                    return;
                }

                const snapshot = this.tasks.map((t) => ({ id: t.id, status: t.status, position: t.position }));
                const ids = this.column(target).map((t) => t.id).filter((id) => id !== card.id);
                ids.splice(Math.max(0, Math.min(index, ids.length)), 0, card.id);
                const from = card.status;
                this.reorder(target, ids);
                if (from !== target) this.reorder(from, this.column(from).map((t) => t.id));

                this.busy[card.id] = true;
                try {
                    const res = await WS.api.tasks.move(card.id, { status: target, column_order: ids });
                    if (res && res.data) this.upsert(res.data);
                    if (from !== target) WS.toast(`${card.code} dipindah ke ${WS.STATUS[target]}`, 'success');
                } catch (err) {
                    snapshot.forEach((s) => Object.assign(this.byId(s.id) || {}, { status: s.status, position: s.position }));
                    WS.toast(err.message + ' Posisi kartu dikembalikan.', 'error');
                } finally {
                    delete this.busy[card.id];
                }
            },

            // ---------- Slide-over detail + URL (?task=T-12) ----------

            openDetail(card, { push = true } = {}) {
                this.detailId = card.id;
                this.detailOpen = true;
                if (push) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('task', card.code);
                    window.history.pushState({ task: card.code }, '', url);
                }
            },

            // Idempoten: dipanggil juga saat slide-over ditutup lewat x-model (detailOpen sudah false).
            closeDetail() {
                this.detailOpen = false;
                const url = new URL(window.location.href);
                if (url.searchParams.has('task')) {
                    url.searchParams.delete('task');
                    window.history.pushState({}, '', url);
                }
            },

            syncFromUrl() {
                const code = new URL(window.location.href).searchParams.get('task');
                const card = code && this.tasks.find((t) => t.code === code);
                if (card) this.openDetail(card, { push: false });
                else this.detailOpen = false;
            },
        });

        // ---------- Board kanban ----------
        Alpine.data('wsBoard', () => ({
            sortables: [],
            draggingId: null,
            justDraggedAt: 0,

            init() {
                if (!window.Sortable) {
                    WS.toast('SortableJS gagal dimuat; gunakan menu "Pindah status".', 'error');
                    return;
                }
                this.$nextTick(() => {
                    this.$root.querySelectorAll('[data-column]').forEach((el) => this.bindColumn(el));
                });
            },

            destroy() {
                this.sortables.forEach((s) => s.destroy());
            },

            bindColumn(el) {
                const store = this.$store.wsTasks;
                const self = this;
                this.sortables.push(window.Sortable.create(el, {
                    group: {
                        name: 'ws-board',
                        put(to, from, dragEl) {
                            const card = store.byId(Number(dragEl.dataset.id));
                            const target = to.el.dataset.column;
                            return !!card && allowedTargets(card).includes(target);
                        },
                    },
                    draggable: '.ws-task-card',
                    filter: '[data-no-drag]',
                    preventOnFilter: false,
                    disabled: store.readOnly,
                    animation: 150,
                    delay: 180,
                    delayOnTouchOnly: true,
                    ghostClass: 'ws-task-card--ghost',
                    chosenClass: 'ws-task-card--chosen',
                    dragClass: 'ws-task-card--drag',
                    onMove(evt) {
                        const card = store.byId(Number(evt.dragged.dataset.id));
                        return !!card && card.can.move;
                    },
                    onStart(evt) {
                        self.draggingId = Number(evt.item.dataset.id);
                    },
                    onEnd(evt) {
                        self.draggingId = null;
                        self.justDraggedAt = Date.now();
                        const card = store.byId(Number(evt.item.dataset.id));
                        const target = evt.to.dataset.column;
                        const index = evt.newDraggableIndex;
                        // Kembalikan DOM ke posisi semula; Alpine yang merender ulang dari data.
                        const siblings = [...evt.from.querySelectorAll(':scope > .ws-task-card')].filter((n) => n !== evt.item);
                        evt.from.insertBefore(evt.item, siblings[evt.oldDraggableIndex] || null);
                        if (!card || (evt.from === evt.to && evt.oldDraggableIndex === evt.newDraggableIndex)) return;
                        store.requestMove(card, target, index);
                    },
                }));
            },

            isDropTarget(status) {
                if (this.draggingId === null) return false;
                const card = this.$store.wsTasks.byId(this.draggingId);
                return !!card && allowedTargets(card).includes(status);
            },

            open(card) {
                // Klik yang terjadi tepat setelah drag bukan niat membuka detail.
                if (Date.now() - this.justDraggedAt < 300) return;
                this.$store.wsTasks.openDetail(card);
            },

            // Bagi tugas: mentor/admin atau ketua sprint tugas ini (can.assign dari server).
            canAssign(card) {
                return !!card.can.assign && !this.$store.wsTasks.readOnly && this.$store.wsTasks.interns.length > 0;
            },

            toggleAssignee(card, internId) {
                const ids = card.assignees.map((a) => a.id);
                const next = ids.includes(internId) ? ids.filter((id) => id !== internId) : ids.concat(internId);
                this.$store.wsTasks.assign(card, next);
            },

            targets: allowedTargets,
            due: dueInfo,
        }));

        // ---------- List / tabel ----------
        Alpine.data('wsTaskList', (labels, interns) => ({
            labels,
            interns,

            init() {
                window.addEventListener('ws-labels-changed', (e) => {
                    this.labels = e.detail.labels;
                    if (this.label && !this.labels.some((l) => String(l.id) === this.label)) this.label = '';
                });
            },

            q: '',
            statuses: [],
            priority: '',
            assignee: '',
            label: '',
            dueFrom: '',
            dueTo: '',
            sortKey: 'due_at',
            sortDir: 'asc',

            get filtered() {
                const q = this.q.trim().toLowerCase();
                const from = this.dueFrom ? new Date(this.dueFrom + 'T00:00:00').getTime() : null;
                const to = this.dueTo ? new Date(this.dueTo + 'T23:59:59').getTime() : null;
                const rows = this.$store.wsTasks.tasks.filter((t) => {
                    if (q && !(t.title.toLowerCase().includes(q) || t.code.toLowerCase().includes(q))) return false;
                    if (this.statuses.length && !this.statuses.includes(t.status)) return false;
                    if (this.priority && t.priority !== this.priority) return false;
                    if (this.assignee && !t.assignees.some((a) => String(a.id) === this.assignee)) return false;
                    if (this.label && !t.labels.some((l) => String(l.id) === this.label)) return false;
                    const due = new Date(t.due_at).getTime();
                    if (from && due < from) return false;
                    if (to && due > to) return false;
                    return true;
                });
                const dir = this.sortDir === 'asc' ? 1 : -1;
                const value = {
                    code: (t) => Number(t.code.replace(/\D/g, '')),
                    title: (t) => t.title.toLowerCase(),
                    status: (t) => STATUSES.indexOf(t.status),
                    priority: (t) => PRIORITY_RANK[t.priority],
                    due_at: (t) => new Date(t.due_at).getTime(),
                }[this.sortKey];
                return rows.sort((a, b) => (value(a) > value(b) ? dir : value(a) < value(b) ? -dir : 0));
            },

            get filtering() {
                return !!(this.q || this.statuses.length || this.priority || this.assignee || this.label || this.dueFrom || this.dueTo);
            },

            toggleStatus(status) {
                const i = this.statuses.indexOf(status);
                if (i === -1) this.statuses.push(status);
                else this.statuses.splice(i, 1);
            },

            sort(key) {
                if (this.sortKey === key) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                else { this.sortKey = key; this.sortDir = 'asc'; }
            },

            ariaSort(key) {
                if (this.sortKey !== key) return 'none';
                return this.sortDir === 'asc' ? 'ascending' : 'descending';
            },

            reset() {
                Object.assign(this, { q: '', statuses: [], priority: '', assignee: '', label: '', dueFrom: '', dueTo: '' });
            },

            due: dueInfo,
        }));
    });
})();
