/*
 * Workspace — lonceng notifikasi.
 * Satu store global (satu poller) dipakai bersama oleh lonceng desktop & mobile.
 */
(function () {
    'use strict';

    const WS = window.WS;

    const TYPE_META = {
        task_assigned:         { icon: 'fa-clipboard-list',      color: '#2563eb', bg: '#eff6ff' },
        task_in_review:        { icon: 'fa-magnifying-glass',    color: '#7c3aed', bg: '#f5f3ff' },
        task_approved:         { icon: 'fa-circle-check',        color: '#059669', bg: '#ecfdf5' },
        task_revision:         { icon: 'fa-rotate-left',         color: '#ea580c', bg: '#fff7ed' },
        task_due_soon:         { icon: 'fa-hourglass-half',      color: '#d97706', bg: '#fffbeb' },
        task_overdue:          { icon: 'fa-triangle-exclamation', color: '#dc2626', bg: '#fef2f2' },
        task_commented:        { icon: 'fa-comment',             color: '#475569', bg: '#f1f5f9' },
        mentioned:             { icon: 'fa-at',                  color: '#4f46e5', bg: '#eef2ff' },
        announcement:          { icon: 'fa-bullhorn',            color: '#0891b2', bg: '#ecfeff' },
        intern_joined:         { icon: 'fa-user-plus',           color: '#059669', bg: '#ecfdf5' },
        group_joined:          { icon: 'fa-users',               color: '#4f46e5', bg: '#eef2ff' },
        sprint_leader:         { icon: 'fa-flag',                color: '#7c3aed', bg: '#f5f3ff' },
        sprint_to_divide:      { icon: 'fa-people-arrows',       color: '#0e7490', bg: '#ecfeff' },
        deadline_extended:     { icon: 'fa-calendar-plus',       color: '#b45309', bg: '#fffbeb' },
        intern_without_mentor: { icon: 'fa-user-clock',          color: '#d97706', bg: '#fffbeb' },
    };
    const FALLBACK_META = { icon: 'fa-bell', color: '#475569', bg: '#f1f5f9' };

    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        Alpine.store('wsNotif', {
            unread: 0,
            items: [],
            loaded: false,
            error: false,
            poller: null,

            init() {
                this.poller = WS.createPoller(() => this.refresh(), {
                    min: 15000,
                    max: 60000,
                });
                this.refresh().finally(() => this.poller.start());
                // Realtime: notifikasi baru langsung dimuat (polling tetap jalan sebagai cadangan).
                if (WS.realtime) WS.realtime.onNotification(() => this.refresh().catch(() => {}));
            },

            async refresh() {
                try {
                    const res = await WS.api.notifications.summary({ limit: 10 });
                    const firstId = res.data.length ? res.data[0].id : null;
                    const prevFirstId = this.items.length ? this.items[0].id : null;
                    const changed = res.unread_count !== this.unread || firstId !== prevFirstId;
                    this.unread = res.unread_count;
                    this.items = res.data;
                    this.loaded = true;
                    this.error = false;
                    // Usulan kontrak #3: badge unread per channel ikut polling ringkas ini (dibaca store wsChat).
                    if (res.unread_messages) {
                        window.dispatchEvent(new CustomEvent('ws-unread-messages', { detail: res.unread_messages }));
                    }
                    return changed;
                } catch (err) {
                    this.error = true;
                    throw err;
                }
            },

            // Mengembalikan true bila berhasil (halaman notifikasi memakai ini untuk membatalkan perubahan lokal).
            async markAllRead() {
                const snapshot = JSON.parse(JSON.stringify(this.items));
                const prevUnread = this.unread;
                const now = new Date().toISOString();
                this.items.forEach((n) => { if (!n.read_at) n.read_at = now; });
                this.unread = 0;
                try {
                    await WS.api.notifications.readAll();
                    return true;
                } catch (err) {
                    this.items = snapshot;
                    this.unread = prevUnread;
                    WS.toast(err.message, 'error');
                    return false;
                }
            },

            // Dipanggil untuk notifikasi yang BELUM dibaca (dari lonceng atau halaman notifikasi).
            markRead(id) {
                const copy = this.items.find((n) => n.id === id);
                if (copy) copy.read_at = copy.read_at || new Date().toISOString();
                this.unread = Math.max(0, this.unread - 1);
                return WS.api.notifications.read(id).catch(() => { /* tidak fatal */ });
            },

            async open(item) {
                const request = item.read_at ? null : this.markRead(item.id);
                item.read_at = item.read_at || new Date().toISOString();
                if (!item.url || item.url === '#') return;
                // Beri waktu request "dibaca" terkirim sebelum pindah halaman (maks. 400 ms).
                if (request) await Promise.race([request, new Promise((r) => setTimeout(r, 400))]);
                window.location.href = item.url;
            },

            meta(type) {
                return TYPE_META[type] || FALLBACK_META;
            },

            badgeText() {
                return this.unread > 99 ? '99+' : String(this.unread);
            },
        });

        Alpine.data('wsNotificationBell', () => ({
            isOpen: false,

            toggle() {
                this.isOpen = !this.isOpen;
                if (!this.isOpen) return;
                const bottom = this.$refs.trigger.getBoundingClientRect().bottom;
                this.$refs.panel.style.setProperty('--ws-dropdown-top', Math.round(bottom + 8) + 'px');
                this.$store.wsNotif.refresh().catch(() => {});
            },

            close() {
                this.isOpen = false;
            },

            // Esc: tutup, dan kembalikan fokus ke tombol lonceng bila fokus sedang di dalam panel.
            escape() {
                if (!this.isOpen) return;
                const inside = this.$root.contains(document.activeElement);
                this.close();
                if (inside) this.$refs.trigger.focus();
            },

            relative(iso) {
                return WS.time.relative(iso);
            },
        }));

        // ---------- Pengaturan notifikasi (/notifications/settings) ----------
        // config: { initial: { types, muted_channels } | null }
        Alpine.data('wsNotifSettings', (config) => ({
            loading: !config.initial,
            error: null,
            saving: false,
            types: [],
            saved: '',      // snapshot JSON untuk deteksi perubahan
            muted: [],
            unmuting: {},

            init() {
                if (config.initial) this.apply(config.initial);
                else this.load();
                // Peringatkan bila halaman ditinggalkan dengan perubahan belum disimpan.
                window.addEventListener('beforeunload', (e) => {
                    if (this.dirty) { e.preventDefault(); e.returnValue = ''; }
                });
            },

            apply(data) {
                this.types = data.types;
                this.muted = data.muted_channels || [];
                this.saved = this.snapshot();
            },

            async load() {
                this.loading = true;
                this.error = null;
                try {
                    this.apply((await WS.api.notifications.preferences()).data);
                } catch (err) {
                    this.error = err.message;
                } finally {
                    this.loading = false;
                }
            },

            snapshot() {
                return JSON.stringify(this.types.map((t) => [t.type, t.in_app, t.email]));
            },

            get dirty() {
                return !this.loading && this.saved !== '' && this.snapshot() !== this.saved;
            },

            get groups() {
                const groups = [];
                this.types.forEach((t) => {
                    let g = groups.find((x) => x.name === t.group);
                    if (!g) groups.push(g = { name: t.group, items: [] });
                    g.items.push(t);
                });
                return groups;
            },

            reset() {
                const saved = new Map(JSON.parse(this.saved).map(([type, inApp, email]) => [type, { inApp, email }]));
                this.types.forEach((t) => { const v = saved.get(t.type); t.in_app = v.inApp; t.email = v.email; });
            },

            async save() {
                this.saving = true;
                try {
                    const payload = { types: Object.fromEntries(this.types.map((t) => [t.type, { in_app: t.in_app, email: t.email === null ? null : t.email }])) };
                    this.apply((await WS.api.notifications.savePreferences(payload)).data);
                    WS.toast('Pengaturan notifikasi disimpan.', 'success');
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.saving = false;
                }
            },

            async unmute(c) {
                this.unmuting[c.id] = true;
                try {
                    await WS.api.channels.mute(c.id, { is_muted: false });
                    this.muted = this.muted.filter((x) => x.id !== c.id);
                    WS.toast(`Notifikasi #${c.name} diaktifkan lagi.`, 'success');
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.unmuting[c.id] = false;
                }
            },

            meta: (type) => window.Alpine.store('wsNotif').meta(type),
        }));

        // ---------- Halaman notifikasi (/notifications) ----------
        // config: { initial: { data, meta } | null, filter: 'all'|'unread', category: string|null }
        // initial null → halaman pertama diambil lewat JSON saat dibuka.
        Alpine.data('wsNotificationsPage', (config) => ({
            items: [],
            page: 1,
            hasMore: false,
            loading: false,
            loadingMore: false,
            error: null,
            filter: config.filter || 'all',
            category: config.category || null,
            hasNew: false,
            request: 0,

            init() {
                if (config.initial) this.apply(config.initial, false);
                else this.load();
                // Notifikasi baru dari polling lonceng → tawarkan muat ulang (tidak menggeser daftar diam-diam).
                this.$watch('$store.wsNotif.items.length ? $store.wsNotif.items[0].id : null', (id, prev) => {
                    if (id && prev && id !== prev && !this.items.some((n) => n.id === id)) this.hasNew = true;
                });
            },

            apply(res, append) {
                this.items = append ? this.items.concat(res.data) : res.data;
                this.page = res.meta.current_page;
                this.hasMore = !!res.meta.has_more;
            },

            query(page) {
                return { page, per_page: 20, filter: this.filter === 'unread' ? 'unread' : null, category: this.category };
            },

            async load() {
                const req = ++this.request;
                this.loading = true;
                this.error = null;
                this.hasNew = false;
                try {
                    const res = await WS.api.notifications.list(this.query(1));
                    if (req === this.request) this.apply(res, false);
                } catch (err) {
                    if (req === this.request) this.error = err.message;
                } finally {
                    if (req === this.request) this.loading = false;
                }
            },

            async loadMore() {
                if (this.loadingMore || !this.hasMore) return;
                const req = this.request;
                this.loadingMore = true;
                try {
                    const res = await WS.api.notifications.list(this.query(this.page + 1));
                    // Abaikan bila filter berubah selama memuat.
                    if (req === this.request) this.apply(res, true);
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.loadingMore = false;
                }
            },

            setFilter(filter) {
                if (this.filter === filter) return;
                this.filter = filter;
                this.syncUrl();
                this.load();
            },

            setCategory(category) {
                this.category = this.category === category ? null : category;
                this.syncUrl();
                this.load();
            },

            syncUrl() {
                const url = new URL(window.location.href);
                if (this.filter === 'unread') url.searchParams.set('filter', 'unread');
                else url.searchParams.delete('filter');
                if (this.category) url.searchParams.set('category', this.category);
                else url.searchParams.delete('category');
                window.history.replaceState(window.history.state, '', url);
            },

            get groups() {
                const groups = [];
                this.items.forEach((item) => {
                    const label = WS.activity.dayLabel(item.created_at);
                    let g = groups[groups.length - 1];
                    if (!g || g.label !== label) groups.push(g = { label, items: [] });
                    g.items.push(item);
                });
                return groups;
            },

            get emptyText() {
                if (this.filter === 'unread') return ['Semua sudah dibaca', 'Tidak ada notifikasi yang belum dibaca.'];
                if (this.category) return ['Belum ada notifikasi', 'Belum ada notifikasi untuk kategori ini.'];
                return ['Belum ada notifikasi', 'Pemberitahuan tugas, mention, dan pengumuman akan muncul di sini.'];
            },

            // Klik dengan Ctrl/⌘/Shift/tombol tengah: biarkan browser membuka tab baru, cukup tandai dibaca.
            open(event, item) {
                if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
                    this.markRead(item);
                    return;
                }
                event.preventDefault();
                this.$store.wsNotif.open(item);
            },

            markRead(item) {
                if (item.read_at) return;
                item.read_at = new Date().toISOString();
                this.$store.wsNotif.markRead(item.id);
            },

            async markAllRead() {
                const unread = this.items.filter((n) => !n.read_at);
                const now = new Date().toISOString();
                unread.forEach((n) => { n.read_at = now; });
                const ok = await this.$store.wsNotif.markAllRead();
                if (!ok) unread.forEach((n) => { n.read_at = null; });
                else WS.toast('Semua notifikasi ditandai dibaca.', 'success');
            },

            meta: (type) => window.Alpine.store('wsNotif').meta(type),
            categoryLabel(type) {
                if (type.startsWith('task_')) return 'Tugas';
                if (type.startsWith('intern_')) return 'Intern';
                if (type.startsWith('sprint_')) return 'Sprint';
                return { mentioned: 'Mention', announcement: 'Pengumuman', group_joined: 'Kelompok', deadline_extended: 'Tugas' }[type] || 'Lainnya';
            },
            relative: (iso) => WS.time.relative(iso),
            dateTime: (iso) => WS.time.dateTime(iso),
        }));
    });
})();
