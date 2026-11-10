/*
 * Workspace — channel diskusi (Fase 1C).
 * Store `wsChat`: daftar channel + jumlah belum dibaca (dipakai juga badge tab Channel).
 * Komponen `wsChannels`: pesan channel aktif — muat awal, scroll ke atas (?before=),
 * polling (?after=&since=) hanya saat tab Channel terbuka, kirim (optimistis), edit, hapus.
 * Komponen `wsComposer`: Enter kirim, Shift+Enter baris baru, autocomplete @mention dan #T-12, lampiran.
 * Fase 2: panel samping (thread, pencarian, pesan disematkan) dan kelola channel topik (buat, ubah, arsipkan).
 * Fase 3: reaksi emoji pada pesan dan pemilih emoji di komposer.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const me = WS.config.user || { id: 0, name: 'Anda', role: 'intern' };
    const meMini = { id: me.id, name: me.name, role: me.role, avatar_url: null };
    const clockFmt = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' });
    const fullFmt = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    const GROUP_GAP = 5 * 60000; // pesan beruntun dari user yang sama dalam 5 menit digabung
    const NEAR_BOTTOM = 80;
    const coarse = () => window.matchMedia('(pointer: coarse)').matches;
    const narrow = () => window.matchMedia('(max-width: 767px)').matches;

    function title(c) {
        if (!c) return '';
        if (c.type !== 'direct') return c.name;
        const members = c.members || [];
        const others = members.filter((m) => m.id !== me.id);
        return (others.length ? others : members).map((m) => m.name).join(' & ') || c.name;
    }

    // Pesan yang belum dikonfirmasi server ditampilkan sebagai teks biasa (markdown dirender server).
    function plain(text) {
        const esc = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return esc ? '<p>' + esc.replace(/\n/g, '<br>') + '</p>' : '';
    }

    const isTemp = (m) => typeof m.id === 'string';
    const escapeHtml = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    // Potongan teks di sekitar kata yang dicari, aman untuk x-html (di-escape, kata disorot <mark>).
    function excerpt(text, q) {
        // Teks polos: tanda markdown sederhana dibuang supaya potongan mudah dibaca.
        const body = (text || '').replace(/\*\*(.+?)\*\*/g, '$1').replace(/`([^`]+)`/g, '$1')
            .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1').replace(/^\s*-\s+/gm, '').replace(/\s+/g, ' ').trim();
        if (!q) return escapeHtml(body.slice(0, 140));
        const at = body.toLowerCase().indexOf(q.toLowerCase());
        const start = Math.max(0, at - 50);
        const cut = (start > 0 ? '…' : '') + body.slice(start, start + 140) + (body.length > start + 140 ? '…' : '');
        const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        return escapeHtml(cut).replace(re, (m) => '<mark class="ws-mark">' + m + '</mark>');
    }

    // "Nama channel": huruf kecil, angka, dan tanda hubung (seperti Slack).
    const slugify = (v) => v.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').slice(0, 40);

    // Posisi vertikal elemen di dalam kontainer scroll (offsetTop tidak terpengaruh animasi transform).
    function offsetWithin(el, container) {
        const top = (node) => { let y = 0; for (let n = node; n; n = n.offsetParent) y += n.offsetTop; return y; };
        return top(el) - top(container);
    }

    // Reaksi cepat (harus sama dengan daftar yang diterima server).
    const REACTIONS = ['👍', '❤️', '😂', '🎉', '🙏', '👀', '✅', '🔥'];

    // Pemilih emoji di komposer: set kecil yang umum dipakai, tanpa pustaka eksternal.
    const EMOJI_GROUPS = [
        ['Wajah', ['😀', '😁', '😂', '🤣', '😊', '😇', '🙂', '😉', '😍', '🤩', '😎', '🤔', '😅', '😮', '😴', '😢', '😭', '😤', '🥳', '🤗']],
        ['Gestur', ['👍', '👎', '👏', '🙌', '🙏', '💪', '👋', '🤝', '✌️', '👌', '☝️', '✍️']],
        ['Simbol', ['❤️', '🔥', '🎉', '✅', '❌', '⚠️', '⭐', '💡', '📌', '📎', '📅', '⏰', '🚀', '💯', '👀', '🎯']],
    ];

    WS.chat = { title, plain, excerpt, slugify, REACTIONS, EMOJI_GROUPS };

    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        // ---------- Store ----------
        Alpine.store('wsChat', {
            booted: false,
            workspaceId: null,
            channels: [],
            activeId: null,
            threadId: null, // pesan induk thread yang sedang dibuka (draf komposer thread)
            readOnly: false,
            viewing: false, // tab Channel sedang terbuka

            boot({ workspaceId, channels, readOnly }) {
                this.workspaceId = workspaceId;
                this.channels = channels;
                this.readOnly = !!readOnly;
                this.booted = true;
                window.addEventListener('ws-unread-messages', (e) => this.applyUnread((e.detail && e.detail.by_channel) || {}));
            },

            byId(id) {
                return this.channels.find((c) => c.id === id) || null;
            },

            // Sama dengan Workspace.unread_messages: channel workspace ini saja, tanpa #pengumuman global
            // dan tanpa channel yang dibisukan.
            get unreadTotal() {
                return this.channels.filter((c) => c.workspace_id === this.workspaceId && !c.is_muted).reduce((n, c) => n + c.unread_count, 0);
            },

            // Channel topik yang diarsipkan dipindah ke bagian "Diarsipkan" (kecuali seluruh workspace read-only).
            get sections() {
                const archived = (c) => c.is_archived && !this.readOnly && c.workspace_id !== null;
                const groups = [
                    ['Channel', this.channels.filter((c) => c.workspace_id !== null && c.type !== 'direct' && !archived(c))],
                    ['Pesan langsung', this.channels.filter((c) => c.type === 'direct')],
                    ['Umum', this.channels.filter((c) => c.workspace_id === null)],
                    ['Diarsipkan', this.channels.filter(archived)],
                ];
                return groups.filter(([, items]) => items.length).map(([label, items]) => ({ label, items }));
            },

            add(channel) {
                const firstGlobal = this.channels.findIndex((c) => c.workspace_id === null);
                if (firstGlobal === -1) this.channels.push(channel);
                else this.channels.splice(firstGlobal, 0, channel);
            },

            // by_channel hanya memuat channel dengan pesan belum dibaca; yang tidak ada = 0.
            // Channel yang sedang dibuka diurus polling-nya sendiri.
            applyUnread(byChannel) {
                this.channels.forEach((c) => {
                    if (this.viewing && c.id === this.activeId) return;
                    c.unread_count = Number(byChannel[c.id] || 0);
                });
            },

            title,
        });

        // ---------- Percakapan ----------
        // config: { workspaceId, members: UserMini[], tabOpen: bool }
        // Elemen akar disimpan di closure (bukan this.$root/this.$refs): method bisa dipanggil dari elemen di dalam
        // x-if yang kemudian dihapus (mis. tombol "Coba lagi"), dan magic dari elemen terlepas bernilai undefined.
        Alpine.data('wsChannels', (config) => {
            let root = null;
            let searchTimer = null;
            let searchReq = 0;
            const scroller = () => root && root.querySelector('[data-scroller]');
            const threadScroller = () => root && root.querySelector('[data-thread-scroller]');
            // Objek baris yang tidak berubah dipakai ulang: x-for lalu tidak memperbarui (dan x-html tidak
            // merender ulang) ratusan pesan lama setiap kali satu pesan baru masuk lewat polling.
            const rowCache = new Map();
            const reuse = (row, same) => {
                const old = rowCache.get(row.key);
                if (old && same(old)) return old;
                rowCache.set(row.key, row);
                return row;
            };
            return {
                view: 'list',        // HP: 'list' | 'chat'
                cache: {},           // channelId → { messages, cursor, serverTime, hasMore, loading, loadingOlder, error, dividerAfter }
                tabOpen: config.tabOpen,
                atBottom: true,
                stickUntil: 0,
                newBelow: 0,
                highlightId: null,
                editingId: null,
                editBody: '',
                savingEdit: false,
                deleting: null,
                deleteOpen: false,
                removing: false,
                poller: null,
                seq: 0,
                // Panel samping: 'thread' | 'search' | 'pins' | null
                panel: null,
                thread: { id: null, parent: null, replies: [], loading: false, error: null },
                search: { q: '', results: [], total: 0, loading: false, error: null, done: false },
                pins: { items: [], loading: false, error: null },
                canCreateChannel: !!config.canCreateChannel,
                channelForm: { open: false, editing: null, name: '', description: '', is_private: false, member_ids: [], errors: {}, saving: false },
                archiveOpen: false,
                archiving: false,
                members: config.members,

                init() {
                    root = this.$el;
                    const store = this.$store.wsChat;
                    const url = new URL(window.location.href);
                    const fromUrl = store.byId(Number(url.searchParams.get('channel')));
                    const first = fromUrl
                        || store.channels.find((c) => c.workspace_id !== null && c.unread_count > 0)
                        || store.channels.find((c) => c.workspace_id !== null)
                        || store.channels[0];
                    this.highlightId = Number(url.searchParams.get('message')) || null;
                    if (first) store.activeId = first.id;
                    if (fromUrl) this.view = 'chat';
                    this.poller = WS.createPoller(() => this.poll(), { min: 5000, max: 30000 });
                    window.addEventListener('ws-tab-changed', (e) => this.onTab(e.detail));
                    let frame = null;
                    window.addEventListener('resize', () => {
                        cancelAnimationFrame(frame);
                        frame = requestAnimationFrame(() => this.fit());
                    });
                    this.fit();
                    this.listenRealtime();
                    // Tab ini dirender malas: saat pertama dibuka, event ws-tab-changed terjadi sebelum komponen ini ada,
                    // jadi status "tab terbuka" diambil dari alamat (select() sudah memperbaruinya).
                    if (!this.tabOpen) this.tabOpen = new URL(window.location.href).searchParams.get('tab') === 'channel';
                    if (this.tabOpen) this.activate();
                },

                // Realtime: sinyal "pesan berubah" → polling channel aktif saat itu juga; channel lain → refresh badge.
                listenRealtime() {
                    if (!WS.realtime) return;
                    const handler = (e) => {
                        const id = Number(e && e.channel_id);
                        if (this.tabOpen && id === this.$store.wsChat.activeId) this.poller.now();
                        else this.$store.wsNotif.refresh().catch(() => {});
                    };
                    WS.realtime.subscribe('workspace.' + config.workspaceId, 'message.changed', handler);
                    WS.realtime.subscribe('announcements', 'message.changed', handler);
                },

                onTab(tab) {
                    this.tabOpen = tab === 'channel';
                    this.$store.wsChat.viewing = this.tabOpen;
                    if (this.tabOpen) this.activate();
                    else this.poller.stop();
                },

                activate() {
                    const store = this.$store.wsChat;
                    store.viewing = true;
                    if (store.activeId) this.open(store.activeId, { keepView: true });
                    this.poller.start();
                    this.$nextTick(() => this.reveal());
                },

                // Tinggi panel = tinggi area konten (<main>) dikurangi baris tab (desktop) dan jarak tepi.
                // Dihitung dari DOM karena tinggi header layout berbeda per role dan per ukuran layar.
                fit() {
                    const main = root.closest('main');
                    if (!main) return;
                    const bar = document.getElementById('ws-tabbar');
                    const height = narrow()
                        ? Math.max(460, main.clientHeight - 16)
                        : Math.max(520, main.clientHeight - (bar ? bar.offsetHeight : 48) - 48);
                    root.style.setProperty('--ws-chat-h', height + 'px');
                },

                // Geser area konten supaya baris tab (desktop) atau panel chat (HP) berada di atas,
                // sehingga daftar channel, pesan, dan komposer terlihat tanpa scroll halaman.
                reveal() {
                    const main = root.closest('main');
                    const anchor = narrow() ? root : document.getElementById('ws-tabbar');
                    if (!main || !anchor) return;
                    main.scrollTo({ top: Math.max(0, offsetWithin(anchor, main) - (narrow() ? 8 : 16)), behavior: WS.scrollBehavior() });
                },

                get channel() {
                    return this.$store.wsChat.byId(this.$store.wsChat.activeId);
                },

                get state() {
                    return this.cache[this.$store.wsChat.activeId] || null;
                },

                get postNote() {
                    const c = this.channel;
                    if (!c || c.can.post) return '';
                    if (c.type === 'announcement') return 'Hanya mentor dan admin yang dapat memposting di #pengumuman.';
                    if (c.is_archived) return this.$store.wsChat.readOnly ? 'Workspace diarsipkan. Pesan hanya bisa dibaca.' : 'Channel ini diarsipkan. Pesan hanya bisa dibaca.';
                    if (c.type === 'direct') return 'Percakapan privat intern dan mentor. Anda hanya dapat membaca.';
                    return 'Anda hanya dapat membaca channel ini.';
                },

                get subtitle() {
                    const c = this.channel;
                    if (!c) return '';
                    const parts = [];
                    if (c.description) parts.push(c.description);
                    if (c.members_count) parts.push(c.members_count + ' anggota');
                    return parts.join(' · ');
                },

                async open(id, { keepView = false } = {}) {
                    const store = this.$store.wsChat;
                    const switching = store.activeId !== id;
                    store.activeId = id;
                    if (!keepView) this.view = 'chat';
                    this.editingId = null;
                    this.newBelow = 0;
                    if (switching) {
                        this.highlightId = null;
                        this.closePanel();
                    }

                    const url = new URL(window.location.href);
                    url.searchParams.set('channel', id);
                    if (switching) url.searchParams.delete('message');
                    window.history.replaceState(window.history.state, '', url);
                    if (!keepView && narrow()) this.$nextTick(() => this.reveal());

                    const c = store.byId(id);
                    let st = this.cache[id];
                    if (st && !st.error) {
                        st.dividerAfter = c.unread_count > 0 ? st.cursor : null;
                        this.$nextTick(() => { this.scrollToBottom(); this.focusComposer(); });
                        // Ambil pesan yang masuk selama channel ini tidak dibuka.
                        this.poll().catch(() => {}).finally(() => this.markRead());
                        return;
                    }
                    this.cache[id] = { messages: [], cursor: 0, serverTime: null, hasMore: false, loading: true, loadingOlder: false, error: null,
                        dividerAfter: c.unread_count > 0 ? c.last_read_message_id : null };
                    st = this.cache[id];
                    try {
                        const res = await WS.api.messages.list(id);
                        st.messages = res.data;
                        st.cursor = res.data.reduce((max, m) => Math.max(max, m.id), 0);
                        st.serverTime = res.meta.server_time;
                        st.hasMore = !!res.meta.has_more_before;
                    } catch (err) {
                        st.error = err.message;
                    } finally {
                        st.loading = false;
                    }
                    if (store.activeId !== id || st.error) return;
                    this.$nextTick(() => { this.scrollInitial(); this.focusComposer(); });
                    this.markRead();
                },

                retryLoad() {
                    const id = this.$store.wsChat.activeId;
                    delete this.cache[id];
                    this.open(id, { keepView: true });
                },

                back() {
                    this.view = 'list';
                },

                focusComposer() {
                    if (coarse()) return;
                    const input = root.querySelector('[data-composer-input]');
                    if (input && input.offsetParent) input.focus({ preventScroll: true });
                },

                // ---------- Polling ----------

                async poll() {
                    const id = this.$store.wsChat.activeId;
                    const st = this.cache[id];
                    if (!st || st.loading || st.error || !this.tabOpen) return false;
                    const res = await WS.api.messages.list(id, { after: st.cursor, since: st.serverTime });
                    st.serverTime = res.meta.server_time;
                    // Balasan thread (parent_id) tidak masuk daftar utama; induknya datang di `updated` dengan reply_count baru.
                    const added = this.merge(st, res.data.filter((m) => !m.parent_id));
                    const replies = res.data.filter((m) => m.parent_id);
                    replies.forEach((r) => {
                        if (r.id > st.cursor) st.cursor = r.id;
                        this.mergeReply(r);
                    });
                    (res.updated || []).forEach((m) => this.patchMessage(m));
                    if (this.$store.wsChat.activeId === id) this.afterIncoming(added);
                    return added.length > 0 || replies.length > 0 || (res.updated || []).length > 0;
                },

                merge(st, list) {
                    const added = [];
                    list.forEach((m) => {
                        if (m.id > st.cursor) st.cursor = m.id;
                        const i = st.messages.findIndex((x) => x.id === m.id);
                        if (i !== -1) { st.messages.splice(i, 1, m); return; }
                        // Pesan asli disisipkan sebelum pesan yang masih "mengirim".
                        const t = st.messages.findIndex(isTemp);
                        if (t === -1) st.messages.push(m);
                        else st.messages.splice(t, 0, m);
                        added.push(m);
                    });
                    return added;
                },

                replace(st, m) {
                    const i = st.messages.findIndex((x) => x.id === m.id);
                    if (i !== -1) st.messages.splice(i, 1, m);
                },

                // Perbarui pesan di mana pun ia tampil: daftar utama, thread, dan daftar pin.
                patchMessage(m) {
                    const st = this.cache[m.channel_id];
                    if (st) this.replace(st, m);
                    const th = this.thread;
                    if (th.parent && th.parent.id === m.id) th.parent = m;
                    const r = th.replies.findIndex((x) => x.id === m.id);
                    if (r !== -1) th.replies.splice(r, 1, m);
                    const p = this.pins.items.findIndex((x) => x.id === m.id);
                    if (p !== -1) {
                        if (m.is_pinned && !m.is_deleted) this.pins.items.splice(p, 1, m);
                        else this.pins.items.splice(p, 1);
                    }
                },

                findMessage(id) {
                    const st = this.state;
                    return (st && st.messages.find((x) => x.id === id)) || (this.thread.parent && this.thread.parent.id === id ? this.thread.parent : null);
                },

                mergeReply(r) {
                    const th = this.thread;
                    if (this.panel !== 'thread' || th.id !== r.parent_id) return;
                    const i = th.replies.findIndex((x) => x.id === r.id);
                    if (i !== -1) { th.replies.splice(i, 1, r); return; }
                    const t = th.replies.findIndex(isTemp);
                    if (t === -1) th.replies.push(r);
                    else th.replies.splice(t, 0, r);
                    this.$nextTick(() => this.threadToBottom());
                },

                afterIncoming(added) {
                    const fromOthers = added.filter((m) => !m.user || m.user.id !== me.id);
                    if (!fromOthers.length) return;
                    if (this.atBottom) {
                        this.$nextTick(() => this.scrollToBottom(true));
                        this.markRead();
                    } else {
                        this.newBelow += fromOthers.length;
                        if (this.channel) this.channel.unread_count += fromOthers.length;
                    }
                },

                markRead() {
                    const c = this.channel;
                    const st = this.state;
                    if (!c || !st || st.loading || !this.tabOpen || document.hidden) return;
                    const last = st.cursor;
                    if (!last || (c.unread_count === 0 && (c.last_read_message_id || 0) >= last)) return;
                    c.unread_count = 0;
                    c.last_read_message_id = last;
                    WS.api.channels.markRead(c.id, { message_id: last }).catch(() => { /* tidak fatal */ });
                },

                // ---------- Scroll ----------

                onScroll() {
                    const el = scroller();
                    if (!el) return;
                    // Selama animasi scroll ke bawah, posisi dianggap sudah di bawah.
                    this.atBottom = el.scrollHeight - el.scrollTop - el.clientHeight < NEAR_BOTTOM || Date.now() < this.stickUntil;
                    if (this.atBottom && this.newBelow) {
                        this.newBelow = 0;
                        this.markRead();
                    }
                    if (el.scrollTop < 120) this.loadOlder();
                },

                scrollToBottom(smooth = false) {
                    const el = scroller();
                    if (!el) return;
                    if (smooth) this.stickUntil = Date.now() + 1200;
                    el.scrollTo({ top: el.scrollHeight, behavior: smooth ? WS.scrollBehavior() : 'auto' });
                    this.atBottom = true;
                    this.newBelow = 0;
                },

                jumpToLatest() {
                    this.scrollToBottom(true);
                    this.markRead();
                },

                // Buka di posisi: pesan dari link notifikasi → penanda "Pesan baru" → paling bawah.
                async scrollInitial() {
                    const el = scroller();
                    if (!el) return;
                    const id = this.$store.wsChat.activeId;
                    if (this.highlightId) {
                        // Pesan dari link notifikasi bisa lebih lama dari 50 pesan pertama.
                        for (let i = 0; i < 5 && !el.querySelector(`[data-message-id="${this.highlightId}"]`) && this.state.hasMore; i++) {
                            await this.loadOlder();
                            await this.$nextTick();
                        }
                        if (this.$store.wsChat.activeId !== id) return;
                        const target = el.querySelector(`[data-message-id="${this.highlightId}"]`);
                        if (target) {
                            el.scrollTop = target.offsetTop - el.clientHeight / 3;
                            const hid = this.highlightId;
                            setTimeout(() => { if (this.highlightId === hid) this.highlightId = null; }, 2600);
                            this.onScroll();
                            return;
                        }
                        this.highlightId = null;
                    }
                    const divider = el.querySelector('[data-new-divider]');
                    if (divider && divider.offsetTop > el.clientHeight / 2) el.scrollTop = divider.offsetTop - 16;
                    else el.scrollTop = el.scrollHeight;
                    this.onScroll();
                },

                async loadOlder() {
                    const id = this.$store.wsChat.activeId;
                    const st = this.cache[id];
                    if (!st || !st.hasMore || st.loadingOlder || st.loading || !st.messages.length) return;
                    st.loadingOlder = true;
                    const el = scroller();
                    try {
                        const res = await WS.api.messages.list(id, { before: st.messages[0].id });
                        const prevHeight = el.scrollHeight;
                        const prevTop = el.scrollTop;
                        st.messages = res.data.concat(st.messages);
                        st.hasMore = !!res.meta.has_more_before;
                        await this.$nextTick();
                        if (this.$store.wsChat.activeId === id) el.scrollTop = prevTop + (el.scrollHeight - prevHeight);
                    } catch (err) {
                        WS.toast(err.message, 'error');
                    } finally {
                        st.loadingOlder = false;
                    }
                },

                // ---------- Tampilan ----------

                // Baris: pemisah hari, penanda "Pesan baru", dan pesan (compact = lanjutan dari pesan sebelumnya).
                get rows() {
                    const st = this.state;
                    if (!st) return [];
                    const rows = [];
                    let prev = null;
                    let prevDay = null;
                    let dividerPlaced = st.dividerAfter === null || st.dividerAfter === undefined;
                    st.messages.forEach((m) => {
                        const day = WS.activity.dayLabel(m.created_at);
                        if (day !== prevDay) {
                            rows.push(reuse({ type: 'day', key: 'd-' + m.id, label: day }, (o) => o.label === day));
                            prevDay = day;
                            prev = null;
                        }
                        if (!dividerPlaced && !isTemp(m) && m.id > st.dividerAfter) {
                            rows.push(reuse({ type: 'new', key: 'new' }, () => true));
                            dividerPlaced = true;
                            prev = null;
                        }
                        const compact = !!prev && !!prev.user && !!m.user && prev.user.id === m.user.id
                            && new Date(m.created_at) - new Date(prev.created_at) < GROUP_GAP;
                        rows.push(reuse({ type: 'msg', key: 'm-' + m.id, msg: m, compact }, (o) => o.msg === m && o.compact === compact));
                        prev = m;
                    });
                    return rows;
                },

                // Balasan thread: dikelompokkan seperti daftar utama, tanpa pemisah hari.
                get threadRows() {
                    const rows = [];
                    let prev = null;
                    this.thread.replies.forEach((m) => {
                        const compact = !!prev && !!prev.user && !!m.user && prev.user.id === m.user.id
                            && new Date(m.created_at) - new Date(prev.created_at) < GROUP_GAP;
                        rows.push(reuse({ type: 'msg', key: 'r-' + m.id, msg: m, compact }, (o) => o.msg === m && o.compact === compact));
                        prev = m;
                    });
                    return rows;
                },

                get threadParentRow() {
                    const m = this.thread.parent;
                    return m ? reuse({ type: 'msg', key: 'p-' + m.id, msg: m, compact: false }, (o) => o.msg === m) : null;
                },

                mentionsMe(m) {
                    if (m.is_deleted || (m.user && m.user.id === me.id)) return false;
                    return (m.mentions || []).includes(me.id) || !!m.mentions_channel;
                },

                roleLabel(user) {
                    if (!user) return 'Sistem';
                    return { mentor: 'Mentor', admin: 'Admin' }[user.role] || '';
                },

                channelIcon(c) {
                    if (c.type === 'announcement') return 'fa-bullhorn';
                    if (c.is_private) return 'fa-lock';
                    return 'fa-hashtag';
                },

                clock: (iso) => clockFmt.format(new Date(iso)),
                relative: (iso) => WS.time.relative(iso),
                fullTime: (iso) => fullFmt.format(new Date(iso)),
                fileIcon: (a) => WS.files.icon(a.mime),
                fileSize: (bytes) => WS.files.size(bytes),

                // Klik #T-12 di pesan: buka slide-over bila tugas ada di board ini, selain itu ikuti link.
                onContentClick(event) {
                    const link = event.target.closest('a[data-task-id]');
                    if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;
                    const tasks = this.$store.wsTasks;
                    const card = tasks && tasks.booted && tasks.byId(Number(link.dataset.taskId));
                    if (!card) return;
                    event.preventDefault();
                    tasks.openDetail(card);
                },

                // ---------- Kirim ----------

                async sendMessage({ body, attachments, mentionIds, parentId }) {
                    if (parentId) return this.sendReply(parentId, { body, attachments, mentionIds });
                    const c = this.channel;
                    const st = this.state;
                    if (!c || !st) return;
                    st.messages.push({
                        id: 'tmp-' + (++this.seq), channel_id: c.id, parent_id: null, user: meMini,
                        body, body_html: plain(body), attachments, mentions: [], mentions_channel: false,
                        is_pinned: false, edited_at: null, is_deleted: false, reply_count: 0, created_at: new Date().toISOString(),
                        can: { edit: false, delete: false, pin: false },
                        _status: 'sending', _error: '',
                        _payload: { body, attachment_ids: attachments.map((a) => a.id), mention_ids: mentionIds },
                    });
                    const temp = st.messages[st.messages.length - 1]; // versi reaktif
                    this.$nextTick(() => this.scrollToBottom());
                    await this.deliver(c.id, st, temp);
                },

                async deliver(channelId, st, temp) {
                    temp._status = 'sending';
                    temp._error = '';
                    try {
                        const res = await WS.api.messages.send(channelId, temp._payload);
                        const i = st.messages.findIndex((m) => m.id === temp.id);
                        if (i === -1) return;
                        // Polling bisa lebih dulu membawa pesan yang sama.
                        if (st.messages.some((m) => m.id === res.data.id)) st.messages.splice(i, 1);
                        else st.messages.splice(i, 1, res.data);
                        if (this.$store.wsChat.activeId === channelId) this.poller.now();
                    } catch (err) {
                        temp._status = 'failed';
                        temp._error = err.status === 422 ? (Object.values(err.errors)[0] || [err.message])[0] : err.message;
                        if (err.status === 403 || err.status === 429) WS.toast(err.message, 'error');
                    }
                },

                retry(m) {
                    if (m.parent_id) this.deliverReply(this.$store.wsChat.activeId, m);
                    else this.deliver(this.$store.wsChat.activeId, this.state, m);
                },

                discard(m) {
                    if (m.parent_id) {
                        this.thread.replies = this.thread.replies.filter((x) => x.id !== m.id);
                        return;
                    }
                    const st = this.state;
                    st.messages = st.messages.filter((x) => x.id !== m.id);
                },

                // ---------- Thread ----------

                async openThread(target, { focusId = null } = {}) {
                    const id = typeof target === 'object' ? target.id : target;
                    this.panel = 'thread';
                    this.$store.wsChat.threadId = id;
                    this.thread = { id, parent: this.findMessage(id), replies: [], loading: true, error: null };
                    try {
                        const res = await WS.api.messages.replies(id);
                        if (this.thread.id !== id) return;
                        this.thread.replies = res.data;
                        if (!this.thread.parent) this.thread.parent = res.parent;
                    } catch (err) {
                        if (this.thread.id === id) this.thread.error = err.message;
                    } finally {
                        if (this.thread.id === id) this.thread.loading = false;
                    }
                    await this.$nextTick();
                    const el = threadScroller();
                    const target2 = focusId && el && el.querySelector(`[data-message-id="${focusId}"]`);
                    if (target2) {
                        el.scrollTop = target2.offsetTop - el.clientHeight / 3;
                        this.highlightId = focusId;
                        setTimeout(() => { if (this.highlightId === focusId) this.highlightId = null; }, 2600);
                    } else {
                        this.threadToBottom();
                    }
                    if (!coarse()) root.querySelector('[data-thread-composer] [data-composer-input]')?.focus({ preventScroll: true });
                },

                threadToBottom() {
                    const el = threadScroller();
                    if (el) el.scrollTop = el.scrollHeight;
                },

                closePanel() {
                    this.panel = null;
                    this.$store.wsChat.threadId = null;
                    this.thread = { id: null, parent: null, replies: [], loading: false, error: null };
                },

                async sendReply(parentId, { body, attachments, mentionIds }) {
                    const th = this.thread;
                    if (th.id !== parentId) return;
                    th.replies.push({
                        id: 'tmp-' + (++this.seq), channel_id: this.channel.id, parent_id: parentId, user: meMini,
                        body, body_html: plain(body), attachments, mentions: [], mentions_channel: false,
                        is_pinned: false, edited_at: null, is_deleted: false, reply_count: 0, created_at: new Date().toISOString(),
                        can: { edit: false, delete: false, pin: false },
                        _status: 'sending', _error: '',
                        _payload: { body, attachment_ids: attachments.map((a) => a.id), mention_ids: mentionIds, parent_id: parentId },
                    });
                    const temp = th.replies[th.replies.length - 1];
                    this.$nextTick(() => this.threadToBottom());
                    await this.deliverReply(this.channel.id, temp);
                },

                async deliverReply(channelId, temp) {
                    temp._status = 'sending';
                    temp._error = '';
                    try {
                        const res = await WS.api.messages.send(channelId, temp._payload);
                        const th = this.thread;
                        const i = th.replies.findIndex((m) => m.id === temp.id);
                        if (i !== -1) {
                            if (th.replies.some((m) => m.id === res.data.id)) th.replies.splice(i, 1);
                            else th.replies.splice(i, 1, res.data);
                        }
                        // Hitungan di bawah pesan induk langsung naik; server juga mengirim induk di `updated`.
                        const parent = this.findMessage(temp.parent_id);
                        if (parent) {
                            const real = th.replies.filter((m) => !isTemp(m)).length;
                            parent.reply_count = Math.max(parent.reply_count || 0, real);
                            parent.last_reply_at = res.data.created_at;
                        }
                    } catch (err) {
                        temp._status = 'failed';
                        temp._error = err.status === 422 ? (Object.values(err.errors)[0] || [err.message])[0] : err.message;
                        if (err.status === 403 || err.status === 429) WS.toast(err.message, 'error');
                    }
                },

                // ---------- Pesan disematkan ----------

                async openPins() {
                    this.panel = 'pins';
                    this.$store.wsChat.threadId = null;
                    this.pins = { items: [], loading: true, error: null };
                    const id = this.$store.wsChat.activeId;
                    try {
                        const res = await WS.api.messages.list(id, { pinned: 1 });
                        if (this.panel === 'pins' && this.$store.wsChat.activeId === id) this.pins.items = res.data;
                    } catch (err) {
                        this.pins.error = err.message;
                    } finally {
                        this.pins.loading = false;
                    }
                },

                async togglePin(m) {
                    const pin = !m.is_pinned;
                    try {
                        const res = await WS.api.messages.pin(m.id, { is_pinned: pin });
                        this.patchMessage(res.data);
                        const c = this.channel;
                        if (c) c.pinned_count = Math.max(0, (c.pinned_count || 0) + (pin ? 1 : -1));
                        if (pin && this.panel === 'pins') this.pins.items.unshift(res.data);
                        WS.toast(pin ? 'Pesan disematkan di channel.' : 'Sematan dilepas.', 'success');
                    } catch (err) {
                        WS.toast(err.message, 'error');
                    }
                },

                // ---------- Pencarian ----------

                openSearch() {
                    this.panel = 'search';
                    this.$store.wsChat.threadId = null;
                    this.$nextTick(() => root.querySelector('[data-search-input]')?.focus());
                },

                onSearchInput() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => this.runSearch(), 300);
                },

                async runSearch() {
                    const q = this.search.q.trim();
                    if (q.length < 2) {
                        Object.assign(this.search, { results: [], total: 0, done: false, error: null, loading: false });
                        return;
                    }
                    const req = ++searchReq;
                    this.search.loading = true;
                    this.search.error = null;
                    try {
                        const res = await WS.api.messages.list(this.$store.wsChat.activeId, { q });
                        if (req !== searchReq) return;
                        Object.assign(this.search, { results: res.data, total: res.meta.total, done: true });
                    } catch (err) {
                        if (req === searchReq) this.search.error = err.message;
                    } finally {
                        if (req === searchReq) this.search.loading = false;
                    }
                },

                excerpt: (m, q) => excerpt(m.body, q),

                // ---------- Reaksi ----------

                reactions: REACTIONS,

                // Boleh bereaksi bila boleh mengirim pesan; #pengumuman juga boleh walau intern tidak bisa posting.
                get canReact() {
                    const c = this.channel;
                    return !!c && !c.is_archived && !this.$store.wsChat.readOnly && (c.can.post || c.type === 'announcement');
                },

                // Toggle optimistis; server mengembalikan daftar reaksi terbaru pesan itu.
                async react(m, emoji) {
                    if (!this.canReact || isTemp(m) || m.is_deleted) return;
                    const before = JSON.parse(JSON.stringify(m.reactions || []));
                    const list = before.map((r) => Object.assign({}, r));
                    const r = list.find((x) => x.emoji === emoji);
                    if (r && r.me) { r.count -= 1; r.me = false; }
                    else if (r) { r.count += 1; r.me = true; }
                    else list.push({ emoji, count: 1, me: true });
                    m.reactions = list.filter((x) => x.count > 0);
                    try {
                        const res = await WS.api.messages.react(m.id, { emoji });
                        m.reactions = res.data;
                    } catch (err) {
                        m.reactions = before;
                        WS.toast(err.message, 'error');
                    }
                },

                reactLabel(r) {
                    return r.emoji + ' ' + r.count + ' reaksi' + (r.me ? ', termasuk Anda. Klik untuk membatalkan' : '. Klik untuk ikut bereaksi');
                },

                // Dari hasil cari / daftar pin: buka pesan di daftar utama, atau buka thread-nya.
                async jumpTo(m) {
                    if (m.parent_id) {
                        this.openThread(m.parent_id, { focusId: m.id });
                        return;
                    }
                    if (narrow()) this.closePanel();
                    this.highlightId = m.id;
                    await this.$nextTick();
                    await this.scrollInitial();
                },

                // ---------- Kelola channel (topik) ----------

                openChannelForm(channel = null) {
                    this.channelForm = {
                        open: true, editing: channel, errors: {}, saving: false,
                        name: channel ? channel.name : '',
                        description: channel ? (channel.description || '') : '',
                        is_private: channel ? !!channel.is_private : false,
                        member_ids: [me.id],
                    };
                },

                onChannelName(event) {
                    this.channelForm.name = slugify(event.target.value);
                    event.target.value = this.channelForm.name;
                },

                toggleMember(id) {
                    if (id === me.id) return; // pembuat selalu anggota
                    const list = this.channelForm.member_ids;
                    const i = list.indexOf(id);
                    if (i === -1) list.push(id);
                    else list.splice(i, 1);
                },

                validateChannel() {
                    const f = this.channelForm;
                    const errors = {};
                    const taken = this.$store.wsChat.channels.some((c) => c.name === f.name && (!f.editing || c.id !== f.editing.id));
                    if (f.name.length < 2) errors.name = 'Nama minimal 2 karakter.';
                    else if (!/^[a-z0-9][a-z0-9-]*$/.test(f.name)) errors.name = 'Gunakan huruf kecil, angka, dan tanda hubung.';
                    else if (taken) errors.name = 'Nama channel sudah dipakai.';
                    if (f.description.length > 120) errors.description = 'Deskripsi maksimal 120 karakter.';
                    if (!f.editing && f.is_private && f.member_ids.length < 2) errors.member_ids = 'Pilih minimal satu anggota lain.';
                    f.errors = errors;
                    return !Object.keys(errors).length;
                },

                async saveChannel() {
                    const f = this.channelForm;
                    if (!this.validateChannel()) return;
                    f.saving = true;
                    try {
                        if (f.editing) {
                            const res = await WS.api.channels.update(f.editing.id, { name: f.name, description: f.description || null });
                            Object.assign(f.editing, res.data);
                            WS.toast('Info channel diperbarui.', 'success');
                        } else {
                            const res = await WS.api.channels.create(config.workspaceId, {
                                name: f.name, description: f.description || null, is_private: f.is_private,
                                member_ids: f.is_private ? f.member_ids : [],
                            });
                            this.$store.wsChat.add(res.data);
                            WS.toast(`#${res.data.name} dibuat.`, 'success');
                            this.open(res.data.id);
                        }
                        f.open = false;
                    } catch (err) {
                        if (err.status === 422) f.errors = Object.fromEntries(Object.entries(err.errors).map(([k, v]) => [k, v[0]]));
                        else WS.toast(err.message, 'error');
                    } finally {
                        f.saving = false;
                    }
                },

                async toggleMute() {
                    const c = this.channel;
                    if (!c) return;
                    const muted = !c.is_muted;
                    c.is_muted = muted;
                    try {
                        const res = await WS.api.channels.mute(c.id, { is_muted: muted });
                        Object.assign(c, res.data);
                        WS.toast(muted ? `Notifikasi ${this.$store.wsChat.title(c)} dibisukan. Mention tetap diberitahukan.` : 'Notifikasi channel diaktifkan lagi.', 'success');
                    } catch (err) {
                        c.is_muted = !muted;
                        WS.toast(err.message, 'error');
                    }
                },

                async confirmArchive() {
                    const c = this.channel;
                    if (!c) return;
                    this.archiving = true;
                    try {
                        const res = await WS.api.channels.update(c.id, { is_archived: !c.is_archived });
                        Object.assign(c, res.data);
                        this.archiveOpen = false;
                        WS.toast(c.is_archived ? `#${c.name} diarsipkan. Pesan tetap bisa dibaca.` : `#${c.name} dipulihkan.`, 'success');
                    } catch (err) {
                        WS.toast(err.message, 'error');
                    } finally {
                        this.archiving = false;
                    }
                },

                // ---------- Edit & hapus ----------

                startEdit(m) {
                    this.editingId = m.id;
                    this.editBody = m.body;
                    this.$nextTick(() => {
                        const input = root.querySelector('[data-edit-input]');
                        if (!input) return;
                        input.focus();
                        input.setSelectionRange(input.value.length, input.value.length);
                        input.style.height = 'auto';
                        input.style.height = input.scrollHeight + 'px';
                    });
                },

                cancelEdit() {
                    this.editingId = null;
                    this.editBody = '';
                },

                onEditKey(event, m) {
                    if (event.key === 'Escape') { event.preventDefault(); this.cancelEdit(); }
                    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing && !coarse()) { event.preventDefault(); this.saveEdit(m); }
                },

                async saveEdit(m) {
                    const body = this.editBody.trim();
                    if (body === m.body) return this.cancelEdit();
                    if (!body && !(m.attachments || []).length) {
                        WS.toast('Pesan tidak boleh kosong. Gunakan Hapus untuk menghapus pesan.', 'error');
                        return;
                    }
                    this.savingEdit = true;
                    try {
                        const res = await WS.api.messages.update(m.id, { body });
                        this.patchMessage(res.data);
                        this.cancelEdit();
                    } catch (err) {
                        WS.toast(err.message, 'error');
                    } finally {
                        this.savingEdit = false;
                    }
                },

                askDelete(m) {
                    this.deleting = m;
                    this.deleteOpen = true;
                },

                async confirmDelete() {
                    const m = this.deleting;
                    if (!m) return;
                    this.removing = true;
                    try {
                        const res = await WS.api.messages.remove(m.id);
                        this.patchMessage(res.data);
                        this.deleteOpen = false;
                        WS.toast('Pesan dihapus.', 'success');
                    } catch (err) {
                        WS.toast(err.message, 'error');
                    } finally {
                        this.removing = false;
                    }
                },

                async copyText(m) {
                    try {
                        await navigator.clipboard.writeText(m.body);
                        WS.toast('Teks pesan disalin.', 'success');
                    } catch (_) {
                        WS.toast('Tidak dapat menyalin teks.', 'error');
                    }
                },
            };
        });

        // ---------- Komposer ----------
        // config: { members: UserMini[], mode?: 'channel' | 'thread' } — kandidat @mention selain anggota DM.
        // Mode thread: draf per pesan induk, kiriman membawa parentId.
        Alpine.data('wsComposer', (config) => ({
            mode: config.mode || 'channel',
            body: '',
            files: [],
            mentionIds: [],
            drafts: {},
            current: null,
            dragging: false,
            seq: 0,
            suggest: { open: false, type: '@', start: 0, items: [], index: 0 },
            accept: WS.files.ACCEPT,
            touch: coarse(),

            init() {
                const key = this.mode === 'thread' ? '$store.wsChat.threadId' : '$store.wsChat.activeId';
                this.current = this.mode === 'thread' ? this.$store.wsChat.threadId : this.$store.wsChat.activeId;
                // Draf per channel (atau per thread): isi komposer tidak hilang saat berpindah.
                this.$watch(key, (id) => {
                    this.drafts[this.current] = this.body;
                    this.current = id;
                    this.body = this.drafts[id] || '';
                    this.files = [];
                    this.mentionIds = [];
                    this.close();
                    this.$nextTick(() => this.autosize());
                });
            },

            get channel() {
                return this.$store.wsChat.byId(this.$store.wsChat.activeId);
            },

            get placeholder() {
                const c = this.channel;
                if (!c) return '';
                if (this.mode === 'thread') return 'Balas di thread…';
                return 'Kirim pesan ke ' + (c.type === 'direct' ? WS.chat.title(c) : '#' + c.name);
            },

            get uploading() {
                return this.files.some((f) => f.status === 'uploading');
            },

            get canSend() {
                return !this.uploading && (this.body.trim().length > 0 || this.files.some((f) => f.status === 'done'));
            },

            get people() {
                const c = this.channel;
                const list = c && c.type === 'direct' ? (c.members || []) : config.members;
                return list.filter((p) => p.id !== me.id);
            },

            autosize() {
                const el = this.$refs.input;
                if (!el) return;
                el.style.height = 'auto';
                el.style.height = Math.min(el.scrollHeight, 168) + 'px';
            },

            // ---------- Lampiran ----------

            pick() {
                this.$refs.file.click();
            },

            onFiles(event) {
                this.addFiles([...event.target.files]);
                event.target.value = '';
            },

            onDrop(event) {
                this.dragging = false;
                this.addFiles([...event.dataTransfer.files]);
            },

            onPaste(event) {
                const files = [...((event.clipboardData && event.clipboardData.files) || [])];
                if (!files.length) return;
                event.preventDefault();
                this.addFiles(files);
            },

            addFiles(list) {
                list.forEach((file) => {
                    this.files.push(WS.files.item(file, ++this.seq));
                    const item = this.files[this.files.length - 1];
                    if (item.status === 'uploading') WS.files.upload(file, item);
                });
                this.$refs.input.focus();
            },

            removeFile(key) {
                this.files = this.files.filter((f) => f.key !== key);
            },

            // ---------- @mention & #tugas ----------

            detect() {
                const el = this.$refs.input;
                const pos = el.selectionStart;
                if (pos !== el.selectionEnd) return this.close();
                const match = this.body.slice(0, pos).match(/(^|\s)([@#])([^\s@#]{0,30})$/);
                if (!match) return this.close();
                const type = match[2];
                const query = match[3].toLowerCase();
                const items = type === '@' ? this.mentionItems(query) : this.taskItems(query);
                if (!items.length) return this.close();
                this.suggest = { open: true, type, start: pos - match[3].length - 1, items, index: 0 };
            },

            mentionItems(query) {
                const words = (name) => name.toLowerCase().split(/\s+/);
                const items = this.people
                    .filter((p) => !query || words(p.name).some((w) => w.startsWith(query)) || p.name.toLowerCase().startsWith(query))
                    .slice(0, 6)
                    .map((p) => ({ key: 'u' + p.id, user: p, label: p.name, hint: { mentor: 'Mentor', admin: 'Admin' }[p.role] || 'Intern', insert: '@' + p.name + ' ' }));
                // @channel hanya untuk mentor/admin (server tetap memvalidasi).
                const c = this.channel;
                if (me.role !== 'intern' && c && c.type !== 'direct' && 'channel'.startsWith(query)) {
                    items.unshift({ key: 'all', user: null, label: 'channel', hint: 'Beri tahu semua anggota', insert: '@channel ' });
                }
                return items;
            },

            taskItems(query) {
                const c = this.channel;
                const store = this.$store.wsTasks;
                if (!c || c.workspace_id === null || !store || !store.booted) return [];
                return store.tasks
                    .filter((t) => !query || t.code.toLowerCase().includes(query) || t.title.toLowerCase().includes(query))
                    .slice(0, 6)
                    .map((t) => ({ key: 't' + t.id, task: t, label: t.code, hint: t.title, insert: '#' + t.code + ' ' }));
            },

            choose(item) {
                const el = this.$refs.input;
                const end = el.selectionStart;
                this.body = this.body.slice(0, this.suggest.start) + item.insert + this.body.slice(end);
                if (item.user) this.mentionIds.push(item.user.id);
                const caret = this.suggest.start + item.insert.length;
                this.close();
                this.$nextTick(() => {
                    el.focus();
                    el.setSelectionRange(caret, caret);
                    this.autosize();
                });
            },

            close() {
                this.suggest.open = false;
            },

            emojiGroups: EMOJI_GROUPS,
            emojiOpen: false,

            insertEmoji(emoji) {
                const el = this.$refs.input;
                const start = el.selectionStart ?? this.body.length;
                const end = el.selectionEnd ?? start;
                this.body = this.body.slice(0, start) + emoji + this.body.slice(end);
                this.emojiOpen = false;
                this.$nextTick(() => {
                    el.focus();
                    el.setSelectionRange(start + emoji.length, start + emoji.length);
                    this.autosize();
                });
            },

            insertMention() {
                const el = this.$refs.input;
                const pos = el.selectionStart;
                const before = this.body.slice(0, pos);
                const insert = (before && !/\s$/.test(before) ? ' ' : '') + '@';
                this.body = before + insert + this.body.slice(pos);
                this.$nextTick(() => {
                    el.focus();
                    el.setSelectionRange(pos + insert.length, pos + insert.length);
                    this.detect();
                });
            },

            onKeydown(event) {
                if (this.suggest.open) {
                    const n = this.suggest.items.length;
                    if (event.key === 'ArrowDown') { event.preventDefault(); this.suggest.index = (this.suggest.index + 1) % n; return; }
                    if (event.key === 'ArrowUp') { event.preventDefault(); this.suggest.index = (this.suggest.index - 1 + n) % n; return; }
                    if (event.key === 'Enter' || event.key === 'Tab') { event.preventDefault(); this.choose(this.suggest.items[this.suggest.index]); return; }
                    if (event.key === 'Escape') { event.preventDefault(); this.close(); return; }
                }
                // Di HP Enter = baris baru; kirim lewat tombol.
                if (event.key === 'Enter' && !event.shiftKey && !event.isComposing && !this.touch) {
                    event.preventDefault();
                    this.submit();
                }
            },

            submit() {
                if (!this.canSend) {
                    if (this.uploading) WS.toast('Tunggu lampiran selesai diunggah.', 'info');
                    return;
                }
                const body = this.body.trim();
                const attachments = this.files.filter((f) => f.status === 'done').map((f) => f.attachment);
                // Hanya mention yang namanya masih ada di teks.
                const mentionIds = [...new Set(this.mentionIds)].filter((id) => {
                    const p = this.people.find((x) => x.id === id);
                    return p && body.includes('@' + p.name);
                });
                this.$dispatch('ws-chat-send', { body, attachments, mentionIds, parentId: this.mode === 'thread' ? this.current : null });
                this.body = '';
                this.files = [];
                this.mentionIds = [];
                this.drafts[this.current] = '';
                this.close();
                this.$nextTick(() => this.autosize());
            },

            fileSize: (bytes) => WS.files.size(bytes),
        }));
    });
})();
