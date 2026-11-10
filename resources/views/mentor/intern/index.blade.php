@extends('layouts.app')

@section('title', 'Anak Magang Bimbingan - Sistem Magang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/mentor-intern-index.css') }}?v={{ @filemtime(public_path('css/pages/mentor-intern-index.css')) }}">
@endpush

@section('content')
<x-workspace.groups-js />
<x-workspace.page-css />
<div class="dash-bg py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ── HERO HEADER ──────────────────────────────── --}}
        <div class="hero-strip shadow-xl a1">
            <div class="relative z-10 px-6 py-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Anak Magang Bimbingan</h1>
                    <p class="text-blue-200 text-sm">Kelola dan pantau perkembangan anak magang Anda</p>
                </div>
            </div>
        </div>

        {{-- ── SEARCH & FILTER PANEL ────────────────────── --}}
        <div class="panel panel-filter a2">
            <div class="panel-header">
                <i class="fas fa-search text-blue-200"></i>
                <h2>Cari Anak Magang</h2>
            </div>
            <div class="panel-body">
                <form method="GET" action="{{ route('mentor.intern.index') }}">
                    <div class="search-box">
                        <div class="search-input-wrapper" style="flex: 1; min-width: 250px;">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama anak magang...">
                        </div>

                        <select name="status" class="filter-select" aria-label="Filter status">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="alumni" {{ request('status') == 'alumni' ? 'selected' : '' }}>Alumni</option>
                        </select>

                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Cari
                        </button>

                        @if (request()->filled('search') || request()->filled('status'))
                            <a href="{{ route('mentor.intern.index') }}" class="btn-clear">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ── INTERNS TABLE PANEL ──────────────────────– --}}
        {{-- Workspace aktif: centang anak bimbingan → "Buat kelompok"; kolom Kelompok dari WS.api.groups.roster(). --}}
        @php($wsGroups = Route::has('workspace.groups.roster'))
        <div class="panel a2" @if ($wsGroups) x-data="wsInternGroups" @endif>
            <div class="panel-header">
                <i class="fas fa-users-cog text-blue-200"></i>
                <h2>Daftar Anak Magang</h2>
                <span style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;">
                    {{ $interns->total() ?? count($interns) }} orang
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
                                <th>Status</th>
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
                            @forelse($interns as $intern)
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
                                    <td>
                                        @if ($intern->is_active)
                                            <span class="badge badge-active">
                                                <i class="fas fa-check-circle"></i> Aktif
                                            </span>
                                        @else
                                            <span class="badge badge-alumni">
                                                <i class="fas fa-user-graduate"></i> Alumni
                                            </span>
                                        @endif
                                    </td>
                                    @if ($wsGroups)
                                        <td class="ws-font hidden sm:table-cell">
                                            <span x-show="!loaded" class="text-xs text-slate-400">…</span>
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
                                            <span x-show="loaded && !group({{ $intern->id }}) && !selectable({{ $intern->id }})" class="text-xs text-slate-400">—</span>
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
                                    <td colspan="{{ $wsGroups ? 9 : 7 }}">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p class="font-semibold text-sm">Belum ada anak magang</p>
                                            <p class="text-xs">Data anak magang akan muncul di sini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($interns, 'links'))
                    <div style="margin-top: 20px;">
                        {{ $interns->links() }}
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

    </div>
</div>
@endsection
