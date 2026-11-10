@extends('layouts.app')

@section('title', 'Lowongan Magang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/institusi-lowongan-index.css') }}?v={{ @filemtime(public_path('css/pages/institusi-lowongan-index.css')) }}">
@endpush

@section('content')
<div class="page-bg py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ── HERO ── --}}
        <div class="hero-card">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-sm font-semibold text-blue-100 backdrop-blur">
                        <i class="fas fa-briefcase"></i>
                        Lowongan Magang
                    </div>
                    <h1 class="mt-4 text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                        Ajukan Peserta Magang ke Lowongan yang Tersedia
                    </h1>
                    <p class="mt-2 text-blue-100 leading-relaxed text-sm">
                        Pilih lowongan magang dari perusahaan mitra dan ajukan peserta magang
                        secara mudah, terstruktur, dan transparan melalui sistem.
                    </p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-2xl p-5 text-center flex-shrink-0">
                    <p class="text-sm text-blue-100">Total Lowongan</p>
                    <h2 class="mt-1 text-4xl font-extrabold text-white">{{ $totalLowongan }}</h2>
                </div>
            </div>
        </div>

        {{-- ── FILTER ── --}}
        <div class="filter-card">
            <form method="GET">
                <div class="flex flex-wrap xl:flex-nowrap items-center gap-3">
                    <div class="w-full xl:flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari lowongan atau perusahaan..."
                            class="filter-input">
                    </div>
                    <div class="w-full sm:w-[200px]">
                        <select name="perusahaan" class="filter-select">
                            <option value="">Semua Perusahaan</option>
                            @foreach($perusahaans as $perusahaan)
                                <option value="{{ $perusahaan->id }}"
                                    {{ request('perusahaan') == $perusahaan->id ? 'selected' : '' }}>
                                    {{ $perusahaan->nama_industri }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-[170px]">
                        <select name="divisi" class="filter-select">
                            <option value="">Semua Divisi</option>
                            @foreach($divisis as $divisi)
                                <option value="{{ $divisi }}" {{ request('divisi') == $divisi ? 'selected' : '' }}>
                                    {{ $divisi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" class="btn-action btn-primary min-w-[120px]">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('perusahaan') || request()->filled('divisi'))
                            <a href="{{ route('institusi.lowongan.index') }}"
                               class="btn-action bg-slate-100 hover:bg-slate-200 text-slate-700 min-w-[100px]">
                                <i class="fas fa-rotate-left"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ── CARD LIST (3 kolom) ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">

            @forelse($lowongans as $lowongan)
                @php
                    $logo = optional($lowongan->industri)->logo_industri
                        ? asset('storage/' . $lowongan->industri->logo_industri)
                        : 'https://ui-avatars.com/api/?name=' . urlencode(optional($lowongan->industri)->nama_industri ?? 'I') . '&background=4f46e5&color=fff&size=64';

                    $status = $lowongan->status ?? 'dibuka';

                    $badgeClass = match($status) {
                        'dibuka'  => 'badge-green',
                        'ditutup' => 'badge-red',
                        default   => 'badge-amber',
                    };

                    $badgeIcon = match($status) {
                        'dibuka'  => 'fa-check-circle',
                        'ditutup' => 'fa-times-circle',
                        default   => 'fa-clock',
                    };
                @endphp

                <div class="lowongan-card">

                    {{-- Status badge pojok kanan atas --}}
                    <div style="display: flex; justify-content: flex-end;">
                        <span class="status-badge {{ $badgeClass }}">
                            <i class="fas {{ $badgeIcon }}"></i>
                            {{ ucfirst($status) }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h2 class="card-title">
                        {{ $lowongan->judul_lowongan ?? '-' }}
                    </h2>

                    {{-- Meta --}}
                    <div class="card-meta">
                        @if($lowongan->posisi_magang)
                            <span class="meta-chip">
                                <i class="fas fa-user-tie"></i>
                                {{ Str::limit($lowongan->posisi_magang, 20) }}
                            </span>
                        @endif
                        @if($lowongan->divisi)
                            <span class="meta-chip">
                                <i class="fas fa-layer-group"></i>
                                {{ Str::limit($lowongan->divisi, 20) }}
                            </span>
                        @endif
                        <span class="meta-chip">
                            <i class="fas fa-users"></i>
                            {{ $lowongan->kuota_peserta ?? 0 }} Peserta
                        </span>
                    </div>

                    {{-- Footer --}}
                    <div class="card-footer">
                        <div class="card-company">
                            <div class="company-logo">
                                <img src="{{ $logo }}" alt="Logo">
                            </div>
                            <div>
                                <p class="company-name">
                                    {{ optional($lowongan->industri)->nama_industri ?? '-' }}
                                </p>
                                <p class="company-sub">
                                    {{ optional($lowongan->created_at)->translatedFormat('d M Y') ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="{{ route('institusi.lowongan.show', $lowongan->id) }}" class="btn-view">
                                Detail
                            </a>
                            {{-- @if($lowongan->status === 'dibuka')
                                <a href="{{ route('institusi.pengajuan.create', $lowongan->id) }}" class="btn-ajukan">
                                    <i class="fas fa-paper-plane"></i> Ajukan
                                </a>
                            @endif --}}
                        </div>
                    </div>

                </div>

            @empty
                <div class="col-span-full">
                    <div class="empty-card text-center">
                        <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center">
                            <i class="fas fa-briefcase text-4xl text-slate-300"></i>
                        </div>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-700">Tidak Ada Lowongan</h3>
                        <p class="mt-2 text-slate-500 max-w-lg mx-auto text-sm leading-relaxed">
                            Saat ini belum ada data lowongan yang sesuai dengan filter
                            atau belum ada pengajuan lowongan magang.
                        </p>
                    </div>
                </div>
            @endforelse

        </div>

        {{-- ── PAGINATION ── --}}
        @if($lowongans->hasPages())
            <div class="pt-2">{{ $lowongans->links() }}</div>
        @endif

    </div>
</div>
@endsection