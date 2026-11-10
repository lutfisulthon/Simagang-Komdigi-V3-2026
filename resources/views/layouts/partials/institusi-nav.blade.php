{{-- Menu sidebar institusi (desktop & drawer mobile). --}}
@php
    $navGroups = [
        'Utama' => [
            ['route' => 'institusi.dashboard', 'match' => ['institusi.dashboard'], 'icon' => 'fa-gauge-high', 'label' => 'Dashboard', 'tone' => 'blue'],
            ['route' => 'public-chat.index', 'match' => ['public-chat.*'], 'icon' => 'fa-comment-dots', 'label' => 'Chat Publik', 'tone' => 'teal', 'badge' => 'chat'],
            ['route' => 'institusi.lowongan.index', 'match' => ['institusi.lowongan.*'], 'icon' => 'fa-briefcase', 'label' => 'Lowongan Magang', 'tone' => 'violet'],
            ['route' => 'institusi.pengajuan.index', 'match' => ['institusi.pengajuan.*'], 'icon' => 'fa-paper-plane', 'label' => 'Pengajuan Magang', 'tone' => 'orange'],
        ],
        'Monitoring' => [
            ['route' => 'institusi.intern.index', 'match' => ['institusi.intern.*'], 'icon' => 'fa-user-graduate', 'label' => 'Peserta Magang', 'tone' => 'teal'],
            ['route' => 'institusi.attendance.index', 'match' => ['institusi.attendance.*'], 'icon' => 'fa-calendar-check', 'label' => 'Absensi', 'tone' => 'green'],
            ['route' => 'institusi.logbook.index', 'match' => ['institusi.logbook.*'], 'icon' => 'fa-book-open', 'label' => 'Logbook', 'tone' => 'amber'],
            ['route' => 'institusi.microskill.index', 'match' => ['institusi.microskill.*'], 'icon' => 'fa-award', 'label' => 'Mikro Skill', 'tone' => 'orange'],
            ['route' => 'institusi.certificate.index', 'match' => ['institusi.certificate.*'], 'icon' => 'fa-certificate', 'label' => 'Nilai', 'tone' => 'rose'],
        ],
    ];
@endphp
@include('layouts.partials.nav-list')
