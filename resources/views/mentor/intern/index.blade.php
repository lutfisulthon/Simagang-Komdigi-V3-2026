@extends('layouts.app')

@section('title', 'Anak Magang Bimbingan - Sistem Magang')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

    *, body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .mono { font-family: 'DM Mono', monospace; }

    /* ── Page background ── */
    .dash-bg {
        min-height: 100vh;
        background: #f1f5ff;
    }

    /* ── Header strip ── */
    .hero-strip {
        background: linear-gradient(110deg, #0f2878 0%, #2d3ecb 55%, #4f46e5 100%);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .hero-strip::before {
        content: '';
        position: absolute;
        top: -80px; right: -60px;
        width: 260px; height: 260px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-strip::after {
        content: '';
        position: absolute;
        bottom: -100px; left: 25%;
        width: 320px; height: 320px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
        pointer-events: none;
    }

    /* ── Panel ── */
    .panel {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(20,40,120,0.06), 0 4px 18px rgba(20,40,120,0.06);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .panel-header {
        background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 100%);
        padding: 16px 22px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .panel-header h2 {
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.01em;
        margin: 0;
    }
    .panel-body { padding: 20px 22px; }

    /* ── Search and filter ── */
    .search-box {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .search-input-wrapper {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .search-input-wrapper input {
        width: 100%;
        padding: 11px 14px 11px 38px;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        font-size: 13px;
        font-family: inherit;
        transition: all .2s ease;
    }
    .search-input-wrapper input:focus {
        outline: none;
        border-color: #3b4fd8;
        box-shadow: 0 0 0 3px rgba(59,79,216,0.1);
    }
    .search-input-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 14px;
    }

    .filter-select {
        min-width: 150px;
        padding: 11px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        font-size: 13px;
        font-family: inherit;
        background: #fff;
        cursor: pointer;
        transition: all .2s ease;
    }
    .filter-select:focus {
        outline: none;
        border-color: #3b4fd8;
        box-shadow: 0 0 0 3px rgba(59,79,216,0.1);
    }

    .btn-search {
        padding: 11px 20px;
        background: linear-gradient(135deg, #3b4fd8, #3b82f6);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59,79,216,0.3);
    }

    .btn-clear {
        padding: 11px 16px;
        background: #e5e7eb;
        color: #374151;
        border: none;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-clear:hover {
        background: #d1d5db;
    }

    /* ── Table ── */
    .table-wrapper {
        overflow-x: auto;
        border-radius: 14px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    thead {
        background: #f0f4ff;
    }
    thead th {
        padding: 14px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #3b4fd8;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 2px solid #e5e7eb;
    }
    tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #e8eeff;
        font-size: 13px;
        color: #374151;
    }
    tbody tr {
        transition: background .15s ease;
    }
    tbody tr:hover {
        background: #f9fafb;
    }

    /* ── Avatar ── */
    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e5e7eb;
    }

    /* ── Status badge ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-active {
        background: #dcfce7;
        color: #15803d;
    }
    .badge-alumni {
        background: #f3f4f6;
        color: #6b7280;
    }
    .badge-count {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        font-family: 'DM Mono', monospace;
    }
    .badge-logbook {
        background: #f3e8ff;
        color: #7e22ce;
    }
    .badge-attendance {
        background: #dcfce7;
        color: #15803d;
    }
    .badge-microskill {
        background: #dbeafe;
        color: #1e40af;
    }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #9ca3af;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    .empty-state p {
        font-size: 14px;
        margin: 4px 0;
    }

    /* ── Quick action cards ── */
    .action-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(20,40,120,0.06), 0 4px 18px rgba(20,40,120,0.06);
        text-decoration: none;
        color: inherit;
        transition: all .2s ease;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 28px rgba(20,40,120,0.12);
    }
    .action-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
    }
    .action-card.attendance .action-card-icon {
        background: linear-gradient(135deg, #3b82f6, #1e40af);
    }
    .action-card.logbook .action-card-icon {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }
    .action-card.report .action-card-icon {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    .action-card h3 {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }
    .action-card p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    /* ── Fade-in animations ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .a1 { animation: fadeUp .5s ease both; }
    .a2 { animation: fadeUp .5s .08s ease both; }
</style>
@endpush

@section('content')
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
                                                alt="{{ $intern->name }}" class="avatar">
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
