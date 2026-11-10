/*
 * Workspace — realtime (Fase 3, Laravel Reverb + Echo) dengan fallback polling.
 * Aktif hanya bila layout mengirim WS.config.realtime:
 *   { driver: 'reverb', key, host, port, scheme, scripts }   → Echo + pusher-js (public/vendor/echo)
 * Event realtime dipakai sebagai SINYAL: komponen langsung memanggil polling/refresh yang sudah ada,
 * sehingga hanya ada satu jalur penggabungan data. Selama terhubung, interval polling diperlambat
 * (lihat WS.createPoller); bila koneksi putus, polling kembali normal dengan sendirinya.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const cfg = WS.config.realtime || null;
    const listeners = new Map(); // channel → Set<{ event, handler }>
    let echo = null;

    const realtime = {
        status: cfg ? 'connecting' : 'off', // off | connecting | connected | unavailable
        driver: cfg ? cfg.driver : null,

        isLive() {
            return this.status === 'connected';
        },

        // subscribe('workspace.12', 'task.changed', fn) → fungsi untuk berhenti berlangganan.
        // Nama channel = channel privat Laravel (routes/channels.php), event = broadcastAs().
        subscribe(channel, event, handler) {
            if (!cfg) return () => {};
            if (!listeners.has(channel)) listeners.set(channel, new Set());
            const entry = { event, handler };
            listeners.get(channel).add(entry);
            if (echo) echo.private(channel).listen('.' + event, handler);
            return () => {
                listeners.get(channel)?.delete(entry);
                if (echo) echo.private(channel).stopListening('.' + event, handler);
            };
        },

        // Notifikasi Laravel (Database + Broadcast) untuk user yang login.
        onNotification(handler) {
            return this.subscribe('App.Models.User.' + (WS.config.user && WS.config.user.id), '__notification', handler);
        },
    };

    function setStatus(status) {
        realtime.status = status;
        window.dispatchEvent(new CustomEvent('ws-realtime-status', { detail: status }));
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = () => reject(new Error('Gagal memuat ' + src));
            document.head.appendChild(s);
        });
    }

    async function connectReverb() {
        try {
            // pusher-js 8.4.0 dan laravel-echo 1.16.1, dihost sendiri di public/vendor/echo (cfg.scripts).
            // Bila Chat Publik sudah memuatnya lebih dulu, tidak dimuat ulang.
            if (!window.Pusher) await loadScript(cfg.scripts[0]);
            if (!window.Echo) await loadScript(cfg.scripts[1]);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            echo = new window.Echo({
                broadcaster: 'reverb',
                key: cfg.key,
                wsHost: cfg.host,
                wsPort: cfg.port,
                wssPort: cfg.port,
                forceTLS: cfg.scheme === 'https',
                enabledTransports: ['ws', 'wss'],
                authEndpoint: (WS.config.baseUrl || '') + '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            });
            const conn = echo.connector.pusher.connection;
            conn.bind('state_change', ({ current }) => {
                setStatus(current === 'connected' ? 'connected' : ['unavailable', 'failed', 'disconnected'].includes(current) ? 'unavailable' : 'connecting');
            });
            // Pelanggan yang mendaftar sebelum Echo siap.
            listeners.forEach((set, channel) => set.forEach(({ event, handler }) => {
                if (event === '__notification') echo.private(channel).notification(handler);
                else echo.private(channel).listen('.' + event, handler);
            }));
        } catch (_) {
            setStatus('unavailable'); // tetap jalan dengan polling
        }
    }

    // onNotification memakai helper Echo .notification(), bukan .listen().
    const baseSubscribe = realtime.subscribe;
    realtime.subscribe = function (channel, event, handler) {
        if (event === '__notification' && echo) {
            echo.private(channel).notification(handler);
            return () => echo.leave(channel);
        }
        return baseSubscribe.call(this, channel, event, handler);
    };

    WS.realtime = realtime;

    if (cfg && cfg.driver === 'reverb') connectReverb();
})();
