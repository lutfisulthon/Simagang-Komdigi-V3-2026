/*
 * Filter langsung: form pencarian/filter berbasis GET otomatis dikirim saat isiannya berubah, tanpa menekan tombol Filter.
 *  - select, tanggal, centang, radio : langsung dikirim saat nilai berubah.
 *  - kolom teks / pencarian           : dikirim setelah berhenti mengetik (DEBOUNCE_MS). Enter tetap berfungsi.
 * Setelah halaman dimuat ulang, fokus dan posisi kursor di kolom teks serta posisi scroll dikembalikan.
 *
 * Tombol kirim polos ("Cari"/"Filter") disembunyikan karena tidak diperlukan lagi; tombol itu tetap ada di DOM (display:none),
 * jadi Enter di kolom teks tetap mengirim form. Tombol ber-name/formaction/onclick (aksi lain) dibiarkan.
 *
 * Kecualikan satu form dengan atribut data-no-live. Form ekspor/unduhan, form dengan target lain, dan form
 * tanpa kolom isian tidak disentuh.
 */
(function () {
    'use strict';

    const DEBOUNCE_MS = 450;
    const KEY = 'simagang-live-filter';
    const TEXT_TYPES = ['text', 'search', 'email', 'tel', 'url', 'number', 'password'];
    const INSTANT_TYPES = ['date', 'datetime-local', 'month', 'week', 'time', 'checkbox', 'radio', 'range', 'color'];
    const SKIP_ACTION = /\/(export|download|print)(\/|$|\?)/i;

    function isFilterForm(form) {
        if (!(form instanceof HTMLFormElement)) return false;
        if ((form.method || 'get').toLowerCase() !== 'get') return false;
        if (form.hasAttribute('data-no-live')) return false;
        if (form.target && form.target !== '_self') return false;
        if (SKIP_ACTION.test(form.getAttribute('action') || '')) return false;
        return !!form.querySelector('select, textarea, input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=reset]):not([type=file])');
    }

    const forms = () => Array.from(document.forms).filter(isFilterForm);
    const snapshot = (form) => new URLSearchParams(new FormData(form)).toString();
    const main = () => document.querySelector('main');

    const initial = new WeakMap();
    const timers = new WeakMap();
    const composing = new WeakSet();

    function submit(form, { quiet } = {}) {
        clearTimeout(timers.get(form));
        if (snapshot(form) === initial.get(form)) return;
        if (!form.checkValidity()) { form.reportValidity(); return; }

        try {
            const field = document.activeElement;
            const index = forms().indexOf(form);
            const scroller = main();
            sessionStorage.setItem(KEY, JSON.stringify({
                path: location.pathname,
                form: index,
                name: field && form.contains(field) && field.name ? field.name : null,
                start: field && typeof field.selectionStart === 'number' ? field.selectionStart : null,
                end: field && typeof field.selectionEnd === 'number' ? field.selectionEnd : null,
                scroll: scroller ? scroller.scrollTop : window.scrollY,
            }));
        } catch (e) { /* abaikan */ }

        // Mengetik di kolom pencarian tidak menutup layar dengan splash; perubahan select/tanggal tetap memakainya.
        if (quiet) {
            form.setAttribute('data-no-splash', '');
            setTimeout(() => form.removeAttribute('data-no-splash'), 200);
        }

        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.submit();
    }

    function onChange(e) {
        const el = e.target;
        const form = el && el.form;
        if (!form || !isFilterForm(form)) return;
        const tag = el.tagName;
        const type = (el.type || '').toLowerCase();

        if (tag === 'SELECT' || INSTANT_TYPES.includes(type)) {
            submit(form);
        } else if (TEXT_TYPES.includes(type) || tag === 'TEXTAREA') {
            // change pada kolom teks terjadi saat blur; kirim hanya bila nilainya memang berbeda.
            if (!composing.has(el)) submit(form, { quiet: true });
        }
    }

    function onInput(e) {
        const el = e.target;
        const form = el && el.form;
        if (!form || !isFilterForm(form) || composing.has(el)) return;
        const type = (el.type || '').toLowerCase();
        if (!(TEXT_TYPES.includes(type) || el.tagName === 'TEXTAREA')) return;
        clearTimeout(timers.get(form));
        timers.set(form, setTimeout(() => submit(form, { quiet: true }), DEBOUNCE_MS));
    }

    document.addEventListener('change', onChange);
    document.addEventListener('input', onInput);
    // Papan ketik IME (mis. HP): jangan kirim di tengah penyusunan huruf.
    document.addEventListener('compositionstart', (e) => composing.add(e.target));
    document.addEventListener('compositionend', (e) => { composing.delete(e.target); onInput(e); });
    // Enter / tombol Filter tetap berfungsi seperti biasa; batalkan kiriman tertunda supaya tidak ganda.
    document.addEventListener('submit', (e) => { if (e.target instanceof HTMLFormElement) clearTimeout(timers.get(e.target)); });

    // Tombol kirim polos tidak berguna lagi: sembunyikan (bukan hapus) supaya kirim dengan Enter tetap bekerja.
    function hideSubmitButtons(form) {
        form.querySelectorAll('button:not([type]), button[type=submit], input[type=submit]').forEach((b) => {
            if (b.name || b.hasAttribute('formaction') || b.hasAttribute('formtarget') || b.hasAttribute('onclick')
                || Array.from(b.attributes).some((a) => /^(x-on:|@)/.test(a.name))) return;
            b.style.display = 'none';
            b.setAttribute('data-live-hidden', '');
        });
    }

    function restore() {
        forms().forEach((f) => { initial.set(f, snapshot(f)); hideSubmitButtons(f); });

        let saved = null;
        try { saved = JSON.parse(sessionStorage.getItem(KEY) || 'null'); sessionStorage.removeItem(KEY); } catch (e) { /* abaikan */ }
        if (!saved || saved.path !== location.pathname) return;

        const scroller = main();
        if (saved.scroll) {
            if (scroller) scroller.scrollTop = saved.scroll;
            else window.scrollTo(0, saved.scroll);
        }

        const form = forms()[saved.form];
        const field = form && saved.name ? form.elements[saved.name] : null;
        if (field && field.focus && !(field instanceof RadioNodeList) && TEXT_TYPES.concat('textarea').includes((field.type || 'textarea').toLowerCase())) {
            field.focus({ preventScroll: true });
            try {
                const len = field.value.length;
                field.setSelectionRange(Math.min(saved.start ?? len, len), Math.min(saved.end ?? len, len));
            } catch (e) { /* tipe input tanpa seleksi (mis. number) */ }
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', restore);
    else restore();

    // Kembali lewat cache browser: nilai form dipulihkan browser, jadi hitung ulang nilai awal.
    window.addEventListener('pageshow', (e) => { if (e.persisted) forms().forEach((f) => initial.set(f, snapshot(f))); });
})();
