/*
 * Workspace — utilitas inti (tanpa build step).
 * Dimuat di <head> SEBELUM Alpine supaya listener `alpine:init` sempat terdaftar.
 * Konfigurasi dikirim layout lewat window.SimagangWorkspace (lihat layouts/partials/workspace-head).
 */
(function () {
    'use strict';

    const config = Object.assign({
        mock: false,
        user: null,
        endpoints: {},
        pollInterval: 30000,
    }, window.SimagangWorkspace || {});

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // ---------- HTTP ----------

    class HttpError extends Error {
        constructor(status, message, errors) {
            super(message);
            this.name = 'HttpError';
            this.status = status;
            this.errors = errors || {};
        }
    }

    function buildUrl(url, query) {
        if (!query) return url;
        const params = new URLSearchParams();
        Object.entries(query).forEach(([key, value]) => {
            if (value === undefined || value === null || value === '') return;
            if (Array.isArray(value)) value.forEach((v) => params.append(key + '[]', v));
            else params.append(key, value);
        });
        const qs = params.toString();
        return qs ? url + (url.includes('?') ? '&' : '?') + qs : url;
    }

    function messageForStatus(status, payload) {
        if (payload && payload.message) return payload.message;
        if (status === 403) return 'Anda tidak memiliki akses untuk aksi ini.';
        if (status === 404) return 'Data tidak ditemukan.';
        if (status === 419) return 'Sesi sudah expired. Silakan muat ulang halaman.';
        if (status === 422) return 'Periksa kembali isian Anda.';
        if (status === 429) return 'Terlalu banyak permintaan. Coba lagi sebentar.';
        if (status >= 500) return 'Terjadi kesalahan di server.';
        return 'Permintaan gagal.';
    }

    async function http(method, url, options = {}) {
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        };
        let body;
        if (options.body instanceof FormData) {
            body = options.body;
        } else if (options.body !== undefined) {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(options.body);
        }

        // Permintaan yang mengubah data memakai layar pemuatan bila lambat (chat dan penanda baca dikecualikan).
        const splash = window.SimagangSplash;
        const track = !!splash && String(method).toUpperCase() !== 'GET' && !options.silent && !/\/(messages|read|typing)(\/|\?|$)/.test(url);
        if (track) splash.begin('Memproses…');

        try {
            let response;
            try {
                response = await fetch(buildUrl(url, options.query), {
                    method,
                    headers,
                    body,
                    credentials: 'same-origin',
                    signal: options.signal,
                });
            } catch (err) {
                if (err.name === 'AbortError') throw err;
                throw new HttpError(0, 'Tidak dapat terhubung ke server. Periksa koneksi Anda.');
            }

            const payload = response.status === 204 ? null : await response.json().catch(() => null);
            if (!response.ok) {
                throw new HttpError(response.status, messageForStatus(response.status, payload), payload && payload.errors);
            }
            return payload;
        } finally {
            if (track) splash.end();
        }
    }

    // fetch tidak menyediakan progres upload, jadi upload memakai XHR.
    function upload(url, formData, onProgress) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
            xhr.withCredentials = true;
            if (onProgress) {
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) onProgress(Math.round((e.loaded / e.total) * 100));
                });
            }
            xhr.addEventListener('load', () => {
                let payload = null;
                try { payload = JSON.parse(xhr.responseText); } catch (_) { /* respons bukan JSON */ }
                if (xhr.status >= 200 && xhr.status < 300) resolve(payload);
                else reject(new HttpError(xhr.status, messageForStatus(xhr.status, payload), payload && payload.errors));
            });
            xhr.addEventListener('error', () => reject(new HttpError(0, 'Upload gagal. Periksa koneksi Anda.')));
            xhr.send(formData);
        });
    }

    // ---------- Poller ----------

    /*
     * Menjalankan `task` berulang hanya saat tab aktif.
     * `task` mengembalikan true bila ada data baru; interval kembali ke `min`.
     * Bila tidak ada perubahan, interval naik bertahap sampai `max` (adaptif 5 → 30 detik).
     * Selama realtime terhubung (WS.realtime), polling hanya jaring pengaman: minimal `live` (default 60 detik).
     */
    function createPoller(task, options = {}) {
        const min = options.min || 5000;
        const max = options.max || 30000;
        const step = options.step || 1.5;
        const live = options.live || 60000;
        let interval = min;
        let timer = null;
        let running = false;
        let inFlight = false;

        async function tick() {
            timer = null;
            if (!running || document.hidden || inFlight) return schedule();
            inFlight = true;
            try {
                const changed = await task();
                interval = changed ? min : Math.min(max, Math.round(interval * step));
            } catch (_) {
                interval = max;
            } finally {
                inFlight = false;
            }
            schedule();
        }

        function schedule() {
            if (!running || timer) return;
            const rt = window.WS && window.WS.realtime;
            timer = setTimeout(tick, rt && rt.isLive() ? Math.max(interval, live) : interval);
        }

        // Status realtime berubah (terhubung / putus): susun ulang jadwal dengan interval yang sesuai.
        function onRealtime() {
            if (!running) return;
            clearTimeout(timer);
            timer = null;
            schedule();
        }

        function onVisibility() {
            if (!document.hidden && running) poller.now();
        }

        const poller = {
            start() {
                if (running) return poller;
                running = true;
                document.addEventListener('visibilitychange', onVisibility);
                window.addEventListener('ws-realtime-status', onRealtime);
                schedule();
                return poller;
            },
            stop() {
                running = false;
                clearTimeout(timer);
                timer = null;
                document.removeEventListener('visibilitychange', onVisibility);
                window.removeEventListener('ws-realtime-status', onRealtime);
            },
            // Jalankan segera dan kembalikan interval ke minimum (dipakai setelah user mengirim sesuatu).
            now() {
                interval = min;
                clearTimeout(timer);
                timer = null;
                tick();
            },
        };
        return poller;
    }

    // ---------- Waktu ----------

    const rtf = new Intl.RelativeTimeFormat('id', { numeric: 'auto' });
    const dateFmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const dateTimeFmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

    const time = {
        relative(iso) {
            if (!iso) return '';
            const diff = (new Date(iso).getTime() - Date.now()) / 1000;
            const abs = Math.abs(diff);
            if (abs < 45) return 'baru saja';
            if (abs < 3600) return rtf.format(Math.round(diff / 60), 'minute');
            if (abs < 86400) return rtf.format(Math.round(diff / 3600), 'hour');
            if (abs < 86400 * 7) return rtf.format(Math.round(diff / 86400), 'day');
            return dateFmt.format(new Date(iso));
        },
        date(iso) {
            return iso ? dateFmt.format(new Date(iso)) : '';
        },
        dateTime(iso) {
            return iso ? dateTimeFmt.format(new Date(iso)) : '';
        },
        // 'overdue' | 'soon' (≤ 24 jam) | 'normal'
        dueState(iso, status) {
            if (!iso || status === 'done') return 'normal';
            const diff = new Date(iso).getTime() - Date.now();
            if (diff < 0) return 'overdue';
            if (diff <= 86400000) return 'soon';
            return 'normal';
        },
    };

    // ---------- Enum (label harus sama dengan komponen Blade) ----------

    const STATUS = {
        todo: 'To Do',
        in_progress: 'In Progress',
        review: 'Review',
        revision: 'Revisi',
        done: 'Selesai',
    };

    const PRIORITY = {
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
        urgent: 'Urgent',
    };

    // ---------- Tugas lewat deadline ----------

    /*
     * Lewat deadline dan belum dikirim (bukan Review/Selesai) → tag "Belum diselesaikan".
     * Intern tidak dapat lagi mengirim hasil sampai mentor memperpanjang deadline.
     * Hanya di UI; server tetap perlu menolak pengiriman setelah deadline.
     */
    const tasks = {
        LOCK_NOTE: 'Deadline sudah lewat, hasil tugas tidak dapat dikirim lagi. Hubungi mentor untuk perpanjangan deadline.',
        unfinished(card) {
            if (!card || card.status === 'review' || card.status === 'done') return false;
            return !!card.is_late || time.dueState(card.due_at, card.status) === 'overdue';
        },
        // Hanya intern yang di-assign (bisa memindahkan, tidak bisa mereview).
        submitLocked(card) {
            return tasks.unfinished(card) && !!card.can && !!card.can.move && !card.can.review;
        },
    };

    // ---------- Lain-lain ----------

    // 'auto' bila user memilih kurangi animasi (prefers-reduced-motion), selain itu 'smooth'.
    function scrollBehavior() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
    }

    function initials(name) {
        return (name || '?').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase();
    }

    function toast(message, type = 'info') {
        if (window.Alpine) window.Alpine.store('wsToast').push(message, type);
    }

    // ---------- Dialog: fokus & keyboard ----------

    /*
     * x-ws-dialog="ekspresiTerbuka" — dipasang di kotak modal / slide-over.
     * Saat terbuka: fokus pindah ke [data-autofocus] atau kotak dialog, Tab/Shift+Tab berputar di dalamnya.
     * Esc hanya menutup dialog paling atas (event `ws-dialog-escape` di elemen ini).
     * Saat tertutup: fokus kembali ke elemen pemicu.
     */
    const dialogStack = [];
    const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function focusablesIn(el) {
        return [...el.querySelectorAll(FOCUSABLE)].filter((n) => n.getClientRects().length > 0 && !n.closest('[inert]'));
    }

    // Fase bubble: menu/saran di dalam dialog yang menangani Esc sendiri memanggil preventDefault lebih dulu.
    document.addEventListener('keydown', (e) => {
        const top = dialogStack[dialogStack.length - 1];
        if (!top) return;
        if (e.key === 'Escape') {
            if (e.defaultPrevented) return;
            e.preventDefault();
            e.stopPropagation();
            top.dispatchEvent(new CustomEvent('ws-dialog-escape', { bubbles: false }));
            return;
        }
        if (e.key !== 'Tab') return;
        const list = focusablesIn(top);
        if (!list.length) { e.preventDefault(); top.focus(); return; }
        const first = list[0];
        const last = list[list.length - 1];
        const active = document.activeElement;
        if (!top.contains(active)) { e.preventDefault(); (e.shiftKey ? last : first).focus(); }
        else if (e.shiftKey && (active === first || active === top)) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && active === last) { e.preventDefault(); first.focus(); }
    });

    document.addEventListener('alpine:init', () => {
        window.Alpine.directive('ws-dialog', (el, { expression }, { effect, evaluateLater, cleanup }) => {
            const isOpen = evaluateLater(expression);
            let previous = null;
            let wasOpen = false;
            if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex', '-1');

            const close = () => {
                const i = dialogStack.indexOf(el);
                if (i !== -1) dialogStack.splice(i, 1);
            };

            effect(() => isOpen((open) => {
                open = !!open;
                if (open === wasOpen) return;
                wasOpen = open;
                if (open) {
                    previous = document.activeElement;
                    dialogStack.push(el);
                    // Tunggu x-show/transisi supaya elemen sudah terlihat dan bisa difokus.
                    setTimeout(() => {
                        if (!wasOpen || el.contains(document.activeElement)) return;
                        const target = el.querySelector('[data-autofocus]');
                        (target && target.getClientRects().length ? target : el).focus({ preventScroll: true });
                    }, 60);
                } else {
                    close();
                    const back = previous;
                    previous = null;
                    if (back && back.isConnected && back !== document.body) setTimeout(() => back.focus({ preventScroll: true }), 0);
                }
            }));
            cleanup(close);
        });

        window.Alpine.store('wsToast', {
            items: [],
            seq: 0,
            push(message, type = 'info') {
                const id = ++this.seq;
                this.items.push({ id, message, type });
                setTimeout(() => this.dismiss(id), type === 'error' ? 6000 : 4000);
            },
            dismiss(id) {
                this.items = this.items.filter((t) => t.id !== id);
            },
        });
    });

    window.WS = {
        config,
        HttpError,
        http,
        upload,
        createPoller,
        time,
        tasks,
        STATUS,
        PRIORITY,
        initials,
        scrollBehavior,
        toast,
    };
})();
