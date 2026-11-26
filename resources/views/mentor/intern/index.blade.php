@extends('layouts.app')

@section('title', 'Anak Magang Bimbingan - Sistem Magang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/mentor-intern-index.css') }}?v={{ @filemtime(public_path('css/pages/mentor-intern-index.css')) }}">
<link rel="stylesheet" href="{{ asset('css/live-filter-ajax.css') }}?v={{ @filemtime(public_path('css/live-filter-ajax.css')) }}">
@endpush

@section('content')
<x-workspace.groups-js />
<x-workspace.page-css />
{{-- Chart.js dimuat sekali di sini (di luar area yang di-swap AJAX) supaya tidak diunduh ulang tiap filter. --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<div class="dash-bg py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" id="mentor-intern-ajax" data-ajax-content>

        {{-- ── HERO HEADER ──────────────────────────────── --}}
        <div class="hero-strip shadow-xl a1">
            <div class="relative z-10 px-6 py-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Anak Magang Bimbingan</h1>
                    <p class="text-blue-200 text-sm">Kelola dan pantau perkembangan anak magang Anda</p>
                </div>
            </div>
        </div>

        {{-- ── KOMPOSISI ANAK MAGANG: Aktif vs Alumni ──────────── --}}
        <div class="panel a2">
            <div class="panel-header">
                <i class="fas fa-chart-pie text-blue-200"></i>
                <h2>Komposisi Anak Magang</h2>
            </div>
            <div class="panel-body">
                @if ($compositionTotal > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 items-center">

                        {{-- Donut + legenda --}}
                        <div class="lg:col-span-2">
                            <div class="composition-chart-wrap">
                                <canvas id="compositionChart" role="img"
                                    aria-label="Diagram komposisi {{ $compositionActive }} anak magang aktif berbanding {{ $compositionAlumni }} alumni"></canvas>
                                <div class="composition-chart-center" aria-hidden="true">
                                    <span class="composition-chart-center__value">{{ $compositionTotal }}</span>
                                    <span class="composition-chart-center__label">Total Anak Magang</span>
                                </div>
                            </div>
                            <div class="composition-legend">
                                <div class="composition-legend__item">
                                    <span class="composition-legend__dot" style="background:#10b981"></span>
                                    <span class="composition-legend__text">Aktif</span>
                                    <span class="composition-legend__value">
                                        {{ $compositionActive }}
                                        <span class="composition-legend__pct">({{ $compositionTotal ? round($compositionActive / $compositionTotal * 100) : 0 }}%)</span>
                                    </span>
                                </div>
                                <div class="composition-legend__item">
                                    <span class="composition-legend__dot" style="background:#64748b"></span>
                                    <span class="composition-legend__text">Alumni</span>
                                    <span class="composition-legend__value">
                                        {{ $compositionAlumni }}
                                        <span class="composition-legend__pct">({{ $compositionTotal ? round($compositionAlumni / $compositionTotal * 100) : 0 }}%)</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Wawasan --}}
                        <div class="lg:col-span-3">
                            <p class="section-label">Wawasan</p>
                            <div class="insight-list">
                                <div class="insight-item">
                                    <span class="insight-item__icon" style="background:#eff6ff;color:#1d4ed8;"><i class="fas fa-percentage"></i></span>
                                    <div>
                                        <p class="insight-item__label">Rasio Aktif : Alumni</p>
                                        <p class="insight-item__value">{{ $compositionActive }} : {{ $compositionAlumni }}</p>
                                    </div>
                                </div>
                                <div class="insight-item">
                                    <span class="insight-item__icon" style="background:#f0fdf4;color:#15803d;"><i class="fas fa-hourglass-half"></i></span>
                                    <div>
                                        <p class="insight-item__label">Rata-rata Lama Magang Alumni</p>
                                        <p class="insight-item__value">
                                            @if ($avgDurationDays)
                                                {{ round($avgDurationDays) }} hari <span class="text-sm text-gray-500 font-normal">(~{{ round($avgDurationDays / 30, 1) }} bulan)</span>
                                            @else
                                                <span class="text-sm text-gray-400 font-normal">Belum ada alumni untuk dihitung</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="insight-item">
                                    <span class="insight-item__icon" style="background:#fff7ed;color:#c2410c;"><i class="fas fa-university"></i></span>
                                    <div>
                                        <p class="insight-item__label">Institusi dengan Alumni Terbanyak</p>
                                        <p class="insight-item__value">
                                            @if ($topAlumniInstitution)
                                                {{ $topAlumniInstitution->institution }}
                                                <span class="text-sm text-gray-500 font-normal">({{ $topAlumniInstitution->total }} orang)</span>
                                            @else
                                                <span class="text-sm text-gray-400 font-normal">Belum ada alumni untuk dihitung</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-chart-pie"></i>
                        <p class="font-semibold text-sm">Belum ada anak magang bimbingan</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── SEARCH & FILTER PANEL ────────────────────── --}}
        <div class="panel panel-filter a2">
            <div class="panel-header">
                <i class="fas fa-search text-blue-200"></i>
                <h2>Cari Anak Magang</h2>
            </div>
            <div class="panel-body">
                <form method="GET" action="{{ route('mentor.intern.index') }}"
                      data-live-filter data-suggest-url="{{ route('mentor.intern.search-suggest') }}">
                    <div class="search-box">
                        <div class="search-input-wrapper" style="flex: 1; min-width: 250px;" data-search-wrap>
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama anak magang...">
                        </div>

                        <select name="institution" class="filter-select" aria-label="Filter kampus">
                            <option value="">Semua Kampus</option>
                            @foreach ($institutions as $inst)
                                <option value="{{ $inst }}" {{ request('institution') === $inst ? 'selected' : '' }}>{{ $inst }}</option>
                            @endforeach
                        </select>

                        <select name="sort" class="filter-select" aria-label="Urutkan">
                            <option value="">Nama A-Z</option>
                            <option value="name_desc" {{ $selectedSort === 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                            <option value="start_desc" {{ $selectedSort === 'start_desc' ? 'selected' : '' }}>Tanggal Mulai Terbaru</option>
                            <option value="start_asc" {{ $selectedSort === 'start_asc' ? 'selected' : '' }}>Tanggal Mulai Terlama</option>
                        </select>

                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Cari
                        </button>

                        @if (request()->anyFilled(['search', 'institution', 'sort']))
                            <a href="{{ route('mentor.intern.index') }}" class="btn-clear">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ── TABEL ANAK MAGANG AKTIF ──────────────────────– --}}
        {{-- Workspace aktif: centang anak bimbingan → "Buat kelompok"; kolom Kelompok dari WS.api.groups.roster(). --}}
        @php($wsGroups = Route::has('workspace.groups.roster'))
        <div class="panel a2" @if ($wsGroups) x-data="wsInternGroups" @endif>
            <div class="panel-header">
                <i class="fas fa-users-cog text-blue-200"></i>
                <h2>Anak Magang Aktif</h2>
                <span style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;">
                    {{ $activeInterns->total() }} orang
                </span>
            </div>
            <div class="panel-body">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                @if ($wsGroups)
                                    <th class="w-10"><span class="sr-only">Pilih</span></th>
                                @endif
                                <th>Foto</th>
                                <th>Nama</th>
                                @if ($wsGroups)
                                    <th class="hidden sm:table-cell">Kelompok</th>
                                @endif
                                <th class="hidden sm:table-cell">Institusi</th>
                                <th>Logbook</th>
                                <th>Absensi</th>
                                <th>Mikroskill</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeInterns as $intern)
                                <tr>
                                    @if ($wsGroups)
                                        <td>
                                            <input type="checkbox" class="w-4 h-4 accent-indigo-600 align-middle"
                                                   :checked="selected.includes({{ $intern->id }})" :disabled="!selectable({{ $intern->id }})"
                                                   x-on:change="toggle({{ $intern->id }})" aria-label="Pilih {{ $intern->name }}">
                                        </td>
                                    @endif
                                    <td>
                                        @if ($intern->photo_path)
                                            <img src="{{ url('storage/' . $intern->photo_path) }}"
                                                alt="{{ $intern->name }}" class="avatar" loading="lazy" decoding="async">
                                        @else
                                            <div class="avatar" style="background: linear-gradient(135deg,#3b82f6,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700;">
                                                {{ strtoupper(substr($intern->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('mentor.intern.show', $intern) }}"
                                            style="color:#3b4fd8; font-weight:600; text-decoration:none; transition:all .2s;">
                                            {{ $intern->name }}
                                        </a>
                                        @if ($wsGroups)
                                            {{-- HP: kolom Kelompok disembunyikan, isinya tampil di bawah nama --}}
                                            <div class="ws-font sm:hidden mt-1.5">
                                                <template x-if="loaded && team({{ $intern->id }})">
                                                    <a :href="team({{ $intern->id }}).url" class="ws-group-chip" style="display:flex;width:fit-content;margin-bottom:6px;" title="Tim/Bagian">
                                                        <span class="ws-group-dot" :style="{ background: team({{ $intern->id }}).color }" aria-hidden="true"></span>
                                                        <span class="truncate" x-text="team({{ $intern->id }}).name"></span>
                                                    </a>
                                                </template>
                                                <template x-if="loaded && group({{ $intern->id }})">
                                                    <a :href="group({{ $intern->id }}).url" class="ws-group-chip">
                                                        <span class="ws-group-dot" :style="{ background: group({{ $intern->id }}).color }" aria-hidden="true"></span>
                                                        <span class="truncate" x-text="group({{ $intern->id }}).name"></span>
                                                    </a>
                                                </template>
                                                <template x-if="loaded && selectable({{ $intern->id }})">
                                                    <button type="button" class="ws-chip-person__action"
                                                            x-on:click="$dispatch('ws-group-assign', { intern: { id: {{ $intern->id }}, name: @js($intern->name) } })">
                                                        Masukkan ke kelompok
                                                    </button>
                                                </template>
                                            </div>
                                        @endif
                                    </td>
                                    @if ($wsGroups)
                                        <td class="ws-font hidden sm:table-cell">
                                            <span x-show="!loaded" class="text-xs text-slate-400">…</span>
                                            <template x-if="loaded && team({{ $intern->id }})">
                                                <a :href="team({{ $intern->id }}).url" class="ws-group-chip" style="display:flex;width:fit-content;margin-bottom:6px;" title="Tim/Bagian">
                                                    <span class="ws-group-dot" :style="{ background: team({{ $intern->id }}).color }" aria-hidden="true"></span>
                                                    <span class="truncate" x-text="team({{ $intern->id }}).name"></span>
                                                </a>
                                            </template>
                                            <template x-if="loaded && group({{ $intern->id }})">
                                                <a :href="group({{ $intern->id }}).url" class="ws-group-chip">
                                                    <span class="ws-group-dot" :style="{ background: group({{ $intern->id }}).color }" aria-hidden="true"></span>
                                                    <span class="truncate" x-text="group({{ $intern->id }}).name"></span>
                                                </a>
                                            </template>
                                            <template x-if="loaded && selectable({{ $intern->id }})">
                                                <button type="button" class="ws-chip-person__action"
                                                        x-on:click="$dispatch('ws-group-assign', { intern: { id: {{ $intern->id }}, name: @js($intern->name) } })">
                                                    Masukkan ke kelompok
                                                </button>
                                            </template>
                                            <span x-show="loaded && !team({{ $intern->id }}) && !group({{ $intern->id }}) && !selectable({{ $intern->id }})" class="text-xs text-slate-400">—</span>
                                        </td>
                                    @endif
                                    <td class="hidden sm:table-cell">
                                        <span style="color:#6b7280; font-size:12px;">{{ $intern->institution }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-count badge-logbook">
                                            <i class="fas fa-book"></i>
                                            {{ $intern->logbooks_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-count badge-attendance">
                                            <i class="fas fa-calendar-check"></i>
                                            {{ $intern->attendances_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-count badge-microskill">
                                            <i class="fas fa-star"></i>
                                            {{ $intern->micro_skills_count ?? 0 }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $wsGroups ? 7 : 5 }}">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p class="font-semibold text-sm">Belum ada anak magang aktif</p>
                                            <p class="text-xs">Data anak magang akan muncul di sini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($activeInterns->hasPages())
                    <div style="margin-top: 20px;">
                        {{ $activeInterns->links() }}
                    </div>
                @endif

                @if ($wsGroups)
                    <div class="ws-selection-bar ws-font" x-show="selected.length" x-cloak x-transition.opacity role="region" aria-label="Aksi anak magang terpilih">
                        <span class="text-sm font-semibold" x-text="selected.length + ' anak magang dipilih'"></span>
                        <button type="button" class="text-sm text-indigo-200 hover:text-white underline" x-on:click="selected = []">Batal</button>
                        <button type="button" class="ws-btn ws-btn--sm ml-auto" style="background:#fff;color:#312e81;" x-on:click="create()">
                            <i class="fas fa-users"></i> Buat kelompok
                        </button>
                    </div>
                @endif
            </div>
        </div>
        @if ($wsGroups)
            @include('workspace.partials.group-modals')
        @endif

        {{-- ── TABEL DATA ALUMNI ──────────────────────– --}}
        <div class="panel a2">
            <div class="panel-header" style="background:linear-gradient(100deg, #4b5563 0%, #6b7280 100%);">
                <i class="fas fa-user-graduate text-blue-100"></i>
                <h2>Data Alumni</h2>
                <span style="margin-left:auto;background:rgba(255,255,255,0.18);color:#fff;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;">
                    {{ $alumniInterns->total() }} orang
                </span>
            </div>
            <div class="panel-body">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th class="hidden sm:table-cell">Institusi</th>
                                <th>Logbook</th>
                                <th>Absensi</th>
                                <th>Mikroskill</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alumniInterns as $intern)
                                <tr>
                                    <td>
                                        @if ($intern->photo_path)
                                            <img src="{{ url('storage/' . $intern->photo_path) }}"
                                                alt="{{ $intern->name }}" class="avatar" style="border-color:#e2e8f0;" loading="lazy" decoding="async">
                                        @else
                                            <div class="avatar" style="background:#e2e8f0; display:flex; align-items:center; justify-content:center; color:#64748b; font-weight:700;">
                                                {{ strtoupper(substr($intern->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('mentor.intern.show', $intern) }}"
                                            style="color:#475569; font-weight:600; text-decoration:none; transition:all .2s;">
                                            {{ $intern->name }}
                                        </a>
                                    </td>
                                    <td class="hidden sm:table-cell">
                                        <span style="color:#6b7280; font-size:12px;">{{ $intern->institution }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-count badge-logbook">
                                            <i class="fas fa-book"></i>
                                            {{ $intern->logbooks_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-count badge-attendance">
                                            <i class="fas fa-calendar-check"></i>
                                            {{ $intern->attendances_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-count badge-microskill">
                                            <i class="fas fa-star"></i>
                                            {{ $intern->micro_skills_count ?? 0 }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p class="font-semibold text-sm">Belum ada alumni</p>
                                            <p class="text-xs">Anak magang yang sudah dilepas akan muncul di sini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($alumniInterns->hasPages())
                    <div style="margin-top: 20px;">
                        {{ $alumniInterns->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Skrip chart ditaruh di DALAM area data-ajax-content supaya ikut dieksekusi ulang
             tiap konten diganti lewat AJAX (lihat public/js/live-filter-ajax.js: execScripts). --}}
        @if ($compositionTotal > 0)
            <script>
                (function () {
                    const compCtx = document.getElementById('compositionChart').getContext('2d');
                    new Chart(compCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Aktif', 'Alumni'],
                            datasets: [{
                                data: [{{ $compositionActive }}, {{ $compositionAlumni }}],
                                backgroundColor: ['#10b981', '#64748b'],
                                borderWidth: 0,
                                hoverOffset: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                            const pct = total ? Math.round((ctx.parsed / total) * 100) : 0;
                                            return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                })();
            </script>
        @endif

    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/live-filter-ajax.js') }}?v={{ @filemtime(public_path('js/live-filter-ajax.js')) }}"></script>
@endpush
@endsection
