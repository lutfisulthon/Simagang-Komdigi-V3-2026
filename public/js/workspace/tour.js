/*
 * Workspace — tutorial pertama kali (tur sorotan), memakai Driver.js (public/vendor/driverjs).
 *
 * Tiga tutorial, masing-masing hanya tampil sekali per pengguna dan bisa diulang dari Panduan:
 *   dasar            semua halaman: menu Workspace, lonceng, mode gelap, Panduan
 *   daftar-kelompok  /workspace
 *   halaman-kelompok /workspace/{id}
 * Naikkan `version` sebuah tutorial untuk menampilkannya lagi ke semua orang (mis. setelah ada fitur baru).
 *
 * Penyimpanan ("sudah pernah lihat") lewat satu lapisan kecil `storage` di bawah:
 *   - sekarang: localStorage browser, kunci per ID pengguna;
 *   - nanti (kebutuhan backend): bila server menyediakan `config.onboarding` (data awal) dan `config.onboardingUrl`
 *     (POST simpan, DELETE reset), data dibaca dan disimpan di akun. Tidak ada kode lain yang perlu diubah.
 *   Untuk pengujian otomatis: localStorage['ws-tour-disabled'] = '1' mematikan tampil-otomatis.
 */
(function () {
    'use strict';

    const WS = window.WS;
    if (!WS) return;

    const cfg = WS.config || {};
    const user = cfg.user || {};
    const role = user.role; // 'mentor' | 'intern' | 'admin'
    const LS_KEY = 'ws-onboarding:' + (user.id || 'anon');

    // ---------- Penyimpanan ----------

    const local = {
        all() {
            try { return JSON.parse(localStorage.getItem(LS_KEY)) || {}; } catch (_) { return {}; }
        },
        save(data) {
            try { localStorage.setItem(LS_KEY, JSON.stringify(data)); } catch (_) { /* storage tidak tersedia */ }
        },
    };

    const storage = {
        // Data awal dari server (bila ada) diutamakan atas data browser.
        all() {
            return Object.assign({}, local.all(), cfg.onboarding || {});
        },
        get(id) {
            return this.all()[id] || null;
        },
        async set(id, record) {
            const data = local.all();
            data[id] = record;
            local.save(data);
            if (cfg.onboarding) cfg.onboarding[id] = record;
            if (cfg.onboardingUrl) {
                try { await WS.http('POST', cfg.onboardingUrl, { body: Object.assign({ tour: id }, record) }); } catch (_) { /* tidak fatal */ }
            }
        },
        async reset(id) {
            const data = local.all();
            delete data[id];
            local.save(data);
            if (cfg.onboarding) delete cfg.onboarding[id];
            if (cfg.onboardingUrl) {
                try { await WS.http('DELETE', cfg.onboardingUrl.replace(/\/$/, '') + '/' + id); } catch (_) { /* tidak fatal */ }
            }
        },
    };

    // ---------- Util DOM ----------

    const visible = (sel) => [...document.querySelectorAll(sel)].find((e) => e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden') || null;
    const byText = (sel, text) => [...document.querySelectorAll(sel)].find((e) => e.getClientRects().length && e.textContent.replace(/\s+/g, ' ').trim().startsWith(text)) || null;
    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;
    const path = () => window.location.pathname.replace(/\/+$/, '') || '/';

    // ---------- Definisi tutorial ----------
    // el: fungsi yang mengembalikan elemen (atau null = langkah tanpa sorotan, di tengah layar).
    // Langkah dengan el yang tidak ditemukan dilewati otomatis.

    const WELCOME = {
        mentor: 'Di sini Anda membuat kelompok, memberi tugas, dan menilai hasil anak bimbingan.',
        intern: 'Di sini Anda melihat dan mengerjakan tugas dari mentor, lalu mengirimnya untuk dinilai.',
        admin: 'Di sini Anda memantau semua kelompok dan progres tugasnya.',
    };

    const TOURS = [
        {
            id: 'dasar',
            version: 1,
            match: () => true,
            steps: () => [
                { el: null, title: 'Selamat datang di Workspace', text: WELCOME[role] || WELCOME.intern + ' Ikuti tur singkat ini (sekitar 1 menit).' },
                { el: () => isDesktop() ? visible('#sidebar a.sb-link[href$="/workspace"]') : null, title: 'Menu Workspace', text: 'Semua kelompok dan tugas ada di menu ini.', side: 'right' },
                { el: () => visible('.tb .ws-icon-btn[aria-label="Notifikasi"], .tb-m [aria-label="Notifikasi"]'), title: 'Notifikasi', text: 'Tugas baru, review, revisi, komentar, dan deadline muncul di sini. Pop-up kecil juga tampil saat ada yang baru.', side: 'bottom' },
                { el: () => visible('.tb .sm-theme__btn, .tb-m .sm-theme__btn'), title: 'Tampilan', text: 'Klik ikon gerigi untuk beralih ke mode gelap.', side: 'bottom' },
                { el: () => isDesktop() ? visible('.sb-help') : null, title: 'Panduan penggunaan', text: 'Bingung dengan sebuah istilah? Buka Panduan kapan saja. Tutorial ini juga bisa diulang dari sana.', side: 'right' },
            ],
        },
        {
            id: 'daftar-kelompok',
            version: 1,
            match: () => path() === '/workspace',
            steps: () => [
                role === 'mentor' && { el: () => byText('.ws-hero-btn', 'Buat kelompok'), title: 'Buat kelompok', text: 'Mulai dari sini: beri nama, pilih warna, dan pilih anak bimbingan sebagai anggota.', side: 'bottom' },
                role === 'admin' && { el: () => visible('input[type="search"][name="q"]'), title: 'Cari kelompok', text: 'Cari kelompok berdasarkan nama atau anggota, lalu saring per unit atau mentor.', side: 'bottom' },
                { el: () => visible('a.ws-card'), title: 'Kartu kelompok', text: 'Tiap kartu menampilkan progres tugas, tugas yang menunggu review atau terlambat, dan anggotanya. Klik "Buka" untuk masuk.', side: 'top' },
            ],
        },
        {
            id: 'halaman-kelompok',
            version: 1,
            match: () => /^\/workspace\/\d+$/.test(path()),
            steps: () => [
                { el: () => visible('.ws-stats'), title: 'Ringkasan tugas', text: 'Jumlah tugas per status, termasuk yang terlambat.', side: 'bottom' },
                { el: () => visible('.ws-tabs'), title: 'Bagian kelompok', text: 'Backlog untuk merencanakan, Board untuk mengerjakan, Timeline untuk jadwal, Laporan untuk grafik, dan Channel untuk chat.', side: 'bottom' },
                { el: () => visible('.ws-sprint-bar'), title: 'Sprint berjalan', text: 'Periode kerja saat ini beserta targetnya. Klik ikon ? di sampingnya untuk penjelasan istilah.', side: 'bottom' },
                {
                    el: () => visible('.ws-task-card'),
                    title: 'Kartu tugas',
                    text: role === 'mentor' ? 'Klik kartu untuk melihat detail, menilai hasil, atau meminta revisi.'
                        : role === 'intern' ? 'Geser kartu ke kolom berikutnya saat dikerjakan, lalu pindahkan ke Review bila sudah selesai. Klik kartu untuk detail.'
                        : 'Klik kartu untuk melihat detail tugas.',
                    side: 'right',
                },
                role !== 'admin' && { el: () => byText('button', 'Buat tugas'), title: 'Buat tugas', text: 'Tambahkan tugas baru dari sini.', side: 'left' },
            ],
        },
    ];

    // ---------- Menjalankan ----------

    let active = null;
    let shownThisPage = new Set();

    function resolveSteps(tour) {
        return tour.steps().filter(Boolean).map((s) => {
            const element = s.el ? s.el() : null;
            return { s, element };
        }).filter(({ s, element }) => !s.el || element).map(({ s, element }) => ({
            element: element || undefined,
            popover: { title: s.title, description: s.text, side: s.side || 'bottom', align: 'center' },
        }));
    }

    function start(id) {
        const tour = TOURS.find((t) => t.id === id);
        if (!tour || active || !window.driver || !window.driver.js) return false;
        const steps = resolveSteps(tour);
        if (!steps.length) return false;

        let finished = false;
        const driverFactory = window.driver.js.driver;
        const drv = driverFactory({
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
                // Kartu tur memakai palet sendiri (terang/gelap lewat CSS), jadi mesin mode gelap tidak boleh membaliknya.
                popover.wrapper.setAttribute('data-dm-native', '');
                if (!popover.footer.querySelector('.ws-tour__skip')) {
                    const skip = document.createElement('button');
                    skip.type = 'button';
                    skip.className = 'ws-tour__skip';
                    skip.textContent = 'Lewati';
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
                storage.set(id, { version: tour.version, status: finished ? 'completed' : 'skipped', at: new Date().toISOString() });
                // Tutorial berikutnya yang cocok dengan halaman ini (mis. dasar → daftar-kelompok) menyusul sebentar lagi.
                setTimeout(autoStart, 700);
            },
        });
        active = drv;
        shownThisPage.add(id);
        drv.drive();
        return true;
    }

    function pending() {
        return TOURS.find((t) => {
            if (shownThisPage.has(t.id) || !t.match()) return false;
            const rec = storage.get(t.id);
            return !rec || (rec.version || 0) < t.version;
        });
    }

    function autoStart() {
        try { if (localStorage.getItem('ws-tour-disabled') === '1') return; } catch (_) { /* abaikan */ }
        // Jangan menimpa dialog yang sedang terbuka (lapisan gelap yang disembunyikan tidak dihitung).
        if (active || visible('.ws-overlay') || document.querySelector('.driver-popover')) return;
        const next = pending();
        if (next) start(next.id);
    }

    // Tunggu halaman siap (splash selesai, mode gelap selesai dipindai, konten Alpine tampil) sebelum menyorot.
    function whenReady(fn) {
        const t0 = Date.now();
        const tick = () => {
            const root = document.documentElement;
            const dark = root.getAttribute('data-theme') === 'dark';
            const ready = document.readyState === 'complete' && (!dark || root.hasAttribute('data-dm-ready')) && !root.classList.contains('sm-splash-on');
            if (ready || Date.now() - t0 > 6000) setTimeout(fn, 600);
            else setTimeout(tick, 250);
        };
        tick();
    }

    document.addEventListener('alpine:initialized', () => whenReady(autoStart));

    // Ulangi dari Panduan: detail { id } atau { id: 'halaman' } (tutorial yang cocok dengan halaman ini).
    window.addEventListener('ws-tour-start', async (e) => {
        const want = e.detail && e.detail.id;
        const tour = want === 'halaman' ? TOURS.slice(1).find((t) => t.match()) : TOURS.find((t) => t.id === want);
        if (!tour) { WS.toast('Halaman ini belum punya tutorial.', 'info'); return; }
        await storage.reset(tour.id);
        shownThisPage.delete(tour.id);
        window.dispatchEvent(new CustomEvent('ws-close-modal'));
        setTimeout(() => { if (!start(tour.id)) WS.toast('Tutorial tidak dapat dimulai di halaman ini.', 'info'); }, 450);
    });

    WS.tour = { start, storage, tours: TOURS.map((t) => t.id) };
})();
