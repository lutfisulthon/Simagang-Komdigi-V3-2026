/*
 * Mode gelap Simagang (gaya: public/css/dark-mode.css).
 *
 * - Pilihan disimpan di localStorage ('simagang-theme' = 'dark' | 'light') dan diterapkan lebih awal oleh
 *   layouts/partials/theme-head.blade.php (tanpa berkedip terang).
 * - Tidak ada filter di <html>. Mesin ini menelusuri DOM dan menandai tiap area:
 *     K (keep)   permukaan yang sudah gelap/berwarna, foto/video, logo merek → dibiarkan persis seperti aslinya
 *     F (flip)   area terang → diberi filter pembalik warna (data-dm-flip) pada akar terbesar yang aman
 *     M (mixed)  pembungkus yang berisi K di dalamnya → difilter tidak boleh; warna latar/garis/teksnya diganti lewat style inline
 *   Akar F tidak boleh memuat elemen yang akan berubah perilakunya bila leluhurnya berfilter (position: fixed, absolute yang
 *   acuannya di luar akar, popup bertumpuk z-index); leluhur elemen seperti itu otomatis menjadi M.
 * - Menu gerigi: [data-theme-menu] berisi [data-theme-trigger], panel, dan [data-theme-switch] (komponen x-theme-menu).
 *
 * API: SimagangTheme.set('dark'|'light'), .toggle(), .isDark(), .rescan()
 * Elemen dengan atribut data-dm-native mengurus tampilannya sendiri (mis. splash screen) dan tidak disentuh mesin ini.
 */
(function () {
    'use strict';

    if (window.SimagangTheme) return;

    const KEY = 'simagang-theme';
    const root = document.documentElement;
    const DARK_LUM = 0.42; // latar dengan luminansi ≤ ini dianggap "gelap/berwarna" dan dipertahankan

    const read = () => { try { return window.localStorage.getItem(KEY); } catch (e) { return null; } };
    const write = (v) => { try { window.localStorage.setItem(KEY, v); } catch (e) { /* penyimpanan tidak tersedia */ } };
    const isDark = () => root.getAttribute('data-theme') === 'dark';

    // ---------- Warna ----------

    const COLOR_RE = /rgba?\([^)]*\)|color\(srgb[^)]*\)/g;

    function parseColor(str) {
        if (!str) return null;
        let m = str.match(/rgba?\(\s*([\d.]+)[,\s]+([\d.]+)[,\s]+([\d.]+)(?:\s*[,/]\s*([\d.]+%?))?\s*\)/);
        if (m) {
            const a = m[4] === undefined ? 1 : (m[4].endsWith('%') ? parseFloat(m[4]) / 100 : parseFloat(m[4]));
            return { r: +m[1], g: +m[2], b: +m[3], a };
        }
        m = str.match(/color\(srgb\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)(?:\s*\/\s*([\d.]+%?))?\s*\)/);
        if (m) {
            const a = m[4] === undefined ? 1 : (m[4].endsWith('%') ? parseFloat(m[4]) / 100 : parseFloat(m[4]));
            return { r: +m[1] * 255, g: +m[2] * 255, b: +m[3] * 255, a };
        }
        return null;
    }

    const lin = (v) => { const x = v / 255; return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4); };
    const lum = (c) => 0.2126 * lin(c.r) + 0.7152 * lin(c.g) + 0.0722 * lin(c.b);

    // Sama persis dengan filter invert(.9) hue-rotate(180deg), dipakai untuk warna pembungkus (M) yang tidak boleh difilter.
    function flipColor(c) {
        const r = 0.9 - 0.8 * (c.r / 255), g = 0.9 - 0.8 * (c.g / 255), b = 0.9 - 0.8 * (c.b / 255);
        const R = -0.574 * r + 1.43 * g + 0.144 * b;
        const G = 0.426 * r + 0.43 * g + 0.144 * b;
        const B = 0.426 * r + 1.43 * g - 0.856 * b;
        const q = (v) => Math.round(Math.min(1, Math.max(0, v)) * 255);
        return 'rgba(' + q(R) + ',' + q(G) + ',' + q(B) + ',' + (+c.a.toFixed(3)) + ')';
    }

    const flipText = (str) => str.replace(COLOR_RE, (m) => { const c = parseColor(m); return c ? flipColor(c) : m; });

    // Foto (jpg/png/webp/…) dipertahankan; ikon/pola SVG ikut dibalik seperti teks.
    const isPhotoUrl = (bi) => {
        const urls = bi.match(/url\(\s*["']?[^)"']+["']?\s*\)/gi) || [];
        return urls.some((u) => !/\.svg(\?|#|["')\s])|data:image\/svg/i.test(u));
    };

    // Apakah permukaan elemen ini gelap/berwarna (atau foto) sehingga harus dipertahankan?
    function isKeptSurface(cs) {
        const bi = cs.backgroundImage;
        const clipsText = (cs.webkitBackgroundClip || cs.backgroundClip) === 'text';
        if (bi && bi !== 'none') {
            if (/url\(/i.test(bi)) return isPhotoUrl(bi);
            if (clipsText) return false; // teks bergradasi ikut dibalik
            const cols = (bi.match(COLOR_RE) || []).map(parseColor).filter(Boolean);
            if (cols.length) {
                const alpha = cols.reduce((n, c) => n + c.a, 0) / cols.length;
                const l = cols.reduce((n, c) => n + lum(c), 0) / cols.length;
                // Gradasi yang salah satu ujungnya sangat terang (mis. biru tua → biru muda pucat di halaman login) diperlakukan
                // sebagai permukaan terang biasa; bila dipertahankan, kartu putih di atasnya ikut tampil terang.
                const peak = Math.max(...cols.map(lum));
                if (alpha >= 0.35 && l <= DARK_LUM && peak <= 0.6) return true;
            }
        }
        const c = parseColor(cs.backgroundColor);
        return !!c && c.a >= 0.25 && lum(c) <= DARK_LUM;
    }

    // ---------- Klasifikasi ----------

    const SKIP = new Set(['SCRIPT', 'STYLE', 'LINK', 'META', 'HEAD', 'NOSCRIPT', 'TEMPLATE', 'SOURCE', 'TRACK', 'BR', 'WBR']);
    const MEDIA = new Set(['IMG', 'VIDEO', 'PICTURE', 'IFRAME', 'EMBED', 'OBJECT']);
    const BRAND_RE = /#(9d272a|086bb0|2dabe2|efc400)\b/i; // warna wordmark SIMAGANG (style inline)
    const ATTRS = ['data-dm-keep', 'data-dm-flip', 'data-dm-mixed'];
    const SEL_MARKED = '[data-dm-keep],[data-dm-flip],[data-dm-mixed]';

    // "Panggung": wadah yang salah satu anaknya (absolut) menutupi seluruh wadah berupa foto/video atau lapisan gelap,
    // mis. hero dengan <img> latar + overlay + teks putih. Teks itu bersaudara dengan fotonya, jadi seluruh wadah harus
    // dipertahankan agar teks tetap di atas latar aslinya.
    function isStage(el) {
        const kids = el.children;
        if (!kids.length || kids.length > 40) return false;
        let box = null;
        for (let i = 0; i < kids.length; i++) {
            const c = kids[i];
            const tag = c.tagName;
            const media = MEDIA.has(tag) && !c.hasAttribute('data-dm-graphic');
            if (!media && SKIP.has(tag)) continue;
            const cs = getComputedStyle(c);
            if (cs.position !== 'absolute') continue;
            if (!media && !isKeptSurface(cs)) continue;
            box = box || el.getBoundingClientRect();
            if (!box.width || !box.height) return false;
            const r = c.getBoundingClientRect();
            if (r.width >= box.width * 0.9 && r.height >= box.height * 0.9) return true;
        }
        return false;
    }

    function isKeeper(el, cs) {
        if (MEDIA.has(el.tagName)) return !el.hasAttribute('data-dm-graphic');
        if (el.tagName === 'SPAN' && BRAND_RE.test(el.getAttribute('style') || '')) return true;
        return isKeptSurface(cs) || isStage(el);
    }

    // Elemen yang bertumpuk di atas saudaranya (popup, dropdown, header lekat). Bila ikut masuk ke akar F, ia terkurung dalam
    // lapisan akar itu dan bisa tertutup saudara berikutnya, jadi leluhurnya harus M dan elemen ini menjadi akar F sendiri.
    function isLayered(el, cs) {
        const pos = cs.position;
        if (pos === 'fixed') return true;
        if ((pos === 'absolute' || pos === 'sticky' || pos === 'relative') && cs.zIndex !== 'auto') return true;
        if (pos === 'absolute') {
            if (cs.display === 'none' || cs.visibility === 'hidden' || +cs.opacity === 0) return true; // calon dropdown/tooltip
            const r = el.getBoundingClientRect();
            if (!r.width || !r.height) return true;
        }
        return false;
    }

    const sigs = new WeakMap(); // tanda tampilan terakhir tiap elemen, untuk mendeteksi perubahan kelas/gaya yang berarti
    const sigOf = (cs) => cs.backgroundColor + '|' + cs.backgroundImage.slice(0, 120) + '|' + cs.position + '|' + cs.zIndex + '|' + cs.display;

    const hasDirectText = (el) => { for (let n = el.firstChild; n; n = n.nextSibling) if (n.nodeType === 3 && n.nodeValue.trim()) return true; return false; };

    // Warna yang harus ditimpa pada pembungkus (M), dihitung dari gaya aslinya.
    function mixedOverrides(n) {
        const el = n.el, cs = n.cs;
        const ov = [];
        const bg = parseColor(cs.backgroundColor);
        if (bg && bg.a > 0) ov.push(['background-color', flipColor(bg)]);
        const bi = cs.backgroundImage;
        const clipsText = (cs.webkitBackgroundClip || cs.backgroundClip) === 'text';
        if (bi && bi !== 'none' && !/url\(/i.test(bi) && !clipsText) ov.push(['background-image', flipText(bi)]);
        ['top', 'right', 'bottom', 'left'].forEach((side) => {
            const cap = side[0].toUpperCase() + side.slice(1);
            if (parseFloat(cs['border' + cap + 'Width']) > 0 && cs['border' + cap + 'Style'] !== 'none') {
                const c = parseColor(cs['border' + cap + 'Color']);
                if (c && c.a > 0) ov.push(['border-' + side + '-color', flipColor(c)]);
            }
        });
        if (hasDirectText(el)) {
            const c = parseColor(cs.color);
            if (c) {
                ov.push(['color', flipColor(c)]);
                // anak mewarisi warna ini; kunci warna aslinya agar tidak terbalik dua kali
                n.kids.forEach((k) => ov.push([k.el, getComputedStyle(k.el).color]));
            }
        }
        return ov;
    }

    /*
     * Penelusuran (hanya membaca). Tiap elemen menjadi simpul {el, cs, k, flip, hard, layered, esc, kids}:
     *   k='K'   dipertahankan (tidak difilter);  k='X' elemen biasa
     *   hard    di dalamnya ada K atau elemen bertumpuk → elemen ini tidak boleh difilter
     *   layered elemen ini sendiri bertumpuk (lihat isLayered): boleh jadi akar filter, tetapi leluhurnya tidak
     *   esc     acuan posisi (leluhur) elemen absolut di dalamnya yang berada di luar elemen ini; bila elemen ini difilter,
     *           acuan itu bergeser, jadi harus kosong
     *   flip    boleh difilter di akarnya (= !hard && esc kosong)
     * `stack` adalah leluhur (untuk mencari acuan posisi); `outEsc` menampung acuan posisi elemen ini sendiri untuk induknya.
     */
    function build(el, stack, outEsc) {
        if (SKIP.has(el.tagName)) return null;
        if (el.hasAttribute('data-dm-native')) return { el, k: 'K' };
        const cs = getComputedStyle(el);
        sigs.set(el, sigOf(cs));
        if (isKeeper(el, cs)) return { el, k: 'K' };

        if (cs.position === 'fixed') outEsc.add('viewport');
        else if (cs.position === 'absolute') {
            for (let i = stack.length - 1; i >= 0; i--) {
                const a = stack[i];
                if (a.cs.position !== 'static' || a.cs.transform !== 'none') { outEsc.add(a.el); break; }
            }
        }

        const esc = new Set();
        const kids = [];
        let hard = cs.display === 'contents';
        stack.push({ el, cs });
        for (let c = el.firstElementChild; c; c = c.nextElementSibling) {
            const n = build(c, stack, esc);
            if (!n) continue;
            kids.push(n);
            if (n.k === 'K' || n.hard || n.layered) hard = true;
        }
        stack.pop();
        esc.delete(el);
        esc.forEach((x) => outEsc.add(x));
        return { el, cs, k: 'X', kids, hard, esc, layered: isLayered(el, cs), flip: !hard && !esc.size };
    }

    // Mengubah simpul yang tidak bisa difilter menjadi keputusan: anak yang bisa difilter menjadi akar F, sisanya ditelusuri.
    function emit(n, acts) {
        if (n.k === 'K') { acts.push({ el: n.el, t: 'keep' }); return; }
        n.kids.forEach((k) => {
            if (k.k !== 'K' && k.flip) acts.push({ el: k.el, t: 'flip' });
            else emit(k, acts);
        });
        acts.push({ el: n.el, t: 'mixed', ov: mixedOverrides(n) });
    }

    // ---------- Penerapan ----------

    const saved = new Map(); // elemen → Map(properti → [nilai, prioritas]) sebelum ditimpa
    const mixedSet = new Set();

    function restoreOverrides(el) {
        const m = saved.get(el);
        if (!m) return;
        m.forEach(([val, prio], prop) => { if (val === '') el.style.removeProperty(prop); else el.style.setProperty(prop, val, prio); });
        saved.delete(el);
    }

    // Menimpa gaya inline; ov berisi [properti, nilai] (!important) atau [elemenAnak, warna] (mengunci warna anak).
    function setOverrides(el, ov) {
        const remember = (target, prop) => {
            if (!saved.has(target)) saved.set(target, new Map());
            const m = saved.get(target);
            if (!m.has(prop)) m.set(prop, [target.style.getPropertyValue(prop), target.style.getPropertyPriority(prop)]);
        };
        ov.forEach(([prop, val]) => {
            if (typeof prop === 'string') {
                remember(el, prop);
                el.style.setProperty(prop, val, 'important');
            } else {
                remember(prop, 'color');
                prop.style.setProperty('color', val);
            }
        });
    }

    function clearMarks(el) {
        ATTRS.forEach((a) => el.removeAttribute(a));
        mixedSet.delete(el);
    }

    const markOf = (el) => (el.hasAttribute('data-dm-keep') ? 'keep' : el.hasAttribute('data-dm-flip') ? 'flip' : el.hasAttribute('data-dm-mixed') ? 'mixed' : null);

    function setMark(el, t) {
        if (markOf(el) === t) return;
        ATTRS.forEach((a) => el.removeAttribute(a));
        el.setAttribute('data-dm-' + t, '');
    }

    // Mengembalikan semua timpaan warna di dalam `top` (wajib sebelum membaca gaya asli elemen).
    function restoreWithin(top) {
        [...saved.keys()].forEach((el) => { if (top.contains(el)) restoreOverrides(el); });
    }

    function commit(top, acts) {
        const next = new Map();
        acts.forEach((a) => next.set(a.el, a));
        const old = [...top.querySelectorAll(SEL_MARKED)];
        if (top.matches(SEL_MARKED)) old.push(top);
        old.forEach((el) => { if (!next.has(el)) clearMarks(el); });
        next.forEach((a, el) => {
            setMark(el, a.t);
            if (a.t === 'mixed') { mixedSet.add(el); if (a.ov.length) setOverrides(el, a.ov); } else mixedSet.delete(el);
        });
        // tanda baru untuk pembungkus (nilai yang terbaca kini adalah nilai hasil timpaan)
        next.forEach((a, el) => { if (a.t === 'mixed') sigs.set(el, sigOf(getComputedStyle(el))); });
    }

    // ---------- Pemindaian ----------

    let observer = null;
    const observeOpts = { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'style', 'hidden', 'aria-selected', 'aria-pressed', 'aria-current', 'aria-expanded'] };

    // Transisi CSS dimatikan selama penanda diubah: tanpa ini, warna yang sedang bertransisi (mis. kartu `transition-all`) terbaca
    // di nilai antara dan salah dikira permukaan gelap. Penahan berhitung supaya tumpang tindih aman.
    let holds = 0;
    function holdAnim() {
        holds++;
        root.setAttribute('data-dm-noanim', '');
    }
    function releaseAnim() {
        requestAnimationFrame(() => requestAnimationFrame(() => { if (--holds <= 0) { holds = 0; root.removeAttribute('data-dm-noanim'); } }));
    }

    function paused(fn) {
        holdAnim();
        if (observer) observer.disconnect();
        try { fn(); } finally {
            if (observer && document.body) observer.observe(document.body, observeOpts);
            releaseAnim();
        }
    }

    const stackOf = (el) => {
        const stack = [];
        for (let p = el.parentElement; p; p = p.parentElement) stack.unshift({ el: p, cs: getComputedStyle(p) });
        return stack;
    };

    // Menelusuri ulang `top` (body bila kosong) dan menerapkan hasilnya.
    function rebuild(top) {
        const el = !top || top === document.body ? document.body : top;
        if (!el) return;
        restoreWithin(el);
        const n = build(el, stackOf(el), new Set());
        if (!n) return;
        const acts = [];
        if (n.k !== 'K' && n.flip) acts.push({ el, t: 'flip' });
        else emit(n, acts);
        commit(el, acts);
    }

    function fullScan() {
        if (!isDark() || !document.body) return;
        paused(() => rebuild(document.body));
    }

    // Tutup semua penanda (kembali ke mode terang / sebelum cetak)
    function clearAll() {
        paused(() => {
            [...saved.keys()].forEach(restoreOverrides);
            document.querySelectorAll(SEL_MARKED).forEach(clearMarks);
            mixedSet.clear();
        });
    }

    // Elemen yang ditambahkan / berubah belakangan (Alpine, fetch, Tailwind) dipindai berkelompok.
    const pendingAdd = new Set();
    const pendingAttr = new Set();
    let timer = null;

    function flush() {
        timer = null;
        if (!isDark()) { pendingAdd.clear(); pendingAttr.clear(); return; }
        const tops = new Set();
        const guard = (n) => !n.isConnected || n.closest('[data-dm-native]') || (n.parentElement && n.parentElement.closest('[data-dm-keep]'));

        pendingAdd.forEach((n) => {
            if (guard(n) || n === document.body) return;
            const flipRoot = n.parentElement && n.parentElement.closest('[data-dm-flip]');
            if (flipRoot) {
                // Isi baru di dalam akar F: cukup bila ia sendiri aman difilter bersama akarnya; bila memuat K/popup/elemen yang
                // acuan posisinya di luar akar, akar itu ditelusuri ulang.
                const own = new Set();
                const node = build(n, stackOf(n), own);
                const inside = (x) => x !== 'viewport' && flipRoot.contains(x);
                const ok = !node || (node.k !== 'K' && !node.hard && !node.layered && [...own].every(inside) && [...node.esc].every(inside));
                if (!ok) tops.add(flipRoot);
            } else {
                tops.add(n);
            }
        });
        pendingAttr.forEach((t) => {
            if (guard(t) && !t.hasAttribute('data-dm-keep')) return;
            const old = sigs.get(t);
            if (old === undefined) return;
            if (sigOf(getComputedStyle(t)) === old) return;
            tops.add(t.closest('[data-dm-flip]') || t);
        });
        pendingAdd.clear();
        pendingAttr.clear();
        if (!tops.size) return;
        // buang akar yang sudah tercakup akar lain
        const list = [...tops].filter((t) => ![...tops].some((o) => o !== t && o.contains(t)));
        paused(() => list.forEach((t) => { if (t.isConnected) rebuild(t); }));
    }

    function schedule() { if (!timer) timer = setTimeout(flush, 90); }

    function makeObserver() {
        observer = new MutationObserver((records) => {
            if (!isDark()) return;
            records.forEach((r) => {
                if (r.type === 'attributes') pendingAttr.add(r.target);
                else r.addedNodes.forEach((n) => { if (n.nodeType === 1) pendingAdd.add(n); });
            });
            if (pendingAdd.size || pendingAttr.size) schedule();
        });
    }

    // ---------- Tema ----------

    function syncSwitches() {
        document.querySelectorAll('[data-theme-switch]').forEach((sw) => sw.setAttribute('aria-checked', isDark() ? 'true' : 'false'));
    }

    function noAnim(fn) {
        holdAnim();
        try { fn(); } finally { releaseAnim(); }
    }

    function setTheme(theme) {
        if (theme === 'dark') {
            noAnim(() => {
                root.setAttribute('data-theme', 'dark');
                fullScan();
                root.setAttribute('data-dm-ready', '');
            });
        } else {
            noAnim(() => {
                clearAll();
                root.removeAttribute('data-theme');
                root.removeAttribute('data-dm-ready');
            });
        }
        syncSwitches();
        window.dispatchEvent(new CustomEvent('simagang:theme', { detail: { theme: isDark() ? 'dark' : 'light' } }));
    }

    // Pergantian dari menu: splash screen menutupi proses (bila komponen splash ada di halaman).
    function change(theme, persist) {
        if (persist) write(theme === 'dark' ? 'dark' : 'light'); // simpan segera; splash hanya menutupi pergantian tampilan
        const finish = () => setTheme(theme);
        if (window.SimagangSplash && window.SimagangSplash.available()) window.SimagangSplash.transition(theme, finish);
        else finish();
    }

    // ---------- Menu gerigi ----------

    function closeMenus(except) {
        document.querySelectorAll('[data-theme-menu]').forEach((menu) => {
            if (menu === except) return;
            const trigger = menu.querySelector('[data-theme-trigger]');
            const panel = menu.querySelector('.sm-theme__panel');
            if (panel && !panel.hidden) { panel.hidden = true; trigger.setAttribute('aria-expanded', 'false'); }
        });
    }

    function openMenu(menu) {
        const trigger = menu.querySelector('[data-theme-trigger]');
        const panel = menu.querySelector('.sm-theme__panel');
        closeMenus(menu);
        panel.hidden = !panel.hidden;
        trigger.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
        if (panel.hidden) return;
        // Di layar sempit ikon bisa berada jauh dari tepi kanan; geser panel supaya selalu utuh di dalam layar.
        panel.style.right = '';
        const r = panel.getBoundingClientRect();
        const vw = document.documentElement.clientWidth;
        if (r.left < 12) panel.style.right = -(12 - r.left) + 'px';
        else if (r.right > vw - 12) panel.style.right = (r.right - (vw - 12)) + 'px';
        panel.querySelector('[data-theme-switch]').focus();
    }

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-theme-trigger]');
        if (trigger) { openMenu(trigger.closest('[data-theme-menu]')); return; }
        if (e.target.closest('[data-theme-switch]')) {
            const menu = e.target.closest('[data-theme-menu]');
            closeMenus();
            if (menu) menu.querySelector('[data-theme-trigger]').focus();
            change(isDark() ? 'light' : 'dark', true);
            return;
        }
        if (!e.target.closest('[data-theme-menu]')) closeMenus();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const open = [...document.querySelectorAll('[data-theme-menu]')].find((m) => { const p = m.querySelector('.sm-theme__panel'); return p && !p.hidden; });
        if (!open) return;
        closeMenus();
        open.querySelector('[data-theme-trigger]').focus();
    });

    // Fokus keluar dari menu (Tab) → tutup
    document.addEventListener('focusout', (e) => {
        const menu = e.target.closest && e.target.closest('[data-theme-menu]');
        if (menu && e.relatedTarget && !menu.contains(e.relatedTarget)) closeMenus();
    });

    // Sinkron antar tab browser
    window.addEventListener('storage', (e) => {
        if (e.key === KEY) setTheme(e.newValue === 'dark' ? 'dark' : 'light');
    });

    // Cetak selalu terang
    window.addEventListener('beforeprint', () => { if (isDark()) clearAll(); });
    window.addEventListener('afterprint', () => { if (isDark()) fullScan(); });

    window.SimagangTheme = {
        isDark,
        set: (t) => change(t, true),
        toggle: () => change(isDark() ? 'light' : 'dark', true),
        rescan: fullScan,
    };

    // ---------- Mulai ----------

    function boot() {
        makeObserver();
        if (read() === 'dark') root.setAttribute('data-theme', 'dark');
        syncSwitches();
        if (document.body) observer.observe(document.body, observeOpts);
        if (!isDark()) return;

        // Tailwind (CDN) menyusun gayanya tepat setelah DOMContentLoaded: tunggu satu frame sebelum pemindaian pertama,
        // lalu buka halaman. Pemindaian ulang menangkap gaya/elemen yang datang belakangan.
        holdAnim();
        const first = () => {
            fullScan();
            root.setAttribute('data-dm-ready', '');
            window.dispatchEvent(new CustomEvent('simagang:theme-ready'));
            releaseAnim();
        };
        requestAnimationFrame(() => setTimeout(first, 30));
        window.addEventListener('load', () => { fullScan(); });
        setTimeout(fullScan, 500);
        setTimeout(fullScan, 1600);
        // Jaring pengaman: jangan pernah membiarkan halaman tersembunyi
        setTimeout(() => root.setAttribute('data-dm-ready', ''), 2500);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
