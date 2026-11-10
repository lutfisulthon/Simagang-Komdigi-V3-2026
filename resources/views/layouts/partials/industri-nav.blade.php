{{-- Menu sidebar industri (desktop & drawer mobile). --}}
@php
    $navGroups = [
        'Utama' => [
            ['route' => 'industri.dashboard', 'match' => ['industri.dashboard'], 'icon' => 'fa-gauge-high', 'label' => 'Dashboard Industri', 'tone' => 'blue'],
            ['route' => 'public-chat.index', 'match' => ['public-chat.*'], 'icon' => 'fa-comment-dots', 'label' => 'Chat Publik', 'tone' => 'teal', 'badge' => 'chat'],
            ['route' => 'industri.lowongan.index', 'match' => ['industri.lowongan.*'], 'icon' => 'fa-briefcase', 'label' => 'Lowongan Magang', 'tone' => 'violet'],
            ['route' => 'industri.pengajuan.index', 'match' => ['industri.pengajuan.*'], 'icon' => 'fa-file-lines', 'label' => 'Pengajuan Magang', 'tone' => 'orange'],
        ],
        'Monitoring' => [
            ['route' => 'industri.intern.index', 'match' => ['industri.intern.*'], 'icon' => 'fa-user-graduate', 'label' => 'Peserta Magang', 'tone' => 'teal'],
            ['route' => 'industri.attendance.index', 'match' => ['industri.attendance.*'], 'icon' => 'fa-calendar-check', 'label' => 'Absensi', 'tone' => 'green'],
            ['route' => 'industri.logbook.index', 'match' => ['industri.logbook.*'], 'icon' => 'fa-book-open', 'label' => 'Logbook', 'tone' => 'amber'],
            ['route' => 'industri.microskill.index', 'match' => ['industri.microskill.*'], 'icon' => 'fa-award', 'label' => 'Mikro Skill', 'tone' => 'orange'],
            ['route' => 'industri.report.index', 'match' => ['industri.report.*'], 'icon' => 'fa-file-signature', 'label' => 'Laporan Akhir', 'tone' => 'rose'],
        ],
    ];
@endphp
@include('layouts.partials.nav-list')
