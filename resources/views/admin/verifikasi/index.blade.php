@extends('layouts.app')

@section('title', 'Verifikasi Lowongan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/admin-verifikasi-index.css') }}?v={{ @filemtime(public_path('css/pages/admin-verifikasi-index.css')) }}">
@endpush

@section('content')
<div class="page-bg py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ── HERO ── --}}
        <div class="hero-card">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-sm font-semibold text-blue-100 backdrop-blur">
                        <i class="fas fa-shield-check"></i>
                        Dashboard Verifikasi
                    </div>
                    <h1 class="mt-4 text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                        Verifikasi Lowongan Industri Mitra
                    </h1>
                    <p class="mt-2 text-blue-100 leading-relaxed text-sm">
                        Tinjau dan verifikasi lowongan magang yang diajukan oleh perusahaan,
                        instansi, dan organisasi mitra.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── STATS ── --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">Total Lowongan</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-slate-800">{{ $totalLowongan }}</h2>
                    </div>
                    <div class="stats-icon bg-blue-100 text-blue-700"><i class="fas fa-briefcase"></i></div>
                </div>
            </div>
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">Pending</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-yellow-500">{{ $totalPending }}</h2>
                    </div>
                    <div class="stats-icon bg-yellow-100 text-yellow-700"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">Disetujui</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-green-600">{{ $totalApprove }}</h2>
                    </div>
                    <div class="stats-icon bg-green-100 text-green-700"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">Ditolak</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-red-600">{{ $totalTolak }}</h2>
                    </div>
                    <div class="stats-icon bg-red-100 text-red-700"><i class="fas fa-times-circle"></i></div>
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
                    <div class="w-full sm:w-[160px]">
                        <select name="status" class="filter-select">
                            <option value="">Semua Status</option>
                            <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak"   {{ request('status') == 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" class="btn-action btn-primary min-w-[120px]">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('perusahaan') || request()->filled('divisi'))
                            <a href="{{ route('admin.verifikasi.index') }}"
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

                    $status = $lowongan->status_verifikasi ?? 'pending';

                    $badgeClass = match($status) {
                        'disetujui' => 'badge-green',
                        'ditolak'   => 'badge-red',
                        default     => 'badge-amber',
                    };

                    $badgeIcon = match($status) {
                        'disetujui' => 'fa-check-circle',
                        'ditolak'   => 'fa-times-circle',
                        default     => 'fa-clock',
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

                        <a href="{{ route('admin.verifikasi.show', $lowongan->id) }}" class="btn-view">
                            View
                        </a>
                    </div>

                </div>

            @empty
                <div class="col-span-full">
                    <div class="empty-card text-center">
                        <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center">
                            <i class="fas fa-briefcase text-4xl text-slate-300"></i>
                        </div>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-700">Belum Ada Lowongan Magang</h3>
                        <p class="mt-2 text-slate-500 max-w-lg mx-auto text-sm leading-relaxed">
                            Belum ditemukan lowongan magang yang sesuai dengan filter yang dipilih.
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

{{-- ── MODAL HAPUS ── --}}
<div x-data="{ showDeleteModal: false, deleteUrl: '', jobTitle: '' }"
     @open-delete-modal-lowongan.window="showDeleteModal = true; deleteUrl = $event.detail.url; jobTitle = $event.detail.title">

    <div x-show="showDeleteModal" style="display:none;"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm"
         x-transition.opacity>

        <div @click.away="showDeleteModal = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
             x-show="showDeleteModal" x-transition.scale.origin.bottom>

            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>

            <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>

            <p class="text-center text-gray-600 mb-6">
                Apakah Anda yakin ingin menghapus lowongan
                <strong x-text="jobTitle"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="flex justify-center gap-3">
                <button type="button" @click="showDeleteModal = false"
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Batal
                </button>
                <form :action="deleteUrl" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-trash"></i> Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection