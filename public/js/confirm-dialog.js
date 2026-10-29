/*
 * Dialog konfirmasi (pengganti confirm() bawaan browser).
 * Pakai di form dengan atribut data-confirm-*; form dikirim hanya setelah pengguna menekan tombol konfirmasi.
 *
 *   <form method="POST" action="…"
 *         data-confirm-title="Setujui logbook ini?"
 *         data-confirm-message="Penjelasan singkat dampak aksi."
 *         data-confirm-subject="Nama · tanggal"        (opsional, kotak ringkasan)
 *         data-confirm-note="Cuplikan isi"              (opsional)
 *         data-confirm-ok="Ya, setujui"                 (label tombol konfirmasi)
 *         data-confirm-busy="Menyetujui…"               (label saat form dikirim)
 *         data-confirm-cancel="Batal"
 *         data-confirm-tone="success|danger|warning|primary"
 *         data-confirm-icon="fa-thumbs-up">
 *
 * Juga bisa dipanggil dari JS: SimagangConfirm.open({ title, message, … }) → Promise<boolean>.
 * Gaya: public/css/confirm-dialog.css.
 */
(function () {
    'use strict';

    if (window.SimagangConfirm) return;

    const TONES = { success: 'fa-circle-check', danger: 'fa-triangle-exclamation', warning: 'fa-circle-exclamation', primary: 'fa-circle-question' };
    let root = null;
    let current = null; // { resolve, trigger, busy }

    function el(tag, className, attrs) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        Object.keys(attrs || {}).forEach((k) => node.setAttribute(k, attrs[k]));
        return node;
    }

    function build() {
        root = el('div', 'sc-confirm', { hidden: '' });
        root.innerHTML =
            '<div class="sc-confirm__backdrop" data-sc-cancel></div>' +
            '<div class="sc-confirm__dialog" role="alertdialog" aria-modal="true" aria-labelledby="sc-confirm-title" aria-describedby="sc-confirm-desc" tabindex="-1">' +
            '  <div class="sc-confirm__icon" aria-hidden="true"><i class="fas"></i></div>' +
            '  <h2 class="sc-confirm__title" id="sc-confirm-title"></h2>' +
            '  <p class="sc-confirm__desc" id="sc-confirm-desc"></p>' +
            '  <div class="sc-confirm__subject" hidden><p class="sc-confirm__subject-title"></p><p class="sc-confirm__subject-note"></p></div>' +
            '  <div class="sc-confirm__actions">' +
            '    <button type="button" class="sc-btn sc-btn--ghost" data-sc-cancel></button>' +
            '    <button type="button" class="sc-btn sc-btn--ok" data-sc-ok><i class="fas fa-spinner fa-spin sc-btn__spin" aria-hidden="true" hidden></i><span class="sc-btn__label"></span></button>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(root);

        root.addEventListener('click', (e) => {
            if (!current || current.busy) return;
            if (e.target.closest('[data-sc-ok]')) finish(true);
            else if (e.target.closest('[data-sc-cancel]')) finish(false);
        });

        root.addEventListener('keydown', (e) => {
            if (!current) return;
            if (e.key === 'Escape' && !current.busy) {
                e.preventDefault();
                finish(false);
            } else if (e.key === 'Tab') {
                // Fokus berputar di dalam dialog.
                const items = [...root.querySelectorAll('button:not([disabled])')];
                if (!items.length) { e.preventDefault(); return; }
                const first = items[0];
                const last = items[items.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });
    }

    function set(selector, text) {
        const node = root.querySelector(selector);
        node.textContent = text || '';
        return node;
    }

    function open(opts) {
        if (current) return Promise.resolve(false);
        if (!root) build();
        // Opsi yang tidak diisi (undefined, mis. atribut data-* yang tidak ada) tidak boleh menimpa nilai bawaan.
        const given = {};
        Object.keys(opts || {}).forEach((k) => { if (opts[k] !== undefined && opts[k] !== '') given[k] = opts[k]; });
        const o = Object.assign({ title: 'Lanjutkan?', message: '', ok: 'Ya, lanjutkan', cancel: 'Batal', busy: 'Memproses…', tone: 'primary' }, given);
        const tone = TONES[o.tone] ? o.tone : 'primary';

        root.className = 'sc-confirm sc-confirm--' + tone;
        root.querySelector('.sc-confirm__icon i').className = 'fas ' + (o.icon || TONES[tone]);
        set('.sc-confirm__title', o.title);
        const desc = set('.sc-confirm__desc', o.message);
        desc.hidden = !o.message;
        const subject = root.querySelector('.sc-confirm__subject');
        subject.hidden = !(o.subject || o.note);
        set('.sc-confirm__subject-title', o.subject).hidden = !o.subject;
        set('.sc-confirm__subject-note', o.note).hidden = !o.note;
        set('[data-sc-cancel].sc-btn', o.cancel);
        set('.sc-btn__label', o.ok);
        root.querySelector('.sc-btn__spin').hidden = true;
        root.querySelectorAll('.sc-btn').forEach((b) => { b.disabled = false; });

        return new Promise((resolve) => {
            current = { resolve, trigger: document.activeElement, busy: false, busyLabel: o.busy, okLabel: o.ok };
            root.hidden = false;
            document.documentElement.classList.add('sc-confirm-open');
            // Aksi merusak: fokus awal di "Batal". Lainnya: di tombol konfirmasi.
            const target = tone === 'danger' ? root.querySelector('.sc-btn--ghost') : root.querySelector('[data-sc-ok]');
            requestAnimationFrame(() => {
                root.classList.add('is-visible');
                target.focus();
            });
        });
    }

    function close() {
        if (!root || !current) return;
        const trigger = current.trigger;
        current = null;
        root.classList.remove('is-visible');
        root.hidden = true;
        document.documentElement.classList.remove('sc-confirm-open');
        if (trigger && typeof trigger.focus === 'function' && document.contains(trigger)) trigger.focus();
    }

    function finish(ok) {
        if (!current) return;
        if (!ok) {
            const { resolve } = current;
            close();
            resolve(false);
            return;
        }
        // Konfirmasi: dialog tetap terbuka (status memproses) sampai halaman berpindah; cegah klik ganda.
        current.busy = true;
        root.querySelectorAll('.sc-btn').forEach((b) => { b.disabled = true; });
        root.querySelector('.sc-btn__spin').hidden = false;
        root.querySelector('.sc-btn__label').textContent = current.busyLabel;
        current.resolve(true);
    }

    // Form dengan data-confirm-title: tahan pengiriman sampai dikonfirmasi.
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-title')) return;
        if (form.__scConfirmed) { form.__scConfirmed = false; return; }

        e.preventDefault();
        const d = form.dataset;
        open({
            title: d.confirmTitle, message: d.confirmMessage, subject: d.confirmSubject, note: d.confirmNote,
            ok: d.confirmOk, cancel: d.confirmCancel, busy: d.confirmBusy, tone: d.confirmTone, icon: d.confirmIcon,
        }).then((ok) => {
            if (!ok) return;
            form.__scConfirmed = true;
            if (typeof form.requestSubmit === 'function') form.requestSubmit(e.submitter || undefined);
            else form.submit();
        });
    }, true);

    // Kembali lewat tombol Back (bfcache): jangan biarkan dialog macet dalam status memproses.
    window.addEventListener('pageshow', (e) => {
        if (e.persisted && current) {
            const { resolve } = current;
            close();
            resolve(false);
        }
    });

    window.SimagangConfirm = { open };
})();
