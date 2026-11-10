@extends('layouts.app')

@section('title', 'Dashboard Industri')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/industri-dashboard.css') }}?v={{ @filemtime(public_path('css/pages/industri-dashboard.css')) }}">
@endpush

@section('content')

@php
    
    $pelamarMasuk   = 47;
    $magangBerjalan = 19;


    $industri = $industri ?? null;


    $profileIncomplete =
        !$industri ||
        empty($industri->deskripsi_industri) ||
        empty($industri->alamat_industri) ||
        empty($industri->logo_industri) ||
        empty($industri->nomor_telepon_industri) ||
        empty($industri->nib);
@endphp

<div class="dash-bg py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- HERO --}}
        <div class="hero-card p-7 anim-1">
            <div class="relative z-10 flex flex-col lg:flex-row gap-6 lg:items-center">

                {{-- Logo --}}
                <div class="company-logo">
                    @if ($industri && $industri->logo_industri)
                        <img src="{{ asset('storage/' . $industri->logo_industri) }}" alt="Logo Industri">
                    @else
                        <i class="fas fa-building text-3xl text-white"></i>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 text-center lg:text-left">

                    <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                        <h1 class="text-2xl lg:text-3xl font-extrabold text-white">
                            {{ $industri->nama_industri ?? 'Industri Belum Diatur' }}
                        </h1>
                    </div>

                    <p class="text-indigo-100 mt-2 font-medium">
                        {{ $industri->bidang_industri ?? 'Bidang industri belum diisi' }}
                    </p>

                    <div class="flex flex-wrap gap-5 mt-5 text-sm text-indigo-100 justify-center lg:justify-start">

                        <div>
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            {{ $industri->kota_kabupaten ?? '-' }}
                        </div>

                        <div>
                            <i class="fas fa-envelope mr-1"></i>
                            {{ $industri->email_industri ?? auth()->user()->email }}
                        </div>

                    </div>
                </div>

                {{-- Statistik --}}
                <div class="text-center lg:text-right">
                    <p class="text-indigo-200 text-xs uppercase tracking-[0.2em] font-bold mb-2">
                        Total Lowongan
                    </p>

                    <h2 class="text-5xl font-extrabold text-white mono">
                        {{ $totalLowongan ?? '0' }}
                    </h2>

                    <p class="text-indigo-200 text-sm mt-1">
                        Lowongan tersedia
                    </p>
                </div>

            </div>
        </div>

        {{-- ALERT --}}
        @if ($profileIncomplete)
            <div class="profile-alert anim-2">

                <div class="flex flex-col lg:flex-row gap-5 lg:items-center">

                    <div class="flex gap-4 flex-1">

                        <div class="w-14 h-14 rounded-2xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-orange-500 text-xl"></i>
                        </div>

                        <div class="flex-1">

                            <h3 class="font-bold text-orange-900 text-lg mb-1">
                                Profil Industri Belum Lengkap
                            </h3>

                            <p class="text-orange-800 text-sm leading-relaxed">
                                Lengkapi profil industri terlebih dahulu agar perusahaan Anda terlihat lebih profesional
                                dan dapat membuka lowongan magang dengan optimal.
                            </p>

                            <div class="mt-4">

                                <div class="flex justify-between text-xs font-semibold text-orange-700 mb-2">
                                    <span>Kelengkapan Profil</span>
                                    <span>{{ $progress }}%</span>
                                </div>

                                <div class="progress-track">
                                    <div class="progress-fill" style="width: {{ $progress }}%"></div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div>
                        <a href="{{ route('industri.profile.create') }}"
                           class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-5 py-3 rounded-2xl transition">
                            <i class="fas fa-user-edit"></i>
                            Lengkapi Profil
                        </a>
                    </div>

                </div>

            </div>
        @endif

        {{-- STATS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 anim-2">

            <div class="stats-card stats-blue">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-semibold text-blue-700">
                            Total Lowongan
                        </p>

                        <h2 class="text-3xl font-extrabold text-slate-800 mt-2 mono">
                            {{ $totalLowongan ?? '0' }}
                        </h2>

                        <p class="text-xs text-slate-500 mt-1">
                            Semua lowongan magang
                        </p>
                    </div>

                    <div class="stats-icon bg-blue-100 text-blue-600">
                        <i class="fas fa-briefcase"></i>
                    </div>

                </div>
            </div>

            <div class="stats-card stats-green">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-semibold text-green-700">
                            Lowongan Terverifikasi
                        </p>

                        <h2 class="text-3xl font-extrabold text-slate-800 mt-2 mono">
                            {{ $totalLowonganVerifikasi ?? '0' }}
                        </h2>

                        <p class="text-xs text-slate-500 mt-1">
                            Semua lowongan yang sudah disetujui admin
                        </p>
                    </div>

                    <div class="stats-icon bg-green-100 text-green-600">
                        <i class="fas fa-bolt"></i>
                    </div>

                </div>
            </div>

            <div class="stats-card stats-orange">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-semibold text-orange-700">
                            Pengajuan Masuk
                        </p>

                        <h2 class="text-3xl font-extrabold text-slate-800 mt-2 mono">
                            {{ $totalPengajuan ?? '0' }}
                        </h2>

                        <p class="text-xs text-slate-500 mt-1">
                            Total pengajuan
                        </p>
                    </div>

                    <div class="stats-icon bg-orange-100 text-orange-600">
                        <i class="fas fa-users"></i>
                    </div>

                </div>
            </div>

            {{-- <div class="stats-card stats-red">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-semibold text-red-700">
                            Magang Berjalan
                        </p>

                        <h2 class="text-3xl font-extrabold text-slate-800 mt-2 mono">
                            {{ $magangBerjalan ?? '0' }}
                        </h2>

                        <p class="text-xs text-slate-500 mt-1">
                            Peserta aktif
                        </p>
                    </div>

                    <div class="stats-icon bg-red-100 text-red-600">
                        <i class="fas fa-user-graduate"></i>
                    </div>

                </div>
            </div> --}}

        </div>

        {{-- MAIN --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- QUICK ACTION --}}
                <div class="panel anim-3">

                    <p class="section-label">
                        Aksi Cepat
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <a href="{{ route('industri.lowongan.create') }}" class="quick-action">
                            <div class="quick-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-800">
                                    Buat Lowongan
                                </h4>

                                <p class="text-sm text-slate-500">
                                    Tambahkan lowongan magang baru
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('industri.lowongan.index') }}" class="quick-action">
                            <div class="quick-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-800">
                                    Kelola Lowongan
                                </h4>

                                <p class="text-sm text-slate-500">
                                    Lihat semua lowongan magang
                                </p>
                            </div>
                        </a>

                        <a href="#" class="quick-action">
                            <div class="quick-icon">
                                <i class="fas fa-user-check"></i>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-800">
                                    Data Pelamar
                                </h4>

                                <p class="text-sm text-slate-500">
                                    Kelola pendaftar magang
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('industri.profile.create') }}" class="quick-action">
                            <div class="quick-icon">
                                <i class="fas fa-building"></i>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-800">
                                    Edit Profil
                                </h4>

                                <p class="text-sm text-slate-500">
                                    Perbarui informasi industri
                                </p>
                            </div>
                        </a>

                    </div>

                </div>

                
            </div>

            {{-- RIGHT --}}
            <div class="space-y-6">

                {{-- PROFILE --}}
                <div class="panel anim-3">

                    <p class="section-label">
                        Informasi Industri
                    </p>

                    <div class="space-y-4">

                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-1">
                                Nama Industri
                            </p>

                            <p class="text-sm font-semibold text-slate-700">
                                {{ $industri->nama_industri ?? '-' }}
                            </p>
                        </div>

                        <div class="divider"></div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-1">
                                Bidang Industri
                            </p>

                            <p class="text-sm font-semibold text-slate-700">
                                {{ $industri->bidang_industri ?? '-' }}
                            </p>
                        </div>

                        <div class="divider"></div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-1">
                                Kota / Kabupaten
                            </p>

                            <p class="text-sm font-semibold text-slate-700">
                                {{ $industri->kota_kabupaten ?? '-' }}
                            </p>
                        </div>

                        <div class="divider"></div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-1">
                                Email
                            </p>

                            <p class="text-sm font-semibold text-slate-700 break-all">
                                {{ $industri->email_industri ?? '-' }}
                            </p>
                        </div>

                        <div class="divider"></div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-1">
                                Nomor Telepon
                            </p>

                            <p class="text-sm font-semibold text-slate-700">
                                {{ $industri->nomor_telepon_industri ?? '-' }}
                            </p>
                        </div>

                        <div class="divider"></div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-1">
                                NIB
                            </p>

                            <p class="text-sm font-semibold text-slate-700">
                                {{ $industri->nib ?? 'Belum diisi' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-1">
                                NIB
                            </p>

                            <p class="text-sm font-semibold text-slate-700">
                                {{ $industri->nib ?? 'Belum diisi' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

@endsection