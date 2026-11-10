/*
 * Panel Panduan + penjalan tutorial per halaman (lihat page-guide.js untuk pemuat dan page-guide-tours.js untuk isinya).
 * Tidak bergantung pada kode Workspace, jadi sama untuk semua peran (admin, super admin, mentor, peserta, institusi, industri).
 *
 * Satu tutorial = { id, match: [regex path], title, about, steps: [{ el, title, text, side?, roles? }] }.
 * `el` (penyorot, opsional) berupa: selector CSS | [selector, ...] (yang pertama terlihat) | { text, in } (elemen ber-teks awal tertentu)
 *   | { card } (kartu yang memuat judul ber-teks tertentu). Langkah yang elemennya tidak ditemukan dilewati; langkah tanpa `el`
 *   tampil di tengah layar. Halaman tanpa tutorial khusus memakai tutorial umum yang dibentuk dari struktur halaman.
 */
(function () {
    'use strict';

    const cfg = window.PageGuideConfig || {};
    const role = cfg.role || 'intern';
    const TOURS = (window.PageGuideTours || []).map((t) => Object.assign({}, t, { match: t.match.map((s) => new RegExp(s)) }));

    const SELF = document.currentScript ? document.currentScript.src : '';
    const SEARCH = SELF ? new URL(SELF).search : '';
    const driverBase = SELF ? new URL('../vendor/driverjs/', SELF).href : '/vendor/driverjs/';
    const tourCss = SELF ? new URL('../css/workspace-tour.css' + SEARCH, SELF).href : '/css/workspace-tour.css';

    // ---------- Util ----------
    const mk = (tag, cls, txt) => { const n = document.createElement(tag); if (cls) n.className = cls; if (txt != null) n.textContent = txt; return n; };
    const path = () => window.location.pathname.replace(/\/+$/, '') || '/';
    const isShown = (e) => e.getClientRects().length > 0 && getComputedStyle(e).visibility !== 'hidden';
    const vis = (sel) => { try { return [...document.querySelectorAll(sel)].find(isShown) || null; } catch (_) { return null; } };
    const norm = (s) => s.replace(/\s+/g, ' ').trim().toLowerCase();
    const byText = (sel, text) => [...document.querySelectorAll(sel)].find((e) => isShown(e) && norm(e.textContent).startsWith(norm(text))) || null;
    // Kartu yang memuat judul ber-teks tertentu: elemen "daun" bernama itu, lalu naik ke kartu terdekat.
    function cardOf(text) {
        const want = norm(text);
        // Teks milik elemen itu sendiri (tanpa anak), supaya judul yang berisi ikon atau teks tebal tetap cocok.
        const own = (e) => [...e.childNodes].filter((n) => n.nodeType === 3).map((n) => n.textContent).join(' ');
        const leaf = [...document.querySelectorAll('main *')].find((e) => isShown(e) && !/^(SCRIPT|STYLE|SVG|PATH)$/i.test(e.tagName) && norm(own(e)).startsWith(want));
        if (!leaf) return null;
        return leaf.closest('.bg-white, [class*="rounded-2xl"], [class*="rounded-xl"], [class*="rounded-lg"], section, article') || leaf.parentElement;
    }
    function find(d) {
        if (!d) return null;
        if (typeof d === 'string') return vis(d);
        if (Array.isArray(d)) { for (const x of d) { const e = find(x); if (e) return e; } return null; }
        if (d.card) return cardOf(d.card);
        if (d.text) return byText(d.in || 'main a, main button', d.text);
        return null;
    }

    function notify(msg) {
        const old = document.querySelector('.pg-toast'); if (old) old.remove();
        const t = mk('div', 'pg-toast', msg); t.setAttribute('role', 'status'); t.setAttribute('data-dm-native', '');
        document.body.appendChild(t);
        setTimeout(() => t.classList.add('is-in'), 10);
        setTimeout(() => { t.classList.remove('is-in'); setTimeout(() => t.remove(), 300); }, 3800);
    }

    // ---------- Riwayat "sudah dilihat" (di browser) ----------
    const SEEN_KEY = 'pg-seen:' + (cfg.userId || 'anon');
    const seen = {
        all() { try { return JSON.parse(localStorage.getItem(SEEN_KEY)) || {}; } catch (_) { return {}; } },
        has(p) { return !!this.all()[p]; },
        mark(p) { const d = this.all(); d[p] = new Date().toISOString(); try { localStorage.setItem(SEEN_KEY, JSON.stringify(d)); } catch (_) { /* penyimpanan tidak tersedia */ } },
    };

    // ---------- Pemilihan tutorial ----------
    const tourFor = (p) => TOURS.find((t) => t.match.some((re) => re.test(p))) || null;
    const stepsOf = (tour) => tour.steps.filter((s) => !s.roles || s.roles.includes(role));

    // Tutorial umum untuk halaman tanpa tutorial khusus, dibentuk dari struktur halaman.
    function genericTour() {
        const title = (document.querySelector('.tb__title') || {}).textContent || document.title;
        return {
            id: 'umum', generic: true, title: title.trim(), about: 'Halaman ini belum punya tutorial khusus. Berikut bagian-bagian utamanya.',
            steps: [
                { el: ['main .hero-strip', 'main .hero-header', 'main [class*="hero"]', 'main h1'], title: 'Judul halaman', text: 'Bagian atas menjelaskan halaman ini dan, bila ada, menyediakan tombol aksi utamanya.' },
                { el: 'main form[method="get" i]', title: 'Cari dan saring', text: 'Isi kolom pencarian atau pilihan saringan untuk mempersempit data yang tampil.' },
                { el: { text: 'Tambah', in: 'main a, main button' }, title: 'Tambah data baru', text: 'Klik untuk menambahkan data baru.' },
                { el: 'main table', title: 'Daftar data', text: 'Data ditampilkan dalam tabel. Gunakan kolom Aksi di ujung kanan untuk membuka, mengubah, atau menghapus.' },
                { el: ['main nav[aria-label*="agination" i]', 'main .pagination', 'main nav[role="navigation"]'], title: 'Halaman data', text: 'Pindah antarhalaman bila datanya banyak.' },
            ],
        };
    }
    const currentTour = () => tourFor(path()) || genericTour();

    // ---------- Driver.js (dimuat malas) ----------
    let driverLoading = null;
    function loadDriver() {
        if (window.driver && window.driver.js) return Promise.resolve(true);
        if (!driverLoading) {
            driverLoading = new Promise((resolve) => {
                [driverBase + 'driver.css', tourCss].forEach((href) => { const l = document.createElement('link'); l.rel = 'stylesheet'; l.href = href; document.head.appendChild(l); });
                const s = document.createElement('script');
                s.src = driverBase + 'driver.js.iife.js';
                s.onload = () => resolve(true);
                s.onerror = () => { driverLoading = null; resolve(false); };
                document.head.appendChild(s);
            });
        }
        return driverLoading;
    }

    // Langkah yang elemennya ada (atau tanpa sorotan) diubah menjadi format Driver.js.
    function resolveSteps(tour) {
        const list = [];
        stepsOf(tour).forEach((s) => {
            const element = s.el ? find(s.el) : null;
            if (s.el && !element) return;
            list.push({ element: element || undefined, popover: { title: s.title, description: s.text, side: s.side || 'bottom', align: 'center' } });
        });
        return list;
    }

    let active = null;

    async function start(opts) {
        opts = opts || {};
        if (active) return false;
        // Tutorial lain (mis. tutorial otomatis Workspace) sedang tampil: jangan menumpuk dua lapisan sorotan.
        if (document.querySelector('.driver-popover')) { notify('Selesaikan atau tutup tutorial yang sedang tampil dulu.'); return false; }
        const tour = currentTour();
        closePanel(true);
        active = 'loading';
        if (!(await loadDriver())) { active = null; notify('Tutorial tidak dapat dimuat. Periksa koneksi lalu coba lagi.'); return false; }
        const body = resolveSteps(tour);
        if (!body.length) { active = null; notify('Belum ada bagian halaman ini yang dapat disorot.'); return false; }
        // Pembuka di tengah layar, lalu langkah-langkah yang menyorot bagian halaman.
        const steps = [{ popover: { title: tour.title, description: tour.about || 'Ikuti tutorial singkat ini.', align: 'center' } }].concat(body);

        let finished = false;
        const drv = window.driver.js.driver({
            steps,
            showProgress: true,
            progressText: '{{current}} dari {{total}}',
            nextBtnText: 'Lanjut',
            prevBtnText: 'Kembali',
            doneBtnText: 'Selesai',
            allowClose: true,
            overlayOpacity: 0.55,
            stagePadding: 6,
            stageRadius: 10,
            popoverClass: 'ws-tour',
            smoothScroll: true,
            onPopoverRender: (popover) => {
                // Kartu tutorial memakai palet sendiri (terang/gelap lewat CSS), jadi mesin mode gelap tidak boleh membaliknya.
                popover.wrapper.setAttribute('data-dm-native', '');
                if (!popover.footer.querySelector('.ws-tour__skip')) {
                    const skip = document.createElement('button');
                    skip.type = 'button'; skip.className = 'ws-tour__skip'; skip.textContent = 'Lewati';
                    skip.addEventListener('click', () => drv.destroy());
                    popover.footer.prepend(skip);
                }
            },
            onNextClick: () => {
                if (drv.hasNextStep()) drv.moveNext();
                else { finished = true; drv.destroy(); }
            },
            onDestroyed: () => {
                active = null;
                if (finished && !tour.generic) seen.mark(path());
            },
        });
        active = drv;
        drv.drive();
        return true;
    }

    // ---------- Panel ----------
    let root = null;
    let lastFocus = null;

    function sidebarGroups() {
        const nav = document.querySelector('#sidebar .sb-nav');
        if (!nav) return [];
        const groups = [];
        let cur = null;
        [...nav.children].forEach((el) => {
            if (el.classList.contains('sb-cap')) { cur = { caption: el.textContent.trim(), items: [] }; groups.push(cur); return; }
            if (!el.matches('a.sb-link')) return;
            if (!cur) { cur = { caption: 'Menu', items: [] }; groups.push(cur); }
            const label = [...el.querySelectorAll('span')].find((s) => !s.classList.contains('sb-ico') && !s.classList.contains('sb-badge'));
            const icon = el.querySelector('.sb-ico i');
            cur.items.push({ href: el.getAttribute('href'), label: (label || el).textContent.replace(/\s+/g, ' ').trim(), icon: icon ? icon.className : 'fas fa-circle', tone: el.getAttribute('data-tone') || '' });
        });
        return groups.filter((g) => g.items.length);
    }

    function renderBody(body) {
        body.textContent = '';
        const tour = currentTour();
        const steps = stepsOf(tour);

        // 1) Halaman ini
        const here = mk('section', 'pg-card pg-card--here');
        here.appendChild(mk('p', 'pg-kicker', 'Halaman ini'));
        here.appendChild(mk('h3', 'pg-here__title', tour.title));
        here.appendChild(mk('p', 'pg-here__about', tour.about || ''));
        const go = mk('button', 'pg-btn pg-btn--primary'); go.type = 'button';
        const play = mk('span', 'pg-btn__ico'); play.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>';
        go.append(play, document.createTextNode(tour.generic ? 'Mulai tutorial umum' : 'Mulai tutorial halaman ini'));
        go.addEventListener('click', () => start());
        here.appendChild(go);
        if (!tour.generic && seen.has(path())) here.appendChild(mk('p', 'pg-here__seen', 'Sudah pernah Anda selesaikan. Bisa diulang kapan saja.'));
        body.appendChild(here);

        // 2) Ringkasan cara pakai (bisa dibaca tanpa menjalankan tutorial)
        if (steps.length) {
            const sec = mk('section', 'pg-card');
            sec.appendChild(mk('p', 'pg-kicker', 'Ringkasan cara pakai'));
            const ol = mk('ol', 'pg-steps');
            steps.forEach((s, i) => {
                const li = mk('li');
                li.appendChild(mk('span', 'pg-num', String(i + 1)));
                const d = mk('div');
                d.append(mk('p', 'pg-step__t', s.title), mk('p', 'pg-step__d', s.text));
                li.appendChild(d);
                ol.appendChild(li);
            });
            sec.appendChild(ol);
            body.appendChild(sec);
        }

        // 3) Tutorial halaman lain (menu sidebar peran ini)
        const groups = sidebarGroups();
        if (groups.length) {
            const sec = mk('section', 'pg-card');
            sec.appendChild(mk('p', 'pg-kicker', 'Tutorial halaman lain'));
            sec.appendChild(mk('p', 'pg-hint', 'Pilih halaman untuk membukanya dan langsung memulai tutorialnya.'));
            groups.forEach((g) => {
                sec.appendChild(mk('p', 'pg-cap', g.caption));
                g.items.forEach((it) => {
                    let p = '';
                    try { p = new URL(it.href, window.location.href).pathname.replace(/\/+$/, '') || '/'; } catch (_) { /* abaikan */ }
                    const here2 = p === path();
                    const a = mk('a', 'pg-row' + (here2 ? ' is-here' : ''));
                    a.href = it.href + (it.href.includes('?') ? '&' : '?') + 'tutorial=1';
                    if (it.tone) a.setAttribute('data-tone', it.tone);
                    const ico = mk('span', 'pg-row__ico'); const i = mk('i', it.icon); i.setAttribute('aria-hidden', 'true'); ico.appendChild(i);
                    a.append(ico, mk('span', 'pg-row__label', it.label));
                    if (here2) a.appendChild(mk('span', 'pg-chip', 'Halaman ini'));
                    else if (seen.has(p)) a.appendChild(mk('span', 'pg-chip pg-chip--ok', 'Sudah dilihat'));
                    const chev = mk('span', 'pg-row__chev'); chev.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>';
                    a.appendChild(chev);
                    if (here2) a.addEventListener('click', (e) => { e.preventDefault(); start(); });
                    sec.appendChild(a);
                });
            });
            body.appendChild(sec);
        }

        // 4) Khusus peran yang memakai Workspace: istilah, alur kerja, dan FAQ
        if (window.WS) {
            const sec = mk('section', 'pg-card');
            sec.appendChild(mk('p', 'pg-kicker', 'Workspace'));
            sec.appendChild(mk('p', 'pg-hint', 'Bingung dengan istilah seperti sprint, backlog, atau kanban? Buka panduan khusus Workspace.'));
            const row = mk('div', 'pg-btnrow');
            const a = mk('button', 'pg-btn'); a.type = 'button'; a.textContent = 'Istilah, alur kerja, dan FAQ';
            a.addEventListener('click', () => { closePanel(true); setTimeout(() => window.dispatchEvent(new CustomEvent('ws-guide-open', { detail: {} })), 260); });
            const b = mk('button', 'pg-btn'); b.type = 'button'; b.textContent = 'Tutorial dasar Workspace';
            b.addEventListener('click', () => { closePanel(true); setTimeout(() => window.dispatchEvent(new CustomEvent('ws-tour-start', { detail: { id: 'dasar' } })), 260); });
            row.append(a, b);
            sec.appendChild(row);
            body.appendChild(sec);
        }
    }

    function build() {
        root = mk('div', 'pg-root');
        root.setAttribute('data-dm-native', '');
        root.hidden = true;
        const backdrop = mk('div', 'pg-backdrop');
        backdrop.addEventListener('click', () => closePanel());
        const panel = mk('aside', 'pg-panel');
        panel.setAttribute('role', 'dialog'); panel.setAttribute('aria-modal', 'true'); panel.setAttribute('aria-labelledby', 'pg-title'); panel.tabIndex = -1;

        const head = mk('header', 'pg-head');
        const ico = mk('span', 'pg-head__ico'); ico.innerHTML = '<i class="fas fa-circle-question" aria-hidden="true"></i>';
        const txt = mk('div', 'pg-head__txt');
        const h = mk('h2', null, 'Panduan penggunaan'); h.id = 'pg-title';
        txt.append(h, mk('p', null, 'Untuk ' + (cfg.roleLabel || 'pengguna')));
        const x = mk('button', 'pg-close'); x.type = 'button'; x.setAttribute('aria-label', 'Tutup panduan');
        x.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
        x.addEventListener('click', () => closePanel());
        head.append(ico, txt, x);

        const body = mk('div', 'pg-body');
        panel.append(head, body);
        root.append(backdrop, panel);
        document.body.appendChild(root);

        document.addEventListener('keydown', (e) => {
            if (root.hidden) return;
            if (e.key === 'Escape') { e.stopPropagation(); closePanel(); return; }
            if (e.key === 'Tab') {
                const f = [...panel.querySelectorAll('a[href], button:not([disabled])')].filter(isShown);
                if (!f.length) return;
                const first = f[0], last = f[f.length - 1];
                if (e.shiftKey && (document.activeElement === first || document.activeElement === panel)) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });
    }

    function open() {
        if (!root) build();
        lastFocus = document.activeElement;
        renderBody(root.querySelector('.pg-body'));
        root.hidden = false;
        requestAnimationFrame(() => root.classList.add('is-open'));
        setTimeout(() => root.querySelector('.pg-panel').focus({ preventScroll: true }), 60);
    }

    function closePanel(silent) {
        if (!root || root.hidden) return;
        root.classList.remove('is-open');
        setTimeout(() => { root.hidden = true; }, 240);
        if (!silent && lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    }

    window.PageGuideUI = {
        open,
        close: closePanel,
        start,
        // Untuk pengujian dan pemeriksaan: daftar langkah tutorial halaman ini beserta status penyorotnya.
        inspect() {
            const tour = currentTour();
            return { id: tour.id, generic: !!tour.generic, title: tour.title, steps: stepsOf(tour).map((s) => ({ title: s.title, found: s.el ? !!find(s.el) : true, spot: !!s.el })) };
        },
    };
})();
