{{--
    Menu sidebar mentor (desktop & drawer mobile). Tiap menu punya ikon dan warna (tone) sendiri;
    warna didefinisikan di public/css/app-shell.css (.sb-link[data-tone]).
--}}
@php
    $mentorNav = [
        'Utama' => [
            ['route' => 'mentor.dashboard', 'match' => ['mentor.dashboard'], 'icon' => 'fa-gauge-high', 'label' => 'Dashboard Mentor', 'tone' => 'blue'],
            ['route' => 'workspace.index', 'match' => ['workspace.*'], 'icon' => 'fa-layer-group', 'label' => 'Workspace', 'tone' => 'violet', 'needs' => 'workspace'],
        ],
        'Bimbingan' => [
            ['route' => 'mentor.intern.index', 'match' => ['mentor.intern.*'], 'icon' => 'fa-user-graduate', 'label' => 'Anak Bimbingan', 'tone' => 'teal'],
            ['route' => 'mentor.attendance.index', 'match' => ['mentor.attendance.*'], 'icon' => 'fa-calendar-check', 'label' => 'Absensi', 'tone' => 'green'],
            ['route' => 'mentor.logbook.index', 'match' => ['mentor.logbook.*'], 'icon' => 'fa-book-open', 'label' => 'Logbook', 'tone' => 'amber'],
        ],
        'Evaluasi' => [
            ['route' => 'mentor.report.index', 'match' => ['mentor.report.*'], 'icon' => 'fa-file-lines', 'label' => 'Laporan Akhir', 'tone' => 'rose'],
            ['route' => 'mentor.microskill.index', 'match' => ['mentor.microskill.*'], 'icon' => 'fa-award', 'label' => 'Mikro Skill', 'tone' => 'orange'],
        ],
    ];
@endphp
<div class="sb-nav">
    @foreach ($mentorNav as $caption => $links)
        @php
            $links = array_filter($links, fn ($l) => ($l['needs'] ?? null) !== 'workspace' || $wsEnabled);
        @endphp
        @continue(empty($links))
        <p class="sb-cap">{{ $caption }}</p>
        @foreach ($links as $l)
            @php $active = request()->routeIs(...$l['match']); @endphp
            <a href="{{ route($l['route']) }}" data-tone="{{ $l['tone'] }}"
               class="sb-link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif>
                <span class="sb-ico"><i class="fas {{ $l['icon'] }}"></i></span>
                <span>{{ $l['label'] }}</span>
            </a>
        @endforeach
    @endforeach
</div>
