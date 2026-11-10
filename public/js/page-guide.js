/*
 * Panduan penggunaan per halaman (semua peran). Berkas ini kecil dan dimuat di semua halaman setelah login; sisanya baru diunduh
 * saat Panduan dipakai:
 *   page-guide-ui.js      panel Panduan + penjalan tutorial (Driver.js)
 *   page-guide-tours.js   isi tutorial tiap halaman menu sidebar (data, per peran)
 *   css/page-guide.css    gaya panel;  css/workspace-tour.css + vendor/driverjs  gaya kartu tutorial
 *
 * Dipakai dari:
 *   - tombol "Panduan penggunaan" di sidebar: PageGuide.open()
 *   - tautan "?tutorial=1" (dari daftar "Tutorial halaman lain" di panel): tutorial halaman itu langsung berjalan saat halaman siap.
 * Konfigurasi dari layout: window.PageGuideConfig = { role, roleLabel, userId }.
 */
(function () {
    'use strict';

    const SELF = document.currentScript ? document.currentScript.src : '';
    const base = SELF ? new URL('./', SELF).href : '/js/';
    const search = SELF ? new URL(SELF).search : '';
    const cssUrl = SELF ? new URL('../css/page-guide.css' + search, SELF).href : '/css/page-guide.css';

    let loading = null;

    function addScript(src) {
        return new Promise((resolve) => {
            const s = document.createElement('script');
            s.src = src + search;
            s.onload = () => resolve(true);
            s.onerror = () => resolve(false);
            document.head.appendChild(s);
        });
    }

    // Muat panel + data tutorial sekali saja.
    function load() {
        if (window.PageGuideUI) return Promise.resolve(window.PageGuideUI);
        if (!loading) {
            loading = (async () => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = cssUrl;
                document.head.appendChild(link);
                const ok = (await addScript(base + 'page-guide-tours.js')) && (await addScript(base + 'page-guide-ui.js'));
                if (!ok) loading = null;
                return ok ? window.PageGuideUI : null;
            })();
        }
        return loading;
    }

    // Di HP menu ada di dalam laci: tutup lebih dulu supaya panel tidak tertutup lapisan laci.
    function closeDrawer() {
        const drawer = document.getElementById('mobile-sidebar');
        const close = document.getElementById('mobile-close-button');
        if (drawer && close && !drawer.classList.contains('hidden')) close.click();
    }

    window.PageGuide = {
        open() { closeDrawer(); return load().then((ui) => { if (ui) ui.open(); return !!ui; }); },
        start(opts) { return load().then((ui) => (ui ? ui.start(opts || {}) : false)); },
        load,
    };

    // "?tutorial=1": mulai tutorial halaman ini begitu halaman siap (splash selesai, mode gelap selesai dipindai).
    const params = new URLSearchParams(window.location.search);
    if (params.get('tutorial') === '1') {
        params.delete('tutorial');
        const qs = params.toString();
        try { history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : '') + window.location.hash); } catch (_) { /* abaikan */ }
        const t0 = Date.now();
        const tick = () => {
            const root = document.documentElement;
            const dark = root.getAttribute('data-theme') === 'dark';
            const ready = document.readyState === 'complete' && (!dark || root.hasAttribute('data-dm-ready')) && !root.classList.contains('sm-splash-on');
            if (ready || Date.now() - t0 > 6000) setTimeout(() => window.PageGuide.start({ auto: true }), 700);
            else setTimeout(tick, 250);
        };
        tick();
    }
})();
