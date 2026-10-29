/*
 * Splash screen Simagang (markup: components/splash-screen.blade.php, gaya: public/css/splash.css).
 *
 * Dua kegunaan:
 *  1. Layar pemuatan: tampil saat refresh / kunjungan pertama di tab (ditandai skrip kecil di theme-head), progres mengikuti
 *     tahap pemuatan halaman (DOM siap → mode gelap siap → semua aset termuat).
 *  2. Transisi tema: SimagangSplash.transition('dark'|'light', fn) menutupi layar selama tema diganti.
 *
 * API: SimagangSplash.available(), .show(), .transition(theme, applyFn), .loadingDone()
 */
(function () {
    'use strict';

    if (window.SimagangSplash) return;

    const el = document.getElementById('sm-splash');
    const root = document.documentElement;
    if (!el) {
        window.SimagangSplash = { available: () => false, transition: (t, fn) => fn(), show() {}, loadingDone() {} };
        root.classList.remove('sm-splash-on');
        return;
    }

    const fill = el.querySelector('.sm-splash__fill');
    const pct = el.querySelector('.sm-splash__pct');
    const status = el.querySelector('.sm-splash__status');
    const meter = el.querySelector('[role="progressbar"]');
    const MIN_MS = 900;       // lama tampil minimum untuk layar pemuatan, supaya tidak berkedip
    const MAX_MS = 7000;      // jaring pengaman

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
            if (value >= 100 && elapsed >= MIN_MS) { setTimeout(hide, 220); return; }
            if (elapsed > MAX_MS) { value = 100; paint(); hide(); return; }
        }
        raf = requestAnimationFrame(tick);
    }

    // ----- Layar pemuatan -----

    function startLoading() {
        variantFromTheme();
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

    window.SimagangSplash = {
        available: () => true,
        show,
        transition,
        loadingDone() { loadFinished = true; goal = 100; },
    };

    if (root.classList.contains('sm-splash-on')) startLoading();
})();
