/*
 * Chat Publik untuk pengguna yang sudah login (semua peran).
 *
 * Dimuat di SEMUA halaman setelah login (resources/views/public-chat.blade.php) dengan dua mode:
 *   - mode lencana: di halaman biasa, hanya menjaga lencana merah "pesan belum dibaca" pada menu Chat Publik di sidebar ([data-pc-badge]).
 *   - mode halaman: di /chat-publik (resources/views/chat-publik/index.blade.php, ada #pc-root) menggerakkan percakapan dan panelnya.
 *
 * Halaman dua panel: kiri = pencarian + saringan peran + daftar orang di ruang (dibentuk dari pengirim pesan, tanpa endpoint tambahan),
 * kanan = header, bilah pengumuman tersemat, percakapan, dan kotak tulis.
 * Pengumuman tersemat: mentor dapat menyematkan pesannya sendiri selama 7 atau 30 hari (maks. 3 aktif), tampil untuk semua peran,
 * berakhir otomatis, dan dapat dihapus kapan saja oleh penulisnya.
 *
 * Lapisan data dipisah (objek `api`) supaya bisa dialihkan ke server tanpa mengubah tampilan:
 *   - mock (bawaan): data contoh tersimpan di localStorage browser, pengguna lain "mengobrol" otomatis.
 *   - server: bila window.PublicChatConfig.endpoints diisi ({ list, send }) dan mock = false. Kontrak: docs/public-chat-backend.md.
 * Semua teks pesan dirender dengan textContent (tanpa HTML dan tanpa auto-link) agar aman dari XSS dan tautan jahat.
 */
(function () {
    'use strict';

    const root = document.getElementById('pc-root');
    const PAGE = !!root;
    const $ = (id) => document.getElementById(id);
    const el = PAGE ? {
        online: $('pc-online'), members: $('pc-members'), rules: $('pc-rules'), rulesX: $('pc-rules-x'),
        search: $('pc-search'), chips: $('pc-chips'), people: $('pc-people'), openList: $('pc-open-list'), listClose: $('pc-list-close'), hsearch: $('pc-hsearch'),
        pinwrap: $('pc-pinwrap'), pinbar: $('pc-pinbar'), pindrop: $('pc-pindrop'),
        list: $('pc-list'), jump: $('pc-jump'), jumpN: $('pc-jump-n'),
        compose: $('pc-compose'), text: $('pc-text'), send: $('pc-send'), me: $('pc-me'), count: $('pc-count'), err: $('pc-err'),
        ann: $('pc-ann'), annbar: $('pc-annbar'), emoji: $('pc-emoji'), emojiBtn: $('pc-emoji-btn'),
    } : {};
    const badges = Array.from(document.querySelectorAll('[data-pc-badge]'));
    const cfg = Object.assign({ mock: true, endpoints: null, maxLen: 10000, maxPins: 3, rateMs: 600, perMinute: 40, pollViewMs: 5000, pollIdleMs: 30000, user: {} }, window.PublicChatConfig || {});

    const ROLES = { admin: 'ADMIN', mentor: 'MENTOR', intern: 'PESERTA', institusi: 'INSTITUSI', industri: 'INDUSTRI' };
    const ROLE_CHIP = { mentor: 'Mentor', intern: 'Peserta', institusi: 'Institusi', industri: 'Industri', admin: 'Admin' };
    const me = { id: String(cfg.user.id || 'tamu'), name: String(cfg.user.name || 'Pengguna'), role: ROLES[cfg.user.role] ? cfg.user.role : 'intern' };
    const canPin = me.role === 'mentor';
    const DAY = 864e5;

    const LS = { msgs: 'pc:msgs:v3', rules: 'pc:rules-hidden', seen: 'pc:seen:' + String(cfg.user.id || 'tamu') };
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch (_) { return null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch (_) { /* penyimpanan tidak tersedia */ } },
        json(k, d) { try { return JSON.parse(localStorage.getItem(k)) || d; } catch (_) { return d; } },
    };

    // ---------- Util ----------
    const timeFmt = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' });
    const dayFmt = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
    const shortFmt = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: '2-digit', year: '2-digit' });
    const hash = (s) => [...s].reduce((a, c) => (a * 31 + c.charCodeAt(0)) >>> 0, 7);
    const hueOf = (name) => hash(name.toLowerCase()) % 360;
    const initial = (name) => (name.trim().match(/[\p{L}\p{N}]/u) || ['?'])[0].toUpperCase();
    const cleanText = (s) => s.replace(/\r/g, '').replace(/\n{3,}/g, '\n\n').trim();
    const sameDay = (a, b) => new Date(a).toDateString() === new Date(b).toDateString();
    const roleOf = (m) => (ROLES[m.role] ? m.role : (m.admin ? 'admin' : 'intern'));
    const mk = (tag, cls, txt) => { const n = document.createElement(tag); if (cls) n.className = cls; if (txt != null) n.textContent = txt; return n; };
    // Ikon statis (bukan data pengguna), aman dimasukkan sebagai markup.
    const ICON = {
        pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 17v5"/><path d="M9 3h6l-1 6 3 3v2H7v-2l3-3-1-6Z"/></svg>',
        chev: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>',
        chat: '<svg viewBox="0 0 36 36" fill="none" aria-hidden="true"><path d="M9 9.5h13a6 6 0 0 1 6 6v6.5a6 6 0 0 1-6 6h-6.6l-5.5 4.4a.8.8 0 0 1-1.3-.62V28A6 6 0 0 1 3 22v-6.500a6 6 0 0 1 6-6Z" fill="#fff"/><circle cx="10.500" cy="19" r="1.900" fill="#2563eb"/><circle cx="16" cy="19" r="1.900" fill="#2563eb"/><circle cx="21.500" cy="19" r="1.900" fill="#2563eb"/></svg>',
    };
    const icon = (name, cls) => { const s = mk('span', cls); s.innerHTML = ICON[name]; return s; };
    // Teks panjang dipangkas beberapa baris dengan tombol "Selengkapnya" (isi lengkap tetap ada di DOM, tidak dipotong).
    const isLong = (txt) => txt.length > 420 || (txt.match(/\n/g) || []).length >= 7;
    function moreBtn() {
        const b = mk('button', 'pc-more', 'Selengkapnya'); b.type = 'button';
        b.dataset.act = 'toggle-text'; b.setAttribute('aria-expanded', 'false');
        return b;
    }
    function timeLeft(until) {
        const ms = until - Date.now();
        if (ms >= DAY) return 'Sisa ' + Math.ceil(ms / DAY) + ' hari';
        const h = Math.ceil(ms / 36e5);
        return h > 1 ? 'Sisa ' + h + ' jam' : 'Berakhir sebentar lagi';
    }
    function rowTime(ts) {
        const d = new Date(ts), t = new Date();
        const diff = Math.round((new Date(t.getFullYear(), t.getMonth(), t.getDate()) - new Date(d.getFullYear(), d.getMonth(), d.getDate())) / DAY);
        return diff <= 0 ? timeFmt.format(d) : diff === 1 ? 'Kemarin' : shortFmt.format(d);
    }

    // ---------- Lapisan data ----------
    // [nama, peran, teks, menit relatif dari sekarang, (lama sematan dalam hari)]
    const SEED = [
        ['Herman', 'mentor', 'Pengumuman: presentasi progres peserta magang dilaksanakan Jumat, 20 November pukul 09.00 di ruang rapat lantai 2. Mohon seluruh peserta hadir dan membawa bahan presentasi.', -4320, 7],
        ['Rudy', 'mentor', 'Pengingat: logbook mingguan dikumpulkan paling lambat Jumat pukul 16.00 lewat menu Logbook.', -1500, 30],
        ['Admin SIMAGANG', 'admin', 'Selamat datang di Chat Publik SIMAGANG. Ruang obrolan ini terbuka untuk peserta, mentor, institusi, dan industri.', -92],
        ['Rina', 'intern', 'Selamat pagi semua, ada yang tahu jadwal bimbingan minggu ini?', -74],
        ['Herman', 'mentor', 'Pagi Rina. Jadwal bimbingan selalu diumumkan di Workspace setiap Senin ya.', -71],
        ['Dimas', 'intern', 'Untuk logbook, batas pengumpulannya jam berapa kak?', -55],
        ['Admin SIMAGANG', 'admin', 'Logbook bisa dikumpulkan sampai pukul 23.59 di hari yang sama.', -52],
        ['SMKN 2 Makassar', 'institusi', 'Terima kasih informasinya, sangat membantu 🙏', -30],
    ];
    const INCOMING = [
        ['Fajar', 'intern', 'Apakah laporan mingguan boleh dikirim lewat Workspace langsung?'],
        ['Herman', 'mentor', 'Pengingat: review tugas minggu ini dibuka sampai Jumat sore.'],
        ['Bagas', 'intern', 'Halo semua, baru gabung 👋'],
        ['PT Nusantara Digital', 'industri', 'Kami membuka kuota baru untuk periode berikutnya, silakan cek menu Lowongan.'],
    ];
    const ADMIN_REPLIES = [
        'Terima kasih sudah bertanya. Pertanyaan Anda kami catat dan akan dijawab oleh tim kami.',
        'Baik, untuk informasi resmi silakan cek menu terkait di dashboard Anda atau Panduan di Workspace.',
    ];
    const EMOJIS = ['😀', '😂', '😊', '😍', '😎', '🤔', '😢', '😮', '👍', '👏', '🙏', '💪', '👌', '🎉', '🔥', '❤️', '✅', '⭐', '📌', '📢', '📅', '📝', '📚', '💡'];

    const mock = {
        msgs: store.json(LS.msgs, null),
        listeners: [],
        seq: 0,
        init() {
            if (!Array.isArray(this.msgs) || !this.msgs.length) {
                const now = Date.now();
                this.msgs = SEED.map(([name, role, text, min, pinDays], i) => {
                    const ts = now + min * 60000;
                    const m = { id: 'm' + i, uid: 'seed-' + name, name, role, admin: role === 'admin', text, ts };
                    if (pinDays) { m.pinned_at = ts; m.pinned_until = ts + pinDays * DAY; }
                    return m;
                });
            }
            this.seq = this.msgs.length + 100;
        },
        persist() { store.set(LS.msgs, JSON.stringify(this.msgs.slice(-60))); },
        pins() {
            const now = Date.now();
            return this.msgs.filter((m) => m.pinned_until > now).sort((a, b) => (b.pinned_at || b.ts) - (a.pinned_at || a.ts)).map((m) => Object.assign({}, m));
        },
        async list() { return { messages: this.msgs.slice(), online: this.online(), pins: this.pins() }; },
        online() { return 11 + (Math.floor(Date.now() / 45000) % 7); },
        checkPin(id, days) {
            if (days !== 7 && days !== 30) throw new Error('Lama sematan tidak valid.');
            if (this.pins().filter((p) => p.id !== id).length >= cfg.maxPins) throw new Error('Maksimal ' + cfg.maxPins + ' pengumuman aktif. Hapus salah satu dulu.');
        },
        async send(text, pinDays) {
            if (pinDays) { if (!canPin) throw new Error('Hanya mentor yang dapat membuat pengumuman.'); this.checkPin(null, pinDays); }
            const m = { id: 'm' + (++this.seq), uid: me.id, name: me.name, role: me.role, admin: me.role === 'admin', text, ts: Date.now() };
            if (pinDays) { m.pinned_at = m.ts; m.pinned_until = m.ts + pinDays * DAY; }
            this.msgs.push(m); this.persist();
            if (!pinDays) setTimeout(() => this.push({ uid: 'seed-Admin SIMAGANG', name: 'Admin SIMAGANG', role: 'admin', admin: true, text: ADMIN_REPLIES[this.seq % ADMIN_REPLIES.length] }), 2600);
            return m;
        },
        async pin(id, days) {
            const m = this.msgs.find((x) => x.id === id);
            if (!m) throw new Error('Pesan tidak ditemukan.');
            if (!canPin || m.uid !== me.id) throw new Error('Hanya mentor penulis yang dapat menyematkan pesannya.');
            this.checkPin(id, days);
            m.pinned_at = Date.now(); m.pinned_until = m.pinned_at + days * DAY; this.persist();
            return { pins: this.pins() };
        },
        async unpin(id) {
            const m = this.msgs.find((x) => x.id === id);
            if (!m || m.uid !== me.id) throw new Error('Hanya penulis yang dapat menghapus pengumuman ini.');
            delete m.pinned_at; delete m.pinned_until; this.persist();
            return { pins: this.pins() };
        },
        push(partial) {
            const m = Object.assign({ id: 'm' + (++this.seq), ts: Date.now() }, partial);
            this.msgs.push(m); this.persist();
            this.listeners.forEach((fn) => fn(m));
        },
        onIncoming(fn) { this.listeners.push(fn); },
        simulate() {
            let i = 0;
            const tick = () => {
                // Sedang dibaca: lebih sering. Di halaman lain: sedikit saja (cukup untuk memperlihatkan lencana).
                const [name, role, text] = INCOMING[i++ % INCOMING.length];
                if (viewing() || unread < 6) this.push({ uid: 'seed-' + name, name, role, admin: role === 'admin', text });
                setTimeout(tick, viewing() ? 22000 + Math.random() * 16000 : 42000 + Math.random() * 20000);
            };
            setTimeout(tick, 14000);
        },
    };

    // Server: identitas (nama, peran) diambil backend dari sesi login, jadi hanya teks (dan lama sematan) yang dikirim.
    const server = {
        last: null,
        headers(json) {
            const csrf = document.querySelector('meta[name="csrf-token"]');
            return Object.assign({ Accept: 'application/json' }, json ? { 'Content-Type': 'application/json' } : {}, csrf ? { 'X-CSRF-TOKEN': csrf.content } : {});
        },
        async fail(r, fallback) {
            if (r.status === 429) return new Error('Terlalu cepat. Coba lagi sebentar.');
            let msg = ''; try { msg = (await r.json()).message || ''; } catch (_) { /* tanpa badan JSON */ }
            return new Error(r.status === 422 && msg ? msg : fallback);
        },
        async list() {
            const url = cfg.endpoints.list + (this.last ? (cfg.endpoints.list.includes('?') ? '&' : '?') + 'after=' + encodeURIComponent(this.last) : '');
            const r = await fetch(url, { headers: this.headers(false), credentials: 'same-origin' });
            if (!r.ok) throw new Error('list ' + r.status);
            const j = await r.json();
            if (j.messages && j.messages.length) this.last = j.messages[j.messages.length - 1].id;
            return j;
        },
        async send(text, pinDays) {
            const r = await fetch(cfg.endpoints.send, {
                method: 'POST', credentials: 'same-origin', headers: this.headers(true),
                body: JSON.stringify(pinDays ? { text, pin_days: pinDays } : { text }),
            });
            if (!r.ok) throw await this.fail(r, 'Pesan gagal terkirim.');
            return (await r.json()).message;
        },
        async pin(id, days) {
            const r = await fetch(cfg.endpoints.send + '/' + encodeURIComponent(id) + '/pin', { method: 'POST', credentials: 'same-origin', headers: this.headers(true), body: JSON.stringify({ days }) });
            if (!r.ok) throw await this.fail(r, 'Pengumuman gagal disematkan.');
            return r.json();
        },
        async unpin(id) {
            const r = await fetch(cfg.endpoints.send + '/' + encodeURIComponent(id) + '/pin', { method: 'DELETE', credentials: 'same-origin', headers: this.headers(false) });
            if (!r.ok) throw await this.fail(r, 'Pengumuman gagal dihapus.');
            return r.json();
        },
    };

    const useMock = cfg.mock || !cfg.endpoints;
    const api = useMock ? mock : server;
    if (useMock) mock.init();
    if (PAGE) {
        el.me.textContent = me.name + ' · ' + ROLES[me.role].charAt(0) + ROLES[me.role].slice(1).toLowerCase();
    }

    // ---------- State ----------
    let unread = 0;
    // Penanda "terakhir dibaca" per pengguna (disimpan di browser) supaya lencana merah di sidebar tetap benar setelah pindah halaman.
    // Saat pertama kali, mode contoh menganggap pesan 1 jam terakhir belum dibaca agar lencananya langsung terlihat.
    let seen = Number(store.get(LS.seen)) || 0;
    const markSeen = () => { seen = Date.now(); store.set(LS.seen, String(seen)); };
    let all = [];
    let pins = [];
    let pinIdx = 0;
    let dropOpen = false;
    let annDays = 0; // 0 = pesan biasa; 7 atau 30 = kirim sebagai pengumuman
    let query = '';
    let chip = 'all';
    let missed = 0;
    let pinSig = '';
    let pollTimer = 0;
    const sentAt = [];
    const isPinned = (id) => pins.some((p) => p.id === id && p.pinned_until > Date.now());
    // "Sedang dibaca" = berada di halaman chat dan tab terlihat.
    const viewing = () => PAGE && !document.hidden;

    function setUnread(n) {
        unread = n;
        badges.forEach((b) => {
            b.hidden = n === 0;
            b.textContent = n >= 99 ? '99+' : String(n);
            b.setAttribute('aria-label', n + ' pesan belum dibaca');
        });
        // HP: menu sidebar ada di dalam laci, jadi tombol hamburger ikut diberi titik merah agar pesan baru tetap terlihat.
        const burger = document.getElementById('mobile-menu-button');
        if (burger) burger.classList.toggle('has-unread', n > 0);
    }

    // ---------- Pesan (mode halaman) ----------
    function bubble(m, prev) {
        const mine = String(m.uid) === me.id;
        const role = roleOf(m);
        const pinned = isPinned(m.id);
        const cont = prev && prev.uid === m.uid && sameDay(prev.ts, m.ts) && m.ts - prev.ts < 4 * 60000;
        const row = mk('div', 'pc-msg' + (mine ? ' is-mine' : '') + (role === 'admin' ? ' is-admin' : '') + (cont ? ' is-cont' : '') + (pinned ? ' is-pinned' : ''));
        row.dataset.id = m.id;
        row.dataset.uid = String(m.uid);

        if (!mine) {
            const av = mk('span', 'pc-avatar' + (cont ? ' is-ghost' : ''), role === 'admin' ? 'S' : initial(m.name));
            av.style.background = role === 'admin' ? 'linear-gradient(135deg,#2563eb,#06b6d4)' : 'hsl(' + hueOf(m.name) + ' 55% 46%)';
            av.setAttribute('aria-hidden', 'true');
            row.appendChild(av);
        }

        const bub = mk('div', 'pc-bubble');
        if (!mine && !cont) {
            const head = mk('div', 'pc-msg__head');
            const nm = mk('span', 'pc-msg__name', m.name);
            nm.style.setProperty('--h', String(hueOf(m.name)));
            head.append(nm, mk('span', 'pc-tag pc-tag--' + role, ROLES[role]));
            bub.appendChild(head);
        }
        const meta = mk('span', 'pc-msg__meta');
        if (pinned) { const p = icon('pin', 'pc-msg__pin'); p.title = 'Disematkan sebagai pengumuman'; meta.appendChild(p); }
        meta.appendChild(document.createTextNode(timeFmt.format(new Date(m.ts))));
        const body = mk('span', 'pc-msg__text', m.text);
        if (isLong(m.text)) {
            body.classList.add('is-clamp');
            const foot = mk('div', 'pc-msg__foot');
            foot.append(moreBtn(), meta);
            bub.append(body, foot);
        } else bub.append(body, meta);
        if (canPin && mine) {
            const more = mk('button', 'pc-msg__more'); more.type = 'button';
            more.dataset.act = 'menu'; more.dataset.id = m.id;
            more.setAttribute('aria-label', 'Opsi pesan'); more.setAttribute('aria-haspopup', 'menu'); more.setAttribute('aria-expanded', 'false');
            more.appendChild(icon('chev'));
            bub.appendChild(more);
        }
        row.appendChild(bub);
        return row;
    }

    function dayLabel(ts) {
        const d = new Date(ts), t = new Date();
        const diff = Math.round((new Date(t.getFullYear(), t.getMonth(), t.getDate()) - new Date(d.getFullYear(), d.getMonth(), d.getDate())) / DAY);
        return diff === 0 ? 'Hari ini' : diff === 1 ? 'Kemarin' : dayFmt.format(d);
    }

    function renderAll(keepScroll) {
        const top = el.list.scrollTop;
        el.list.textContent = '';
        if (useMock) el.list.appendChild(mk('div', 'pc-sys', 'Mode contoh · pesan hanya tersimpan di browser ini, bukan data sungguhan'));
        if (!all.length) {
            el.list.appendChild(mk('p', 'pc-empty', 'Belum ada pesan. Jadilah yang pertama menyapa!'));
            return;
        }
        all.forEach((m, i) => appendOne(m, all[i - 1]));
        el.list.scrollTop = keepScroll ? top : el.list.scrollHeight;
    }

    function appendOne(m, prev) {
        if (!prev || !sameDay(prev.ts, m.ts)) el.list.appendChild(mk('div', 'pc-day', dayLabel(m.ts)));
        el.list.appendChild(bubble(m, prev && sameDay(prev.ts, m.ts) ? prev : null));
    }

    const distBottom = () => el.list.scrollHeight - el.list.scrollTop - el.list.clientHeight;
    const nearBottom = () => distBottom() < 80;
    function toBottom() { el.list.scrollTop = el.list.scrollHeight; missed = 0; updateJump(); }

    function updateJump() {
        const far = distBottom() > 240;
        el.jump.hidden = !far;
        if (!far) missed = 0;
        el.jumpN.hidden = missed === 0;
        el.jumpN.textContent = missed > 99 ? '99+' : String(missed);
    }

    function addIncoming(m) {
        if (all.some((x) => x.id === m.id)) return;
        const mine = String(m.uid) === me.id;
        if (!PAGE) {
            all.push(m);
            if (!mine && m.ts > seen) setUnread(Math.min(99, unread + 1));
            return;
        }
        const wasNear = nearBottom() || mine;
        const empty = el.list.querySelector('.pc-empty'); if (empty) empty.remove();
        appendOne(m, all[all.length - 1]);
        all.push(m);
        if (viewing()) {
            markSeen();
            if (wasNear) toBottom(); else { missed++; updateJump(); }
        } else if (!mine && m.ts > seen) {
            setUnread(Math.min(99, unread + 1));
        }
        renderPeople(); updateMembers();
    }

    // ---------- Panel kiri: orang di ruang, pencarian, saringan peran ----------
    function buildPeople() {
        const map = new Map();
        all.forEach((m) => {
            const k = String(m.uid);
            const e = map.get(k) || { uid: k, name: m.name, role: roleOf(m), count: 0 };
            e.last = m; e.count++;
            map.set(k, e);
        });
        return [...map.values()].sort((a, b) => b.last.ts - a.last.ts);
    }

    function updateMembers() {
        const names = buildPeople().filter((p) => p.uid !== me.id).slice(0, 5).map((p) => p.name);
        el.members.textContent = names.length ? '· ' + names.join(', ') + ', Anda' : '';
    }

    function renderChips(people) {
        el.chips.textContent = '';
        const counts = {};
        people.forEach((p) => { counts[p.role] = (counts[p.role] || 0) + 1; });
        const roles = ['mentor', 'intern', 'institusi', 'industri', 'admin'].filter((r) => counts[r]);
        if (people.length < 2 || roles.length < 2) { chip = 'all'; return; }
        if (chip !== 'all' && !counts[chip]) chip = 'all';
        [['all', 'Semua', people.length]].concat(roles.map((r) => [r, ROLE_CHIP[r], counts[r]])).forEach(([key, label, n]) => {
            const b = mk('button', 'pcl-chip'); b.type = 'button'; b.dataset.act = 'chip'; b.dataset.chip = key;
            b.setAttribute('aria-pressed', chip === key ? 'true' : 'false');
            b.append(document.createTextNode(label + ' '), mk('small', null, String(n)));
            el.chips.appendChild(b);
        });
    }

    function highlight(text, q) {
        const frag = document.createDocumentFragment();
        const i = q ? text.toLowerCase().indexOf(q) : -1;
        if (i < 0) { frag.append(text); return frag; }
        frag.append(text.slice(0, i), Object.assign(mk('mark'), { textContent: text.slice(i, i + q.length) }), text.slice(i + q.length));
        return frag;
    }

    function pRow(o) {
        const b = mk('button', 'pcl-row' + (o.active ? ' is-active' : '')); b.type = 'button';
        b.dataset.act = o.act; if (o.id != null) b.dataset.id = o.id;
        const ava = mk('span', 'pcl-row__ava');
        ava.textContent = o.initial;
        ava.style.background = o.bg;
        ava.setAttribute('aria-hidden', 'true');
        const main = mk('span', 'pcl-row__main');
        const top = mk('span', 'pcl-row__top');
        top.append(mk('span', 'pcl-row__name', o.name));
        if (o.role) top.append(mk('span', 'pc-tag pc-tag--' + o.role, ROLES[o.role]));
        top.append(mk('span', 'pcl-row__time', o.time));
        const sub = mk('span', 'pcl-row__sub'); const subTxt = mk('span'); subTxt.append(o.sub); sub.append(subTxt);
        main.append(top, sub);
        b.append(ava, main);
        return b;
    }

    function renderPeople() {
        const people = buildPeople();
        renderChips(people);
        const q = query.trim().toLowerCase();
        const box = el.people;
        box.textContent = '';
        const shown = people.filter((p) => (chip === 'all' || p.role === chip) && (!q || p.name.toLowerCase().includes(q)));
        const personRow = (p) => pRow({
            act: 'person', id: p.uid, name: p.uid === me.id ? 'Anda' : p.name, role: p.role, time: rowTime(p.last.ts),
            initial: p.role === 'admin' ? 'S' : initial(p.name), bg: p.role === 'admin' ? 'linear-gradient(135deg,#2563eb,#06b6d4)' : 'hsl(' + hueOf(p.name) + ' 55% 46%)',
            sub: highlight(p.last.text, ''),
        });
        if (!q) {
            box.appendChild(mk('div', 'pcl__cap', 'Orang di ruang ini · ' + shown.length));
            shown.forEach((p) => box.appendChild(personRow(p)));
            return;
        }
        if (shown.length) { box.appendChild(mk('div', 'pcl__cap', 'Orang · ' + shown.length)); shown.forEach((p) => box.appendChild(personRow(p))); }
        const hits = all.filter((m) => m.text.toLowerCase().includes(q)).slice(-30).reverse();
        if (hits.length) {
            box.appendChild(mk('div', 'pcl__cap', 'Pesan · ' + hits.length));
            hits.forEach((m) => {
                const i = m.text.toLowerCase().indexOf(q);
                const from = Math.max(0, i - 24);
                const snip = (from > 0 ? '…' : '') + m.text.slice(from, from + 90);
                const r = pRow({
                    act: 'msg', id: m.id, name: String(m.uid) === me.id ? 'Anda' : m.name, role: roleOf(m), time: rowTime(m.ts),
                    initial: roleOf(m) === 'admin' ? 'S' : initial(m.name), bg: roleOf(m) === 'admin' ? 'linear-gradient(135deg,#2563eb,#06b6d4)' : 'hsl(' + hueOf(m.name) + ' 55% 46%)',
                    sub: highlight(snip, q),
                });
                box.appendChild(r);
            });
        }
        if (!shown.length && !hits.length) box.appendChild(mk('p', 'pcl__empty', 'Tidak ada orang atau pesan yang cocok dengan "' + query.trim() + '".'));
    }

    function closeList() { root.classList.remove('is-list-open'); }
    function openList(focus) {
        root.classList.add('is-list-open');
        if (focus) setTimeout(() => { el.search.focus(); el.search.select(); }, 60);
    }

    // ---------- Pengumuman tersemat: bilah satu baris + daftar lengkap ----------
    function pinCard(p) {
        const item = mk('div', 'pc-pin');
        const meta = mk('div', 'pc-pin__meta');
        const role = roleOf(p);
        meta.append(mk('b', null, String(p.uid) === me.id ? 'Anda' : p.name), mk('span', 'pc-tag pc-tag--' + role, ROLES[role]), mk('span', 'pc-pin__left', timeLeft(p.pinned_until)));
        const acts = mk('div', 'pc-pin__acts');
        if (all.some((m) => m.id === p.id)) { const g = mk('button', 'pc-link', 'Lihat di obrolan'); g.type = 'button'; g.dataset.act = 'goto'; g.dataset.id = p.id; acts.appendChild(g); }
        if (String(p.uid) === me.id) { const d = mk('button', 'pc-link pc-link--danger', 'Hapus pengumuman'); d.type = 'button'; d.dataset.act = 'unpin'; d.dataset.id = p.id; acts.appendChild(d); }
        const body = mk('p', 'pc-pin__text', p.text);
        if (p.text.length > 200) { body.classList.add('is-clamp'); acts.prepend(moreBtn()); }
        item.append(meta, body);
        if (acts.childElementCount) item.appendChild(acts);
        return item;
    }

    function renderPinbar() {
        if (!PAGE) return;
        const now = Date.now();
        pins = pins.filter((p) => p.pinned_until > now);
        el.pinwrap.hidden = !pins.length;
        el.pinbar.textContent = '';
        el.pindrop.textContent = '';
        if (!pins.length) { dropOpen = false; el.pindrop.hidden = true; return; }
        pinIdx = Math.min(pinIdx, pins.length - 1);
        const p = pins[pinIdx];

        const seg = mk('span', 'pcp__pinseg');
        pins.forEach((_, i) => seg.appendChild(mk('i', i === pinIdx ? 'is-on' : '')));
        seg.setAttribute('aria-hidden', 'true');

        const main = mk('button', 'pcp__pinmain'); main.type = 'button'; main.dataset.act = 'pin-next';
        main.title = 'Lihat pengumuman ini di percakapan';
        const txt = mk('span', 'pcp__pintxt');
        txt.append(mk('b', null, String(p.uid) === me.id ? 'Anda' : p.name), document.createTextNode(p.text));
        main.append(icon('pin', 'pcp__pinico'), mk('span', 'pcp__pinlbl', 'Pengumuman ' + (pinIdx + 1) + '/' + pins.length), txt);

        const more = mk('button', 'pcp__iconbtn pcp__pinmore'); more.type = 'button'; more.dataset.act = 'pin-drop';
        more.setAttribute('aria-expanded', dropOpen ? 'true' : 'false');
        more.setAttribute('aria-label', dropOpen ? 'Tutup daftar pengumuman' : 'Buka daftar pengumuman (' + pins.length + ')');
        more.appendChild(icon('chev'));
        el.pinbar.append(seg, main, more);

        el.pindrop.hidden = !dropOpen;
        if (dropOpen) pins.forEach((x) => el.pindrop.appendChild(pinCard(x)));
    }

    function applyPins(res) {
        if (res && Array.isArray(res.pins)) pins = res.pins;
        renderPinbar();
        renderAll(true);
    }

    const flash = (id) => {
        const row = el.list.querySelector('[data-id="' + (window.CSS && CSS.escape ? CSS.escape(id) : id) + '"]');
        if (!row) return false;
        row.scrollIntoView({ block: 'center', behavior: 'smooth' });
        row.classList.add('is-flash'); setTimeout(() => row.classList.remove('is-flash'), 1800);
        return true;
    };

    function showErr(msg) { el.err.textContent = msg; el.err.hidden = false; }

    async function runPin(fn) {
        el.err.hidden = true;
        try { applyPins(await fn()); } catch (err) { showErr(err && err.message ? err.message : 'Gagal memproses pengumuman.'); }
    }

    // ---------- Menu opsi pesan (mentor, pesan sendiri) ----------
    function closeMenu() {
        el.list.querySelectorAll('.pc-menu').forEach((n) => n.remove());
        el.list.querySelectorAll('.pc-msg__more[aria-expanded="true"]').forEach((b) => b.setAttribute('aria-expanded', 'false'));
    }
    function openMenu(btn) {
        const wasOpen = btn.getAttribute('aria-expanded') === 'true';
        closeMenu();
        if (wasOpen) return;
        const id = btn.dataset.id;
        const menu = mk('div', 'pc-menu'); menu.setAttribute('role', 'menu');
        const item = (label, act, extra, danger) => {
            const b = mk('button', danger ? 'is-danger' : '', label); b.type = 'button'; b.setAttribute('role', 'menuitem');
            b.dataset.act = act; b.dataset.id = id; if (extra) b.dataset.days = extra;
            menu.appendChild(b);
        };
        if (isPinned(id)) item('Hapus pengumuman', 'unpin', null, true);
        else { item('Sematkan 7 hari', 'pin-do', '7'); item('Sematkan 30 hari', 'pin-do', '30'); }
        btn.closest('.pc-bubble').appendChild(menu);
        btn.setAttribute('aria-expanded', 'true');
        const first = menu.querySelector('button'); if (first) first.focus({ preventScroll: true });
    }

    // ---------- Emoji ----------
    function buildEmoji() {
        EMOJIS.forEach((e) => { const b = mk('button', null, e); b.type = 'button'; b.dataset.act = 'emoji'; b.dataset.emoji = e; b.setAttribute('aria-label', e); el.emoji.appendChild(b); });
    }
    function setEmoji(open) {
        el.emoji.hidden = !open;
        el.emojiBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    function insertEmoji(e) {
        const t = el.text, s = t.selectionStart == null ? t.value.length : t.selectionStart, en = t.selectionEnd == null ? s : t.selectionEnd;
        t.value = t.value.slice(0, s) + e + t.value.slice(en);
        t.selectionStart = t.selectionEnd = s + e.length;
        t.focus(); syncSend(); autosize();
    }

    // ---------- Aksi (delegasi klik di seluruh halaman) ----------
    function handleAct(e) {
        const b = e.target.closest('[data-act]');
        if (!b || !root.contains(b)) return;
        const { act, id } = b.dataset;
        if (act === 'toggle-text') {
            const scope = b.closest('.pc-bubble, .pc-pin');
            const body = scope && scope.querySelector('.pc-msg__text, .pc-pin__text');
            if (body) {
                const open = body.classList.toggle('is-open');
                b.textContent = open ? 'Sembunyikan' : 'Selengkapnya';
                b.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
        }
        else if (act === 'menu') openMenu(b);
        else if (act === 'pin-do') runPin(() => api.pin(id, Number(b.dataset.days)));
        else if (act === 'unpin') runPin(() => api.unpin(id));
        else if (act === 'pin-next') {
            const p = pins[pinIdx];
            if (p) flash(p.id);
            pinIdx = (pinIdx + 1) % pins.length; renderPinbar();
        }
        else if (act === 'pin-drop') { dropOpen = !dropOpen; renderPinbar(); }
        else if (act === 'goto') { dropOpen = false; renderPinbar(); flash(id); }
        else if (act === 'chip') { chip = b.dataset.chip; renderPeople(); }
        else if (act === 'person') {
            const p = buildPeople().find((x) => x.uid === id);
            if (p) flash(p.last.id);
            closeList();
        }
        else if (act === 'msg') { flash(id); closeList(); }
        else if (act === 'emoji') insertEmoji(b.dataset.emoji);
    }

    // Mode pengumuman di kotak tulis (khusus mentor)
    function setAnn(days) {
        annDays = days;
        if (!el.ann) return;
        el.ann.setAttribute('aria-pressed', days ? 'true' : 'false');
        el.ann.classList.toggle('is-on', !!days);
        el.annbar.hidden = !days;
        el.send.classList.toggle('is-ann', !!days);
        el.text.placeholder = days ? 'Tulis pengumuman' : 'Ketik pesan';
        el.annbar.querySelectorAll('[data-ann-days]').forEach((b) => b.setAttribute('aria-pressed', Number(b.dataset.annDays) === days ? 'true' : 'false'));
        if (days) el.text.focus({ preventScroll: true });
    }

    // ---------- Muat & polling ----------
    async function load(first) {
        try {
            const res = await api.list();
            if (PAGE && typeof res.online === 'number') el.online.textContent = res.online + ' online';
            if (Array.isArray(res.pins)) pins = res.pins;
            const incoming = res.messages || [];
            if (first) {
                all = incoming.slice();
                if (!seen) { seen = Date.now() - (useMock ? 60 * 60000 : 0); store.set(LS.seen, String(seen)); }
                if (PAGE) { markSeen(); setUnread(0); renderPinbar(); renderAll(); renderPeople(); updateMembers(); updateJump(); }
                else setUnread(Math.min(99, all.filter((m) => String(m.uid) !== me.id && m.ts > seen).length));
            } else {
                const sig = pins.map((p) => p.id).join(',');
                incoming.forEach(addIncoming);
                if (PAGE) { if (sig !== pinSig) { pinSig = sig; renderPinbar(); renderAll(true); } else renderPinbar(); }
            }
            // Bila server mengirim jumlah belum dibaca (disimpan per user), itu yang dipakai untuk lencana merah.
            if (typeof res.unread === 'number' && !viewing()) setUnread(Math.min(99, Math.max(0, res.unread)));
        } catch (_) { /* jaringan bermasalah: coba lagi pada polling berikutnya */ }
    }

    function schedule() {
        clearTimeout(pollTimer);
        if (useMock) return; // mock memakai pendengar langsung
        pollTimer = setTimeout(async () => { await load(false); schedule(); }, viewing() ? cfg.pollViewMs : cfg.pollIdleMs);
    }

    // ---------- Kotak tulis & interaksi (mode halaman) ----------
    function syncSend() {
        const len = cleanText(el.text.value).length;
        el.count.textContent = `${len}/${cfg.maxLen}`;
        el.count.classList.toggle('is-near', len > cfg.maxLen * .9 && len <= cfg.maxLen);
        el.count.classList.toggle('is-over', len > cfg.maxLen);
        const idle = !len || len > cfg.maxLen;
        el.send.classList.toggle('is-idle', idle);
        el.send.setAttribute('aria-disabled', idle ? 'true' : 'false');
        if (len > cfg.maxLen) showErr('Pesan terlalu panjang: kurangi ' + (len - cfg.maxLen) + ' karakter atau pecah menjadi beberapa pesan.');
        else if (el.err.textContent.indexOf('terlalu panjang') > -1) el.err.hidden = true;
    }
    function autosize() { el.text.style.height = 'auto'; el.text.style.height = Math.min(140, el.text.scrollHeight) + 'px'; }

    // Kirim pesan. Dipanggil dari tombol Kirim (submit formulir). Isi kotak dibaca langsung saat klik, jadi tetap terkirim
    // walau teksnya terisi tanpa event input (mis. dipulihkan browser setelah refresh atau ditempel lewat menu).
    let sending = false;
    async function submitMessage() {
        if (sending) return;
        syncSend();
        const text = cleanText(el.text.value);
        if (!text) { el.err.hidden = false; el.err.textContent = 'Tulis pesan terlebih dahulu.'; el.text.focus(); return; }
        if (text.length > cfg.maxLen) return; // syncSend sudah menampilkan peringatan panjang
        const now = Date.now();
        while (sentAt.length && now - sentAt[0] > 60000) sentAt.shift();
        if (sentAt.length && now - sentAt[sentAt.length - 1] < cfg.rateMs || sentAt.length >= cfg.perMinute) {
            showErr('Terlalu cepat, tunggu sebentar lalu klik Kirim lagi. Pesan Anda belum terkirim.'); return;
        }
        sending = true; el.send.classList.add('is-busy'); el.err.hidden = true;
        const days = annDays;
        setEmoji(false);
        try {
            const m = await api.send(text, days);
            sentAt.push(now);
            el.text.value = ''; autosize(); syncSend();
            if (days) { setAnn(0); await load(false); pinIdx = 0; renderPinbar(); }
            addIncoming(Object.assign({ uid: me.id, name: me.name, role: me.role }, m));
            if (days) renderAll();
            toBottom();
            el.text.focus({ preventScroll: true });
        } catch (err) {
            showErr(err && err.message ? err.message : 'Pesan gagal terkirim. Coba lagi.');
        } finally {
            sending = false; el.send.classList.remove('is-busy'); syncSend();
        }
    }

    function closePopups() {
        closeMenu();
        if (!el.emoji.hidden) setEmoji(false);
        if (dropOpen) { dropOpen = false; renderPinbar(); }
    }

    function bindPage() {
        root.addEventListener('click', handleAct);
        buildEmoji();
        el.emojiBtn.addEventListener('click', () => setEmoji(el.emoji.hidden));
        if (el.ann) {
            el.ann.addEventListener('click', () => setAnn(annDays ? 0 : 7));
            el.annbar.addEventListener('click', (e) => { const b = e.target.closest('[data-ann-days]'); if (b) setAnn(Number(b.dataset.annDays)); });
        }
        el.text.addEventListener('input', () => { el.err.hidden = true; syncSend(); autosize(); });
        el.compose.addEventListener('submit', (e) => { e.preventDefault(); submitMessage(); });
        el.jump.addEventListener('click', toBottom);
        el.list.addEventListener('scroll', updateJump, { passive: true });

        // Panel kiri: pencarian, buka/tutup (layar sempit), aturan
        el.search.addEventListener('input', () => { query = el.search.value; renderPeople(); });
        el.search.addEventListener('keydown', (e) => { if (e.key === 'Escape' && el.search.value) { e.stopPropagation(); el.search.value = ''; query = ''; renderPeople(); } });
        el.openList.addEventListener('click', () => openList(false));
        el.hsearch.addEventListener('click', () => { openList(true); });
        el.listClose.addEventListener('click', closeList);
        el.rules.hidden = store.get(LS.rules) === '1';
        el.rulesX.addEventListener('click', () => { el.rules.hidden = true; store.set(LS.rules, '1'); });

        // Menutup menu/emoji/daftar pengumuman: klik di luar, atau Esc
        document.addEventListener('click', (e) => {
            if (e.target.closest('.pc-menu, .pc-msg__more, .pc-emoji, #pc-emoji-btn, #pc-pinwrap')) return;
            if (!e.target.isConnected) return;
            closePopups();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closePopups(); closeList();
        });

        // Kembali ke tab ini: anggap semua pesan terbaca dan lanjutkan ke pesan terbaru.
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) return;
            markSeen(); setUnread(0); schedule();
            if (nearBottom()) toBottom();
        });
        // Sematan yang habis masa berlakunya hilang otomatis; jam di daftar orang ikut diperbarui (tiap menit).
        setInterval(() => {
            const before = pins.length;
            renderPinbar();
            if (pins.length !== before) renderAll(true);
            renderPeople();
        }, 60000);
        syncSend(); autosize();
        window.addEventListener('pageshow', () => { syncSend(); autosize(); });
        setTimeout(() => { syncSend(); autosize(); el.text.focus({ preventScroll: true }); }, 150);
    }

    // ---------- Mulai ----------
    if (PAGE) bindPage();
    load(true).then(() => {
        if (PAGE) pinSig = pins.map((p) => p.id).join(',');
        if (useMock) { mock.onIncoming(addIncoming); mock.simulate(); }
        schedule();
    });
})();
