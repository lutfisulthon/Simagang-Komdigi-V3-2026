<?php

/*
|--------------------------------------------------------------------------
| DATA CONTOH untuk routes/workspace-preview.php — sementara, hanya local.
|--------------------------------------------------------------------------
| Bentuk array mengikuti docs/workspace/kontrak-frontend.md (hasil
| JsonResource::resolve()). Model: satu-satunya wadah kerja adalah KELOMPOK
| yang dibuat mentor dari anak bimbingannya (1 anak magang = 1 kelompok,
| anggota tidak bisa dikeluarkan). Unit kerja (tabel `teams`) hanya punya
| channel umum. Nama tim diambil dari tabel `teams`.
|
| Akun review berpasangan: mentor & intern di $pair selalu tampil di kelompok
| yang sama, siapa pun yang login. User yang login dipetakan ke slotnya
| (mentor → mentor pertama, intern → anggota kelompok pertama). Anak bimbingan
| asli mentor (tabel interns) muncul sebagai "belum punya kelompok".
|
| Perubahan dari mentor (buat kelompok, ganti nama, tambah anggota, tandai
| selesai) disimpan di SESSION user itu saja — tidak sinkron antar akun.
*/

use App\Models\Intern;
use App\Models\Team;
use App\Models\User;

return function (User $user, bool $forcePending = false): array {
    // Angka deterministik per kunci, supaya data tidak berubah setiap refresh.
    $num = fn (string $key, int $max) => crc32($key) % ($max + 1);
    $avatar = fn (?string $path) => $path ? asset('storage/' . $path) : null;
    $now = now();

    $teamNames = Team::orderBy('id')->limit(3)->pluck('name', 'id')->all();
    $teamFallback = [1 => 'Tim Aptika', 2 => 'Tim Media', 3 => 'Tim Umum'];
    $teams = [];
    foreach (array_slice($teamNames ?: $teamFallback, 0, 3, true) as $id => $name) {
        $teams[] = ['id' => $id, 'name' => $name];
    }

    $role = $user->isSuperAdmin() || $user->isAdminFull() ? 'admin' : ($user->isMentor() ? 'mentor' : 'intern');

    // ---------- Akun review berpasangan ----------
    $pair = ['mentor' => 'herman@komdigi.com', 'intern' => 'siti.nurhaliza@example.com'];
    $pairMentor = $role === 'mentor' ? $user : User::where('email', $pair['mentor'])->first();
    $pairIntern = $role === 'intern' && ! $forcePending ? $user : User::where('email', $pair['intern'])->first();

    // ---------- Orang ----------
    $mentors = [
        ['id' => 9001, 'name' => 'Herman', 'team' => 0, 'avatar_url' => null],
        ['id' => 9002, 'name' => 'Dewi Kartika', 'team' => 0, 'avatar_url' => null],
        ['id' => 9003, 'name' => 'Hendra Wijaya', 'team' => 1, 'avatar_url' => null],
        ['id' => 9004, 'name' => 'Maya Sari', 'team' => 2, 'avatar_url' => null],
    ];
    if ($pairMentor) {
        $mentors[0] = array_merge($mentors[0], ['id' => $pairMentor->id, 'name' => $pairMentor->name, 'avatar_url' => $avatar($pairMentor->mentor?->photo_path)]);
    }
    $meMentor = $role === 'mentor' ? 0 : null;

    // Kelompok aktif: key => [nama, warna, index mentor, dibuat, deskripsi]
    $groupDefs = [
        'g1' => ['Tim Redesign Website', '#4f46e5', 0, '-3 weeks', 'Redesain halaman publik Simagang dan komponen dashboard.'],
        'g2' => ['Riset Pengguna', '#0f766e', 0, '-12 days', 'Wawancara pengguna dan analisis kebutuhan fitur magang.'],
        'g3' => ['Konten Media Sosial', '#be185d', 0, '-2 days', 'Kalender konten dan desain unggahan kampanye digital.'],
        'g4' => ['Dashboard Monitoring', '#2563eb', 1, '-1 month', 'Dashboard pemantauan layanan internal.'],
        'g5' => ['Aplikasi Layanan Publik', '#c2410c', 2, '-5 weeks', 'Prototipe aplikasi pengaduan layanan publik.'],
        'g6' => ['Desain Konten Kampanye', '#7c3aed', 3, '-9 days', 'Aset visual kampanye literasi digital.'],
    ];
    // Anak magang aktif: key => [id intern, id user, nama, index mentor, akhir magang, kelompok]
    $interns = [
        'siti' => [501, 9101, 'Siti Nurhaliza', 0, '+10 weeks', 'g1'],
        'andi' => [502, 9102, 'Andi Pratama', 0, '+2 months', 'g1'],
        'rina' => [503, 9103, 'Rina Wulandari', 0, '+5 weeks', 'g1'],
        'fajar' => [504, 9104, 'Fajar Nugroho', 0, '+6 weeks', 'g2'],
        'laras' => [505, 9105, 'Larasati Putri', 0, '+3 months', 'g2'],
        'yusuf' => [506, 9106, 'Yusuf Hidayat', 0, '+2 months', 'g3'],
        'baru' => [507, 9107, 'Dimas Saputra', 0, '+3 months', null],
        'nadia' => [508, 9108, 'Nadia Putri', 1, '+2 months', 'g4'],
        'bayu' => [509, 9109, 'Bayu Pratama', 1, '+7 weeks', 'g4'],
        'eka' => [510, 9110, 'Eka Saputri', 2, '+2 months', 'g5'],
        'galih' => [511, 9111, 'Galih Ramadhan', 2, '+9 weeks', 'g5'],
        'putri' => [512, 9112, 'Putri Ayu', 3, '+3 months', 'g6'],
        'lestari' => [513, 9113, 'Dewi Lestari', 3, '+3 months', null],
    ];
    // Kelompok periode lalu (semua anggota sudah selesai magang): [nama, warna, mentor, selesai (bulan lalu), anggota]
    $pastDefs = [
        ['Aplikasi Absensi Mobile', '#0e7490', 0, 1, ['Hana Safitri', 'Reza Maulana']],
        ['Dokumentasi API Layanan', '#475569', 0, 2, ['Intan Permata']],
        ['Survei Kepuasan Layanan', '#15803d', 0, 3, ['Agus Setiawan', 'Melati Sari', 'Rudi Hartono']],
        ['Portal Informasi Publik', '#9333ea', 0, 4, ['Citra Lestari', 'Doni Saputra']],
        ['Identitas Visual Unit', '#e11d48', 0, 5, ['Fitri Handayani']],
        ['Analisis Data Kehadiran', '#a16207', 0, 6, ['Gilang Ramadhan', 'Wulan Sari']],
        ['Chatbot Layanan Informasi', '#0369a1', 0, 8, ['Joko Susilo', 'Lina Marlina']],
        ['Arsip Digital Surat', '#4d7c0f', 0, 10, ['Oki Pratama']],
        ['Infografis Program', '#a21caf', 1, 3, ['Sari Wijaya', 'Tegar Aditya']],
        ['Sistem Antrean Online', '#dc2626', 2, 4, ['Umar Faruq']],
    ];

    $people = [];
    foreach ($interns as $key => [$id, $userId, $name, $mentor, $end, $group]) {
        $people[$key] = [
            'key' => $key, 'id' => $id, 'user_id' => $userId, 'name' => $name, 'avatar_url' => null,
            'mentor' => $mentor, 'team' => $mentors[$mentor]['team'], 'end_date' => $now->copy()->modify($end)->toDateString(),
            'group' => $group, 'is_real' => false,
        ];
    }
    // Anak bimbingan asli mentor pasangan (selain intern pasangan) → belum punya kelompok.
    $realInterns = $pairMentor?->mentor
        ? Intern::where('mentor_id', $pairMentor->mentor->id)->where('is_active', true)
            ->when($pairIntern, fn ($q) => $q->where('user_id', '!=', $pairIntern->id))
            ->orderBy('id')->get(['id', 'user_id', 'name', 'end_date', 'photo_path'])
        : collect();
    if ($realInterns->isNotEmpty()) unset($people['baru']);
    foreach ($realInterns as $real) {
        $people['real-' . $real->id] = [
            'key' => 'real-' . $real->id, 'id' => $real->id, 'user_id' => $real->user_id, 'name' => $real->name,
            'avatar_url' => $avatar($real->photo_path), 'mentor' => 0, 'team' => 0,
            'end_date' => optional($real->end_date)->toDateString() ?? $now->copy()->addMonths(3)->toDateString(),
            'group' => null, 'is_real' => true,
        ];
    }
    if ($pairIntern) {
        $people['siti'] = array_merge($people['siti'], [
            'id' => $pairIntern->intern?->id ?? 501, 'user_id' => $pairIntern->id, 'name' => $pairIntern->name,
            'avatar_url' => $avatar($pairIntern->intern?->photo_path),
            'end_date' => optional($pairIntern->intern?->end_date)->toDateString() ?? $people['siti']['end_date'],
            'is_real' => (bool) $pairIntern->intern,
        ]);
    }

    // Kelompok lalu + anggotanya (alumni).
    $groupMeta = [];
    foreach ($groupDefs as $key => [$name, $color, $mentor, $created, $desc]) {
        $groupMeta[$key] = ['name' => $name, 'color' => $color, 'mentor' => $mentor, 'created_at' => $now->copy()->modify($created), 'description' => $desc, 'finished_at' => null];
    }
    $alumniId = 700;
    foreach ($pastDefs as $k => [$name, $color, $mentor, $monthsAgo, $members]) {
        $key = 'p' . ($k + 1);
        $end = $now->copy()->subMonths($monthsAgo)->subDays(3 + $k);
        $groupMeta[$key] = [
            'name' => $name, 'color' => $color, 'mentor' => $mentor, 'created_at' => $end->copy()->subMonths(3),
            'description' => 'Kelompok periode magang sebelumnya.', 'finished_at' => $end,
        ];
        foreach ($members as $memberName) {
            $alumniId++;
            $people["a$alumniId"] = [
                'key' => "a$alumniId", 'id' => $alumniId, 'user_id' => 9000 + $alumniId, 'name' => $memberName, 'avatar_url' => null,
                'mentor' => $mentor, 'team' => $mentors[$mentor]['team'], 'end_date' => $end->toDateString(), 'group' => $key, 'is_real' => false,
            ];
        }
    }

    // ---------- Perubahan mentor (session user ini) ----------
    $saved = session('ws_preview_groups', []) + ['created' => [], 'members' => [], 'renamed' => [], 'finished' => []];
    foreach ($saved['created'] as $c) {
        $groupMeta[$c['key']] = [
            'name' => $c['name'], 'color' => $c['color'], 'mentor' => 0, 'created_at' => \Illuminate\Support\Carbon::parse($c['created_at']),
            'description' => $c['description'] ?: null, 'finished_at' => null,
        ];
    }
    foreach ($saved['members'] as $personKey => $groupKey) {
        if (isset($people[$personKey], $groupMeta[$groupKey]) && ! $people[$personKey]['group']) $people[$personKey]['group'] = $groupKey;
    }

    // Intern yang login dipetakan ke slot: anggota kelompok pertama, atau ?state=pending → belum punya kelompok.
    $meIntern = null;
    if ($role === 'intern') {
        $meIntern = $forcePending
            ? (collect($people)->first(fn ($p) => $p['mentor'] === 0 && ! $p['group'])['key'] ?? 'lestari')
            : 'siti';
        $people[$meIntern] = array_merge($people[$meIntern], ['user_id' => $user->id, 'name' => $user->name]);
    }

    $mentorMini = fn (int $i) => ['id' => $mentors[$i]['id'], 'name' => $mentors[$i]['name'], 'role' => 'mentor', 'avatar_url' => $mentors[$i]['avatar_url']];
    $internUser = fn (array $p) => ['id' => $p['user_id'], 'name' => $p['name'], 'role' => 'intern', 'avatar_url' => $p['avatar_url']];
    $internMini = fn (array $p) => ['id' => $p['id'], 'user_id' => $p['user_id'], 'name' => $p['name'], 'avatar_url' => $p['avatar_url']];

    $stats = function (string $key, bool $small = false) use ($num) {
        $s = [
            'todo' => $num("$key-todo", $small ? 3 : 6),
            'in_progress' => $num("$key-prog", $small ? 2 : 4),
            'review' => $num("$key-rev", $small ? 2 : 3),
            'revision' => $num("$key-rvs", 2),
            'done' => $num("$key-done", $small ? 8 : 18) + 1,
        ];
        $s['late'] = min($num("$key-late", 2), $s['todo'] + $s['in_progress']);
        return $s;
    };

    // ---------- Sprint per kelompok ----------
    // Kelompok aktif: Sprint 1 selesai, Sprint 2 berjalan, Sprint 3 direncanakan. Kelompok selesai magang:
    // semua sprint selesai. Kelompok buatan session: belum punya sprint. Id sprint = workspace_id * 10 + k.
    // end_date = deadline sprint (dipilih saat sprint dibuat). leader = ketua tim (InternMini) per sprint.
    $sprintsOf = function (int $id, ?\Illuminate\Support\Carbon $finishedAt, bool $isNew, array $members) use ($now) {
        if ($isNew) return [];
        $mini = fn ($p) => $p ? ['id' => $p['id'], 'user_id' => $p['user_id'], 'name' => $p['name'], 'avatar_url' => $p['avatar_url']] : null;
        $lead = fn (int $k) => $mini(collect($members)->firstWhere('key', 'siti') && $k === 2 ? collect($members)->firstWhere('key', 'siti') : ($members[($k - 1) % max(1, count($members))] ?? null));
        $make = fn (int $k, string $status, $start, $end, string $goal) => [
            'id' => $id * 10 + $k, 'workspace_id' => $id, 'name' => 'Sprint ' . $k, 'goal' => $goal, 'status' => $status,
            'start_date' => $start?->toDateString(), 'end_date' => $end?->toDateString(),
            'leader' => $status === 'planned' ? null : $lead($k),
            'completed_at' => $status === 'completed' ? $end->copy()->setTime(17, 0)->toIso8601String() : null,
        ];
        if ($finishedAt) {
            $end = $finishedAt->copy();
            return [
                $make(1, 'completed', $end->copy()->subDays(28), $end->copy()->subDays(14), 'Riset & rancangan awal'),
                $make(2, 'completed', $end->copy()->subDays(14), $end, 'Penyelesaian & presentasi hasil'),
            ];
        }
        return [
            $make(1, 'completed', $now->copy()->subDays(20), $now->copy()->subDays(7), 'Onboarding & riset kebutuhan'),
            $make(2, 'active', $now->copy()->subDays(6), $now->copy()->addDays(8), 'Selesaikan prototipe halaman utama'),
            $make(3, 'planned', null, $now->copy()->addDays(22), 'Integrasi API & uji tampilan HP'),
        ];
    };

    // ---------- Kelompok (Workspace type=group) ----------
    // Id: kelompok contoh 101, 102, … (urutan $groupMeta); kelompok buatan session 301, 302, …
    $workspaces = [];
    $idOf = [];
    $seq = 100;
    foreach ($groupMeta as $key => $g) {
        $idOf[$key] = str_starts_with($key, 'c') ? (int) substr($key, 1) : ++$seq;
    }
    foreach ($groupMeta as $key => $g) {
        $id = $idOf[$key];
        $members = array_values(array_filter($people, fn ($p) => $p['group'] === $key));
        $lastEnd = collect($members)->max('end_date');
        $finishedAt = $g['finished_at']
            ?? (isset($saved['finished'][$id]) ? \Illuminate\Support\Carbon::parse($saved['finished'][$id]) : null)
            ?? ($members && $lastEnd < $now->toDateString() ? \Illuminate\Support\Carbon::parse($lastEnd) : null);
        $finished = $finishedAt !== null;
        $name = $saved['renamed'][$id]['name'] ?? $g['name'];
        $color = $saved['renamed'][$id]['color'] ?? $g['color'];
        $isNew = str_starts_with($key, 'c');

        $s = $isNew
            ? ['todo' => 0, 'in_progress' => 0, 'review' => 0, 'revision' => 0, 'done' => 0, 'late' => 0]
            : $stats("group-$id", count($members) <= 1);
        if ($finished) $s = ['todo' => 0, 'in_progress' => 0, 'review' => 0, 'revision' => 0, 'done' => $s['done'] + 4, 'late' => 0];

        $userMembers = array_merge([$mentorMini($g['mentor'])], array_map($internUser, $members));
        $owner = $meMentor === $g['mentor'];
        $sprints = $sprintsOf($id, $finishedAt, $isNew, $members);
        $active = collect($sprints)->firstWhere('status', 'active');
        if ($active) $s['todo'] += 7; // rencana: 3 tugas di sprint berikutnya + 4 di backlog
        $workspaces[$id] = [
            'id' => $id,
            'type' => 'group',
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'color' => $color,
            'description' => $g['description'],
            'team' => $teams[$mentors[$g['mentor']]['team']],
            'mentor' => $mentorMini($g['mentor']),
            'status' => $finished ? 'finished' : 'active',
            'created_at' => $g['created_at']->toIso8601String(),
            'finished_at' => $finishedAt?->toIso8601String(),
            'end_date' => $lastEnd,
            'is_archived' => $finished,
            'is_read_only' => $finished,
            // Sprint yang sedang berjalan (null = tidak ada; board menampilkan semua tugas).
            'active_sprint' => $active ? array_intersect_key($active, array_flip(['id', 'name', 'goal', 'start_date', 'end_date', 'leader'])) : null,
            'stats' => $s,
            'unread_messages' => $finished || $isNew ? 0 : $num("unread-$id", 6),
            'members_count' => count($userMembers),
            'members_preview' => array_slice($userMembers, 0, 4),
            'url' => route('workspace.show', $id),
            'can' => [
                'rename' => ! $finished && ($owner || $role === 'admin'),
                'add_members' => ! $finished && $owner,
                'finish' => ! $finished && ($owner || $role === 'admin'),
            ],
            '_members' => $userMembers,
            '_assignables' => array_map($internMini, $members),
            '_person_keys' => array_column($members, 'key'),
            '_team_index' => $mentors[$g['mentor']]['team'],
            '_mentor_index' => $g['mentor'],
            '_key' => $key,
            '_sprints' => $sprints,
        ];
    }

    // ---------- Hak lihat (meniru WorkspacePolicy@view) ----------
    $canView = function (array $ws) use ($role, $meMentor, $meIntern) {
        if ($role === 'admin') return true;
        if ($role === 'mentor') return $ws['_mentor_index'] === $meMentor;
        return in_array($meIntern, $ws['_person_keys'], true);
    };

    // Anak bimbingan mentor yang login (admin: mentor pertama) + kelompoknya. Belum berkelompok di atas.
    $roster = function () use ($people, $workspaces, $idOf, $role, $now) {
        if ($role === 'intern') return [];
        $out = [];
        foreach ($people as $p) {
            if ($p['mentor'] !== 0 || $p['end_date'] < $now->toDateString()) continue;
            $ws = $p['group'] ? $workspaces[$idOf[$p['group']]] : null;
            $out[] = [
                'id' => $p['id'], 'user_id' => $p['user_id'], 'name' => $p['name'], 'avatar_url' => $p['avatar_url'],
                'end_date' => $p['end_date'],
                'group' => $ws ? ['id' => $ws['id'], 'name' => $ws['name'], 'color' => $ws['color'], 'url' => $ws['url']] : null,
                '_key' => $p['key'],
            ];
        }
        usort($out, fn ($a, $b) => [(bool) $a['group'], $a['name']] <=> [(bool) $b['group'], $b['name']]);
        return $out;
    };

    // Intern dari id mana pun (id DB di halaman detail/monitoring) → orang di fixture. Id yang tidak dikenal
    // dipetakan deterministik ke salah satu anggota kelompok aktif.
    $personFor = function (int $internId) use ($people, $workspaces, $idOf) {
        foreach ($people as $p) if ($p['id'] === $internId) return $p;
        $active = array_values(array_filter($people, fn ($p) => $p['group'] && ! $workspaces[$idOf[$p['group']]]['is_read_only']));
        return $active[$internId % count($active)];
    };

    // ---------- Label (per tim) ----------
    $labels = [
        ['id' => 1, 'name' => 'frontend', 'color' => '#6366f1'],
        ['id' => 2, 'name' => 'backend', 'color' => '#0ea5e9'],
        ['id' => 3, 'name' => 'dokumen', 'color' => '#f59e0b'],
        ['id' => 4, 'name' => 'desain', 'color' => '#ec4899'],
        ['id' => 5, 'name' => 'riset', 'color' => '#10b981'],
    ];

    // ---------- Tugas ----------
    $taskTitles = [
        'Integrasi API absensi', 'Rancang wireframe halaman profil', 'Dokumentasi API', 'Perbaiki validasi form login',
        'Riset kompetitor aplikasi magang', 'Desain ulang kartu dashboard', 'Uji coba fitur upload', 'Rapikan dataset survei',
        'Buat slide sharing session', 'Lengkapi profil', 'Baca panduan magang', 'Perkenalan di channel tim',
        'Optimasi query laporan bulanan', 'Susun user flow pendaftaran', 'Tulis unit test modul logbook', 'Revisi copywriting landing page',
        'Audit aksesibilitas halaman login', 'Buat komponen tabel reusable', 'Analisis data kehadiran', 'Siapkan demo untuk mentor',
    ];
    $isStaff = in_array($role, ['admin', 'mentor'], true);

    // TaskCard[]: diturunkan dari stats supaya angka di kartu & board konsisten.
    $tasksFor = function (array $ws) use ($num, $taskTitles, $labels, $user, $role, $isStaff) {
        $s = $ws['stats'];
        $mentorsOnly = array_values(array_filter($ws['_members'], fn ($p) => $p['role'] === 'mentor'));
        $assignables = $ws['_assignables'];
        if (! $assignables || ! $mentorsOnly) return [];
        // Nomor kecil = tugas lama: Selesai dulu, lalu Revisi, Review, In Progress, To Do.
        $plan = [];
        foreach (['done' => min($s['done'], 8), 'revision' => $s['revision'], 'review' => $s['review'], 'in_progress' => $s['in_progress'], 'todo' => $s['todo']] as $status => $count) {
            for ($i = 0; $i < $count; $i++) $plan[] = $status;
        }
        $late = $s['late'];
        $todoIndex = 0;
        $positions = [];
        $cards = [];
        foreach ($plan as $k => $status) {
            $n = $k + 1;
            $key = "task-{$ws['id']}-$n";
            $isLate = in_array($status, ['todo', 'in_progress'], true) && $late > 0;
            if ($isLate) $late--;

            $assignees = [$assignables[$num("$key-a", count($assignables) - 1)]];
            if (count($assignables) > 1 && $num("$key-a2", 4) === 0) {
                $assignees = array_slice($assignables, 0, 2);
            }

            // Sprint: tugas aktif di sprint berjalan; To Do dibagi ke sprint berjalan / berikutnya / backlog.
            $sprints = $ws['_sprints'];
            $active = collect($sprints)->firstWhere('status', 'active');
            $planned = collect($sprints)->firstWhere('status', 'planned');
            $lastDone = collect($sprints)->where('status', 'completed')->last();
            $sprintId = match (true) {
                ! $sprints => null,
                ! $active => $lastDone['id'] ?? null,
                $status === 'done' => $num("$key-sp", 2) === 0 ? $active['id'] : ($lastDone['id'] ?? $active['id']),
                $status !== 'todo' => $active['id'],
                // 4 To Do terakhir di backlog, 3 sebelumnya di sprint berikutnya, sisanya di sprint berjalan.
                default => (fn ($fromEnd) => $fromEnd <= 4 ? null : ($fromEnd <= 7 ? ($planned['id'] ?? null) : $active['id']))($s['todo'] - $todoIndex),
            };
            if ($status === 'todo') $todoIndex++;
            // Tugas di backlog / sprint berikutnya sebagian belum di-assign (assign dilakukan di board).
            if ($status === 'todo' && $active && $sprintId !== $active['id'] && $num("$key-un", 1) === 0) $assignees = [];
            $creator = $mentorsOnly[$num("$key-c", count($mentorsOnly) - 1)];
            $creatorIsMe = $creator['id'] === $user->id;
            $assignedToMe = collect($assignees)->contains('user_id', $user->id);
            $taskSprint = $sprintId ? collect($sprints)->firstWhere('id', $sprintId) : null;
            $iLead = $taskSprint && $taskSprint['status'] !== 'completed' && ($taskSprint['leader']['user_id'] ?? null) === $user->id;
            $readOnly = $ws['is_read_only'];

            $due = match (true) {
                $isLate => now()->subHours(5 + $num("$key-due", 70)),
                $status === 'done' => now()->subDays(2 + $num("$key-due", 12)),
                default => now()->addHours(8 + $num("$key-due", 300)),
            };
            $checkTotal = $num("$key-ct", 5);
            $checkDone = match ($status) {
                'done', 'review' => $checkTotal,
                'todo' => 0,
                default => intdiv($checkTotal * (1 + $num("$key-cd", 2)), 4),
            };
            $labelCount = $num("$key-lc", 2);

            $positions[$status] = ($positions[$status] ?? -1) + 1;
            $id = $ws['id'] * 100 + $n;
            $cards[] = [
                'id' => $id,
                'workspace_id' => $ws['id'],
                'code' => 'T-' . $n,
                'title' => $taskTitles[($n + $ws['id']) % count($taskTitles)],
                'status' => $status,
                'priority' => ['low', 'medium', 'medium', 'high', 'urgent'][$num("$key-p", 4)],
                'position' => $positions[$status],
                'start_at' => $num("$key-st", 1) ? $due->copy()->subDays(4)->toIso8601String() : null,
                'due_at' => $due->toIso8601String(),
                // Sebagian kecil tugas selesai setelah deadline (untuk grafik ketepatan waktu).
                'completed_at' => $status === 'done' ? $due->copy()->addHours($num("$key-ot", 3) === 0 ? 9 : -3)->toIso8601String() : null,
                'is_late' => $isLate,
                'sprint_id' => $sprintId,
                'rank' => $n,
                'labels' => array_slice(array_merge(array_slice($labels, $num("$key-l", 4)), $labels), 0, $labelCount),
                'assignees' => $assignees,
                'checklist' => ['done' => $checkDone, 'total' => $checkTotal],
                'comments_count' => $num("$key-cm", 3),
                'attachments_count' => $status === 'todo' ? 0 : $num("$key-at", 2),
                'url' => route('workspace.tasks.show', $id),
                'can' => [
                    'update' => ! $readOnly && ($isStaff || $creatorIsMe),
                    'move' => ! $readOnly && ($isStaff || $assignedToMe),
                    'review' => ! $readOnly && $isStaff,
                    'delete' => ! $readOnly && ($role === 'admin' || ($role === 'mentor' && $creatorIsMe)),
                    // Bagi tugas: mentor/admin, atau ketua sprint tugas ini.
                    'assign' => ! $readOnly && ($isStaff || $iLead),
                    // Perpanjang deadline: mentor/admin, tugas belum selesai.
                    'extend_deadline' => ! $readOnly && $isStaff && $status !== 'done',
                ],
                '_creator' => $creator,
            ];
        }
        return $cards;
    };

    $markdown = fn (string $text) => \Illuminate\Support\Str::markdown($text, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

    // TaskDetail: TaskCard + deskripsi, checklist, lampiran, komentar, riwayat.
    $taskDetail = function (array $ws, array $card) use ($num, $markdown) {
        $key = 'detail-' . $card['id'];
        $intern = $card['assignees'][0] ?? $ws['_assignables'][0];
        $internUser = ['id' => $intern['user_id'], 'name' => $intern['name'], 'role' => 'intern', 'avatar_url' => $intern['avatar_url']];
        $creator = $card['_creator'];
        $mentor = $creator['role'] === 'mentor' ? $creator : (collect($ws['_members'])->firstWhere('role', 'mentor') ?? $creator);

        $description = "**Tujuan**: {$card['title']} untuk mendukung modul magang.\n\n"
            . "Langkah yang diharapkan:\n\n- Pelajari kebutuhan di dokumen PRD\n- Kerjakan di branch terpisah\n- Lampirkan hasil atau link sebelum kirim untuk review\n\n"
            . "Referensi: [Panduan magang](https://komdigi.go.id) · gunakan `snake_case` untuk nama file.";

        $steps = ['Baca kebutuhan', 'Buat draft awal', 'Diskusikan dengan mentor', 'Implementasi', 'Uji & dokumentasikan'];
        $checklist = [];
        for ($i = 0; $i < $card['checklist']['total']; $i++) {
            $checklist[] = ['id' => $card['id'] * 10 + $i, 'content' => $steps[$i], 'is_done' => $i < $card['checklist']['done'], 'position' => $i];
        }

        $files = [
            ['hasil-' . strtolower($card['code']) . '.pdf', 'application/pdf', 482113],
            ['screenshot.png', 'image/png', 214533],
            ['dataset.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 96200],
        ];
        $attachments = [];
        for ($i = 0; $i < $card['attachments_count']; $i++) {
            [$name, $mime, $size] = $files[$i];
            $attachments[] = [
                'id' => $card['id'] * 10 + $i, 'original_name' => $name, 'mime' => $mime, 'size' => $size,
                'is_image' => str_starts_with($mime, 'image/'), 'thumbnail_url' => null, 'download_url' => '#',
            ];
        }

        $commentTexts = [
            [$mentor, "Kalau ada kendala, tulis di sini ya. Prioritaskan bagian **validasi** dulu."],
            [$internUser, "Siap kak. Draft awal sudah saya kerjakan, lanjut ke implementasi hari ini."],
            [$mentor, "Oke, jangan lupa lampirkan screenshot hasilnya sebelum kirim review."],
        ];
        $comments = [];
        for ($i = 0; $i < $card['comments_count']; $i++) {
            [$author, $body] = $commentTexts[$i];
            $comments[] = [
                'id' => $card['id'] * 10 + $i, 'user' => $author, 'body' => $body, 'body_html' => $markdown($body),
                'attachments' => [], 'created_at' => now()->subHours(30 - $i * 9)->toIso8601String(), 'is_deleted' => false,
                'can' => ['delete' => false],
            ];
        }

        $history = $card['assignees'] ? [[$creator, 'created', []], [$creator, 'assigned', ['assignee' => $intern['name']]]] : [[$creator, 'created', []]];
        $path = ['todo' => [], 'in_progress' => ['in_progress'], 'review' => ['in_progress', 'review'],
            'revision' => ['in_progress', 'review', 'revision'], 'done' => ['in_progress', 'review', 'done']][$card['status']];
        $prev = 'todo';
        foreach ($path as $to) {
            $history[] = match ($to) {
                'done' => [$mentor, 'reviewed', ['decision' => 'approve', 'score' => 78 + $num("$key-sc", 20)]],
                'revision' => [$mentor, 'reviewed', ['decision' => 'revision']],
                default => [$internUser, 'status_changed', ['from' => $prev, 'to' => $to]],
            };
            $prev = $to;
        }
        if ($card['is_late']) $history[] = [null, 'marked_late', []];
        $activities = [];
        foreach (array_reverse($history) as $i => [$actor, $action, $meta]) {
            $activities[] = [
                'id' => $card['id'] * 10 + $i, 'user' => $actor, 'action' => $action, 'meta' => $meta,
                'task' => ['id' => $card['id'], 'code' => $card['code'], 'title' => $card['title'], 'url' => $card['url']],
                'created_at' => now()->subHours(2 + $i * 7)->toIso8601String(),
            ];
        }

        $score = $card['status'] === 'done' ? 78 + $num("$key-sc", 20) : null;
        $feedback = match ($card['status']) {
            'done' => "Hasil sudah sesuai. Struktur kode **rapi** dan dokumentasinya jelas.",
            'revision' => "Tambahkan validasi untuk input kosong, lalu lampirkan screenshot hasil uji.",
            default => null,
        };

        $detail = $card;
        unset($detail['_creator']);
        $detail['can']['comment'] = ! $ws['is_read_only'];
        return $detail + [
            'description' => $description,
            'description_html' => $markdown($description),
            'creator' => $creator,
            'workspace' => ['id' => $ws['id'], 'name' => $ws['name'], 'type' => $ws['type'], 'url' => $ws['url']],
            'checklist_items' => $checklist,
            'attachments' => $attachments,
            'result_links' => in_array($card['status'], ['review', 'revision', 'done'], true)
                ? [['url' => 'https://github.com/komdigi/simagang/pull/' . (100 + $num("$key-pr", 80)), 'label' => 'Pull request']]
                : [],
            'score' => $score,
            'feedback' => $feedback,
            'feedback_html' => $feedback ? $markdown($feedback) : null,
            'comments' => $comments,
            'activities' => $activities,
        ];
    };

    // ---------- Template tugas (per tim) ----------
    // Id = (index tim + 1) * 10 + k. Mentor hanya melihat template timnya; admin semua tim.
    $templateDefs = [
        ['Lengkapi profil', "Lengkapi data profil di menu **Profil** supaya mentor dan admin mudah menghubungi Anda.", ['Unggah foto profil', 'Isi data kampus & jurusan', 'Isi kontak darurat'], 'medium', [3], 2, true],
        ['Baca panduan magang', "Baca panduan magang sampai selesai. Tanyakan di channel tim bila ada yang belum jelas.", ['Aturan kehadiran', 'Tata cara mengisi logbook', 'Etika komunikasi'], 'medium', [3], 3, true],
        ['Perkenalan di channel tim', "Perkenalkan diri di channel **#umum** tim: nama, kampus, dan minat bidang.", [], 'low', [], 1, true],
        ['Absen hari pertama', "Lakukan absensi pada hari pertama magang sebelum pukul 08.30 WIB.", [], 'high', [], 1, true],
        ['Laporan mingguan', "Rangkum pekerjaan minggu ini dan rencana minggu depan.\n\n- Lampirkan dalam format PDF\n- Maksimal 2 halaman", ['Ringkasan pekerjaan', 'Kendala', 'Rencana minggu depan'], 'medium', [3], 7, false],
        ['Review kode pull request', "Review satu pull request rekan tim, beri minimal 3 komentar yang membangun.", ['Baca deskripsi PR', 'Jalankan di lokal', 'Tulis komentar'], 'medium', [1, 2], 3, false],
        ['Riset kompetitor aplikasi', "Bandingkan 3 aplikasi sejenis: fitur utama, kelebihan, dan kekurangan.", ['Pilih 3 aplikasi', 'Buat tabel perbandingan', 'Tulis kesimpulan'], 'low', [5], 5, false],
    ];
    $templatesFor = function () use ($templateDefs, $teams, $labels, $mentors, $mentorMini, $role, $meMentor, $num, $markdown) {
        $labelById = collect($labels)->keyBy('id');
        $out = [];
        foreach ($teams as $t => $team) {
            if ($role === 'mentor' && $mentors[$meMentor]['team'] !== $t) continue;
            if (! in_array($role, ['admin', 'mentor'], true)) continue;
            $creatorIndex = collect($mentors)->search(fn ($m) => $m['team'] === $t);
            foreach ($templateDefs as $k => [$title, $desc, $checklist, $priority, $labelIds, $days, $onboarding]) {
                if ($k === 6 && $t !== 0) continue; // template tambahan hanya di tim pertama
                $id = ($t + 1) * 10 + $k + 1;
                $editable = $role === 'admin' || ($role === 'mentor' && $mentors[$meMentor]['team'] === $t);
                $out[] = [
                    'id' => $id,
                    'team' => $team,
                    'title' => $title,
                    'description' => $desc,
                    'description_html' => $markdown($desc),
                    'priority' => $priority,
                    'labels' => array_values(array_map(fn ($l) => $labelById[$l], $labelIds)),
                    'due_in_days' => $days,
                    'checklist' => array_map(fn ($c) => ['content' => $c], $checklist),
                    'is_onboarding' => $onboarding,
                    'creator' => $creatorIndex !== false ? $mentorMini($creatorIndex) : null,
                    'usage_count' => $onboarding ? 3 + $num("tpl-use-$id", 9) : $num("tpl-use-$id", 14),
                    'updated_at' => now()->subDays(1 + $num("tpl-upd-$id", 40))->toIso8601String(),
                    'can' => ['update' => $editable, 'delete' => $editable, 'assign' => $editable],
                ];
            }
        }
        return $out;
    };

    // Intern yang boleh ditugaskan dari halaman template (meniru TaskPolicy@create): anak magang aktif
    // yang sudah punya kelompok — tugas dibuat di kelompoknya. Admin semua, mentor anak bimbingannya.
    $templateTargets = function () use ($people, $teams, $workspaces, $idOf, $role, $meMentor) {
        $out = [];
        foreach ($people as $p) {
            if (! $p['group'] || ! in_array($role, ['admin', 'mentor'], true)) continue;
            if ($role === 'mentor' && $p['mentor'] !== $meMentor) continue;
            $ws = $workspaces[$idOf[$p['group']]];
            if ($ws['is_read_only']) continue;
            $out[] = [
                'id' => $p['id'], 'user_id' => $p['user_id'], 'name' => $p['name'], 'avatar_url' => $p['avatar_url'],
                'team' => $teams[$p['team']],
                'group' => ['id' => $ws['id'], 'name' => $ws['name'], 'url' => $ws['url']],
            ];
        }
        return $out;
    };

    // ---------- Aktivitas workspace ----------
    $activities = function (array $ws) use ($num, $tasksFor) {
        $tasks = $tasksFor($ws);
        $people = $ws['_members'];
        $mentorsOnly = array_values(array_filter($people, fn ($p) => $p['role'] === 'mentor')) ?: $people;
        $internsOnly = array_values(array_filter($people, fn ($p) => $p['role'] === 'intern')) ?: $people;
        $statuses = ['in_progress', 'review', 'review', 'in_progress', 'todo'];
        $items = [];
        $minutes = 4;
        for ($k = 0; $k < 20 && $tasks; $k++) {
            $key = "act-{$ws['id']}-$k";
            $t = $tasks[$num("$key-n", count($tasks) - 1)];
            $task = ['id' => $t['id'], 'code' => $t['code'], 'title' => $t['title'], 'url' => $t['url']];
            $number = (int) substr($t['code'], 2);
            $kind = $num("$key-kind", 7);
            $intern = $internsOnly[$num("$key-i", count($internsOnly) - 1)];
            $mentor = $mentorsOnly[$num("$key-m", count($mentorsOnly) - 1)];
            [$actor, $action, $meta] = match ($kind) {
                0 => [$mentor, 'created', []],
                1 => [$intern, 'status_changed', ['from' => 'todo', 'to' => $statuses[$num("$key-s", 4)]]],
                2 => [$mentor, 'reviewed', ['decision' => 'approve', 'score' => 70 + $num("$key-sc", 30)]],
                3 => [$mentor, 'reviewed', ['decision' => 'revision']],
                4 => [$intern, 'commented', []],
                5 => [$intern, 'attachment_added', ['file' => 'hasil-' . $number . '.pdf']],
                6 => [$mentor, 'assigned', ['assignee' => $intern['name']]],
                default => [null, 'marked_late', []],
            };
            $items[] = [
                'id' => 10000 - $k,
                'user' => $actor,
                'action' => $action,
                'meta' => $meta,
                'task' => $task,
                'created_at' => now()->subMinutes($minutes)->toIso8601String(),
            ];
            $minutes += 20 + $num("$key-gap", 400);
        }
        return $items;
    };

    // ---------- Channel ----------
    // Kelompok: #<nama-kelompok> (id = workspace_id * 10 + 1) + pesan langsung mentor ↔ tiap anggota
    // (id = workspace_id * 10 + 2 + k). Unit kerja: #umum-<tim> (id = 2 + index tim, workspace_id null,
    // dipakai bersama semua kelompok di unit itu). #pengumuman (global) = 1.
    // Id pesan = channel_id * 1000 + n, sehingga last_read_message_id konsisten dengan mock-channels.js.
    $messageCount = ['team' => 64, 'topic' => 18, 'direct' => 26, 'announcement' => 12];
    $channelsFor = function (array $ws) use ($num, $role, $isStaff, $user, $messageCount, $teams) {
        $readOnly = $ws['is_read_only'];
        $make = function (int $id, ?int $workspaceId, string $name, string $type, int $unread, array $extra = []) use ($readOnly, $isStaff, $role, $messageCount, $ws) {
            $global = $type === 'announcement';
            return array_merge([
                'id' => $id,
                'workspace_id' => $workspaceId,
                'name' => $name,
                'type' => $type,
                'description' => null,
                'is_private' => false,
                'is_archived' => $workspaceId !== null && $ws['is_archived'],
                'members_count' => $workspaceId !== null ? count($ws['_members']) : null,
                'members' => null,
                'unread_count' => $unread,
                'last_read_message_id' => $id * 1000 + $messageCount[$type] - $unread,
                // Jumlah pesan disematkan (mock-channels.js menyematkan pesan sebanyak ini).
                'pinned_count' => ['team' => 2, 'announcement' => 1, 'topic' => 0, 'direct' => 1][$type],
                'is_muted' => false,
                'can' => [
                    'post' => $global ? $isStaff : ! $readOnly && ($type !== 'direct' || $role !== 'admin'),
                    'manage' => false, // channel dibuat sistem; tidak ada channel buatan sendiri di kelompok
                    'pin' => $global ? $isStaff : ! $readOnly && $isStaff,
                ],
            ], $extra);
        };

        $unread = $ws['unread_messages'];
        $directs = [];
        $mentor = collect($ws['_members'])->firstWhere('role', 'mentor');
        foreach (array_values(array_filter($ws['_members'], fn ($m) => $m['role'] === 'intern')) as $k => $intern) {
            if ($role === 'intern' && $intern['id'] !== $user->id) continue; // intern hanya melihat DM miliknya
            $directs[] = [$ws['id'] * 10 + 2 + $k, $intern];
        }
        $dmUnread = $unread > 2 && $directs ? 1 : 0;

        $channels = [$make($ws['id'] * 10 + 1, $ws['id'], $ws['slug'], 'team', $unread - $dmUnread,
            ['description' => 'Diskusi kelompok ' . $ws['name'] . '. Semua anggota otomatis bergabung.'])];
        foreach ($directs as $i => [$id, $intern]) {
            $channels[] = $make($id, $ws['id'], 'dm-' . \Illuminate\Support\Str::slug($intern['name']), 'direct', $i === 0 ? $dmUnread : 0,
                ['is_private' => true, 'members' => [$mentor, $intern], 'members_count' => 2, 'description' => 'Pesan langsung mentor dan anak magang.']);
        }
        $team = $teams[$ws['_team_index']];
        $channels[] = $make(2 + $ws['_team_index'], null, 'umum-' . \Illuminate\Support\Str::slug($team['name']), 'team', $readOnly ? 0 : $num("unit-{$user->id}-{$team['id']}", 3),
            ['description' => 'Channel umum unit ' . $team['name'] . ' untuk semua kelompok, mentor, dan anak magang di unit ini.']);
        $channels[] = $make(1, null, 'pengumuman', 'announcement', $num("ann-{$user->id}", 2),
            ['description' => 'Pengumuman untuk semua peserta magang dan mentor.']);
        return $channels;
    };

    // ---------- Ringkasan deadline per kelompok (kartu daftar kelompok, kontrak #20) ----------
    // Dihitung dari tugas yang belum dikirim (To Do / In Progress / Revisi). Kelompok selesai magang: null.
    //   next            tugas terdekat yang belum lewat deadline
    //   overdue_count   tugas yang sudah lewat deadline dan belum dikirim ("Belum diselesaikan")
    //   due_soon_count  tugas yang deadline-nya ≤ 3 hari lagi
    foreach ($workspaces as &$wsRef) {
        $wsRef['deadline'] = null;
        if ($wsRef['status'] === 'finished') continue;
        $nowTs = now()->getTimestamp();
        $open = array_values(array_filter($tasksFor($wsRef), fn ($t) => in_array($t['status'], ['todo', 'in_progress', 'revision'], true)));
        $overdue = array_filter($open, fn ($t) => $t['is_late'] || strtotime($t['due_at']) < $nowTs);
        $upcoming = array_values(array_filter($open, fn ($t) => ! $t['is_late'] && strtotime($t['due_at']) >= $nowTs));
        usort($upcoming, fn ($a, $b) => strcmp($a['due_at'], $b['due_at']));
        $wsRef['deadline'] = [
            'next' => $upcoming ? array_intersect_key($upcoming[0], array_flip(['id', 'code', 'title', 'due_at', 'url'])) : null,
            'overdue_count' => count($overdue),
            'due_soon_count' => count(array_filter($upcoming, fn ($t) => strtotime($t['due_at']) <= $nowTs + 3 * 86400)),
        ];
    }
    unset($wsRef);

    $strip = fn (array $ws) => array_filter($ws, fn ($key) => ! str_starts_with($key, '_'), ARRAY_FILTER_USE_KEY);

    // Kelompok aktif perlu perhatian: ada tugas lewat deadline, atau deadline ≤ 3 hari lagi.
    $needsAttention = fn (array $ws) => $ws['status'] === 'active'
        && (($ws['deadline']['overdue_count'] ?? 0) > 0 || ($ws['deadline']['due_soon_count'] ?? 0) > 0);

    // Urutan daftar kelompok: aktif dulu, lalu selesai magang (terbaru selesai).
    //   by = 'newest'   (default) aktif terbaru dibuat
    //   by = 'deadline' ada tugas lewat deadline (terbanyak) → deadline tugas terdekat → belum ada deadline
    $sortGroups = function (array $list, string $by = 'newest') {
        $rank = function (array $w) {
            $overdue = $w['deadline']['overdue_count'] ?? 0;
            $next = $w['deadline']['next']['due_at'] ?? null;
            return [$overdue > 0 ? 0 : ($next ? 1 : 2), -$overdue, $next ?? ''];
        };
        usort($list, function ($a, $b) use ($by, $rank) {
            $fa = $a['status'] === 'finished';
            $fb = $b['status'] === 'finished';
            if ($fa !== $fb) return $fa <=> $fb;
            if ($fa) return strcmp($b['finished_at'], $a['finished_at']);
            if ($by === 'deadline' && ($cmp = $rank($a) <=> $rank($b)) !== 0) return $cmp;
            return strcmp($b['created_at'], $a['created_at']);
        });
        return $list;
    };

    return [
        'role' => $role,
        'teams' => $teams,
        'mentors' => array_map(fn ($m) => ['id' => $m['id'], 'name' => $m['name']], $mentors),
        'workspaces' => $workspaces,
        'labels' => $labels,
        'canView' => $canView,
        'activities' => $activities,
        'tasks' => fn (array $ws) => array_map($strip, $tasksFor($ws)),
        'taskDetail' => function (array $ws, int $taskId) use ($tasksFor, $taskDetail) {
            $card = collect($tasksFor($ws))->firstWhere('id', $taskId);
            return $card ? $taskDetail($ws, $card) : null;
        },
        'channels' => $channelsFor,
        'sprints' => fn (array $ws) => $ws['_sprints'],
        'templates' => $templatesFor,
        'templateTargets' => $templateTargets,
        'strip' => $strip,
        'sortGroups' => $sortGroups,
        'needsAttention' => $needsAttention,
        'roster' => $roster,
        'personFor' => $personFor,
        'people' => $people,
        'groupIdOf' => $idOf,
        'saved' => $saved,
        // Intern yang login: kelompoknya (null = belum dimasukkan mentor ke kelompok).
        'myGroup' => $role === 'intern' && $people[$meIntern]['group'] ? $workspaces[$idOf[$people[$meIntern]['group']]] : null,
        'myMentor' => $role === 'intern' ? $mentorMini($people[$meIntern]['mentor']) : null,
        'meIntern' => $meIntern,
    ];
};
