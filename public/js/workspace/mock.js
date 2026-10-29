/*
 * Workspace — mock backend untuk pengembangan frontend.
 * Hanya dimuat saat preview lokal aktif (routes/workspace-preview.php).
 * Handler meniru bentuk respons di docs/workspace/kontrak-frontend.md,
 * termasuk aturan yang seharusnya ditolak backend (403/422), supaya UI teruji.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const handlers = {};
    const role = (WS.config.user && WS.config.user.role) || 'intern';

    const minutesAgo = (m) => new Date(Date.now() - m * 60000).toISOString();
    const delay = (ms) => new Promise((r) => setTimeout(r, ms));
    const clone = (v) => JSON.parse(JSON.stringify(v));

    function define(name, fn) {
        handlers[name] = fn;
    }

    // ---------- Notifikasi ----------

    // URL tujuan mengikuti fixture preview: kelompok pertama = 101 (channel kelompok = 1011, #pengumuman = 1).
    // ?task=T-n membuka slide-over bila tugasnya ada di board.
    const base = (WS.config.baseUrl || '').replace(/\/$/, '');
    const url = {
        task: (ws, n) => `${base}/workspace/${ws}?task=T-${n}`,
        channel: (id) => `${base}/workspace/101?tab=channel&channel=${id}`,
        workspace: (ws) => `${base}/workspace${ws ? '/' + ws : ''}`,
    };

    const notificationsByRole = {
        intern: [
            { type: 'sprint_leader', title: 'Anda ketua Sprint 2 · Tim Redesign Website', body: 'Bagi tugas sprint ke anggota lewat Board.', url: url.workspace(101), actor: 'Herman', ago: 4 },
            { type: 'task_assigned', title: 'Tugas baru: T-4 Rancang wireframe halaman profil', body: 'Dari Herman · deadline 28 Okt', url: url.task(101, 4), actor: 'Herman', ago: 6 },
            { type: 'task_revision', title: 'T-3 perlu revisi', body: '"Tambahkan validasi di form login, lalu kirim ulang."', url: url.task(101, 3), actor: 'Herman', ago: 42 },
            { type: 'mentioned', title: 'Herman menyebut Anda di #tim-redesign-website', body: 'tolong cek PR terakhir ya', url: url.channel(1011), actor: 'Herman', ago: 180, read: true },
            { type: 'task_due_soon', title: 'Deadline besok: T-2 Dokumentasi API', body: 'Deadline 27 Okt 17.00', url: url.task(101, 2), actor: null, ago: 600, read: true },
            { type: 'announcement', title: 'Pengumuman baru di #pengumuman', body: 'Sharing session Jumat ini pukul 14.00', url: url.channel(1), actor: 'Admin Simagang', ago: 1500, read: true },
        ],
        mentor: [
            { type: 'intern_joined', title: 'Anak bimbingan baru: Herman', body: 'Belum masuk kelompok. Masukkan ke kelompok agar bisa menerima tugas.', url: url.workspace(), actor: null, ago: 3 },
            { type: 'task_in_review', title: 'T-12 menunggu review', body: 'Andi Pratama mengirim hasil "Integrasi API absensi"', url: url.task(101, 12), actor: 'Andi Pratama', ago: 25 },
            { type: 'task_commented', title: 'Komentar baru di T-10', body: 'Rina: "Sudah saya update link Figma-nya."', url: url.task(101, 10), actor: 'Rina Wulandari', ago: 95, read: true },
            { type: 'task_overdue', title: 'T-8 terlambat', body: 'Deadline lewat 1 hari · Andi Pratama', url: url.task(101, 8), actor: null, ago: 900, read: true },
        ],
        admin: [
            { type: 'intern_without_mentor', title: 'Intern aktif tanpa mentor', body: 'Dewi Lestari (Industri) belum punya mentor.', url: url.workspace(), actor: null, ago: 10 },
        ],
    };

    // Riwayat lama (sudah dibaca, 1–30 hari lalu) supaya halaman notifikasi punya beberapa halaman.
    const historyByRole = {
        intern: [
            (n) => ({ type: 'task_assigned', title: `Tugas baru: T-${n} Lengkapi dokumentasi modul`, body: 'Dari Herman', url: url.task(101, n), actor: 'Herman' }),
            (n) => ({ type: 'task_approved', title: `T-${n} disetujui · nilai ${78 + (n % 20)}`, body: '"Hasil sudah sesuai, terima kasih."', url: url.task(101, n), actor: 'Herman' }),
            (n) => ({ type: 'task_commented', title: `Komentar baru di T-${n}`, body: 'Herman: "Sudah saya cek, lanjutkan."', url: url.task(101, n), actor: 'Herman' }),
            (n) => ({ type: 'task_revision', title: `T-${n} perlu revisi`, body: '"Lengkapi screenshot hasil uji."', url: url.task(101, n), actor: 'Herman' }),
            (n) => ({ type: 'mentioned', title: 'Rina Wulandari menyebut Anda di #tim-redesign-website', body: 'nanti siang kita bahas pembagian tugas ya', url: url.channel(1011), actor: 'Rina Wulandari' }),
            (n) => ({ type: 'announcement', title: 'Pengumuman baru di #pengumuman', body: 'Pengingat: absensi dibuka pukul 07.30–08.30 WIB.', url: url.channel(1), actor: 'Admin Simagang' }),
        ],
        mentor: [
            (n) => ({ type: 'task_in_review', title: `T-${n} menunggu review`, body: 'Andi Pratama mengirim hasil kerja', url: url.task(101, n), actor: 'Andi Pratama' }),
            (n) => ({ type: 'task_commented', title: `Komentar baru di T-${n}`, body: 'Siti Rahma: "Izin bertanya soal format laporan."', url: url.task(101, n), actor: 'Siti Rahma' }),
            (n) => ({ type: 'task_overdue', title: `T-${n} terlambat`, body: 'Deadline lewat · Rina Wulandari', url: url.task(101, n), actor: null }),
            (n) => ({ type: 'mentioned', title: 'Andi Pratama menyebut Anda di #tim-redesign-website', body: 'Kak, tugasnya sudah saya kirim untuk review.', url: url.channel(1011), actor: 'Andi Pratama' }),
            (n) => ({ type: 'intern_joined', title: 'Anak bimbingan baru', body: 'Masukkan ke kelompok agar bisa menerima tugas.', url: url.workspace(), actor: null }),
            (n) => ({ type: 'announcement', title: 'Pengumuman baru di #pengumuman', body: 'Formulir evaluasi mentor sudah dibuka.', url: url.channel(1), actor: 'Admin Simagang' }),
        ],
        admin: [
            (n) => ({ type: 'announcement', title: 'Pengumuman baru di #pengumuman', body: 'Jadwal evaluasi tengah magang sudah dibuka.', url: url.channel(1), actor: 'Admin Simagang' }),
            (n) => ({ type: 'intern_without_mentor', title: 'Intern aktif tanpa mentor', body: 'Pendaftar dari role Industri belum punya mentor.', url: url.workspace(), actor: null }),
        ],
    };

    let seq = 100;
    const toNotification = (n, ago, read) => ({
        id: 'mock-' + (++seq),
        type: n.type,
        title: n.title,
        body: n.body,
        url: n.url,
        actor: n.actor ? { name: n.actor, avatar_url: null } : null,
        read_at: read ? minutesAgo(ago - 1) : null,
        created_at: minutesAgo(ago),
    });
    const notifications = (notificationsByRole[role] || notificationsByRole.intern).map((n) => toNotification(n, n.ago, n.read));
    const history = historyByRole[role] || historyByRole.intern;
    for (let i = 0; i < 38; i++) {
        const ago = 1440 + i * 1100 + (i % 5) * 97; // ± 1–30 hari lalu
        notifications.push(toNotification(history[i % history.length](2 + (i % 17)), ago, true));
    }

    // Simulasi notifikasi baru masuk ±45 detik setelah halaman dibuka, untuk menguji polling.
    setTimeout(() => {
        notifications.unshift(toNotification({
            type: 'announcement',
            title: 'Pengumuman baru di #pengumuman',
            body: '@channel rapat sinkronisasi jam 15.00',
            url: url.channel(1),
            actor: 'Admin Simagang',
        }, 0, false));
    }, 45000);

    // Kategori filter halaman notifikasi (sama dengan WS.notifCategories di notifications.js).
    const CATEGORY = {
        task: (t) => t.startsWith('task_'),
        mention: (t) => t === 'mentioned',
        announcement: (t) => t === 'announcement',
        intern: (t) => t.startsWith('intern_'),
    };

    function summary() {
        const res = { unread_count: notifications.filter((n) => !n.read_at).length };
        // Usulan kontrak #3: jumlah pesan belum dibaca per channel (diisi mock-channels.js bila ada).
        if (typeof WS.mock.unreadMessages === 'function') res.unread_messages = WS.mock.unreadMessages();
        return res;
    }

    // Lonceng: ?limit=10. Halaman notifikasi: ?page=&per_page=&filter=unread&category=.
    define('notifications.summary', async (query) => {
        await delay(250);
        const limit = (query && query.limit) || 10;
        return Object.assign(summary(), { data: clone(notifications.slice(0, limit)) });
    });

    define('notifications.list', async (query) => {
        const q = query || {};
        await delay(350);
        const page = Math.max(1, Number(q.page) || 1);
        const perPage = Math.min(50, Number(q.per_page) || 20);
        if (q.category && !CATEGORY[q.category]) throw new WS.HttpError(422, 'Kategori tidak dikenal.', { category: ['Kategori tidak dikenal.'] });
        const list = notifications.filter((n) => (q.filter !== 'unread' || !n.read_at) && (!q.category || CATEGORY[q.category](n.type)));
        const data = list.slice((page - 1) * perPage, page * perPage);
        return Object.assign(summary(), { data: clone(data), meta: { current_page: page, has_more: list.length > page * perPage } });
    });

    // Dipakai mock lain (mis. mention di channel) untuk memunculkan notifikasi baru.
    function notify(n) {
        notifications.unshift(Object.assign({
            id: 'mock-' + (++seq), url: '#', actor: null, read_at: null, created_at: new Date().toISOString(),
        }, n));
        if (WS.realtime && WS.realtime.driver === 'mock') {
            WS.realtime.emit('App.Models.User.' + (WS.config.user && WS.config.user.id), '__notification', notifications[0]);
        }
    }

    // Preferensi notifikasi: dimuat sekali dari route preview, perubahan disimpan di memori.
    let preferences = null;
    define('notifications.preferences', async () => {
        if (!preferences) preferences = (await WS.http('GET', base + '/notifications/preferences')).data;
        await delay(200);
        return { data: clone(preferences) };
    });

    define('notifications.savePreferences', async (p) => {
        await delay(400);
        if (!preferences) preferences = (await WS.http('GET', base + '/notifications/preferences')).data;
        const known = new Map(preferences.types.map((t) => [t.type, t]));
        const errors = {};
        Object.entries(p.types || {}).forEach(([type, v]) => {
            const t = known.get(type);
            if (!t) errors['types.' + type] = ['Jenis notifikasi tidak dikenal.'];
            else if (v.email && t.email === null) errors['types.' + type] = ['Notifikasi ini tidak tersedia lewat email.'];
        });
        if (Object.keys(errors).length) throw new WS.HttpError(422, Object.values(errors)[0][0], errors);
        Object.entries(p.types || {}).forEach(([type, v]) => {
            const t = known.get(type);
            t.in_app = !!v.in_app;
            if (t.email !== null) t.email = !!v.email;
        });
        return { data: clone(preferences) };
    });

    define('notifications.readAll', async () => {
        await delay(200);
        const now = new Date().toISOString();
        notifications.forEach((n) => { if (!n.read_at) n.read_at = now; });
        return null;
    });

    define('notifications.read', async (id) => {
        await delay(120);
        const n = notifications.find((x) => x.id === id);
        if (!n) throw new WS.HttpError(404, 'Notifikasi tidak ditemukan.');
        n.read_at = n.read_at || new Date().toISOString();
        return null;
    });

    // ---------- Ringkasan dashboard ----------
    // Dihitung route preview dari fixture yang sama dengan board (GET /workspace/summary).
    define('dashboard.summary', () => WS.http('GET', base + '/workspace/summary'));
    define('tasks.mine', () => WS.http('GET', base + '/workspace/tasks/mine'));
    define('recap.get', (query) => WS.http('GET', base + '/workspace/recap', { query }));
    define('interns.tasks', (id) => WS.http('GET', base + '/workspace/interns/' + id + '/tasks'));
    define('monitoring.progress', (query) => WS.http('GET', base + '/workspace/progress', { query }));
    // Kelompok: route preview menyimpan perubahan di session user ini (tidak sinkron antar akun).
    define('groups.roster', () => WS.http('GET', base + '/workspace/groups/roster'));
    define('groups.create', (p) => WS.http('POST', base + '/workspace/groups', { body: p }));
    define('groups.update', (id, p) => WS.http('PATCH', base + '/workspace/groups/' + id, { body: p }));
    define('groups.addMembers', (id, p) => WS.http('POST', base + '/workspace/groups/' + id + '/members', { body: p }));

    // ---------- Aktivitas workspace ----------
    // Halaman men-seed data awal (WS.mock.seed); halaman berikutnya dibuat dari data itu
    // dengan id lebih kecil dan waktu mundur 3 hari per halaman. Maksimal 3 halaman tambahan.

    const seeds = {};

    define('activities.list', async (workspaceId, query) => {
        await delay(450);
        const base = seeds['activities:' + workspaceId] || [];
        if (!base.length) return { data: [], meta: { has_more: false } };
        const before = Number((query && query.before) || base[base.length - 1].id);
        const page = Math.max(1, Math.round((base[0].id - before + 1) / base.length));
        const data = base.map((item, i) => Object.assign(clone(item), {
            id: before - 1 - i,
            created_at: new Date(new Date(item.created_at).getTime() - page * 3 * 86400000).toISOString(),
        }));
        return { data, meta: { has_more: page < 3 } };
    });

    WS.mock = {
        has: (name) => Object.prototype.hasOwnProperty.call(handlers, name),
        // Uji keadaan error/lambat dari console: WS.mock.failing = ['tasks.move'] (gagal 500; failStatus = 0 → offline),
        // WS.mock.slow = 3000 (tambahan jeda ms). Kosongkan untuk kembali normal.
        handle: (name, args) => {
            const m = WS.mock;
            const run = () => {
                if (m.failing && m.failing.includes(name)) {
                    return Promise.reject(m.failStatus === 0
                        ? new WS.HttpError(0, 'Tidak dapat terhubung ke server. Periksa koneksi Anda.')
                        : new WS.HttpError(500, 'Terjadi kesalahan di server.'));
                }
                return handlers[name](...args);
            };
            return m.slow ? delay(m.slow).then(run) : run();
        },
        failing: [],
        failStatus: 500,
        slow: 0,
        seed: (key, value) => { if (!seeds[key]) seeds[key] = clone(value); },
        define,
        notify,
        delay,
        clone,
        minutesAgo,
    };
})();
