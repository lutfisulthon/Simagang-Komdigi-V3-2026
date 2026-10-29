/*
 * Workspace — mock endpoint sprint (hanya mode preview).
 * Halaman men-seed 'sprint-context' { workspace_id, sprints }. Perubahan disimpan di memori halaman.
 * Aturan meniru usulan kontrak #19: satu sprint aktif per kelompok, hanya sprint planned yang bisa
 * dihapus, tugas belum selesai dipindahkan saat sprint diselesaikan.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const { define, delay, clone } = WS.mock;
    const isStaff = ['mentor', 'admin'].includes(WS.config.user && WS.config.user.role);

    let ctx = { workspace_id: null, sprints: [], members: [] };
    let seq = 900;

    WS.mock.seed = (function (original) {
        return function (key, value) {
            if (key === 'sprint-context') {
                if (ctx.workspace_id !== value.workspace_id) ctx = clone(value);
                return;
            }
            original(key, value);
        };
    })(WS.mock.seed);

    const forbidden = (msg) => new WS.HttpError(403, msg || 'Hanya mentor yang dapat mengatur sprint.');
    const invalid = (errors) => new WS.HttpError(422, Object.values(errors)[0][0], errors);
    const find = (id) => {
        const sp = ctx.sprints.find((x) => x.id === Number(id));
        if (!sp) throw new WS.HttpError(404, 'Sprint tidak ditemukan.');
        return sp;
    };
    const nextName = () => 'Sprint ' + (Math.max(0, ...ctx.sprints.map((x) => Number(String(x.name).replace(/\D/g, '')) || 0)) + 1);

    // Ketua harus anggota kelompok (intern). Kembalikan InternMini atau lempar 422.
    function leaderOf(id) {
        if (id === undefined || id === null || id === '') return null;
        const m = ctx.members.find((x) => x.id === Number(id));
        if (!m) throw invalid({ leader_id: ['Ketua harus anggota kelompok ini.'] });
        return clone(m);
    }

    function validateDates(p, required) {
        const errors = {};
        if (p.name !== undefined && !String(p.name).trim()) errors.name = ['Nama sprint wajib diisi.'];
        if (p.name && String(p.name).length > 60) errors.name = ['Nama sprint maksimal 60 karakter.'];
        if (required && !p.start_date) errors.start_date = ['Tanggal mulai wajib diisi.'];
        if (required && !p.end_date) errors.end_date = ['Tanggal selesai wajib diisi.'];
        if (p.start_date && p.end_date && p.end_date <= p.start_date) errors.end_date = ['Tanggal selesai harus setelah tanggal mulai.'];
        if (Object.keys(errors).length) throw invalid(errors);
    }

    define('sprints.create', async (workspaceId, p) => {
        await delay(400);
        if (!isStaff) throw forbidden();
        p = p || {};
        const errors = {};
        if (!p.end_date) errors.end_date = ['Deadline sprint wajib diisi.'];
        else if (new Date(p.end_date + 'T23:59:00') < new Date()) errors.end_date = ['Deadline sprint tidak boleh di masa lalu.'];
        (p.tasks || []).forEach((t, i) => {
            if (!String(t.title || '').trim()) errors['tasks.' + i + '.title'] = ['Judul tugas wajib diisi.'];
            else if (t.title.length > 150) errors['tasks.' + i + '.title'] = ['Judul maksimal 150 karakter.'];
        });
        if (Object.keys(errors).length) throw invalid(errors);
        validateDates(p, false);
        const sp = {
            id: ++seq, workspace_id: Number(workspaceId), name: (p.name && p.name.trim()) || nextName(),
            goal: (p.goal || '').trim(), status: 'planned', start_date: null, end_date: p.end_date,
            leader: leaderOf(p.leader_id), completed_at: null,
        };
        ctx.sprints.push(sp);
        const due = new Date(p.end_date + 'T23:59:00').toISOString();
        const tasks = (p.tasks || []).map((t) => WS.mock.createTask(Number(workspaceId), {
            title: t.title, priority: t.priority || 'medium', start_at: null, due_at: due, sprint_id: sp.id, assignee_ids: [], label_ids: [],
        }));
        return { data: clone(sp), tasks };
    });

    define('sprints.update', async (id, p) => {
        await delay(300);
        if (!isStaff) throw forbidden();
        const sp = find(id);
        if (sp.status === 'completed') throw forbidden('Sprint yang sudah selesai tidak bisa diubah.');
        validateDates(Object.assign({ start_date: sp.start_date, end_date: sp.end_date }, p), sp.status === 'active');
        ['name', 'goal', 'start_date', 'end_date'].forEach((k) => { if (k in p) sp[k] = typeof p[k] === 'string' ? p[k].trim() : p[k]; });
        if ('leader_id' in p) sp.leader = leaderOf(p.leader_id);
        return { data: clone(sp) };
    });

    define('sprints.start', async (id, p) => {
        await delay(400);
        if (!isStaff) throw forbidden();
        const sp = find(id);
        if (sp.status !== 'planned') throw invalid({ sprint: ['Sprint ini tidak bisa dimulai.'] });
        if (ctx.sprints.some((x) => x.status === 'active')) throw invalid({ sprint: ['Selesaikan sprint yang sedang berjalan terlebih dahulu.'] });
        validateDates(p, true);
        const leader = leaderOf(p.leader_id !== undefined ? p.leader_id : sp.leader && sp.leader.id);
        if (!leader) throw invalid({ leader_id: ['Pilih ketua tim sebelum memulai sprint.'] });
        Object.assign(sp, { name: p.name.trim(), goal: (p.goal || '').trim(), start_date: p.start_date, end_date: p.end_date, leader, status: 'active' });
        return { data: clone(sp) };
    });

    define('sprints.complete', async (id, p) => {
        await delay(450);
        if (!isStaff) throw forbidden();
        const sp = find(id);
        if (sp.status !== 'active') throw invalid({ sprint: ['Hanya sprint yang berjalan yang bisa diselesaikan.'] });
        const open = WS.mock.tasksOfSprint(sp.id).filter((t) => t.status !== 'done').map((t) => t.id);
        let target = null;
        let created = null;
        if (p.move_to === 'new') {
            created = { id: ++seq, workspace_id: sp.workspace_id, name: nextName(), goal: '', status: 'planned', start_date: null, end_date: null, completed_at: null };
            ctx.sprints.push(created);
            target = created.id;
        } else if (p.move_to !== 'backlog') {
            const t = find(p.move_to);
            if (t.status !== 'planned') throw invalid({ move_to: ['Pilih sprint yang belum dimulai.'] });
            target = t.id;
        }
        WS.mock.setSprint(open, target);
        Object.assign(sp, { status: 'completed', completed_at: new Date().toISOString() });
        return { data: clone(sp), moved: { task_ids: open, sprint_id: target }, created_sprint: created ? clone(created) : null };
    });

    define('sprints.remove', async (id) => {
        await delay(300);
        if (!isStaff) throw forbidden();
        const sp = find(id);
        if (sp.status !== 'planned') throw forbidden('Hanya sprint yang belum dimulai yang bisa dihapus.');
        WS.mock.setSprint(WS.mock.tasksOfSprint(sp.id).map((t) => t.id), null);
        ctx.sprints = ctx.sprints.filter((x) => x !== sp);
        return null;
    });
})();
