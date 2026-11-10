@extends('layouts.app')

@section('title', 'Verifikasi Lowongan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/admin-lowongan-index.css') }}?v={{ @filemtime(public_path('css/pages/admin-lowongan-index.css')) }}">
@endpush

@section('content')
<div class="page-bg py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-7">

        {{-- ── HERO ── --}}
        <div class="hero-card">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-sm font-semibold text-blue-100 backdrop-blur">
                        <i class="fas fa-shield-check"></i>
                        Dashboard Verifikasi
                    </div>
                    <h1 class="mt-5 text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                        Manajemen Lowongan Magang
                    </h1>
                    <p class="mt-3 text-blue-100 leading-relaxed">
                        Kelola dan publikasikan berbagai lowongan magang dari setiap unit kerja,
                        divisi, atau tim. Tambahkan peluang magang baru, perbarui informasi
                        lowongan, serta pantau status publikasi secara terpusat.
                    </p>
                </div>
                <div class="flex-shrink-0 flex flex-col items-center gap-4">
                    <div class="text-center">
                        <p class="text-sm font-semibold text-white/70">Total Lowongan</p>
                        <h2 class="mt-1 text-4xl font-extrabold text-white">{{ $totalLowongan }}</h2>
                    </div>
                    <a href="{{ route('admin.lowongan.create') }}"
                       class="inline-flex items-center justify-center bg-white text-blue-700 font-semibold px-6 py-3 rounded-2xl shadow-lg hover:bg-blue-50 transition gap-2">
                        <i class="fas fa-plus"></i>
                        Tambah Lowongan
                    </a>
                </div>
            </div>
        </div>

        {{-- ── FILTER ── --}}
        <div class="filter-card">
            <form method="GET">
                <div class="flex flex-wrap xl:flex-nowrap items-center gap-4">
                    <div class="w-full xl:flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari lowongan atau perusahaan..."
                            class="filter-input">
                    </div>
                    <div class="w-full sm:w-[200px]">
                        <select name="divisi" class="filter-select">
                            <option value="">Semua Divisi</option>
                            @foreach($divisis as $divisi)
                                <option value="{{ $divisi }}" {{ request('divisi') == $divisi ? 'selected' : '' }}>
                                    {{ $divisi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="submit" class="btn-action btn-primary min-w-[140px]">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('perusahaan') || request()->filled('divisi'))
                            <a href="{{ route('admin.lowongan.index') }}"
                               class="btn-action bg-slate-100 hover:bg-slate-200 text-slate-700 min-w-[120px]">
                                <i class="fas fa-rotate-left"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ── TABLE ── --}}
        <div class="tbl-wrapper">
            <div class="tbl-scroll">
                <table>
                    <thead>
                        <tr>
                            <th style="width:3rem; text-align:center;">No</th>
                            <th class="text-center">Lowongan</th>
                            <th class="text-center">Posisi</th>
                            <th class="text-center">Divisi</th>
                            <th style="text-align:center;">Kuota</th>
                            <th class="text-center">Tanggal</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowongans as $i => $lowongan)
                            @php
                                $logo = optional($lowongan->industri)->logo_industri
                                    ? asset('storage/' . $lowongan->industri->logo_industri)
                                    : 'https://ui-avatars.com/api/?name=' . urlencode(optional($lowongan->industri)->nama_industri ?? 'Industri') . '&background=4f46e5&color=fff';
                            @endphp
                            <tr>
                                <td style="text-align:center; color:#94a3b8; font-size:.8rem; font-weight:700;">
                                    {{ $lowongans->firstItem() + $i }}
                                </td>

                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="tbl-logo">
                                            <img src="{{ $logo }}" alt="Logo">
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 leading-snug"
                                               style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                {{ $lowongan->judul_lowongan ?? '-' }}
                                            </p>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                {{ optional($lowongan->industri)->nama_industri ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td style="font-weight:600; color:#475569;">
                                    {{ $lowongan->posisi_magang ?? '-' }}
                                </td>

                                <td style="font-weight:600; color:#475569;">
                                    {{ $lowongan->divisi ?? '-' }}
                                </td>

                                <td style="text-align:center;">
                                    <span style="display:inline-flex; align-items:center; gap:.3rem;
                                                 background:#fef9c3; color:#a16207;
                                                 padding:.25rem .75rem; border-radius:999px;
                                                 font-size:.75rem; font-weight:800;">
                                        <i class="fas fa-users" style="font-size:.65rem;"></i>
                                        {{ $lowongan->kuota_peserta ?? 0 }}
                                    </span>
                                </td>

                                <td style="color:#64748b; font-size:.82rem; white-space:nowrap;">
                                    {{ optional($lowongan->created_at)->translatedFormat('d M Y') ?? '-' }}
                                </td>

                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.lowongan.show', $lowongan->id) }}"
                                           class="btn-action btn-soft">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.lowongan.edit', $lowongan->id) }}"
                                           class="btn-action btn-soft">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                            class="btn-action btn-danger"
                                            onclick="window.dispatchEvent(new CustomEvent('open-delete-modal-lowongan', {
                                                detail: {
                                                    url: '{{ route('admin.lowongan.destroy', $lowongan->id) }}',
                                                    title: '{{ addslashes($lowongan->judul_lowongan) }}'
                                                }
                                            }))">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center">
                                            <i class="fas fa-briefcase text-4xl text-slate-300"></i>
                                        </div>
                                        <h3 class="mt-6 text-xl font-extrabold text-slate-700">Belum Ada Lowongan Magang</h3>
                                        <p class="mt-2 text-slate-500 max-w-md mx-auto">
                                            Belum ditemukan lowongan magang yang sesuai dengan filter yang dipilih.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
             class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all"
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