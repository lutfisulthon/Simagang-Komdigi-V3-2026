/*
 * Pratinjau Buku Manual (PDF) di dalam aplikasi, lengkap dengan tombol Unduh. Dimuat sesuai kebutuhan oleh PageGuide.juknis()
 * (public/js/page-guide.js). Berkas PDF: public/unduhan/Buku-Manual-SIMAGANG.pdf (sumber: docs/juknis/).
 * Peramban tanpa penampil PDF bawaan (mis. sebagian HP) menampilkan pilihan Unduh / Buka di tab baru.
 */
(function () {
    'use strict';
    if (window.JuknisViewer) return;

    const SELF = document.currentScript ? document.currentScript.src : '';
    const PDF = SELF ? new URL('../unduhan/Buku-Manual-SIMAGANG.pdf', SELF).href : '/unduhan/Buku-Manual-SIMAGANG.pdf';
    const FILENAME = 'Buku-Manual-SIMAGANG.pdf';

    const CSS = `
.jv-root { position: fixed; inset: 0; z-index: 1250; display: flex; align-items: stretch; justify-content: center; padding: 18px; background: rgba(15, 23, 42, .55); opacity: 0; transition: opacity .18s ease; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.jv-root.is-open { opacity: 1; }
.jv-root[hidden] { display: none; }
.jv-box { width: min(980px, 100%); display: flex; flex-direction: column; background: #fff; color: #0f172a; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0, 0, 0, .35); transform: translateY(10px); transition: transform .2s ease; }
.jv-root.is-open .jv-box { transform: none; }
.jv-head { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; }
.jv-ico { width: 36px; height: 36px; border-radius: 11px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.jv-ttl { min-width: 0; flex: 1 1 180px; }
.jv-ttl b { display: block; font-size: 15px; font-weight: 800; line-height: 1.2; }
.jv-ttl span { display: block; font-size: 12px; color: #64748b; margin-top: 2px; }
.jv-act { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.jv-btn { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border-radius: 10px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; font: 700 13px/1 inherit; cursor: pointer; text-decoration: none; transition: background .15s ease; }
.jv-btn:hover { background: #f1f5f9; }
.jv-btn--pri { background: #2563eb; border-color: #2563eb; color: #fff; }
.jv-btn--pri:hover { background: #1d4ed8; }
.jv-x { width: 36px; height: 36px; padding: 0; justify-content: center; }
.jv-body { position: relative; flex: 1; min-height: 0; background: #e2e8f0; }
.jv-frame { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: #fff; }
.jv-load { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 13px; color: #64748b; pointer-events: none; }
.jv-fallback { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 24px; text-align: center; font-size: 14px; color: #334155; background: #f8fafc; }
.jv-fallback i { font-size: 44px; color: #d97706; }
@media (max-width: 640px) { .jv-root { padding: 0; } .jv-box { border-radius: 0; } .jv-btn span { display: none; } .jv-btn { padding: 9px 12px; } .jv-fallback .jv-btn span { display: inline; } }
html[data-theme="dark"] .jv-root { background: rgba(0, 0, 0, .7); }
html[data-theme="dark"] .jv-box { background: #171717; color: #ececec; box-shadow: 0 24px 60px rgba(0, 0, 0, .7); }
html[data-theme="dark"] .jv-head { border-bottom-color: #2e2e2e; }
html[data-theme="dark"] .jv-ttl span { color: #a3a3a3; }
html[data-theme="dark"] .jv-ico { background: rgba(245, 158, 11, .16); color: #fbbf24; }
html[data-theme="dark"] .jv-btn { background: #232323; border-color: #3a3a3a; color: #ececec; }
html[data-theme="dark"] .jv-btn:hover { background: #2e2e2e; }
html[data-theme="dark"] .jv-btn--pri { background: #2563eb; border-color: #2563eb; color: #fff; }
html[data-theme="dark"] .jv-body { background: #0f0f0f; }
html[data-theme="dark"] .jv-load { color: #a3a3a3; }
html[data-theme="dark"] .jv-fallback { background: #1a1a1a; color: #d4d4d4; }
`;

    let root = null, frame = null, lastFocus = null;

    function el(tag, cls, html) {
        const n = document.createElement(tag);
        if (cls) n.className = cls;
        if (html != null) n.innerHTML = html;
        return n;
    }

    function build() {
        const st = document.createElement('style');
        st.textContent = CSS;
        document.head.appendChild(st);

        root = el('div', 'jv-root');
        root.setAttribute('data-dm-native', '');
        root.hidden = true;
        root.addEventListener('mousedown', (e) => { if (e.target === root) close(); });

        const box = el('div', 'jv-box');
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');
        box.setAttribute('aria-labelledby', 'jv-title');

        const head = el('div', 'jv-head');
        head.appendChild(el('div', 'jv-ico', '<i class="fas fa-file-pdf"></i>'));
        head.appendChild(el('div', 'jv-ttl', '<b id="jv-title">Buku Manual SIMAGANG</b><span>Panduan penggunaan untuk semua peran (PDF, ukuran A5)</span>'));

        const act = el('div', 'jv-act');
        const dl = el('a', 'jv-btn jv-btn--pri', '<i class="fas fa-download"></i><span>Unduh PDF</span>');
        dl.href = PDF; dl.setAttribute('download', FILENAME);
        const tab = el('a', 'jv-btn', '<i class="fas fa-up-right-from-square"></i><span>Tab baru</span>');
        tab.href = PDF; tab.target = '_blank'; tab.rel = 'noopener';
        const x = el('button', 'jv-btn jv-x', '<i class="fas fa-xmark"></i>');
        x.type = 'button'; x.setAttribute('aria-label', 'Tutup'); x.addEventListener('click', close);
        act.append(dl, tab, x);
        head.appendChild(act);

        const body = el('div', 'jv-body');
        if (navigator.pdfViewerEnabled === false) {
            // Tanpa penampil PDF bawaan (umumnya HP): jangan tampilkan bingkai kosong.
            const fb = el('div', 'jv-fallback', '<i class="fas fa-file-pdf"></i><div>Peramban ini tidak dapat menampilkan PDF langsung.<br>Unduh atau buka di tab baru untuk membacanya.</div>');
            const d2 = el('a', 'jv-btn jv-btn--pri', '<i class="fas fa-download"></i><span>Unduh PDF</span>');
            d2.href = PDF; d2.setAttribute('download', FILENAME);
            fb.appendChild(d2);
            body.appendChild(fb);
        } else {
            body.appendChild(el('div', 'jv-load', 'Memuat dokumen…'));
            frame = el('iframe', 'jv-frame');
            frame.title = 'Buku Manual SIMAGANG (PDF)';
            body.appendChild(frame);
        }

        box.append(head, body);
        root.appendChild(box);
        document.body.appendChild(root);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && root && !root.hidden) close(); });
    }

    function open() {
        if (!root) build();
        lastFocus = document.activeElement;
        if (frame && !frame.src) frame.src = PDF + '#view=FitH';
        root.hidden = false;
        requestAnimationFrame(() => root.classList.add('is-open'));
        document.documentElement.style.overflow = 'hidden';
        const first = root.querySelector('.jv-btn--pri');
        if (first) first.focus({ preventScroll: true });
    }

    function close() {
        if (!root || root.hidden) return;
        root.classList.remove('is-open');
        document.documentElement.style.overflow = '';
        setTimeout(() => { root.hidden = true; }, 200);
        if (lastFocus && lastFocus.focus) { try { lastFocus.focus({ preventScroll: true }); } catch (_) { /* abaikan */ } }
    }

    window.JuknisViewer = { open, close, url: PDF };
})();
