/*
 * Workspace — mock endpoint otomasi (hanya mode preview).
 * Aturan disimpan di localStorage browser per kelompok (preview saja), jadi bertahan setelah halaman dimuat ulang.
 * "Jalankan sekarang" benar-benar membuat tugas lewat WS.mock.createTask sehingga board, list, timeline, dan laporan ikut berubah.
 * Validasi meniru usulan kontrak #21. Pembentuk jadwal/kalimat ada di WS.automations (automations.js).
 */
(function () {
    'use strict';

    const WS = window.WS;
    const { define, delay, clone } = WS.mock;
    const isStaff = ['mentor', 'admin'].includes(WS.config.user && WS.config.user.role);

    const cache = {};
    const key = (wsId) => 'ws-automations-' + wsId;
    const wsOf = (id) => Math.floor(id / 1000);
    const forbidden = () => new WS.HttpError(403, 'Hanya mentor yang dapat mengatur otomasi.');
    const invalid = (errors) => new WS.HttpError(422, Object.values(errors)[0][0], errors);

    function save(wsId) {
        try { window.localStorage.setItem(key(wsId), JSON.stringify(cache[wsId])); } catch (e) { /* penyimpanan tidak tersedia: data hidup selama halaman terbuka */ }
    }

    function rulesOf(wsId) {
        if (cache[wsId]) return cache[wsId];
        let stored = null;
        try { stored = JSON.parse(window.localStorage.getItem(key(wsId))); } catch (e) { /* abaikan */ }
        if (!Array.isArray(stored)) {
            // Contoh awal: dua aturan supaya daftar tidak kosong saat pertama dilihat.
            const make = (n, name, presetKey, enabled) => Object.assign(WS.automations.PRESETS[presetKey].build(), { id: wsId * 1000 + n, workspace_id: wsId, name, enabled, runs: [], last_run_at: null });
            stored = [make(1, 'Laporan mingguan', 'report', true), make(2, 'Pengingat logbook harian', 'logbook', false)];
        }
        cache[wsId] = stored;
        save(wsId);
        return stored;
    }

    const find = (id) => {
        const rule = rulesOf(wsOf(id)).find((r) => r.id === id);
        if (!rule) throw new WS.HttpError(404, 'Aturan tidak ditemukan.');
        return rule;
    };

    // Bentuk respons: tambah next_run_at (null bila nonaktif).
    function view(rule) {
        const out = clone(rule);
        const next = rule.enabled ? WS.automations.nextRuns(rule, 1)[0] : null;
        out.next_run_at = next ? next.toISOString() : null;
        return out;
    }

    function validate(p) {
        const e = {};
        const name = String(p.name || '').trim();
        if (!name) e.name = ['Nama aturan wajib diisi.'];
        else if (name.length > 60) e.name = ['Nama aturan maksimal 60 karakter.'];

        const t = p.trigger || {};
        if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(t.time || '')) e['trigger.time'] = ['Jam tidak valid.'];
        if (!['daily', 'weekly', 'monthly'].includes(t.frequency)) e['trigger.frequency'] = ['Pilih frekuensi.'];
        if (t.frequency === 'weekly' && !(t.days || []).length) e['trigger.days'] = ['Pilih minimal satu hari.'];
        if (t.frequency === 'monthly' && !(t.month_day === 'last' || (Number(t.month_day) >= 1 && Number(t.month_day) <= 31))) e['trigger.month_day'] = ['Pilih tanggal 1–31 atau akhir bulan.'];

        if (!(p.actions || []).length) e.actions = ['Tambahkan minimal satu aksi.'];
        (p.actions || []).forEach((a, i) => {
            const at = (f) => `actions.${i}.${f}`;
            if (a.type === 'create_task') {
                if (!String(a.title || '').trim()) e[at('title')] = ['Judul tugas wajib diisi.'];
                else if (a.title.length > 150) e[at('title')] = ['Judul maksimal 150 karakter.'];
                if (!['low', 'medium', 'high', 'urgent'].includes(a.priority)) e[at('priority')] = ['Prioritas tidak valid.'];
                if (!['all', 'leader', 'selected'].includes(a.assignees)) e[at('assignees')] = ['Pilih penanggung jawab.'];
                if (a.assignees === 'selected' && !(a.assignee_ids || []).length) e[at('assignee_ids')] = ['Pilih minimal satu anggota.'];
                const days = Number(a.due_in_days);
                if (!Number.isInteger(days) || days < 0 || days > 60) e[at('due_in_days')] = ['Deadline 0–60 hari.'];
                if (!['active_sprint', 'backlog'].includes(a.destination)) e[at('destination')] = ['Pilih tujuan tugas.'];
            } else if (a.type === 'notify') {
                if (!Object.keys(WS.automations.NOTIFY_TO).includes(a.to)) e[at('to')] = ['Pilih penerima.'];
                if (!String(a.message || '').trim()) e[at('message')] = ['Isi pesan wajib diisi.'];
                else if (a.message.length > 200) e[at('message')] = ['Pesan maksimal 200 karakter.'];
            } else {
                e[at('type')] = ['Jenis aksi tidak dikenal.'];
            }
        });
        if (Object.keys(e).length) throw invalid(e);
    }

    const pick = (p) => ({
        name: String(p.name).trim(), enabled: p.enabled !== false,
        trigger: { type: 'schedule', frequency: p.trigger.frequency, time: p.trigger.time, days: (p.trigger.days || []).map(Number), month_day: p.trigger.month_day },
        conditions: (p.conditions || []).filter((c) => WS.automations.COND[c.type]).map((c) => ({ type: c.type })),
        actions: clone(p.actions),
    });

    define('automations.list', async (workspaceId) => {
        await delay(300);
        if (!isStaff) throw forbidden();
        return { data: rulesOf(Number(workspaceId)).map(view) };
    });

    define('automations.create', async (workspaceId, p) => {
        await delay(400);
        if (!isStaff) throw forbidden();
        validate(p || {});
        const wsId = Number(workspaceId);
        const rules = rulesOf(wsId);
        const rule = Object.assign(pick(p), { id: wsId * 1000 + Math.max(0, ...rules.map((r) => r.id - wsId * 1000)) + 1, workspace_id: wsId, runs: [], last_run_at: null });
        rules.push(rule);
        save(wsId);
        return { data: view(rule) };
    });

    define('automations.update', async (id, p) => {
        await delay(300);
        if (!isStaff) throw forbidden();
        const rule = find(id);
        if (Object.keys(p).length === 1 && 'enabled' in p) {
            rule.enabled = !!p.enabled;
        } else {
            validate(p);
            Object.assign(rule, pick(p));
        }
        save(rule.workspace_id);
        return { data: view(rule) };
    });

    define('automations.remove', async (id) => {
        await delay(300);
        if (!isStaff) throw forbidden();
        const rule = find(id);
        cache[rule.workspace_id] = rulesOf(rule.workspace_id).filter((r) => r !== rule);
        save(rule.workspace_id);
        return null;
    });

    // Jalankan sekarang: kondisi dihormati, aksi dijalankan terhadap data halaman (store wsTasks).
    define('automations.run', async (id) => {
        await delay(600);
        if (!isStaff) throw forbidden();
        const rule = find(id);
        const S = window.Alpine.store('wsTasks');
        const members = S.interns;
        const sprint = S.activeSprint;
        const now = new Date();

        const record = (ok, summary, tasks) => {
            const run = { at: now.toISOString(), ok, summary };
            rule.runs = [run].concat(rule.runs || []).slice(0, 5);
            rule.last_run_at = run.at;
            save(rule.workspace_id);
            return { data: view(rule), run, tasks: tasks || [] };
        };

        const dow = (now.getDay() + 6) % 7 + 1;
        if (rule.conditions.some((c) => c.type === 'workday') && dow > 5) return record(false, 'Dilewati: hari ini bukan hari kerja.');
        if (rule.conditions.some((c) => c.type === 'active_sprint') && !sprint) return record(false, 'Dilewati: tidak ada sprint yang sedang berjalan.');

        const created = [];
        const notes = [];
        rule.actions.forEach((a) => {
            if (a.type === 'create_task') {
                if (rule.conditions.some((c) => c.type === 'skip_if_open') && S.tasks.concat(created).some((t) => t.title === a.title && t.status !== 'done')) {
                    notes.push(`"${a.title}" dilewati (tugas sebelumnya belum selesai)`);
                    return;
                }
                const pool = a.assignees === 'all' ? members : a.assignees === 'leader' ? (sprint && sprint.leader ? [sprint.leader] : []) : members.filter((m) => (a.assignee_ids || []).includes(m.id));
                if (a.assignees === 'leader' && !pool.length) notes.push('ketua sprint belum dipilih, tugas dibuat tanpa penanggung jawab');
                const groups = a.per_member && a.assignees !== 'leader' && pool.length > 1 ? pool.map((m) => [m]) : [pool];
                const due = new Date(now.getFullYear(), now.getMonth(), now.getDate() + Number(a.due_in_days), 17, 0, 0);
                groups.forEach((g) => created.push(WS.mock.createTask(rule.workspace_id, {
                    title: a.title, priority: a.priority, start_at: now.toISOString(), due_at: due.toISOString(),
                    sprint_id: a.destination === 'active_sprint' && sprint ? sprint.id : null, assignee_ids: g.map((m) => m.id), label_ids: [],
                })));
            } else if (a.type === 'notify') {
                const to = a.to === 'all' ? members.length : a.to === 'assignees' ? new Set(created.flatMap((t) => t.assignees.map((x) => x.id))).size : a.to === 'leader' ? (sprint && sprint.leader ? 1 : 0) : 1;
                notes.push(to ? `notifikasi dikirim ke ${to} orang` : 'notifikasi tidak terkirim (penerima kosong)');
            }
        });

        const made = created.length ? `Membuat ${created.length} tugas (${created.map((t) => t.code).join(', ')})` : '';
        const summary = [made, ...notes].filter(Boolean).join(' · ') || 'Tidak ada yang dijalankan.';
        return record(true, summary.charAt(0).toUpperCase() + summary.slice(1) + '.', created);
    });
})();
