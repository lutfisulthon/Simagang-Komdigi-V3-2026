{{--
    Daftar kelompok (lihat docs/workspace/kontrak-frontend.md §5).
    Intern tidak melihat halaman ini bila sudah punya kelompok (langsung diarahkan ke kelompoknya).
    Variabel:
      $noGroup    bool                      intern belum dimasukkan mentor ke kelompok
      $mentor     UserMini|null             mentor intern (untuk keterangan $noGroup)
      $groups     LengthAwarePaginator|null Workspace[] type=group; aktif dulu (urut $sort), lalu selesai magang
      $counts     ['active' => int, 'finished' => int, 'attention' => int]
      $sort       'deadline'|'newest'       urutan kelompok aktif (default deadline tugas terdekat)
      $attention  bool                      hanya kelompok yang perlu perhatian (lewat deadline / jatuh tempo ≤ 3 hari)
      $ungrouped  Roster[]                  mentor: anak bimbingan yang belum punya kelompok
      $teams      {id,name}[]               admin: filter unit kerja
      $mentors    {id,name}[]               admin: filter mentor
      $can        ['create_group', 'manage_templates' => bool]
--}}
@extends('workspace.layout')

@section('title', 'Workspace - Sistem Magang')

@section('workspace-content')
    @php
        $viewerIsAdmin = ! empty($teams);
        $total = $noGroup ? 0 : $counts['active'] + $counts['finished'];
        $filtering = request()->filled('q') || request()->filled('team') || request()->filled('mentor');
        $showFilter = $viewerIsAdmin || $total > 9 || $filtering;
        $firstFinished = $noGroup ? null : collect($groups->items())->search(fn ($g) => $g['status'] === 'finished');
        $sort = $sort ?? 'deadline';
        $attention = $attention ?? false;
        $showSort = ! $noGroup && ($counts['active'] > 1 || $attention);
    @endphp

    <x-workspace.hero class="ws-anim-1 mb-6"
                      eyebrow="Workspace"
                      :title="$noGroup ? 'Kelompok Magang' : ($viewerIsAdmin ? 'Semua Kelompok' : 'Kelompok Bimbingan')"
                      :subtitle="$noGroup ? 'Tugas dan diskusi bersama kelompokmu' : ($viewerIsAdmin ? 'Pantau kelompok kerja semua mentor' : 'Kelompokkan anak bimbingan, beri tugas, dan pantau progresnya')">
        @unless ($noGroup)
            <x-slot:meta>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-users"></i> {{ $counts['active'] }} kelompok aktif</span>
                <span class="opacity-50 hidden sm:inline">·</span>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-flag-checkered"></i> {{ $counts['finished'] }} selesai magang</span>
            </x-slot:meta>
        @endunless
        @if (($can['manage_templates'] ?? false) && Route::has('workspace.templates.index'))
            <a href="{{ route('workspace.templates.index') }}" class="ws-hero-btn">
                <i class="fas fa-copy"></i> Template tugas
            </a>
        @endif
        @if ($can['create_group'] ?? false)
            <button type="button" class="ws-hero-btn ws-hero-btn--solid" x-data x-on:click="$dispatch('ws-group-create', {})">
                <i class="fas fa-plus"></i> Buat kelompok
            </button>
        @endif
    </x-workspace.hero>

    @if ($noGroup)
        <div class="ws-panel ws-anim-2">
            <x-workspace.empty-state icon="fa-users"
                                     title="Anda belum masuk kelompok"
                                     :description="'Mentor Anda' . ($mentor ? ' (' . $mentor['name'] . ')' : '') . ' akan memasukkan Anda ke sebuah kelompok. Setelah itu, tugas dan diskusi kelompok muncul di sini.'" />
        </div>
    @else
        {{-- Anak bimbingan yang belum punya kelompok --}}
        @if (count($ungrouped))
            <div class="ws-anim-2 mb-6 ws-callout" role="status">
                <span class="ws-callout__icon" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-sm text-gray-800">{{ count($ungrouped) }} anak bimbingan belum punya kelompok</p>
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($ungrouped as $intern)
                            <li class="ws-chip-person">
                                <x-workspace.avatar :name="$intern['name']" :src="$intern['avatar_url']" size="xs" />
                                <span class="text-sm font-medium text-gray-800">{{ $intern['name'] }}</span>
                                <button type="button" class="ws-chip-person__action" x-data
                                        x-on:click="$dispatch('ws-group-assign', { intern: @js(['id' => $intern['id'], 'name' => $intern['name']]) })">
                                    Masukkan<span class="sr-only"> {{ $intern['name'] }} ke kelompok</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if (count($ungrouped) > 1)
                    <button type="button" class="ws-btn ws-btn--primary ws-btn--sm shrink-0 self-start" x-data
                            x-on:click="$dispatch('ws-group-create', { intern_ids: @js(array_column($ungrouped, 'id')) })">
                        <i class="fas fa-plus"></i> Kelompokkan semua
                    </button>
                @endif
            </div>
        @endif

        @if ($showFilter)
            <form method="GET" action="{{ route('workspace.index') }}" class="ws-anim-2 mb-6 flex flex-wrap sm:flex-nowrap gap-2 sm:gap-3" role="search">
                <label class="relative flex-1 min-w-0">
                    <span class="sr-only">Cari kelompok</span>
                    <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 ws-text-faint text-sm" aria-hidden="true"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="ws-input !pl-10" placeholder="Cari nama kelompok atau anggota…">
                </label>
                @if ($viewerIsAdmin)
                    <label class="order-last sm:order-none basis-[calc(50%-4px)] sm:basis-auto sm:w-52 min-w-0">
                        <span class="sr-only">Filter unit kerja</span>
                        <select name="team" class="ws-input" onchange="this.form.submit()">
                            <option value="">Semua unit</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team['id'] }}" @selected((string) request('team') === (string) $team['id'])>{{ $team['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="order-last sm:order-none basis-[calc(50%-4px)] sm:basis-auto sm:w-52 min-w-0">
                        <span class="sr-only">Filter mentor</span>
                        <select name="mentor" class="ws-input" onchange="this.form.submit()">
                            <option value="">Semua mentor</option>
                            @foreach ($mentors as $m)
                                <option value="{{ $m['id'] }}" @selected((string) request('mentor') === (string) $m['id'])>{{ $m['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <button type="submit" class="ws-btn ws-btn--soft shrink-0" aria-label="Cari"><i class="fas fa-magnifying-glass"></i><span class="hidden sm:inline">Cari</span></button>
                @if ($filtering)
                    <a href="{{ route('workspace.index') }}" class="ws-btn ws-btn--ghost shrink-0" aria-label="Reset filter"><i class="fas fa-xmark"></i><span class="hidden sm:inline">Reset</span></a>
                @endif
            </form>
        @endif

        {{-- Urutan kelompok aktif + saring yang perlu perhatian (tugas lewat deadline / jatuh tempo ≤ 3 hari) --}}
        @if ($showSort)
            <nav class="ws-anim-2 ws-sortbar mb-5" aria-label="Urutan dan filter kelompok">
                <span class="ws-sortbar__label">Urutkan</span>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'deadline', 'page' => null]) }}" class="ws-chip ws-chip--plain !py-1 !text-xs"
                   @if ($sort === 'deadline') aria-current="true" @endif>Deadline terdekat</a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest', 'page' => null]) }}" class="ws-chip ws-chip--plain !py-1 !text-xs"
                   @if ($sort === 'newest') aria-current="true" @endif>Terbaru</a>
                <a href="{{ request()->fullUrlWithQuery(['attention' => $attention ? null : 1, 'page' => null]) }}"
                   class="ws-chip ws-chip--plain ws-chip--late !py-1 !text-xs sm:ml-auto" @if ($attention) aria-current="true" @endif>
                    <i class="fas fa-triangle-exclamation text-[11px]" aria-hidden="true"></i> Perlu perhatian
                    <span class="ws-dl-count">{{ $counts['attention'] ?? 0 }}</span>
                </a>
            </nav>
        @endif

        @if ($groups->total())
            <div class="ws-anim-3 grid gap-3 sm:gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($groups->items() as $i => $group)
                    @if ($i === 0 && $group['status'] === 'active')
                        <h2 class="ws-section-label sm:col-span-2 xl:col-span-3">Kelompok aktif</h2>
                    @elseif ($i === $firstFinished)
                        <h2 class="ws-section-label sm:col-span-2 xl:col-span-3 {{ $i > 0 ? 'mt-4' : '' }}">Selesai magang</h2>
                    @endif
                    @include('workspace.partials.group-card', ['ws' => $group, 'showMentor' => $viewerIsAdmin])
                @endforeach
            </div>

            @include('workspace.partials.pagination', ['paginator' => $groups, 'label' => 'kelompok'])
        @else
            <div class="ws-panel ws-anim-3">
                @if ($attention)
                    <x-workspace.empty-state icon="fa-circle-check" title="Tidak ada kelompok yang perlu perhatian"
                                             description="Tidak ada tugas yang lewat deadline atau jatuh tempo dalam 3 hari ke depan.">
                        <a href="{{ request()->fullUrlWithQuery(['attention' => null, 'page' => null]) }}" class="ws-btn ws-btn--soft ws-btn--sm">Tampilkan semua kelompok</a>
                    </x-workspace.empty-state>
                @elseif ($filtering)
                    <x-workspace.empty-state icon="fa-magnifying-glass" title="Tidak ada kelompok yang cocok" description="Ubah kata kunci atau filter." />
                @elseif ($can['create_group'] ?? false)
                    <x-workspace.empty-state icon="fa-users" title="Belum ada kelompok"
                                             description="Buat kelompok dari anak bimbingan Anda. Setiap kelompok otomatis menjadi workspace dengan board tugas dan channel diskusi.">
                        <button type="button" class="ws-btn ws-btn--primary mt-4" x-data x-on:click="$dispatch('ws-group-create', {})">
                            <i class="fas fa-plus"></i> Buat kelompok
                        </button>
                    </x-workspace.empty-state>
                @else
                    <x-workspace.empty-state icon="fa-users" title="Belum ada kelompok" description="Kelompok dibuat oleh mentor dari anak bimbingannya." />
                @endif
            </div>
        @endif

        @if ($can['create_group'] ?? false)
            @include('workspace.partials.group-modals')
        @endif

        @if (Route::has('workspace.preview.reset') && ($can['create_group'] ?? false))
            <p class="mt-8 text-xs text-amber-800 flex items-center gap-2">
                <i class="fas fa-flask" aria-hidden="true"></i>
                Mode preview: kelompok yang Anda buat atau ubah tersimpan di sesi login ini saja.
                <a href="{{ route('workspace.preview.reset') }}" class="font-semibold underline">Kembalikan data contoh</a>
            </p>
        @endif
    @endif
@endsection
