<?php

/*
|--------------------------------------------------------------------------
| PREVIEW FRONTEND WORKSPACE — sementara, hanya APP_ENV=local
|--------------------------------------------------------------------------
| Dimuat dari routes/web.php hanya di environment local. Tidak berisi logika
| backend: hanya merender view dengan data contoh (routes/workspace-preview-data.php)
| supaya UI bisa dicoba sebelum controller asli dibuat. Selama file ini dimuat,
| layout menyalakan mode mock (public/js/workspace/mock.js).
|
| SAAT BACKEND SIAP: hapus file ini, workspace-preview-data.php, dan baris
| require di routes/web.php, lalu daftarkan route asli dengan nama yang sama
| (workspace.index, workspace.show, dst.). Lihat docs/workspace/kontrak-frontend.md.
*/

use Illuminate\Support\Facades\Route;

$fixtures = require __DIR__ . '/workspace-preview-data.php';

Route::middleware('auth')->group(function () use ($fixtures) {
    // Didaftarkan sebelum /workspace/{workspace} supaya tidak tertangkap wildcard.
    Route::view('/workspace/_preview/komponen', 'workspace.preview.components')
        ->name('workspace.preview.components');

    // Daftar kelompok. Intern langsung diarahkan ke kelompoknya (1 anak magang = 1 kelompok).
    // ?state=pending: lihat sebagai intern yang belum dimasukkan ke kelompok.
    Route::get('/workspace', function () use ($fixtures) {
        $data = $fixtures(auth()->user(), request('state') === 'pending');
        if ($data['role'] === 'intern') {
            if ($data['myGroup']) return redirect($data['myGroup']['url']);
            return view('workspace.index', ['noGroup' => true, 'mentor' => $data['myMentor'], 'groups' => null, 'counts' => [], 'ungrouped' => [], 'teams' => [], 'mentors' => [], 'can' => []]);
        }

        $q = mb_strtolower(trim((string) request('q')));
        $visible = array_filter($data['workspaces'], $data['canView']);
        $filtered = array_filter($visible, function ($ws) use ($q) {
            if (request('team') && (string) $ws['team']['id'] !== (string) request('team')) return false;
            if (request('mentor') && (string) $ws['mentor']['id'] !== (string) request('mentor')) return false;
            $text = mb_strtolower($ws['name'] . ' ' . implode(' ', array_column($ws['_members'], 'name')));
            return $q === '' || str_contains($text, $q);
        });
        // Urutan: deadline tugas terdekat (default) atau terbaru; ?attention=1 hanya kelompok yang perlu perhatian.
        $sort = request('sort') === 'newest' ? 'newest' : 'deadline';
        $attention = request()->boolean('attention');
        if ($attention) $filtered = array_filter($filtered, $data['needsAttention']);
        $sorted = array_map($data['strip'], ($data['sortGroups'])(array_values($filtered), $sort));
        $perPage = 9;
        $page = max(1, (int) request('page', 1));
        $groups = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($sorted, ($page - 1) * $perPage, $perPage), count($sorted), $perPage, $page,
            ['path' => route('workspace.index'), 'query' => request()->except('page')]
        );

        return view('workspace.index', [
            'noGroup' => false,
            'mentor' => null,
            'groups' => $groups,
            'counts' => [
                'active' => count(array_filter($visible, fn ($ws) => $ws['status'] === 'active')),
                'finished' => count(array_filter($visible, fn ($ws) => $ws['status'] === 'finished')),
                'attention' => count(array_filter($visible, $data['needsAttention'])),
            ],
            'sort' => $sort,
            'attention' => $attention,
            'ungrouped' => $data['role'] === 'mentor' ? array_values(array_filter(($data['roster'])(), fn ($i) => ! $i['group'])) : [],
            'teams' => $data['role'] === 'admin' ? $data['teams'] : [],
            'mentors' => $data['role'] === 'admin' ? $data['mentors'] : [],
            'can' => [
                'create_group' => $data['role'] === 'mentor',
                'manage_templates' => in_array($data['role'], ['admin', 'mentor'], true),
            ],
        ]);
    })->name('workspace.index');

    // ---------- Kelompok (usulan kontrak #18) ----------
    // Perubahan disimpan di session user ini saja (preview, tidak sinkron antar akun).
    $groupJson = fn (int $id) => (function () use ($fixtures, $id) {
        $data = $fixtures(auth()->user());
        return ($data['strip'])($data['workspaces'][$id]);
    })();
    $rosterRules = function (array $data, $ids) {
        $roster = collect(($data['roster'])())->keyBy('id');
        $errors = [];
        if (! is_array($ids) || ! count($ids)) $errors['intern_ids'] = ['Pilih minimal satu anak magang.'];
        foreach ((array) $ids as $id) {
            $intern = $roster->get((int) $id);
            if (! $intern) $errors['intern_ids'] = ['Anak magang tidak ditemukan di daftar bimbingan Anda.'];
            elseif ($intern['group']) $errors['intern_ids'] = [$intern['name'] . ' sudah berada di kelompok ' . $intern['group']['name'] . '.'];
        }
        if ($errors) abort(response()->json(['message' => reset($errors)[0], 'errors' => $errors], 422));
        return array_map(fn ($id) => $roster->get((int) $id)['_key'], $ids);
    };
    $groupRules = function (array $input, bool $partial = false) {
        $errors = [];
        if (! $partial || array_key_exists('name', $input)) {
            $name = trim((string) ($input['name'] ?? ''));
            if ($name === '') $errors['name'] = ['Nama kelompok wajib diisi.'];
            elseif (mb_strlen($name) > 60) $errors['name'] = ['Nama kelompok maksimal 60 karakter.'];
        }
        if (array_key_exists('color', $input) && ! preg_match('/^#[0-9a-f]{6}$/i', (string) $input['color'])) $errors['color'] = ['Warna tidak valid.'];
        if ($errors) abort(response()->json(['message' => reset($errors)[0], 'errors' => $errors], 422));
    };

    // Anak bimbingan + kelompoknya (modal buat kelompok, halaman Anak Bimbingan, widget dashboard).
    Route::get('/workspace/groups/roster', function () use ($fixtures) {
        $data = $fixtures(auth()->user());
        abort_unless(in_array($data['role'], ['admin', 'mentor'], true), 403, 'Hanya mentor yang mengelola kelompok.');
        $groups = array_values(array_filter($data['workspaces'], fn ($ws) => ($data['canView'])($ws) && $ws['can']['add_members']));
        return response()->json([
            'interns' => array_map(fn ($i) => array_diff_key($i, ['_key' => 1]), ($data['roster'])()),
            'groups' => array_map(fn ($ws) => ['id' => $ws['id'], 'name' => $ws['name'], 'color' => $ws['color'], 'url' => $ws['url'], 'members_count' => $ws['members_count']], ($data['sortGroups'])($groups)),
        ]);
    })->name('workspace.groups.roster');

    // payload: { name, color, description?, intern_ids: [internId] }
    Route::post('/workspace/groups', function () use ($fixtures, $rosterRules, $groupRules, $groupJson) {
        $data = $fixtures(auth()->user());
        abort_unless($data['role'] === 'mentor', 403, 'Hanya mentor yang dapat membuat kelompok.');
        $groupRules(request()->all());
        $keys = $rosterRules($data, request('intern_ids'));
        $saved = $data['saved'];
        $id = 301 + count($saved['created']);
        $saved['created'][] = [
            'key' => 'c' . $id, 'name' => trim(request('name')), 'color' => request('color', '#4f46e5'),
            'description' => trim((string) request('description')), 'created_at' => now()->toIso8601String(),
        ];
        foreach ($keys as $key) $saved['members'][$key] = 'c' . $id;
        session(['ws_preview_groups' => $saved]);
        return response()->json(['data' => $groupJson($id)], 201);
    })->name('workspace.groups.store');

    // payload: { name?, color?, status?: 'finished' }
    Route::patch('/workspace/groups/{group}', function (int $group) use ($fixtures, $groupRules, $groupJson) {
        $data = $fixtures(auth()->user());
        $ws = $data['workspaces'][$group] ?? abort(404);
        abort_unless(($data['canView'])($ws), 403, 'Anda tidak memiliki akses ke kelompok ini.');
        $groupRules(request()->all(), true);
        $saved = $data['saved'];
        if (request()->hasAny(['name', 'color'])) {
            abort_unless($ws['can']['rename'], 403, 'Kelompok ini tidak dapat diubah.');
            $saved['renamed'][$group] = [
                'name' => trim(request('name', $ws['name'])),
                'color' => request('color', $ws['color']),
            ];
        }
        if (request('status') === 'finished') {
            abort_unless($ws['can']['finish'], 403, 'Kelompok ini tidak dapat ditandai selesai.');
            $saved['finished'][$group] = now()->toIso8601String();
        }
        session(['ws_preview_groups' => $saved]);
        return response()->json(['data' => $groupJson($group)]);
    })->whereNumber('group')->name('workspace.groups.update');

    // payload: { intern_ids: [internId] } — hanya anak bimbingan yang belum punya kelompok.
    Route::post('/workspace/groups/{group}/members', function (int $group) use ($fixtures, $rosterRules, $groupJson) {
        $data = $fixtures(auth()->user());
        $ws = $data['workspaces'][$group] ?? abort(404);
        abort_unless(($data['canView'])($ws) && $ws['can']['add_members'], 403, 'Anda tidak dapat menambah anggota kelompok ini.');
        $keys = $rosterRules($data, request('intern_ids'));
        $saved = $data['saved'];
        foreach ($keys as $key) $saved['members'][$key] = $ws['_key'];
        session(['ws_preview_groups' => $saved]);
        return response()->json(['data' => $groupJson($group)]);
    })->whereNumber('group')->name('workspace.groups.members');

    // Kembalikan data contoh ke awal (hapus perubahan kelompok di session ini).
    Route::get('/workspace/_preview/reset', function () {
        session()->forget('ws_preview_groups');
        return redirect()->route('workspace.index');
    })->name('workspace.preview.reset');

    // Ringkasan untuk widget dashboard (usulan kontrak #8). JSON saja.
    Route::get('/workspace/summary', function () use ($fixtures) {
        $user = auth()->user();
        $data = $fixtures($user);
        $visible = array_filter($data['workspaces'], $data['canView']);
        $wsMini = fn (array $ws) => ['id' => $ws['id'], 'name' => $ws['name'], 'type' => $ws['type'], 'color' => $ws['color'], 'url' => $ws['url']];
        $byDue = fn ($a, $b) => strcmp($a['due_at'], $b['due_at']);

        if ($data['role'] === 'intern') {
            $mine = [];
            $unread = [];
            $announcementAdded = false;
            foreach ($visible as $ws) {
                foreach (($data['tasks'])($ws) as $card) {
                    if ($card['status'] !== 'done' && collect($card['assignees'])->contains('user_id', $user->id)) {
                        $mine[] = $card + ['workspace' => $wsMini($ws)];
                    }
                }
                foreach (($data['channels'])($ws) as $c) {
                    if ($c['unread_count'] < 1 || ($c['type'] === 'announcement' && $announcementAdded)) continue;
                    $announcementAdded = $announcementAdded || $c['type'] === 'announcement';
                    $preview = match ($c['type']) {
                        'announcement' => [['id' => 9000, 'name' => 'Admin Simagang', 'role' => 'admin', 'avatar_url' => null], 'Pengingat: absensi dibuka pukul 07.30–08.30 WIB.'],
                        'direct' => [collect($c['members'])->firstWhere('role', 'mentor'), 'Halo, bagaimana progres minggu ini? Ada kendala?'],
                        'topic' => [collect($ws['_members'])->firstWhere('role', 'intern'), 'Ada yang sudah coba endpoint absensi yang baru? Responsnya agak lambat.'],
                        default => [collect($ws['_members'])->firstWhere('role', 'mentor'), 'Rapat sinkronisasi hari ini pukul 14.00 di ruang rapat lantai 3.'],
                    };
                    $unread[] = [
                        'channel' => array_intersect_key($c, array_flip(['id', 'name', 'type', 'members'])),
                        'workspace' => $c['type'] === 'announcement' ? null : $wsMini($ws),
                        'url' => $ws['url'] . '?tab=channel&channel=' . $c['id'],
                        'unread_count' => $c['unread_count'],
                        'last_message' => ['user' => $preview[0], 'body' => $preview[1], 'created_at' => now()->subMinutes(9 + $c['id'] % 40)->toIso8601String()],
                    ];
                }
            }
            usort($mine, $byDue);
            usort($unread, fn ($a, $b) => strcmp($b['last_message']['created_at'], $a['last_message']['created_at']));
            return response()->json([
                'role' => 'intern',
                'pending_mentor' => ! $data['myGroup'],
                'workspace_url' => $data['myGroup']['url'] ?? route('workspace.index'),
                'my_tasks' => [
                    'data' => array_slice($mine, 0, 5),
                    'counts' => [
                        'active' => count($mine),
                        'revision' => collect($mine)->where('status', 'revision')->count(),
                        'late' => collect($mine)->where('is_late', true)->count(),
                    ],
                ],
                'unread_channels' => array_slice($unread, 0, 5),
            ]);
        }

        // Mentor/admin: tugas menunggu review & tugas terlambat per intern di workspace yang dibimbing.
        $review = [];
        $late = [];
        $upcoming = [];
        $groupProgress = [];
        $workload = [];
        $weekAhead = now()->addDays(7)->toIso8601String();
        foreach ($visible as $ws) {
            if ($ws['is_read_only']) continue;
            $cards = ($data['tasks'])($ws);
            // Grafik mini dashboard: progres kelompok + beban tugas aktif per anak bimbingan.
            $groupProgress[] = [
                'workspace' => $wsMini($ws),
                'total' => count($cards),
                'done' => count(array_filter($cards, fn ($c) => $c['status'] === 'done')),
                'overdue' => count(array_filter($cards, fn ($c) => $c['is_late'] && $c['status'] !== 'done')),
            ];
            foreach ($cards as $card) {
                if ($card['status'] === 'done') continue;
                foreach ($card['assignees'] as $a) {
                    $workload[$a['id']] ??= ['intern' => $a, 'workspace' => $wsMini($ws), 'counts' => ['todo' => 0, 'in_progress' => 0, 'review' => 0, 'revision' => 0], 'overdue' => 0];
                    $workload[$a['id']]['counts'][$card['status']]++;
                    if ($card['is_late']) $workload[$a['id']]['overdue']++;
                }
            }
            foreach ($cards as $card) {
                // Deadline mendatang: belum dikirim, belum lewat, jatuh tempo ≤ 7 hari lagi.
                if (in_array($card['status'], ['todo', 'in_progress', 'revision'], true) && ! $card['is_late']
                    && strtotime($card['due_at']) >= time() && $card['due_at'] <= $weekAhead) {
                    $upcoming[] = $card + ['workspace' => $wsMini($ws)];
                }
                if ($card['status'] === 'review') {
                    $review[] = $card + ['workspace' => $wsMini($ws), 'submitted_at' => now()->subHours(1 + ($card['id'] % 47))->toIso8601String()];
                }
                if ($card['is_late']) {
                    foreach ($card['assignees'] as $a) {
                        $key = $a['id'];
                        $late[$key] ??= ['intern' => $a, 'workspace' => $wsMini($ws), 'late_count' => 0, 'tasks' => []];
                        $late[$key]['late_count']++;
                        $late[$key]['tasks'][] = array_intersect_key($card, array_flip(['id', 'code', 'title', 'due_at', 'url']));
                    }
                }
            }
        }
        usort($review, fn ($a, $b) => strcmp($a['submitted_at'], $b['submitted_at'])); // terlama menunggu dulu
        usort($upcoming, $byDue);
        // Kelompok yang paling perlu perhatian dulu: banyak terlambat, lalu progres terendah.
        usort($groupProgress, fn ($a, $b) => [$b['overdue'], $a['total'] ? $a['done'] / $a['total'] : 0] <=> [$a['overdue'], $b['total'] ? $b['done'] / $b['total'] : 0]);
        $workload = array_values($workload);
        usort($workload, fn ($a, $b) => array_sum($b['counts']) <=> array_sum($a['counts']));
        $late = array_values($late);
        usort($late, fn ($a, $b) => $b['late_count'] <=> $a['late_count']);

        // Mentor: anak bimbingan yang belum dimasukkan ke kelompok (widget "Anak bimbingan baru").
        $ungrouped = $data['role'] === 'mentor'
            ? array_values(array_map(fn ($i) => array_diff_key($i, ['_key' => 1]), array_filter(($data['roster'])(), fn ($i) => ! $i['group'])))
            : [];

        return response()->json([
            'role' => $data['role'],
            'to_review' => ['data' => array_slice($review, 0, 6), 'total' => count($review)],
            'late_by_intern' => $late,
            'upcoming_deadlines' => ['data' => array_slice($upcoming, 0, 6), 'total' => count($upcoming)],
            'group_progress' => array_slice($groupProgress, 0, 6),
            'workload' => array_slice($workload, 0, 8),
            'ungrouped_interns' => $ungrouped,
            'groups_url' => route('workspace.index'),
        ]);
    })->name('workspace.summary');

    // Tugas milik intern yang login (dropdown "Tugas terkait" di logbook, usulan kontrak #13).
    // Didaftarkan sebelum /workspace/tasks/{task}; tugas aktif + 5 tugas Selesai terbaru.
    Route::get('/workspace/tasks/mine', function () use ($fixtures) {
        $user = auth()->user();
        $data = $fixtures($user);
        abort_unless($data['role'] === 'intern', 403, 'Hanya peserta magang yang memiliki daftar tugas pribadi.');
        $active = [];
        $done = [];
        foreach (array_filter($data['workspaces'], $data['canView']) as $ws) {
            foreach (($data['tasks'])($ws) as $card) {
                if (! collect($card['assignees'])->contains('user_id', $user->id)) continue;
                $item = [
                    'id' => $card['id'], 'code' => $card['code'], 'title' => $card['title'], 'status' => $card['status'],
                    'due_at' => $card['due_at'], 'workspace' => ['id' => $ws['id'], 'name' => $ws['name']],
                ];
                if ($card['status'] === 'done') $done[] = $item;
                else $active[] = $item;
            }
        }
        usort($active, fn ($a, $b) => strcmp($a['due_at'], $b['due_at']));

        return response()->json(['data' => array_merge($active, array_slice(array_reverse($done), 0, 5))]);
    })->name('workspace.tasks.mine');

    // Rekap tugas selesai untuk laporan akhir & sertifikat (usulan kontrak #17).
    // Intern: miliknya sendiri. Mentor/admin: ?intern_id= (dipetakan ke space intern fixture).
    // HTML (halaman cetak) atau JSON bila Accept: application/json.
    Route::get('/workspace/recap', function () use ($fixtures) {
        $user = auth()->user();
        $data = $fixtures($user);
        $isStaff = in_array($data['role'], ['admin', 'mentor'], true);
        abort_if($isStaff && ! request('intern_id'), 422, 'Parameter intern_id wajib untuk mentor/admin.');

        // Mentor/admin: intern dari id mana pun dipetakan ke anggota kelompok di fixture.
        $person = $isStaff ? ($data['personFor'])((int) request('intern_id')) : null;
        $visible = $isStaff
            ? ($person['group'] ? [$data['workspaces'][$data['groupIdOf'][$person['group']]]] : []) // belum berkelompok → rekap kosong
            : array_filter($data['workspaces'], $data['canView']);
        $targetUserId = $isStaff ? $person['user_id'] : $user->id;
        $tasks = [];
        $active = 0;
        $name = $isStaff ? $person['name'] : $user->name;
        foreach ($visible as $ws) {
            foreach (($data['tasks'])($ws) as $card) {
                if (! collect($card['assignees'])->contains('user_id', $targetUserId)) continue;
                if ($card['status'] !== 'done') { $active++; continue; }
                $h = crc32('recap-' . $card['id']);
                $onTime = $h % 5 !== 0;
                $completed = \Illuminate\Support\Carbon::parse($card['due_at'])->addHours($onTime ? -3 : 20);
                $tasks[] = [
                    'id' => $card['id'], 'code' => $card['code'], 'title' => $card['title'],
                    'workspace' => ['id' => $ws['id'], 'name' => $ws['name']],
                    'due_at' => $card['due_at'], 'completed_at' => $completed->toIso8601String(),
                    'score' => 72 + $h % 27, 'on_time' => $onTime,
                    'url' => $card['url'],
                ];
            }
        }
        usort($tasks, fn ($a, $b) => strcmp($a['completed_at'], $b['completed_at']));
        $scores = array_column($tasks, 'score');
        $recap = [
            'intern' => ['id' => (int) request('intern_id', 0) ?: null, 'name' => $name],
            'period' => ['start' => now()->subMonths(3)->toDateString(), 'end' => now()->addMonths(1)->toDateString()],
            'summary' => [
                'done' => count($tasks),
                'active' => $active,
                'on_time' => count(array_filter($tasks, fn ($t) => $t['on_time'])),
                'avg_score' => $scores ? round(array_sum($scores) / count($scores)) : null,
                'max_score' => $scores ? max($scores) : null,
                'min_score' => $scores ? min($scores) : null,
            ],
            'tasks' => $tasks,
        ];

        if (request()->expectsJson()) return response()->json(['data' => $recap]);
        return view('workspace.recap', ['recap' => $recap]);
    })->name('workspace.recap');

    // Tugas satu intern untuk halaman detail intern (usulan kontrak #14). Admin & mentor; intern → 403.
    // Intern di halaman detail adalah data asli (id DB), jadi dipetakan ke anggota kelompok di fixture.
    // Belum punya kelompok → data kosong + group null.
    Route::get('/workspace/interns/{intern}/tasks', function (int $intern) use ($fixtures) {
        $data = $fixtures(auth()->user());
        abort_unless(in_array($data['role'], ['admin', 'mentor'], true), 403, 'Anda tidak memiliki akses ke data ini.');
        $person = collect($data['people'])->firstWhere('id', $intern) ?? ($data['personFor'])($intern);
        $ws = $person['group'] ? $data['workspaces'][$data['groupIdOf'][$person['group']]] : null;
        $cards = [];
        foreach ($ws ? ($data['tasks'])($ws) : [] as $card) {
            if (! collect($card['assignees'])->contains('user_id', $person['user_id'])) continue;
            $card['workspace'] = ['id' => $ws['id'], 'name' => $ws['name'], 'url' => $ws['url']];
            $card['score'] = $card['status'] === 'done' ? 75 + crc32('score-' . $card['id']) % 25 : null;
            $cards[] = $card;
        }
        usort($cards, fn ($a, $b) => strcmp($a['due_at'], $b['due_at']));
        $scores = array_filter(array_column($cards, 'score'));
        $count = fn (string $status) => count(array_filter($cards, fn ($c) => $c['status'] === $status));
        $stats = ['todo' => $count('todo'), 'in_progress' => $count('in_progress'), 'review' => $count('review'), 'revision' => $count('revision'), 'done' => $count('done'),
            'late' => count(array_filter($cards, fn ($c) => $c['is_late'] && $c['status'] !== 'done'))];

        return response()->json([
            'data' => $cards,
            'stats' => $stats + ['avg_score' => $scores ? round(array_sum($scores) / count($scores)) : null],
            'group' => $ws ? ['id' => $ws['id'], 'name' => $ws['name'], 'color' => $ws['color'], 'url' => $ws['url']] : null,
        ]);
    })->whereNumber('intern')->name('workspace.interns.tasks');

    // Admin → Monitoring (usulan kontrak #12): progres tugas per intern + rekap per tim.
    // Intern di tabel Monitoring adalah data asli (id DB), jadi progresnya dibuat deterministik dari id.
    Route::get('/workspace/progress', function () use ($fixtures) {
        $data = $fixtures(auth()->user());
        abort_unless($data['role'] === 'admin', 403, 'Hanya admin yang dapat melihat rekap ini.');
        $interns = [];
        foreach ((array) request('intern_ids', []) as $id) {
            $h = crc32('progress-' . (int) $id);
            $total = $h % 7 === 0 ? 0 : 3 + $h % 10;
            $done = intdiv($total * ($h % 101), 100);
            $interns[(int) $id] = [
                'total' => $total,
                'done' => $done,
                'review' => min($total - $done, $h % 3),
                'late' => $h % 4 === 0 ? min($total - $done, 1 + $h % 3) : 0,
                'group_url' => null,
            ];
        }
        // Rekap per unit kerja = jumlah semua kelompok aktif di unit itu.
        $teams = [];
        foreach ($data['teams'] as $t => $team) {
            $groups = array_filter($data['workspaces'], fn ($ws) => $ws['_team_index'] === $t && ! $ws['is_read_only']);
            $stats = ['todo' => 0, 'in_progress' => 0, 'review' => 0, 'revision' => 0, 'done' => 0, 'late' => 0];
            $mentors = [];
            foreach ($groups as $ws) {
                foreach ($stats as $k => $v) $stats[$k] += $ws['stats'][$k];
                $mentors[$ws['mentor']['id']] = $ws['mentor'];
            }
            $teams[] = [
                'team' => $team,
                'workspace_url' => route('workspace.index', ['team' => $team['id']]),
                'groups_count' => count($groups),
                'stats' => $stats,
                'idle_mentors' => array_values(array_filter($mentors, fn ($m) => crc32('idle-' . $m['id']) % 3 === 0)),
            ];
        }

        return response()->json(['interns' => (object) $interns, 'teams' => $teams]);
    })->name('workspace.progress');

    // Template tugas: kelola + tugaskan massal (admin: semua tim, mentor: tim sendiri, intern: 403).
    Route::get('/workspace/templates', function () use ($fixtures) {
        $data = $fixtures(auth()->user());
        abort_unless(in_array($data['role'], ['admin', 'mentor'], true), 403, 'Hanya mentor atau admin yang dapat mengelola template tugas.');
        $templates = ($data['templates'])();
        $targets = ($data['templateTargets'])();
        $teamIds = collect($targets)->pluck('team.id')->merge(collect($templates)->pluck('team.id'))->unique();

        return view('workspace.templates.index', [
            'templates' => $templates,
            'teams' => $data['role'] === 'admin' ? $data['teams'] : array_values(array_filter($data['teams'], fn ($t) => $teamIds->contains($t['id']))),
            'assignableInterns' => $targets,
            'labels' => $data['labels'],
            'can' => ['create' => true, 'choose_team' => $data['role'] === 'admin', 'manage_labels' => true],
        ]);
    })->name('workspace.templates.index');

    Route::get('/workspace/{workspace}', function (int $workspace) use ($fixtures) {
        $data = $fixtures(auth()->user());
        $ws = $data['workspaces'][$workspace] ?? abort(404);
        abort_unless(($data['canView'])($ws), 403, 'Anda tidak memiliki akses ke workspace ini.');

        $isStaff = in_array($data['role'], ['admin', 'mentor'], true);
        $readOnly = $ws['is_read_only'];
        $allowedTabs = $isStaff
            ? array_values(array_filter(['backlog', 'board', 'list', 'timeline', 'deadline', 'laporan', $readOnly ? null : 'otomasi', 'channel', 'activity']))
            : ['board', 'list', 'timeline', 'deadline', 'laporan', 'channel', 'activity'];
        $tab = in_array(request('tab'), $allowedTabs, true) ? request('tab') : 'board';
        // Calon anggota baru: anak bimbingan mentor yang belum punya kelompok.
        $candidates = $ws['can']['add_members']
            ? array_values(array_map(fn ($i) => array_diff_key($i, ['_key' => 1]), array_filter(($data['roster'])(), fn ($i) => ! $i['group'])))
            : [];

        return view('workspace.show', [
            'workspace' => ($data['strip'])($ws),
            'members' => $ws['_members'],
            'activities' => ($data['activities'])($ws),
            'initialTasks' => ($data['tasks'])($ws),
            'assignableInterns' => $ws['_assignables'],
            'labels' => $data['labels'],
            'channels' => ($data['channels'])($ws),
            // Template tim workspace ini (untuk "Pakai template" di form tugas). Intern: kosong.
            'templates' => array_values(array_filter(($data['templates'])(), fn ($t) => $t['team']['id'] === ($ws['team']['id'] ?? null))),
            'tab' => $tab,
            'sprints' => ($data['sprints'])($ws),
            'candidates' => $candidates,
            'can' => [
                'create_task' => ! $readOnly && $isStaff,
                'manage_sprints' => ! $readOnly && $isStaff,
                'manage_channels' => false,
                'create_template' => ! $readOnly && $isStaff,
                'manage_labels' => ! $readOnly && $isStaff,
            ],
        ]);
    })->whereNumber('workspace')->name('workspace.show');

    // Detail tugas: HTML (halaman penuh) atau JSON { data: TaskDetail } untuk slide-over.
    Route::get('/workspace/tasks/{task}', function (int $task) use ($fixtures) {
        $data = $fixtures(auth()->user());
        $ws = $data['workspaces'][intdiv($task, 100)] ?? abort(404);
        abort_unless(($data['canView'])($ws), 403, 'Anda tidak memiliki akses ke tugas ini.');
        $detail = ($data['taskDetail'])($ws, $task) ?? abort(404);

        if (request()->expectsJson()) {
            return response()->json(['data' => $detail]);
        }

        $isStaff = in_array($data['role'], ['admin', 'mentor'], true);

        return view('workspace.tasks.show', [
            'task' => $detail,
            'workspace' => ($data['strip'])($ws),
            'members' => $ws['_members'],
            'assignableInterns' => $ws['_assignables'],
            'labels' => $data['labels'],
            'templates' => array_values(array_filter(($data['templates'])(), fn ($t) => $t['team']['id'] === ($ws['team']['id'] ?? null))),
            'can' => ['create_template' => $isStaff && ! $ws['is_read_only']],
        ]);
    })->whereNumber('task')->name('workspace.tasks.show');

    // Pengaturan notifikasi (usulan kontrak #16). Halaman + JSON preferensi.
    Route::view('/notifications/settings', 'notifications.settings', ['preferences' => null])->name('notifications.settings');
    Route::get('/notifications/preferences', function () use ($fixtures) {
        $data = $fixtures(auth()->user());
        // [grup, label, keterangan, bisa lewat email (PRD: "In-app + email")]
        $catalog = [
            'task_assigned' => ['Tugas', 'Tugas baru di-assign', 'Saat mentor memberi Anda tugas baru.', true],
            'task_approved' => ['Tugas', 'Tugas disetujui', 'Saat mentor menandai tugas Selesai beserta nilainya.', true],
            'task_revision' => ['Tugas', 'Tugas perlu revisi', 'Saat mentor mengembalikan tugas dengan catatan revisi.', true],
            'task_due_soon' => ['Tugas', 'Pengingat deadline (H-1)', 'Sehari sebelum deadline tugas yang belum selesai.', true],
            'task_in_review' => ['Tugas', 'Tugas menunggu review', 'Saat intern mengirim hasil untuk direview.', false],
            'task_overdue' => ['Tugas', 'Tugas terlambat', 'Saat tugas melewati deadline dan belum selesai.', false],
            'task_commented' => ['Tugas', 'Komentar baru di tugas', 'Komentar di tugas yang Anda buat atau kerjakan.', false],
            'mentioned' => ['Diskusi', 'Mention', 'Saat seseorang menyebut Anda di channel atau komentar.', false],
            'announcement' => ['Diskusi', 'Pengumuman baru', 'Pesan baru di #pengumuman.', false],
            'sprint_leader' => ['Sprint', 'Ditunjuk jadi ketua sprint', 'Saat mentor menunjuk Anda sebagai ketua sebuah sprint.', true],
            'sprint_to_divide' => ['Sprint', 'Tugas menunggu dibagi', 'Saat sprint yang Anda pimpin dimulai dan ada tugas yang belum dibagi.', true],
            'deadline_extended' => ['Tugas', 'Deadline diperpanjang', 'Saat mentor memperpanjang deadline tugas Anda.', true],
            'intern_joined' => ['Kelompok', 'Anak bimbingan baru', 'Saat admin menetapkan anak magang baru kepada Anda (belum masuk kelompok).', false],
            'group_joined' => ['Kelompok', 'Masuk kelompok', 'Saat mentor memasukkan Anda ke sebuah kelompok.', false],
            'intern_without_mentor' => ['Kelompok', 'Intern aktif tanpa mentor', 'Saat ada intern aktif yang belum punya mentor.', false],
        ];
        $byRole = [
            'intern' => ['task_assigned', 'task_approved', 'task_revision', 'task_due_soon', 'task_overdue', 'task_commented', 'mentioned', 'announcement', 'group_joined', 'sprint_leader', 'sprint_to_divide', 'deadline_extended'],
            'mentor' => ['task_in_review', 'task_overdue', 'task_commented', 'mentioned', 'announcement', 'intern_joined'],
            'admin' => ['mentioned', 'announcement', 'intern_joined', 'intern_without_mentor'],
        ];
        $types = array_map(fn ($type) => [
            'type' => $type,
            'group' => $catalog[$type][0],
            'label' => $catalog[$type][1],
            'description' => $catalog[$type][2],
            'in_app' => true,
            'email' => $catalog[$type][3] ? in_array($type, ['task_assigned', 'task_revision', 'sprint_leader', 'sprint_to_divide', 'deadline_extended'], true) : null,
        ], $byRole[$data['role']]);

        return response()->json(['data' => ['types' => $types, 'muted_channels' => []]]);
    })->name('notifications.preferences');

    // Halaman notifikasi. Di preview, daftar diambil lewat WS.api (mock) saat halaman dibuka.
    Route::get('/notifications', function () {
        $category = request('category');

        return view('notifications.index', [
            'notifications' => null,
            'filter' => request('filter') === 'unread' ? 'unread' : 'all',
            'category' => in_array($category, ['task', 'mention', 'announcement', 'intern'], true) ? $category : null,
        ]);
    })->name('notifications.index');
});
