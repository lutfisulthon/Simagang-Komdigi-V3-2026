/*
 * Peningkatan untuk halaman daftar peserta magang (Monitoring Bulanan, Kelola Peserta Magang,
 * Anak Magang Bimbingan): saat mengetik/memfilter, konten diperbarui lewat AJAX (fetch + ganti
 * innerHTML) sehingga navbar/sidebar TIDAK ikut reload — cukup area konten yang menampilkan
 * loading singkat. Juga menambahkan dropdown saran nama (seperti saran pencarian) sambil mengetik.
 *
 * Kontrak markup per halaman (lihat resources/views/admin/monitoring, admin/intern, mentor/intern):
 *   - Pembungkus konten utama: elemen ber-id unik + atribut [data-ajax-content].
 *   - Form filter: <form data-live-filter data-suggest-url="{{ route(...search-suggest) }}">.
 *   - Pembungkus kolom cari (untuk jangkar dropdown saran): [data-search-wrap] (induk langsung input[name=search]).
 *
 * Form filter tetap form GET biasa (bekerja tanpa JS); skrip ini hanya mencegat submit/klik tautan
 * di dalam area yang sama lalu menukarnya dengan fetch, supaya tetap progressive enhancement.
 * Form lain (ekspor, hapus, dsb.) tidak disentuh karena tidak ber-atribut data-live-filter.
 */
(function () {
    'use strict';

    function debounce(fn, ms) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function execScripts(container) {
        container.querySelectorAll('script').forEach((old) => {
            const s = document.createElement('script');
            Array.from(old.attributes).forEach((a) => s.setAttribute(a.name, a.value));
            s.textContent = old.textContent;
            old.replaceWith(s);
        });
    }

    function suggestGroupHtml(list, label, color) {
        if (!list || !list.length) return '';
        let html = '<div class="live-suggest__group"><span class="live-suggest__dot" style="background:' + color + '"></span>' + label + '</div>';
        list.forEach((i) => {
            html += '<button type="button" class="live-suggest__item" data-name="' + escapeHtml(i.name) + '">'
                + '<span class="live-suggest__name">' + escapeHtml(i.name) + '</span>'
                + (i.institution ? '<span class="live-suggest__sub">' + escapeHtml(i.institution) + '</span>' : '')
                + '</button>';
        });
        return html;
    }

    function setupRoot(root) {
        let suggestBox = null;
        let activeIndex = -1;
        let swapToken = 0; // menandai permintaan swap terbaru; respons yang sudah basi (lebih lama) diabaikan

        function closeSuggest() {
            if (suggestBox) { suggestBox.remove(); suggestBox = null; }
            activeIndex = -1;
        }

        async function doSwap(url, push) {
            const myToken = ++swapToken;
            const input = root.querySelector('input[name="search"]');
            let focusState = null;
            if (input && document.activeElement === input) {
                focusState = { start: input.selectionStart, end: input.selectionEnd };
            }

            root.classList.add('ajax-loading');
            const overlay = document.createElement('div');
            overlay.className = 'ajax-loading-overlay';
            overlay.innerHTML = '<div class="ajax-spinner"></div>';
            root.appendChild(overlay);

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (myToken !== swapToken) return; // ada permintaan lebih baru yang menyusul: buang respons ini
                if (!res.ok) throw new Error('respons tidak ok');
                const html = await res.text();
                if (myToken !== swapToken) return;
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const fresh = (root.id && doc.getElementById(root.id)) || doc.querySelector('[data-ajax-content]');
                if (!fresh) throw new Error('target tidak ditemukan di respons');

                root.innerHTML = fresh.innerHTML;
                execScripts(root);
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(root);
                }
                if (push) history.pushState({ ajaxUrl: url }, '', url);

                if (focusState) {
                    const newInput = root.querySelector('input[name="search"]');
                    if (newInput) {
                        newInput.focus({ preventScroll: true });
                        try { newInput.setSelectionRange(focusState.start, focusState.end); } catch (e) { /* abaikan */ }
                    }
                }
            } catch (err) {
                if (myToken !== swapToken) return; // sudah digantikan permintaan lain, jangan ikut campur navigasi
                // AJAX gagal (mis. jaringan putus): jatuh ke navigasi biasa supaya fitur tetap jalan.
                window.location = url;
            } finally {
                if (myToken === swapToken) root.classList.remove('ajax-loading');
            }
        }

        root.addEventListener('submit', (e) => {
            const form = e.target.closest('form[data-live-filter]');
            if (!form) return;
            e.preventDefault();
            closeSuggest();
            const params = new URLSearchParams(new FormData(form));
            Array.from(params.keys()).forEach((k) => { if (!params.get(k)) params.delete(k); });
            const qs = params.toString();
            doSwap(form.getAttribute('action') + (qs ? '?' + qs : ''), true);
        });

        root.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a || !root.contains(a)) return;
            let url;
            try { url = new URL(a.href, location.href); } catch (err) { return; }
            if (url.pathname !== location.pathname) return; // cuma tautan filter/pagination ke halaman yang sama
            e.preventDefault();
            closeSuggest();
            doSwap(a.href, true);
        });

        const form = root.querySelector('form[data-live-filter]');
        const suggestUrl = form ? form.getAttribute('data-suggest-url') : null;
        if (!suggestUrl) return;

        const fetchSuggest = debounce(async (input) => {
            const q = input.value.trim();
            closeSuggest();
            if (!q) return;
            try {
                const res = await fetch(suggestUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                const active = data.active || [];
                const alumni = data.alumni || [];
                let html = suggestGroupHtml(active, 'Aktif', '#10b981') + suggestGroupHtml(alumni, 'Alumni', '#64748b');
                if (!active.length && !alumni.length) {
                    html = '<div class="live-suggest__empty">Tidak ditemukan anak magang bernama &ldquo;' + escapeHtml(q) + '&rdquo;</div>';
                }
                const wrap = input.closest('[data-search-wrap]') || input.parentElement;
                suggestBox = document.createElement('div');
                suggestBox.className = 'live-suggest';
                suggestBox.innerHTML = html;
                wrap.appendChild(suggestBox);
                activeIndex = -1;
                suggestBox.querySelectorAll('.live-suggest__item').forEach((el) => {
                    el.addEventListener('mousedown', (ev) => {
                        ev.preventDefault();
                        input.value = el.getAttribute('data-name');
                        closeSuggest();
                        if (input.form) {
                            if (typeof input.form.requestSubmit === 'function') input.form.requestSubmit();
                            else input.form.submit();
                        }
                    });
                });
            } catch (e) { /* saran gagal dimuat: bukan fitur kritikal, abaikan diam-diam */ }
        }, 300);

        root.addEventListener('input', (e) => {
            if (e.target.name !== 'search' || e.target.tagName !== 'INPUT') return;
            fetchSuggest(e.target);
        });

        root.addEventListener('keydown', (e) => {
            if (e.target.name !== 'search' || !suggestBox) return;
            const items = Array.from(suggestBox.querySelectorAll('.live-suggest__item'));
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                e.target.value = items[activeIndex].getAttribute('data-name');
                closeSuggest();
            } else if (e.key === 'Escape') {
                closeSuggest();
            }
        });

        document.addEventListener('click', (e) => {
            if (suggestBox && !e.target.closest('[data-search-wrap]')) closeSuggest();
        });

        window.addEventListener('popstate', () => doSwap(location.href, false));
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-ajax-content]').forEach(setupRoot);
    });
})();
