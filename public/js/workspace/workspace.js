/*
 * Workspace — komponen Alpine halaman daftar & detail workspace (Fase 1A).
 * Dimuat oleh resources/views/workspace/layout.blade.php.
 */
(function () {
    'use strict';

    const WS = window.WS;

    // ---------- Aktivitas: kalimat & ikon dari action + meta ----------

    const ACTIVITY = {
        created:          { icon: 'fa-plus',             color: '#2563eb', bg: '#eff6ff', text: () => ['membuat tugas', ''] },
        updated:          { icon: 'fa-pen',              color: '#475569', bg: '#f1f5f9', text: () => ['memperbarui', ''] },
        status_changed:   { icon: 'fa-arrow-right',      color: '#7c3aed', bg: '#f5f3ff', text: (m) => ['memindahkan', 'ke ' + (WS.STATUS[m.to] || m.to)] },
        assigned:         { icon: 'fa-user-plus',        color: '#0891b2', bg: '#ecfeff', text: (m) => ['menugaskan', 'ke ' + (m.assignee || 'intern')] },
        unassigned:       { icon: 'fa-user-minus',       color: '#64748b', bg: '#f1f5f9', text: (m) => ['melepas ' + (m.assignee || 'intern') + ' dari', ''] },
        commented:        { icon: 'fa-comment',          color: '#475569', bg: '#f1f5f9', text: () => ['mengomentari', ''] },
        attachment_added: { icon: 'fa-paperclip',        color: '#0f766e', bg: '#f0fdfa', text: (m) => ['melampirkan ' + (m.file || 'berkas') + ' di', ''] },
        marked_late:      { icon: 'fa-triangle-exclamation', color: '#dc2626', bg: '#fef2f2', text: () => ['menandai', 'terlambat'] },
        deadline_extended: {
            icon: 'fa-calendar-plus', color: '#b45309', bg: '#fffbeb',
            text: (m) => ['memperpanjang deadline', 'sampai ' + (m.to ? WS.time.dateTime(m.to) : '') + (m.reason ? ' — "' + m.reason + '"' : '')],
        },
        reviewed: {
            icon: 'fa-clipboard-check', color: '#059669', bg: '#ecfdf5',
            text: (m) => (m.decision === 'approve'
                ? ['menyetujui', m.score != null ? 'dengan nilai ' + m.score : '']
                : ['meminta revisi untuk', '']),
        },
    };
    const ACTIVITY_FALLBACK = { icon: 'fa-circle', color: '#64748b', bg: '#f1f5f9', text: () => ['mengubah', ''] };

    function describeActivity(item) {
        const def = ACTIVITY[item.action] || ACTIVITY_FALLBACK;
        const [before, after] = def.text(item.meta || {});
        const revision = item.action === 'reviewed' && item.meta && item.meta.decision === 'revision';
        return {
            icon: revision ? 'fa-rotate-left' : def.icon,
            color: revision ? '#ea580c' : def.color,
            bg: revision ? '#fff7ed' : def.bg,
            actor: item.user ? item.user.name : 'Sistem',
            before,
            after,
        };
    }

    const dayFmt = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });

    function dayLabel(iso) {
        const d = new Date(iso);
        const startOf = (x) => new Date(x.getFullYear(), x.getMonth(), x.getDate()).getTime();
        const diffDays = Math.round((startOf(new Date()) - startOf(d)) / 86400000);
        if (diffDays === 0) return 'Hari ini';
        if (diffDays === 1) return 'Kemarin';
        return dayFmt.format(d);
    }

    WS.activity = { describe: describeActivity, dayLabel };

    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        // ---------- Daftar workspace: pencarian & filter tim ----------
        // entries: [{ id, type: 'team'|'intern', text, team_id }]
        Alpine.data('wsWorkspaceIndex', (entries) => ({
            entries,
            q: '',
            team: '',

            normalize(s) {
                return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            },

            isVisible(id) {
                const e = this.entries.find((x) => x.id === id);
                if (!e) return true;
                if (this.team && String(e.team_id) !== String(this.team)) return false;
                const q = this.normalize(this.q.trim());
                return !q || this.normalize(e.text).includes(q);
            },

            count(type) {
                return this.entries.filter((e) => e.type === type && this.isVisible(e.id)).length;
            },

            get filtering() {
                return this.q.trim() !== '' || this.team !== '';
            },

            reset() {
                this.q = '';
                this.team = '';
            },
        }));

        // ---------- Detail workspace: tab ----------
        Alpine.data('wsWorkspaceShow', (config) => ({
            tab: config.tab,
            tabs: config.tabs,

            init() {
                this.$nextTick(() => this.revealTab());
                // Tautan antar tab dari dalam panel (mis. "Backlog" di bilah sprint board).
                window.addEventListener('ws-select-tab', (e) => {
                    this.select(e.detail);
                    this.$nextTick(() => document.getElementById('ws-tabbar')?.scrollIntoView({ behavior: WS.scrollBehavior(), block: 'nearest' }));
                });
            },

            select(tab) {
                if (!this.tabs.includes(tab) || tab === this.tab) return;
                this.tab = tab;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
                this.revealTab();
                this.$dispatch('ws-tab-changed', tab);
            },

            // Di HP tablist bisa di-scroll horizontal; pastikan tab aktif terlihat tanpa menggeser halaman.
            revealTab() {
                const list = this.$root.querySelector('[role="tablist"]');
                const el = list && list.querySelector(`[data-tab="${this.tab}"]`);
                if (!el || list.scrollWidth <= list.clientWidth) return;
                list.scrollTo({ left: el.offsetLeft - (list.clientWidth - el.offsetWidth) / 2, behavior: WS.scrollBehavior() });
            },

            // Navigasi keyboard pada tablist (kiri/kanan/Home/End).
            onKey(event) {
                const i = this.tabs.indexOf(this.tab);
                const map = {
                    ArrowRight: (i + 1) % this.tabs.length,
                    ArrowLeft: (i - 1 + this.tabs.length) % this.tabs.length,
                    Home: 0,
                    End: this.tabs.length - 1,
                };
                if (!(event.key in map)) return;
                event.preventDefault();
                this.select(this.tabs[map[event.key]]);
                this.$nextTick(() => this.$root.querySelector(`[role="tab"][data-tab="${this.tab}"]`)?.focus());
            },
        }));

        // ---------- Feed aktivitas ----------
        Alpine.data('wsActivityFeed', (workspaceId, initial) => ({
            items: initial,
            loading: false,
            hasMore: initial.length >= 20,

            init() {
                if (WS.config.mock && WS.mock) WS.mock.seed('activities:' + workspaceId, initial);
            },

            get groups() {
                const groups = [];
                this.items.forEach((item) => {
                    const label = dayLabel(item.created_at);
                    let g = groups[groups.length - 1];
                    if (!g || g.label !== label) groups.push(g = { label, items: [] });
                    g.items.push(item);
                });
                return groups;
            },

            describe: describeActivity,

            relative(iso) {
                return WS.time.relative(iso);
            },

            fullTime(iso) {
                return WS.time.dateTime(iso);
            },

            async loadMore() {
                if (this.loading || !this.hasMore) return;
                this.loading = true;
                try {
                    const last = this.items[this.items.length - 1];
                    const res = await WS.api.activities.list(workspaceId, { before: last ? last.id : undefined });
                    this.items = this.items.concat(res.data);
                    this.hasMore = !!(res.meta && res.meta.has_more);
                } catch (err) {
                    WS.toast(err.message, 'error');
                } finally {
                    this.loading = false;
                }
            },
        }));
    });
})();
