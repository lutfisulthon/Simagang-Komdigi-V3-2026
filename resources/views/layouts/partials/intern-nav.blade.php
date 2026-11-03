{{--
    Menu sidebar intern (desktop & drawer mobile). Tiap menu punya ikon dan warna (tone) sendiri;
    warna didefinisikan di public/css/app-shell.css (.sb-link[data-tone]).
--}}
@php
    $internNav = [
        'Utama' => [
            ['route' => 'intern.dashboard', 'match' => ['intern.dashboard'], 'icon' => 'fa-gauge-high', 'label' => 'Dashboard', 'tone' => 'blue'],
            ['route' => 'workspace.index', 'match' => ['workspace.*'], 'icon' => 'fa-layer-group', 'label' => 'Workspace', 'tone' => 'violet', 'needs' => 'workspace'],
        ],
        'Aktivitas' => [
            ['route' => 'intern.attendance.index', 'match' => ['intern.attendance.*'], 'icon' => 'fa-calendar-check', 'label' => 'Absensi', 'tone' => 'green'],
            ['route' => 'intern.logbook.index', 'match' => ['intern.logbook.*'], 'icon' => 'fa-book-open', 'label' => 'Logbook', 'tone' => 'amber'],
            ['route' => 'intern.sharing-session.index', 'match' => ['intern.sharing-session.*'], 'icon' => 'fa-comments', 'label' => 'Sharing Session', 'tone' => 'teal'],
        ],
        'Evaluasi' => [
            ['route' => 'intern.report.index', 'match' => ['intern.report.*'], 'icon' => 'fa-file-lines', 'label' => 'Laporan Akhir', 'tone' => 'rose'],
            ['route' => 'intern.microskill.index', 'match' => ['intern.microskill.*'], 'icon' => 'fa-award', 'label' => 'Mikro Skill', 'tone' => 'orange'],
        ],
    ];
@endphp
<div class="sb-nav">
    @foreach ($internNav as $caption => $links)
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
