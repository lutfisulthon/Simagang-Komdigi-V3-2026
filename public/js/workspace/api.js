/*
 * Workspace — lapisan data.
 * Satu-satunya tempat yang tahu URL endpoint. Bentuk request/response mengikuti
 * docs/workspace/kontrak-frontend.md. Saat WS.config.mock = true, panggilan
 * dialihkan ke WS.mock (mock.js) sehingga UI bisa dikembangkan tanpa backend.
 */
(function () {
    'use strict';

    const WS = window.WS;
    const base = (WS.config.baseUrl || '').replace(/\/$/, '');
    const url = (path) => base + path;

    function call(name, args, real) {
        if (WS.config.mock) {
            if (WS.mock && WS.mock.has(name)) return WS.mock.handle(name, args);
            return Promise.reject(new WS.HttpError(501, 'Mock belum tersedia untuk: ' + name));
        }
        return real();
    }

    WS.api = {
        tasks: {
            list: (workspaceId, filters) => call('tasks.list', [workspaceId, filters],
                () => WS.http('GET', url(`/workspace/${workspaceId}/tasks`), { query: filters })),
            create: (workspaceId, payload) => call('tasks.create', [workspaceId, payload],
                () => WS.http('POST', url(`/workspace/${workspaceId}/tasks`), { body: payload })),
            show: (taskId) => call('tasks.show', [taskId],
                () => WS.http('GET', url(`/workspace/tasks/${taskId}`))),
            update: (taskId, payload) => call('tasks.update', [taskId, payload],
                () => WS.http('PUT', url(`/workspace/tasks/${taskId}`), { body: payload })),
            // payload: { status, column_order?: [taskId, ...], result_links?, attachment_ids?, note? }
            // Tiga field terakhir hanya saat intern mengirim hasil (status → review).
            move: (taskId, payload) => call('tasks.move', [taskId, payload],
                () => WS.http('PATCH', url(`/workspace/tasks/${taskId}/status`), { body: payload })),
            // payload: { decision: 'approve' | 'revision', score?, feedback? }
            review: (taskId, payload) => call('tasks.review', [taskId, payload],
                () => WS.http('PATCH', url(`/workspace/tasks/${taskId}/review`), { body: payload })),
            remove: (taskId) => call('tasks.remove', [taskId],
                () => WS.http('DELETE', url(`/workspace/tasks/${taskId}`))),
            comment: (taskId, payload) => call('tasks.comment', [taskId, payload],
                () => WS.http('POST', url(`/workspace/tasks/${taskId}/comments`), { body: payload })),
            // Tugas milik intern yang login, untuk dropdown "Tugas terkait" di logbook (usulan kontrak #13).
            mine: () => call('tasks.mine', [],
                () => WS.http('GET', url('/workspace/tasks/mine'))),
            // Backlog (usulan kontrak #19): pindah ke sprint / backlog + urutan.
            // payload: { sprint_id: number|null, rank_order: [taskId, ...] } (urutan seluruh bagian tujuan)
            plan: (taskId, payload) => call('tasks.plan', [taskId, payload],
                () => WS.http('PATCH', url(`/workspace/tasks/${taskId}/sprint`), { body: payload })),
            // Bagi tugas (usulan kontrak #19, v1.2): mentor/admin atau ketua sprint tugas itu. payload: { assignee_ids: [] }
            assign: (taskId, payload) => call('tasks.assign', [taskId, payload],
                () => WS.http('PATCH', url(`/workspace/tasks/${taskId}/assignees`), { body: payload })),
            // Perpanjang deadline (mentor/admin). payload: { due_at, reason? } → { data: TaskDetail }
            extendDeadline: (taskId, payload) => call('tasks.extendDeadline', [taskId, payload],
                () => WS.http('PATCH', url(`/workspace/tasks/${taskId}/deadline`), { body: payload })),
            // Usulan endpoint (kontrak "Celah PRD" #4). payload: { is_done }
            toggleChecklist: (taskId, itemId, payload) => call('tasks.toggleChecklist', [taskId, itemId, payload],
                () => WS.http('PATCH', url(`/workspace/tasks/${taskId}/checklist/${itemId}`), { body: payload })),
        },

        messages: {
            // query: { after } untuk polling, { before } untuk scroll ke atas, { since } untuk edit/hapus terbaru,
            // { q } untuk pencarian, { pinned: 1 } untuk pesan disematkan
            list: (channelId, query) => call('messages.list', [channelId, query],
                () => WS.http('GET', url(`/workspace/channels/${channelId}/messages`), { query })),
            send: (channelId, payload) => call('messages.send', [channelId, payload],
                () => WS.http('POST', url(`/workspace/channels/${channelId}/messages`), { body: payload })),
            update: (messageId, payload) => call('messages.update', [messageId, payload],
                () => WS.http('PATCH', url(`/workspace/messages/${messageId}`), { body: payload })),
            remove: (messageId) => call('messages.remove', [messageId],
                () => WS.http('DELETE', url(`/workspace/messages/${messageId}`))),
            // Thread (usulan kontrak #10): { data: Message[] balasan, parent: Message }
            replies: (messageId) => call('messages.replies', [messageId],
                () => WS.http('GET', url(`/workspace/messages/${messageId}/replies`))),
            // Reaksi emoji (usulan kontrak #15). payload: { emoji } → toggle milik user; { data: Reaction[] }
            react: (messageId, payload) => call('messages.react', [messageId, payload],
                () => WS.http('POST', url(`/workspace/messages/${messageId}/reactions`), { body: payload })),
            // payload: { is_pinned }
            pin: (messageId, payload) => call('messages.pin', [messageId, payload],
                () => WS.http('PATCH', url(`/workspace/messages/${messageId}/pin`), { body: payload })),
        },

        channels: {
            markRead: (channelId, payload) => call('channels.markRead', [channelId, payload],
                () => WS.http('POST', url(`/workspace/channels/${channelId}/read`), { body: payload })),
            // payload: { name, description?, is_private, member_ids?: [userId] }
            create: (workspaceId, payload) => call('channels.create', [workspaceId, payload],
                () => WS.http('POST', url(`/workspace/${workspaceId}/channels`), { body: payload })),
            // Bisukan channel untuk user ini (channel_members.is_muted). payload: { is_muted } (usulan kontrak #16)
            mute: (channelId, payload) => call('channels.mute', [channelId, payload],
                () => WS.http('PATCH', url(`/workspace/channels/${channelId}/mute`), { body: payload })),
            // payload: { name?, description?, is_archived? }
            update: (channelId, payload) => call('channels.update', [channelId, payload],
                () => WS.http('PATCH', url(`/workspace/channels/${channelId}`), { body: payload })),
        },

        activities: {
            // Endpoint belum ada di PRD — usulan, lihat kontrak bagian "Celah PRD".
            list: (workspaceId, query) => call('activities.list', [workspaceId, query],
                () => WS.http('GET', url(`/workspace/${workspaceId}/activities`), { query })),
        },

        attachments: {
            // Endpoint upload belum ada di PRD — usulan, lihat kontrak bagian "Celah PRD".
            upload: (formData, onProgress) => call('attachments.upload', [formData, onProgress],
                () => WS.upload(url('/workspace/attachments'), formData, onProgress)),
            downloadUrl: (attachmentId) => url(`/workspace/attachments/${attachmentId}`),
        },

        // Usulan kontrak #9 (PRD: TaskTemplateController). payload template:
        // { team_id, title, description?, priority, label_ids?, due_in_days, checklist?: [{content}], is_onboarding }
        templates: {
            create: (payload) => call('templates.create', [payload],
                () => WS.http('POST', url('/workspace/templates'), { body: payload })),
            update: (templateId, payload) => call('templates.update', [templateId, payload],
                () => WS.http('PUT', url(`/workspace/templates/${templateId}`), { body: payload })),
            remove: (templateId) => call('templates.remove', [templateId],
                () => WS.http('DELETE', url(`/workspace/templates/${templateId}`))),
            // payload: { assignee_ids: [internId], due_at, start_at? } — tugas dibuat di kelompok tiap intern
            assign: (templateId, payload) => call('templates.assign', [templateId, payload],
                () => WS.http('POST', url(`/workspace/templates/${templateId}/assign`), { body: payload })),
        },

        // Label per tim (usulan kontrak #11). payload: { name, color }
        labels: {
            create: (teamId, payload) => call('labels.create', [teamId, payload],
                () => WS.http('POST', url(`/workspace/teams/${teamId}/labels`), { body: payload })),
            update: (labelId, payload) => call('labels.update', [labelId, payload],
                () => WS.http('PATCH', url(`/workspace/labels/${labelId}`), { body: payload })),
            remove: (labelId) => call('labels.remove', [labelId],
                () => WS.http('DELETE', url(`/workspace/labels/${labelId}`))),
        },

        // Sprint per kelompok (usulan kontrak #19). Sprint: { id, workspace_id, name, goal, status, start_date, end_date, completed_at }
        sprints: {
            // payload: { name?, goal?, end_date, leader_id?, tasks?: [{ title, priority }] }
            // → { data: Sprint, tasks: TaskCard[] } (status planned; tugas dibuat di sprint, deadline = end_date)
            create: (workspaceId, payload) => call('sprints.create', [workspaceId, payload],
                () => WS.http('POST', url(`/workspace/${workspaceId}/sprints`), { body: payload })),
            update: (sprintId, payload) => call('sprints.update', [sprintId, payload],
                () => WS.http('PATCH', url(`/workspace/sprints/${sprintId}`), { body: payload })),
            // payload: { name, goal?, start_date, end_date, leader_id } → { data: Sprint } (status active).
            // 422 bila ada sprint aktif / ketua belum dipilih.
            start: (sprintId, payload) => call('sprints.start', [sprintId, payload],
                () => WS.http('POST', url(`/workspace/sprints/${sprintId}/start`), { body: payload })),
            // payload: { move_to: 'backlog' | 'new' | sprintId } → { data: Sprint, moved: { task_ids, sprint_id }, created_sprint?: Sprint }
            complete: (sprintId, payload) => call('sprints.complete', [sprintId, payload],
                () => WS.http('POST', url(`/workspace/sprints/${sprintId}/complete`), { body: payload })),
            // Hanya sprint planned; tugasnya kembali ke backlog. → 204
            remove: (sprintId) => call('sprints.remove', [sprintId],
                () => WS.http('DELETE', url(`/workspace/sprints/${sprintId}`))),
        },

        // Otomasi pekerjaan berulang per kelompok (usulan kontrak #21). Hanya mentor pemilik/admin; kelompok selesai → 403.
        // Rule: { id, workspace_id, name, enabled, trigger, conditions[], actions[], next_run_at, last_run_at, runs[] }
        automations: {
            list: (workspaceId) => call('automations.list', [workspaceId],
                () => WS.http('GET', url(`/workspace/${workspaceId}/automations`))),
            // payload: { name, enabled, trigger, conditions, actions } → 201 { data: Rule }. 422 per field ("actions.0.title").
            create: (workspaceId, payload) => call('automations.create', [workspaceId, payload],
                () => WS.http('POST', url(`/workspace/${workspaceId}/automations`), { body: payload })),
            // Aturan penuh, atau hanya { enabled } untuk saklar aktif/nonaktif → { data: Rule }
            update: (id, payload) => call('automations.update', [id, payload],
                () => WS.http('PATCH', url(`/workspace/automations/${id}`), { body: payload })),
            remove: (id) => call('automations.remove', [id],
                () => WS.http('DELETE', url(`/workspace/automations/${id}`))),
            // Jalankan sekarang (uji). Kondisi tetap dihormati. → { data: Rule, run: { at, ok, summary }, tasks: TaskCard[] }
            run: (id) => call('automations.run', [id],
                () => WS.http('POST', url(`/workspace/automations/${id}/run`))),
        },

        // Kelompok buatan mentor (usulan kontrak #18). 1 anak magang = 1 kelompok; anggota tidak bisa dikeluarkan.
        groups: {
            // { interns: [{ id, name, avatar_url, end_date, group: {id,name,color,url}|null }], groups: [{ id, name, color, url, members_count }] }
            roster: () => call('groups.roster', [],
                () => WS.http('GET', url('/workspace/groups/roster'))),
            // payload: { name, color, description?, intern_ids: [internId] } → { data: Workspace }
            create: (payload) => call('groups.create', [payload],
                () => WS.http('POST', url('/workspace/groups'), { body: payload })),
            // payload: { name?, color? } atau { status: 'finished' } → { data: Workspace }
            update: (groupId, payload) => call('groups.update', [groupId, payload],
                () => WS.http('PATCH', url(`/workspace/groups/${groupId}`), { body: payload })),
            // payload: { intern_ids: [internId] } → { data: Workspace }
            addMembers: (groupId, payload) => call('groups.addMembers', [groupId, payload],
                () => WS.http('POST', url(`/workspace/groups/${groupId}/members`), { body: payload })),
        },

        // Detail intern (admin/mentor), usulan kontrak #14: { data: TaskCard+workspace+score[], stats, group }
        interns: {
            tasks: (internId) => call('interns.tasks', [internId],
                () => WS.http('GET', url(`/workspace/interns/${internId}/tasks`))),
        },

        // Rekap tugas selesai (laporan akhir & sertifikat), usulan kontrak #17. query: { intern_id? }
        recap: {
            get: (query) => call('recap.get', [query],
                () => WS.http('GET', url('/workspace/recap'), { query })),
        },

        // Admin → Monitoring (usulan kontrak #12). query: { intern_ids: [] }
        monitoring: {
            progress: (query) => call('monitoring.progress', [query],
                () => WS.http('GET', url('/workspace/progress'), { query })),
        },

        dashboard: {
            // Usulan endpoint (kontrak "Celah PRD" #8): ringkasan untuk widget dashboard mentor/intern.
            summary: () => call('dashboard.summary', [],
                () => WS.http('GET', url('/workspace/summary'))),
        },

        notifications: {
            // Lonceng: { limit: 10 }.
            summary: (query) => call('notifications.summary', [query],
                () => WS.http('GET', url('/notifications'), { query })),
            // Halaman notifikasi: { page, per_page, filter?: 'unread', category?: 'task'|'mention'|'announcement'|'intern' }.
            list: (query) => call('notifications.list', [query],
                () => WS.http('GET', url('/notifications'), { query })),
            // Pengaturan notifikasi per user (usulan kontrak #16)
            preferences: () => call('notifications.preferences', [],
                () => WS.http('GET', url('/notifications/preferences'))),
            savePreferences: (payload) => call('notifications.savePreferences', [payload],
                () => WS.http('PUT', url('/notifications/preferences'), { body: payload })),
            readAll: () => call('notifications.readAll', [],
                () => WS.http('POST', url('/notifications/read-all'))),
            read: (notificationId) => call('notifications.read', [notificationId],
                () => WS.http('POST', url(`/notifications/${notificationId}/read`))),
        },
    };
})();
