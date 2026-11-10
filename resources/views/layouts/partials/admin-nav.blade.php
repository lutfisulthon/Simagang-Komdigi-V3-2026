{{-- Menu sidebar admin & super admin (desktop & drawer mobile). --}}
@php
    $navGroups = [
        'Utama' => [
            ['route' => 'admin.dashboard', 'match' => ['admin.dashboard'], 'icon' => 'fa-gauge-high', 'label' => 'Dashboard', 'tone' => 'blue'],
            ['route' => 'public-chat.index', 'match' => ['public-chat.*'], 'icon' => 'fa-comment-dots', 'label' => 'Chat Publik', 'tone' => 'teal', 'badge' => 'chat'],
            ['route' => 'workspace.index', 'match' => ['workspace.*'], 'icon' => 'fa-layer-group', 'label' => 'Workspace', 'tone' => 'violet', 'needs' => 'workspace'],
        ],
        'Pengguna' => [
            ['route' => 'admin.accounts.index', 'match' => ['admin.accounts.*'], 'icon' => 'fa-user-shield', 'label' => 'Admin', 'tone' => 'rose', 'super' => true],
            ['route' => 'admin.mentor.index', 'match' => ['admin.mentor.*'], 'icon' => 'fa-chalkboard-user', 'label' => 'Mentor', 'tone' => 'teal', 'can' => 'manage_mentors'],
            ['route' => 'admin.mitra.index', 'match' => ['admin.mitra.*'], 'icon' => 'fa-handshake', 'label' => 'Mitra', 'tone' => 'amber'],
            ['route' => 'admin.intern.index', 'match' => ['admin.intern.*'], 'icon' => 'fa-user-graduate', 'label' => 'Peserta Magang', 'tone' => 'green'],
            ['route' => 'admin.team.index', 'match' => ['admin.team.*'], 'icon' => 'fa-people-group', 'label' => 'Tim Kerja/Bagian', 'tone' => 'violet', 'can' => 'manage_teams'],
        ],
        'Operasional' => [
            ['route' => 'admin.lowongan.index', 'match' => ['admin.lowongan.*'], 'icon' => 'fa-briefcase', 'label' => 'Lowongan', 'tone' => 'blue', 'can' => 'manage_lowongan'],
            ['route' => 'admin.verifikasi.index', 'match' => ['admin.verifikasi.*'], 'icon' => 'fa-clipboard-check', 'label' => 'Verifikasi Lowongan', 'tone' => 'teal', 'can' => 'view_verifikasi_lowongan'],
            ['route' => 'admin.pengajuan.index', 'match' => ['admin.pengajuan.*'], 'icon' => 'fa-file-contract', 'label' => 'Pengajuan', 'tone' => 'orange', 'can' => 'manage_pengajuan'],
            ['route' => 'admin.attendance.index', 'match' => ['admin.attendance.*'], 'icon' => 'fa-calendar-check', 'label' => 'Absensi', 'tone' => 'green', 'can' => 'view_attendance'],
            ['route' => 'admin.logbook.index', 'match' => ['admin.logbook.*'], 'icon' => 'fa-book-open', 'label' => 'Logbook', 'tone' => 'amber', 'can' => 'view_logbook'],
            ['route' => 'admin.microskill.index', 'match' => ['admin.microskill.index', 'admin.microskill.create', 'admin.microskill.edit', 'admin.microskill.show'], 'icon' => 'fa-award', 'label' => 'Mikro Skill', 'tone' => 'orange'],
            ['route' => 'admin.sharing-session.index', 'match' => ['admin.sharing-session.*'], 'icon' => 'fa-comments', 'label' => 'Sharing Session', 'tone' => 'teal'],
            ['route' => 'admin.aktivitas.index', 'match' => ['admin.aktivitas.*'], 'icon' => 'fa-newspaper', 'label' => 'Aktivitas Terbaru', 'tone' => 'rose'],
        ],
        'Monitoring & Evaluasi' => [
            ['route' => 'admin.monitoring.index', 'match' => ['admin.monitoring.index'], 'icon' => 'fa-chart-line', 'label' => 'Monitoring Peserta Magang', 'tone' => 'blue'],
            ['route' => 'admin.monitoring.industri.index', 'match' => ['admin.monitoring.industri.*'], 'icon' => 'fa-building', 'label' => 'Monitoring Industri', 'tone' => 'violet'],
            ['route' => 'admin.report.index', 'match' => ['admin.report.*'], 'icon' => 'fa-file-lines', 'label' => 'Laporan', 'tone' => 'rose', 'can' => 'view_reports'],
        ],
        'Sistem' => [
            ['route' => 'admin.rag.index', 'match' => ['admin.rag.*'], 'icon' => 'fa-brain', 'label' => 'Manajemen AI RAG', 'tone' => 'violet', 'super' => true],
        ],
    ];
@endphp
@include('layouts.partials.nav-list')
