@extends('layouts.app')

@section('title', 'Detail Verifikasi Lowongan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-lowongan-show.css') }}?v={{ @filemtime(public_path('css/pages/institusi-lowongan-show.css')) }}">
@endpush

@section('content')
    @php
        $logo = optional($lowongan->industri)->logo_industri
            ? asset('storage/' . $lowongan->industri->logo_industri)
            : 'https://ui-avatars.com/api/?name=' .
                urlencode(optional($lowongan->industri)->nama_industri ?? 'Industri') .
                '&background=4f46e5&color=fff';

        $status = $lowongan->status ?? 'dibuka';

        $badgeClass = match ($status) {
            'dibuka' => 'bg-green-100 text-green-700',
            'ditutup' => 'bg-red-100 text-red-700',
            default => 'bg-yellow-100 text-yellow-700',
        };

        $badgeIcon = match ($status) {
            'dibuka' => 'fa-check-circle',
            'ditutup' => 'fa-times-circle',
            default => 'fa-clock',
        };
    @endphp

    <div class="page-bg py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- HERO --}}
            <div class="hero-card">
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div>
                        <div class="hero-tag">
                            <i class="fas fa-briefcase"></i> Detail Lowongan
                        </div>
                        <h1 class="mt-1 text-2xl lg:text-3xl font-bold text-white leading-snug">
                            {{ $lowongan->judul_lowongan ?? '-' }}
                        </h1>
                        <p class="mt-2 text-blue-200 text-sm">
                            {{ optional($lowongan->industri)->nama_industri ?? '-' }}
                        </p>
                    </div>
                    <span class="status-badge {{ $badgeClass }} mt-1">
                        <i class="fas {{ $badgeIcon }}"></i>
                        {{ ucfirst($status) }}
                    </span>
                </div>
            </div>

            {{-- CONTENT CARD --}}
            <div class="content-card">

                {{-- HEADER --}}
                <div class="p-6 border-b border-slate-100 flex flex-col lg:flex-row items-start gap-5">
                    <div class="logo-wrapper">
                        <img src="{{ $logo }}" alt="Logo Industri">
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-slate-800">{{ $lowongan->judul_lowongan ?? '-' }}</h2>
                        <p class="mt-1 text-slate-500 text-sm">
                            <i class="fas fa-building mr-1"></i>{{ optional($lowongan->industri)->nama_industri ?? '-' }}
                        </p>
                    </div>
                    @if ($lowongan->status === 'dibuka')
                        <a href="{{ route('institusi.pengajuan.create', $lowongan->id) }}"
                            class="btn-action bg-green-100 hover:bg-green-200 text-green-700 min-w-[180px]">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Ajukan Permohonan Magang
                        </a>
                    @endif
                </div>

                {{-- BODY --}}
                <div class="p-6 space-y-8">

                    {{-- Informasi Lowongan --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="section-icon bg-indigo-50 text-indigo-600"><i class="fas fa-briefcase"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Informasi Lowongan</p>
                                <p class="text-xs text-slate-400">Detail informasi lowongan magang.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-3">
                            <div class="detail-box">
                                <p class="detail-label">Judul Lowongan</p>
                                <p class="detail-value">{{ $lowongan->judul_lowongan ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Posisi Magang</p>
                                <p class="detail-value">{{ $lowongan->posisi_magang ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Divisi</p>
                                <p class="detail-value">{{ $lowongan->divisi ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Fasilitas</p>
                                <p class="detail-value whitespace-pre-line">{{ $lowongan->fasilitas ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Kuota Peserta</p>
                                <p class="detail-value">{{ $lowongan->kuota_peserta ?? 0 }} Peserta</p>
                            </div>
                            {{-- <div class="detail-box"><p class="detail-label">Durasi Magang</p><p class="detail-value">{{ $lowongan->durasi_magang ?? '-' }}</p></div> --}}
                            <div class="detail-box">
                                <p class="detail-label">Status Lowongan</p>
                                <p class="detail-value">{{ ucfirst($lowongan->status ?? '-') }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Status Verifikasi</p>
                                <p class="detail-value">{{ ucfirst($lowongan->status_verifikasi ?? '-') }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Tanggal Dibuat</p>
                                <p class="detail-value">
                                    {{ optional($lowongan->created_at)->translatedFormat('d F Y, H:i') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Fasilitas --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="section-icon bg-emerald-50 text-emerald-600"><i class="fas fa-gift"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Fasilitas</p>
                                <p class="text-xs text-slate-400">Benefit yang akan didapatkan peserta magang.</p>
                            </div>
                        </div>
                        <div class="description-box">
                            <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                                {{ $lowongan->fasilitas ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Deskripsi Pekerjaan --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="section-icon bg-blue-50 text-blue-600"><i class="fas fa-file-alt"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Deskripsi Pekerjaan</p>
                                <p class="text-xs text-slate-400">Penjelasan pekerjaan dan aktivitas magang.</p>
                            </div>
                        </div>
                        <div class="description-box">
                            <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                                {{ $lowongan->deskripsi_pekerjaan ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Requirements --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="section-icon bg-amber-50 text-amber-600"><i class="fas fa-list-check"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Requirements / Persyaratan</p>
                                <p class="text-xs text-slate-400">Persyaratan peserta magang.</p>
                            </div>
                        </div>
                        <div class="description-box">
                            <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">{{ $lowongan->requirements ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Informasi Industri --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="section-icon bg-emerald-50 text-emerald-600"><i class="fas fa-building"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Informasi Industri</p>
                                <p class="text-xs text-slate-400">Informasi perusahaan pengaju lowongan.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-3">
                            <div class="detail-box">
                                <p class="detail-label">Nama Industri</p>
                                <p class="detail-value">{{ optional($lowongan->industri)->nama_industri ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Bidang Industri</p>
                                <p class="detail-value">{{ optional($lowongan->industri)->bidang_industri ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Kota / Kabupaten</p>
                                <p class="detail-value">{{ optional($lowongan->industri)->kota_kabupaten ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Email Industri</p>
                                <p class="detail-value break-all">{{ optional($lowongan->industri)->email_industri ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">Nomor Telepon</p>
                                <p class="detail-value">{{ optional($lowongan->industri)->nomor_telepon_industri ?? '-' }}</p>
                            </div>
                            <div class="detail-box">
                                <p class="detail-label">NIB</p>
                                <p class="detail-value">{{ optional($lowongan->industri)->nib ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Alamat Industri --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="section-icon bg-rose-50 text-rose-600"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Alamat Industri</p>
                                <p class="text-xs text-slate-400">Lokasi lengkap perusahaan.</p>
                            </div>
                        </div>
                        <div class="description-box">
                            <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">{{ optional($lowongan->industri)->alamat_industri ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Deskripsi Industri --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="section-icon bg-violet-50 text-violet-600"><i class="fas fa-circle-info"></i></div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Deskripsi Industri</p>
                                <p class="text-xs text-slate-400">Gambaran umum perusahaan.</p>
                            </div>
                        </div>
                        <div class="description-box">
                            <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">{{ optional($lowongan->industri)->deskripsi_industri ?? '-' }}</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection
