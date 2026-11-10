@extends('layouts.app')

@section('title', 'Tambah Lowongan Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/industri-lowongan-create.css') }}?v={{ @filemtime(public_path('css/pages/industri-lowongan-create.css')) }}">
@endpush

@section('content')

    <div class="page-wrapper py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ALERT --}}
            @if (session('success'))
                <div class="success-box mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-box mb-6">
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- HERO --}}
            <div class="hero-card p-8 lg:p-10 mb-8">

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                    <div class="flex items-start gap-5">

                        <div
                            class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-xl border border-white/10 flex items-center justify-center shadow-lg">
                            <i class="fas fa-briefcase text-white text-3xl"></i>
                        </div>

                        <div>

                            <div class="floating-badge mb-4">
                                <i class="fas fa-plus-circle"></i>
                                Lowongan Magang
                            </div>

                            <h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                                Tambah Lowongan Magang
                            </h1>

                            <p class="text-blue-100 mt-4 leading-relaxed max-w-2xl">
                                Buat lowongan magang baru untuk menjaring peserta terbaik
                                sesuai kebutuhan perusahaan Anda.
                            </p>

                        </div>

                    </div>

                    <div class="bg-white/10 border border-white/10 backdrop-blur-xl rounded-3xl p-6 min-w-[280px]">

                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center">
                                <i class="fas fa-lightbulb text-white"></i>
                            </div>

                            <div>
                                <h4 class="text-white font-bold">
                                    Tips Lowongan
                                </h4>

                                <p class="text-blue-100 text-sm">
                                    Buat deskripsi yang jelas.
                                </p>
                            </div>
                        </div>

                        <p class="text-sm text-blue-100 leading-relaxed">
                            Lowongan dengan informasi lengkap lebih menarik
                            dan meningkatkan jumlah pendaftar.
                        </p>

                    </div>

                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                {{-- FORM --}}
                <div class="lg:col-span-3">

                    <div class="main-card">

                        <form method="POST" action="{{ route('industri.lowongan.store') }}">

                            @csrf

                            {{-- INFORMASI LOWONGAN --}}
                            <div class="section-card">

                                <div class="flex items-start gap-4 mb-8">

                                    <div class="section-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>

                                    <div>
                                        <h2 class="section-title">
                                            Informasi Lowongan
                                        </h2>

                                        <p class="section-subtitle">
                                            Informasi dasar mengenai lowongan magang.
                                        </p>
                                    </div>

                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                    <div class="md:col-span-2">
                                        <label class="form-label">
                                            Judul Lowongan
                                        </label>

                                        <input type="text" name="judul_lowongan" value="{{ old('judul_lowongan') }}"
                                            placeholder="Contoh: Lowongan Magang UI/UX Designer" class="form-input">
                                    </div>

                                    <div>
                                        <label class="form-label">
                                            Posisi Magang
                                        </label>

                                        <input type="text" name="posisi_magang" value="{{ old('posisi_magang') }}"
                                            placeholder="Contoh: Frontend Developer" class="form-input">
                                    </div>

                                    <div>
                                        <label class="form-label">
                                            Divisi
                                        </label>

                                        <input
                                            type="text"
                                            name="divisi"
                                            class="form-input"
                                            placeholder="Masukkan nama divisi"
                                            value="{{ old('divisi') }}"
>
                                    </div>

                                </div>

                            </div>

                            {{-- DESKRIPSI --}}
                            <div class="bg-gray-50 section-card section-divider">

                                <div class="flex items-start gap-4 mb-8">

                                    <div class="section-icon">
                                        <i class="fas fa-align-left"></i>
                                    </div>

                                    <div>
                                        <h2 class="section-title">
                                            Detail Pekerjaan
                                        </h2>

                                        <p class="section-subtitle">
                                            Jelaskan tugas dan kebutuhan peserta magang.
                                        </p>
                                    </div>

                                </div>

                                <div class="space-y-5">

                                    <div>
                                        <label class="form-label">
                                            Deskripsi Pekerjaan
                                        </label>

                                        <textarea name="deskripsi_pekerjaan" placeholder="Jelaskan tugas dan tanggung jawab peserta magang..."
                                            class="form-input">{{ old('deskripsi_pekerjaan') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="form-label">
                                            Requirements
                                        </label>

                                        <textarea name="requirements" placeholder="Tuliskan syarat atau kualifikasi peserta..." class="form-input">{{ old('requirements') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="form-label">
                                            Fasilitas
                                        </label>

                                        <textarea name="fasilitas" placeholder="Contoh: Sertifikat, uang transport, makan siang, laptop kerja..."
                                            class="form-input">{{ old('fasilitas') }}</textarea>
                                    </div>

                                </div>

                            </div>

                            {{-- INFORMASI MAGANG --}}
                            <div class="section-card section-divider">

                                <div class="flex items-start gap-4 mb-8">

                                    <div class="section-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>

                                    <div>
                                        <h2 class="section-title">
                                            Informasi Magang
                                        </h2>

                                        <p class="section-subtitle">
                                            Tentukan kuota, status, dan fasilitas yang akan didapatkan peserta.
                                        </p>
                                    </div>

                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                                    <div>
                                        <label class="form-label">
                                            Kuota Peserta
                                        </label>

                                        <input type="number" name="kuota_peserta" value="{{ old('kuota_peserta') }}"
                                            placeholder="0" class="form-input">
                                    </div>

                                    {{-- <div>
                                        <label class="form-label">
                                            Durasi Magang
                                        </label>

                                        <input type="text" name="durasi_magang" value="{{ old('durasi_magang') }}"
                                            placeholder="Contoh: 3 Bulan" class="form-input">
                                    </div> --}}

                                    <div>
                                        <label class="form-label">
                                            Status
                                        </label>

                                        <select name="status" class="form-input">

                                            <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>
                                                Aktif
                                            </option>

                                            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>
                                                Nonaktif
                                            </option>

                                        </select>
                                    </div>

                                </div>

                            </div>

                            {{-- ACTION --}}
                            <div
                                class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex flex-col-reverse md:flex-row md:items-center md:justify-between gap-4">

                                <a href="{{ route('industri.lowongan.index') }}" class="back-btn">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali
                                </a>

                                <button type="submit" class="submit-btn">
                                    <i class="fas fa-save"></i>
                                    Simpan Lowongan
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

                {{-- SIDEBAR --}}
                <div class="space-y-6">

                    <div class="tips-card sticky top-6">

                        <h3 class="text-lg font-bold text-slate-800 mb-5">
                            Tips Membuat Lowongan
                        </h3>

                        <div>

                            <div class="tips-item">

                                <div class="tips-icon">
                                    <i class="fas fa-pen"></i>
                                </div>

                                <div>
                                    <h4 class="font-semibold text-slate-800 mb-1">
                                        Judul yang Jelas
                                    </h4>

                                    <p class="text-sm text-slate-500 leading-relaxed">
                                        Gunakan judul lowongan yang mudah dipahami peserta.
                                    </p>
                                </div>

                            </div>

                            <div class="tips-item">

                                <div class="tips-icon">
                                    <i class="fas fa-users"></i>
                                </div>

                                <div>
                                    <h4 class="font-semibold text-slate-800 mb-1">
                                        Jelaskan Kebutuhan
                                    </h4>

                                    <p class="text-sm text-slate-500 leading-relaxed">
                                        Tuliskan requirement dengan detail agar seleksi lebih tepat.
                                    </p>
                                </div>

                            </div>

                            <div class="tips-item">

                                <div class="tips-icon">
                                    <i class="fas fa-bolt"></i>
                                </div>

                                <div>
                                    <h4 class="font-semibold text-slate-800 mb-1">
                                        Aktifkan Lowongan
                                    </h4>

                                    <p class="text-sm text-slate-500 leading-relaxed">
                                        Pastikan status lowongan aktif agar dapat dilihat peserta.
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
