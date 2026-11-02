/*
 * Splash screen Simagang (markup: components/splash-screen.blade.php, gaya: public/css/splash.css).
 *
 * Dua kegunaan:
 *  1. Layar pemuatan: tampil saat refresh / kunjungan pertama di tab (ditandai skrip kecil di theme-head), progres mengikuti
 *     tahap pemuatan halaman (DOM siap → mode gelap siap → semua aset termuat).
 *  2. Transisi tema: SimagangSplash.transition('dark'|'light', fn) menutupi layar selama tema diganti.
 *
 *  3. Aktivitas: layar yang sama tampil saat pengguna berpindah halaman (klik tautan / kirim form) dan saat permintaan
 *     AJAX yang mengubah data berjalan lebih lama dari ~0,45 detik. Progres merayap sampai selesai, lalu penuh dan menghilang.
 *
 * API: SimagangSplash.available(), .show(), .transition(theme, applyFn), .loadingDone(), .begin(label), .end(), .navigate()
 */
(function () {
    'use strict';

    if (window.SimagangSplash) return;

    const el = document.getElementById('sm-splash');
    const root = document.documentElement;
    if (!el) {
        window.SimagangSplash = { available: () => false, transition: (t, fn) => fn(), show() {}, loadingDone() {}, begin() {}, end() {}, navigate() {} };
        root.classList.remove('sm-splash-on');
        return;
    }

    const fill = el.querySelector('.sm-splash__fill');
    const pct = el.querySelector('.sm-splash__pct');
    const status = el.querySelector('.sm-splash__status');
    const meter = el.querySelector('[role="progressbar"]');
    const MIN_MS = 900;       // lama tampil minimum untuk layar pemuatan, supaya tidak berkedip
    const MAX_MS = 7000;      // jaring pengaman
    const ACTIVITY_DELAY = 450;   // permintaan AJAX yang lebih cepat dari ini tidak menampilkan layar
    const ACTIVITY_MIN_MS = 500;  // setelah tampil, minimal selama ini supaya tidak berkedip
    const NAV_MAX_MS = 12000;     // berpindah halaman yang tidak jadi (mis. unduhan): layar dilepas otomatis
    const NAV_FLAG = 'simagang-splash-nav';

    const STAGES = [[0, 'Menyiapkan tampilan…'], [35, 'Memuat komponen…'], [70, 'Menyelaraskan data…'], [94, 'Hampir selesai…']];

    let value = 0;
    let goal = 0;
    let cap = 30;
    let raf = 0;
    let startedAt = 0;
    let loadFinished = false;
    let timed = null; // { duration, midAt, fn, done }
    let busy = false;
    let label = null;
    let minMs = MIN_MS;
    let maxMs = MAX_MS;
    let activityOn = false;
    let pending = 0;
    let delayTimer = 0;
    let navigating = false;

    const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function paint() {
        const n = Math.round(value);
        fill.style.transform = 'scaleX(' + (Math.min(100, value) / 100) + ')';
        pct.textContent = n + '%';
        meter.setAttribute('aria-valuenow', String(n));
        let text = STAGES[0][1];
        STAGES.forEach(([from, t]) => { if (n >= from) text = t; });
        status.textContent = label || text;
    }

    function variantFromTheme() {
        el.setAttribute('data-variant', root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
    }

    function show() {
        el.classList.remove('is-leaving');
        el.classList.add('is-active');
        el.setAttribute('aria-hidden', 'false');
        root.classList.add('sm-splash-on');
        startedAt = performance.now();
        value = 0;
        paint();
        if (!raf) raf = requestAnimationFrame(tick);
    }

    function hide() {
        cancelAnimationFrame(raf);
        raf = 0;
        el.classList.add('is-leaving');
        el.setAttribute('aria-hidden', 'true');
        setTimeout(() => {
            el.classList.remove('is-active', 'is-leaving');
            root.classList.remove('sm-splash-on');
            label = null;
            busy = false;
            timed = null;
            activityOn = false;
            navigating = false;
            minMs = MIN_MS;
            maxMs = MAX_MS;
            loadFinished = false;
            goal = 0;
            cap = 30;
        }, prefersReduced ? 30 : 480);
    }

    const ease = (t) => 1 - Math.pow(1 - t, 3);

    function tick(now) {
        raf = 0;
        const elapsed = now - startedAt;

        if (timed) {
            const t = Math.min(1, elapsed / timed.duration);
            value = ease(t) * 100;
            if (!timed.done && t >= timed.midAt) { timed.done = true; try { timed.fn(); } catch (e) { /* tema gagal diterapkan */ } }
            paint();
            if (t >= 1) { setTimeout(hide, 160); return; }
        } else {
            // Layar pemuatan: maju mendekati tujuan tahap saat ini, tidak pernah melewati batas (cap) sebelum halaman selesai.
            const ceiling = loadFinished ? 100 : Math.min(cap, goal + 12);
            value += Math.max(0.12, (Math.min(goal, ceiling) - value) * 0.09);
            if (value > ceiling) value = ceiling;
            if (value > 99.4 && loadFinished) value = 100;
            paint();
            if (value >= 100 && elapsed >= minMs) { setTimeout(hide, 220); return; }
            if (elapsed > maxMs) {
                value = 100; paint(); hide();
                if (navigating) { try { sessionStorage.removeItem(NAV_FLAG); } catch (e) { /* abaikan */ } }
                return;
            }
        }
        raf = requestAnimationFrame(tick);
    }

    // ----- Layar pemuatan -----

    function startLoading() {
        variantFromTheme();
        // Datang dari berpindah halaman: lanjutan layar yang sama, tampil lebih singkat.
        if (root.classList.contains('sm-splash-nav')) { minMs = 350; label = 'Memuat halaman…'; }
        el.classList.add('is-active');
        el.setAttribute('aria-hidden', 'false');
        startedAt = performance.now();
        goal = 20; cap = 40;
        paint();
        raf = requestAnimationFrame(tick);

        const stage = (g, c) => { goal = Math.max(goal, g); cap = Math.max(cap, c); };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => stage(55, 72));
        else stage(55, 72);
        // Mode gelap: halaman baru dibuka setelah pemindaian warna pertama selesai.
        window.addEventListener('simagang:theme-ready', () => stage(78, 88));
        if (document.readyState === 'complete') { loadFinished = true; goal = 100; }
        else window.addEventListener('load', () => { loadFinished = true; goal = 100; cap = 100; });
    }

    // ----- Transisi tema -----

    function transition(theme, fn) {
        if (busy) { fn(); return; }
        busy = true;
        el.setAttribute('data-variant', theme === 'dark' ? 'dark' : 'light');
        label = theme === 'dark' ? 'Mengaktifkan mode gelap…' : 'Mengaktifkan mode terang…';
        el.classList.remove('is-leaving');
        el.classList.add('is-active');
        el.setAttribute('aria-hidden', 'false');
        root.classList.add('sm-splash-on');
        startedAt = performance.now();
        value = 0;
        timed = { duration: prefersReduced ? 500 : 1250, midAt: 0.42, fn, done: false };
        paint();
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(tick);
    }

    // ----- Aktivitas (berpindah halaman & permintaan AJAX) -----

    function startActivity(text, opts) {
        if (busy || activityOn || el.classList.contains('is-active')) return false;
        activityOn = true;
        variantFromTheme();
        label = text || 'Memproses…';
        loadFinished = false;
        goal = 88; cap = 92;
        minMs = opts.min;
        maxMs = opts.max;
        show();
        return true;
    }

    // AJAX: begin() di awal, end() di akhir (boleh bertumpuk). Layar baru muncul bila permintaan lebih lama dari ACTIVITY_DELAY.
    function begin(text) {
        pending++;
        if (pending > 1 || activityOn) return;
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => { if (pending > 0) startActivity(text, { min: ACTIVITY_MIN_MS, max: 60000 }); }, ACTIVITY_DELAY);
    }

    function end() {
        pending = Math.max(0, pending - 1);
        if (pending > 0) return;
        clearTimeout(delayTimer);
        if (activityOn && !navigating) { loadFinished = true; goal = 100; cap = 100; }
    }

    // Berpindah halaman: layar tampil sampai halaman lama diganti; halaman baru melanjutkannya (penanda di sessionStorage).
    function navigate() {
        if (navigating || activityOn) return;
        try { sessionStorage.setItem(NAV_FLAG, '1'); } catch (e) { /* abaikan */ }
        if (startActivity('Memuat halaman…', { min: 0, max: NAV_MAX_MS })) navigating = true;
    }

    // Tautan/form yang bukan berpindah halaman biasa (unduhan, tab baru, hash, domain lain) tidak memakai layar.
    const FILE_RE = /(\/(download|export|print|attachments)(\/|$|\?)|\.(pdf|zip|rar|xlsx?|docx?|pptx?|csv|png|jpe?g|gif|webp|svg|mp4|mp3)(\?|$))/i;

    function linkQualifies(a) {
        if (a.hasAttribute('download') || a.hasAttribute('data-no-splash')) return false;
        if (a.target && a.target !== '_self') return false;
        const raw = a.getAttribute('href') || '';
        if (!raw || raw.charAt(0) === '#' || /^(javascript:|mailto:|tel:)/i.test(raw)) return false;
        if (a.origin !== location.origin) return false;
        if (a.pathname === location.pathname && a.search === location.search) return false;
        return !FILE_RE.test(a.pathname + a.search);
    }

    document.addEventListener('click', (e) => {
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a) return;
        // Dicek setelah semua handler selesai: klik yang di-preventDefault (Alpine, dialog konfirmasi) bukan berpindah halaman.
        setTimeout(() => { if (!e.defaultPrevented && linkQualifies(a)) navigate(); }, 0);
    });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        setTimeout(() => {
            if (e.defaultPrevented || form.hasAttribute('data-no-splash')) return;
            if (form.target && form.target !== '_self') return;
            const action = form.getAttribute('action') || location.href;
            if (FILE_RE.test(action)) return;
            navigate();
        }, 0);
    });

    // Kembali lewat cache browser (bfcache): halaman dipulihkan apa adanya, lepaskan layar yang tertinggal.
    window.addEventListener('pageshow', (e) => {
        if (e.persisted && activityOn) {
            try { sessionStorage.removeItem(NAV_FLAG); } catch (err) { /* abaikan */ }
            hide();
        }
    });

    window.SimagangSplash = {
        available: () => true,
        show,
        transition,
        begin,
        end,
        navigate,
        loadingDone() { loadFinished = true; goal = 100; },
    };

    if (root.classList.contains('sm-splash-on')) startLoading();
})();
