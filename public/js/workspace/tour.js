/*
 * Workspace — tutorial pertama kali (tur sorotan), memakai Driver.js (public/vendor/driverjs).
 *
 * Enam tutorial, masing-masing hanya tampil sekali per pengguna dan bisa diulang dari Panduan:
 *   dasar            semua halaman: menu Workspace, lonceng, mode gelap, Panduan
 *   daftar-kelompok  /workspace
 *   channel          /workspace/{id} saat tab Channel dibuka
 *   backlog          /workspace/{id} saat tab Backlog dibuka (penanda [data-tour] di partials/backlog)
 *   halaman-kelompok /workspace/{id}
 *   detail-tugas     /workspace/tasks/{id} (penanda [data-tour] di partials/task-detail)
 * Naikkan `version` sebuah tutorial untuk menampilkannya lagi ke semua orang (mis. setelah ada fitur baru).
 *
 * Penyimpanan ("sudah pernah lihat") lewat satu lapisan kecil `storage` di bawah:
 *   - sekarang: localStorage browser, kunci per ID pengguna;
 *   - nanti (kebutuhan backend): bila server menyediakan `config.onboarding` (data awal) dan `config.onboardingUrl`
 *     (POST simpan, DELETE reset), data dibaca dan disimpan di akun. Tidak ada kode lain yang perlu diubah.
 *   Untuk pengujian otomatis: localStorage['ws-tour-disabled'] = '1' mematikan tampil-otomatis.
 *
 * Preferensi "Tutorial otomatis" (saklar di panel ikon gerigi):
 *   - pengguna baru: selalu menyala, saklar belum tampil;
 *   - saklar baru tampil setelah tutorial dasar selesai/dilewati DAN aplikasi dipakai di >= USAGE_DAYS hari berbeda
 *     (atau bila pengguna pernah mematikannya, supaya bisa dinyalakan lagi);
 *   - dilewati 2 kali: ditawari sekali untuk mematikan;
 *   - saat mati, hanya tampil-otomatis yang berhenti. "Ulangi tutorial" di Panduan tetap jalan, dan tutorial yang naik
 *     versi hanya ditandai titik "Baru" di tombol Panduan.
 */
(function () {
    'use strict';

    const WS = window.WS;
    if (!WS) return;

    // Driver.js (JS + CSS) dan gaya tutorial dimuat HANYA saat tutorial benar-benar dimulai, jadi halaman yang tidak
    // menampilkan tutorial tidak mengunduhnya. Alamatnya diturunkan dari alamat skrip ini (public/js/workspace/tour.js).
    const SELF = document.currentScript ? document.currentScript.src : '';
    const driverBase = SELF ? new URL('../../vendor/driverjs/', SELF).href : '/vendor/driverjs/';
    const tourCss = SELF ? new URL('../../css/workspace-tour.css' + new URL(SELF).search, SELF).href : '/css/workspace-tour.css';
    let driverLoading = null;
    function loadDriver() {
        if (window.driver && window.driver.js) return Promise.resolve(true);
        if (!driverLoading) {
            driverLoading = new Promise((resolve) => {
                [driverBase + 'driver.css', tourCss].forEach((href) => {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = href;
                    document.head.appendChild(link);
                });
                const s = document.createElement('script');
                s.src = driverBase + 'driver.js.iife.js';
                s.onload = () => resolve(true);
                s.onerror = () => { driverLoading = null; resolve(false); };
                document.head.appendChild(s);
            });
        }
        return driverLoading;
    }

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

    // ---------- Preferensi & pemakaian ----------

    const USAGE_DAYS = 3;       // hari berbeda sebelum saklar "Tutorial otomatis" tampil
    const SKIPS_BEFORE_ASK = 2; // jumlah "Lewati" sebelum ditawari mematikan
    const PREF_KEY = 'ws-tour-prefs:' + (user.id || 'anon');

    const prefs = {
        read() {
            try { return JSON.parse(localStorage.getItem(PREF_KEY)) || {}; } catch (_) { return {}; }
        },
        write(data) {
            try { localStorage.setItem(PREF_KEY, JSON.stringify(data)); } catch (_) { /* storage tidak tersedia */ }
        },
        // Data dari server (bila ada) diutamakan; tanpa data berarti menyala.
        get auto() {
            if (cfg.onboardingPrefs && typeof cfg.onboardingPrefs.auto === 'boolean') return cfg.onboardingPrefs.auto;
            return this.read().auto !== false;
        },
        async setAuto(on) {
            const d = this.read();
            d.auto = !!on;
            this.write(d);
            if (cfg.onboardingPrefs) cfg.onboardingPrefs.auto = !!on;
            syncUI();
            if (cfg.onboardingUrl) {
                try { await WS.http('PATCH', cfg.onboardingUrl.replace(/\/$/, '') + '/preferences', { body: { auto: !!on } }); } catch (_) { /* tidak fatal */ }
            }
        },
        // Mencatat hari ini sebagai hari pemakaian (maks. 30 hari terakhir disimpan).
        touchToday() {
            const d = this.read();
            const day = new Date().toLocaleDateString('sv-SE'); // YYYY-MM-DD, zona waktu lokal
            d.days = Array.isArray(d.days) ? d.days : [];
            if (!d.days.includes(day)) { d.days.push(day); d.days = d.days.slice(-30); this.write(d); }
        },
        get days() { return (this.read().days || []).length; },
        // Opsi mematikan baru muncul setelah pengguna terbiasa; yang sudah mematikannya tetap bisa menyalakan lagi.
        get canToggle() {
            return !this.auto || (!!storage.get('dasar') && this.days >= USAGE_DAYS);
        },
        // Satu "Lewati" dihitung (bukan dari pemutaran ulang). true bila saatnya menawarkan mematikan.
        countSkip() {
            const d = this.read();
            d.skips = (d.skips || 0) + 1;
            const ask = this.auto && !d.asked && d.skips >= SKIPS_BEFORE_ASK;
            if (ask) d.asked = true;
            this.write(d);
            return ask;
        },
    };
    prefs.touchToday();

    // Baris saklar di panel gerigi ([data-tour-row], lihat components/theme-menu) dan titik "Baru" di tombol Panduan.
    function syncUI() {
        const show = prefs.canToggle;
        document.querySelectorAll('[data-tour-row]').forEach((row) => {
            row.hidden = !show;
            const sw = row.querySelector('[data-tour-switch]');
            if (sw) sw.setAttribute('aria-checked', prefs.auto ? 'true' : 'false');
        });
        // Tutorial yang naik versi saat pop-up dimatikan hanya ditandai titik kecil.
        const updated = TOURS.some((tr) => { const r = storage.get(tr.id); return r && (r.version || 0) < tr.version; });
        document.querySelectorAll('.sb-help').forEach((b) => b.classList.toggle('has-new', !prefs.auto && updated));
    }

    document.addEventListener('click', (e) => {
        const sw = e.target.closest && e.target.closest('[data-tour-switch]');
        if (!sw) return;
        const next = !prefs.auto;
        prefs.setAuto(next);
        WS.toast(next ? 'Tutorial otomatis dinyalakan.' : 'Tutorial otomatis dimatikan. Nyalakan lagi lewat ikon gerigi, atau ulangi dari Panduan.', 'info');
    });

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
        // Tutorial tab (Channel, Backlog) didahulukan dari halaman-kelompok supaya "Tutorial halaman ini" memilihnya saat tab itu terbuka.
        {
            id: 'channel',
            version: 1,
            match: () => /^\/workspace\/\d+$/.test(path()) && !!visible('#ws-panel-channel'),
            steps: () => [
                { el: () => visible('#ws-panel-channel .ws-chat__side'), title: 'Daftar channel', text: 'Channel kelompok Anda dan #pengumuman. Angka merah menandai pesan yang belum dibaca.', side: 'right' },
                { el: () => visible('#ws-panel-channel .ws-composer'), title: 'Tulis pesan', text: 'Enter untuk kirim, Shift+Enter untuk baris baru. Ketik @ untuk menyebut anggota atau # untuk merujuk tugas. Lampirkan file lewat ikon klip.', side: 'top' },
                { el: () => visible('#ws-panel-channel [aria-label="Cari pesan"]'), title: 'Cari pesan', text: 'Cari pesan lama di channel ini, termasuk balasan di thread.', side: 'bottom' },
                { el: () => visible('#ws-panel-channel [aria-label^="Pesan disematkan"]'), title: 'Pesan disematkan', text: 'Pesan penting yang disematkan mentor dikumpulkan di sini.', side: 'bottom' },
                { el: () => visible('#ws-panel-channel [aria-label="Opsi channel"]'), title: 'Opsi channel', text: role === 'intern' ? 'Bisukan channel bila terlalu ramai. Pesan tetap bisa dibaca kapan saja.' : 'Bisukan channel, ubah info, atau arsipkan channel.', side: 'left' },
                { el: null, title: 'Aksi pada pesan', text: 'Arahkan kursor ke sebuah pesan untuk memberi reaksi, membalas di thread, menyalin, atau mengedit dan menghapus pesan Anda sendiri.' },
            ],
        },
        {
            id: 'backlog',
            version: 1,
            match: () => /^\/workspace\/\d+$/.test(path()) && !!visible('#ws-panel-backlog'),
            steps: () => {
                const bl = (sel) => visible('#ws-panel-backlog ' + sel);
                const btn = (text) => byText('#ws-panel-backlog button', text);
                const staff = role !== 'intern';
                return [
                    {
                        el: () => bl('[data-tour="bl-intro"]'), title: 'Backlog dan sprint',
                        text: staff ? 'Alurnya dari bawah ke atas: tugas dicatat dulu di Backlog (paling bawah), lalu diseret naik ke sprint, dan dikerjakan di Board saat sprintnya berjalan. Sprint ditaruh di atas supaya sprint yang sedang berjalan langsung terlihat.'
                            : 'Rencana tugas kelompok, dibaca dari bawah ke atas: tugas dicatat di Backlog (paling bawah), lalu dimasukkan mentor ke sprint di atasnya. Tugas sprint yang sedang berjalan dikerjakan di Board.',
                        side: 'bottom',
                    },
                    staff && { el: () => bl('[data-tour="bl-new-sprint"]'), title: 'Buat sprint', text: 'Isi nama, deadline sprint, tujuan, ketua, dan tugas-tugasnya. Sprint baru berstatus terencana sampai dimulai.', side: 'bottom' },
                    {
                        el: () => bl('.ws-bl-section--active') || bl('[data-tour="bl-sprint"]'), title: 'Kotak sprint',
                        text: 'Kepalanya menampilkan tanggal, jumlah tugas, total story point, dan Ketua. Sprint yang sedang berjalan ditandai "Berjalan". Tiga angka di kanan: To Do, dikerjakan, dan selesai.',
                        side: 'bottom',
                    },
                    {
                        el: () => bl('.ws-leader-chip'), title: 'Ketua sprint',
                        text: 'Ketua membagi tugas sprint ke anggota di Board. Tanda "belum dibagi" menunjukkan jumlah tugas yang belum punya assignee.',
                        side: 'bottom',
                    },
                    staff && { el: () => btn('Mulai sprint'), title: 'Mulai sprint', text: 'Tentukan durasi atau tanggal, lalu pilih Ketua. Hanya satu sprint yang dapat berjalan sekaligus.', side: 'left' },
                    staff && { el: () => btn('Selesaikan sprint'), title: 'Selesaikan sprint', text: 'Di akhir periode, tutup sprint dan pilih ke mana tugas yang belum selesai dipindahkan.', side: 'left' },
                    staff && { el: () => bl('[aria-label^="Opsi "]'), title: 'Opsi sprint', text: 'Ubah sprint, atau hapus sprint yang belum dimulai. Tugasnya kembali ke Backlog, tidak ikut terhapus.', side: 'left' },
                    staff && { el: () => bl('.ws-bl-row__handle'), title: 'Pindahkan tugas', text: 'Seret baris dari pegangan ini ke sprint atau ke Backlog. Bisa juga lewat menu titik tiga di ujung baris.', side: 'right' },
                    { el: () => bl('[data-tour="bl-backlog"]'), title: 'Backlog', text: staff ? 'Titik awal setiap tugas. Klik "Buat tugas" di sini untuk mencatat tugas yang belum dijadwalkan, lalu seret naik ke sprint saat siap dikerjakan.' : 'Titik awal setiap tugas: dicatat mentor di sini, lalu dinaikkan ke sprint saat siap dikerjakan.', side: 'top' },
                    staff && { el: () => bl('[data-tour="bl-epic"]'), title: 'Epic', text: 'Kelompokkan tugas yang bertema sama lintas sprint. Epic dipilih saat membuat atau mengubah tugas.', side: 'bottom' },
                    !staff && { el: () => bl('.ws-bl-create'), title: 'Anda Ketua Sprint', text: 'Tambahkan tugas ke sprint yang Anda pimpin dari sini, lalu bagi ke anggota di Board.', side: 'top' },
                    { el: () => bl('[data-tour="bl-done"]'), title: 'Sprint selesai', text: staff ? 'Riwayat sprint yang sudah ditutup beserta hasilnya. "Buka kembali" tersedia bila tidak ada sprint yang sedang berjalan.' : 'Riwayat sprint yang sudah ditutup beserta jumlah tugas yang selesai.', side: 'top' },
                ];
            },
        },
        {
            id: 'halaman-kelompok',
            version: 2,
            match: () => /^\/workspace\/\d+$/.test(path()),
            steps: () => [
                { el: () => visible('.ws-stats'), title: 'Ringkasan tugas', text: 'Jumlah tugas per status, termasuk yang terlambat.', side: 'bottom' },
                { el: () => visible('.ws-tabs'), title: 'Bagian kelompok', text: 'Backlog untuk merencanakan, Board untuk mengerjakan, List untuk mencari dan menyaring, Timeline dan Deadline untuk jadwal, Laporan untuk grafik, Channel untuk chat, dan Aktivitas untuk riwayat perubahan.', side: 'bottom' },
                { el: () => visible('.ws-sprint-bar'), title: 'Sprint berjalan', text: 'Periode kerja saat ini beserta targetnya. Klik ikon ? di sampingnya untuk penjelasan istilah.', side: 'bottom' },
                {
                    el: () => visible('.ws-task-card'),
                    title: 'Kartu tugas',
                    text: role === 'mentor' ? 'Klik kartu untuk melihat detail, menilai hasil, atau meminta revisi.'
                        : role === 'intern' ? 'Geser kartu ke kolom berikutnya saat dikerjakan, lalu pindahkan ke Review bila sudah selesai. Klik kartu untuk detail.'
                        : 'Klik kartu untuk melihat detail tugas.',
                    side: 'right',
                },
                { el: () => [...document.querySelectorAll('.ws-hero-btn')].find((b) => b.getClientRects().length && /anggota\s*$/.test(b.textContent.trim())) || null, title: 'Anggota', text: 'Lihat daftar mentor dan anak magang di kelompok ini.', side: 'bottom' },
                role !== 'intern' && { el: () => byText('.ws-hero-btn', 'Kelola'), title: 'Kelola kelompok', text: 'Ganti nama dan warna, tambah anggota, atau tandai kelompok selesai magang.', side: 'bottom' },
                { el: () => byText('button', 'Buat tugas'), title: 'Buat tugas', text: role === 'intern' ? 'Sebagai Ketua Sprint, Anda dapat menambah tugas ke sprint yang Anda pimpin.' : 'Tambahkan tugas baru dari sini.', side: 'left' },
                { el: () => isDesktop() ? visible('.sb-help') : null, title: 'Ingin tahu lebih banyak?', text: 'Tab Fitur di Panduan menjelaskan setiap bagian, dari Channel sampai rekap nilai.', side: 'right' },
            ],
        },
        {
            id: 'detail-tugas',
            version: 1,
            match: () => /^\/workspace\/tasks\/\d+$/.test(path()),
            steps: () => [
                { el: () => visible('[data-tour="task-actions"]'), title: 'Aksi tugas', text: role === 'intern' ? 'Tombol di sini mengikuti status: Mulai kerjakan, Kirim untuk review, atau Perbaiki sekarang. Bila deadline lewat, minta mentor memperpanjangnya.' : 'Setujui & beri nilai atau Minta revisi untuk tugas di Review. Perpanjang deadline bila anak magang terlambat.', side: 'bottom' },
                { el: () => visible('[data-tour="task-meta"]'), title: 'Info tugas', text: 'Assignee, pembuat, deadline, epic, story point, dan label tugas ini.', side: 'bottom' },
                { el: () => visible('[data-tour="task-checklist"]'), title: 'Checklist', text: 'Langkah kecil di dalam tugas. Centang yang sudah selesai; persentasenya tampil di kartu.', side: 'top' },
                { el: () => visible('[data-tour="task-comments"]'), title: 'Komentar', text: 'Diskusi khusus tugas ini. Tekan Ctrl+Enter untuk mengirim.', side: 'top' },
                { el: () => visible('[data-tour="task-history"]'), title: 'Riwayat', text: 'Siapa mengubah apa dan kapan pada tugas ini.', side: 'top' },
                { el: () => visible('[data-tour="task-menu"]'), title: 'Menu lain', text: role === 'intern' ? 'Salin link untuk membagikan tugas ini.' : 'Salin link, edit, atau hapus tugas.', side: 'left' },
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

    async function start(id, opts = {}) {
        const tour = TOURS.find((t) => t.id === id);
        if (!tour || active) return false;
        active = 'loading';
        if (!(await loadDriver())) { active = null; return false; }
        const steps = resolveSteps(tour);
        if (!steps.length) { active = null; return false; }

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
            onDestroyed: async () => {
                active = null;
                await storage.set(id, { version: tour.version, status: finished ? 'completed' : 'skipped', at: new Date().toISOString() });
                syncUI();
                if (opts.manual) return; // diputar ulang dari Panduan: tidak dihitung dan tidak disusul tutorial lain
                if (!finished && prefs.countSkip()) {
                    // Sudah dua kali dilewati: tawarkan mematikan (sekali saja).
                    const off = window.SimagangConfirm ? await window.SimagangConfirm.open({
                        title: 'Matikan tutorial otomatis?',
                        message: 'Tutorial tidak akan muncul sendiri lagi. Anda bisa menyalakannya kembali lewat ikon gerigi, dan Panduan tetap tersedia kapan saja.',
                        ok: 'Ya, matikan', cancel: 'Tidak, tetap tampilkan', tone: 'primary', icon: 'fa-graduation-cap',
                    }) : false;
                    if (off) { await prefs.setAuto(false); return; }
                }
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
        if (!prefs.auto) return;
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

    document.addEventListener('alpine:initialized', () => { syncUI(); whenReady(autoStart); });

    // Tutorial yang terikat tab (mis. Channel) menyusul saat tab itu dibuka.
    document.addEventListener('click', (e) => {
        if (e.target.closest && e.target.closest('.ws-tab[data-tab]')) setTimeout(autoStart, 500);
    });

    // Ulangi dari Panduan: detail { id } atau { id: 'halaman' } (tutorial yang cocok dengan halaman ini).
    window.addEventListener('ws-tour-start', async (e) => {
        const want = e.detail && e.detail.id;
        const tour = want === 'halaman' ? TOURS.slice(1).find((t) => t.match()) : TOURS.find((t) => t.id === want);
        if (!tour) { WS.toast('Halaman ini belum punya tutorial.', 'info'); return; }
        await storage.reset(tour.id);
        shownThisPage.delete(tour.id);
        window.dispatchEvent(new CustomEvent('ws-close-modal'));
        setTimeout(async () => { if (!(await start(tour.id, { manual: true }))) WS.toast('Tutorial tidak dapat dimulai di halaman ini.', 'info'); }, 450);
    });

    WS.tour = { start, storage, prefs, tours: TOURS.map((t) => t.id) };
})();
