@extends('layouts.app')

@section('title', isset($industri) && $industri ? 'Edit Profil Industri' : 'Lengkapi Profil Industri')

@php
    $isEdit = isset($industri) && $industri;
@endphp

@push('styles')
<style>

    *{
        font-family:'Plus Jakarta Sans',sans-serif;
    }

    body{
        background:
            radial-gradient(circle at top right, rgba(99,102,241,.10), transparent 25%),
            linear-gradient(135deg,#eff6ff 0%,#f8fafc 45%,#eef2ff 100%);
    }

    .page-wrapper{
        min-height:100vh;
    }

    .hero-card{
        position:relative;
        overflow:hidden;
        border-radius:32px;
        background:linear-gradient(135deg,#1e3a8a 0%,#4338ca 55%,#6366f1 100%);
        box-shadow:0 20px 50px rgba(30,58,138,.18);
    }

    .hero-card::before{
        content:'';
        position:absolute;
        width:300px;
        height:300px;
        border-radius:999px;
        background:rgba(255,255,255,.06);
        top:-120px;
        right:-80px;
    }

    .hero-card::after{
        content:'';
        position:absolute;
        width:340px;
        height:340px;
        border-radius:999px;
        background:rgba(255,255,255,.04);
        bottom:-180px;
        left:-100px;
    }

    .main-card{
        background:rgba(255,255,255,.92);
        backdrop-filter:blur(18px);
        border-radius:32px;
        border:1px solid rgba(255,255,255,.7);
        box-shadow:
            0 10px 40px rgba(15,23,42,.06),
            0 2px 8px rgba(15,23,42,.04);
        overflow:hidden;
    }

    .section-card{
        padding:2rem;
    }

    .section-divider{
        border-top:1px solid #eef2ff;
    }

    .section-icon{
        width:58px;
        height:58px;
        border-radius:20px;
        background:linear-gradient(135deg,#dbeafe,#e0e7ff);
        display:flex;
        align-items:center;
        justify-content:center;
        color:#4338ca;
        font-size:22px;
        flex-shrink:0;
    }

    .section-title{
        font-size:1.2rem;
        font-weight:800;
        color:#0f172a;
    }

    .section-subtitle{
        font-size:.92rem;
        color:#64748b;
        margin-top:4px;
        line-height:1.6;
    }

    .form-label{
        display:block;
        font-size:.92rem;
        font-weight:700;
        color:#334155;
        margin-bottom:.6rem;
    }

    .form-input{
        width:100%;
        border-radius:18px;
        border:1px solid #dbe4f0;
        background:#fff;
        padding:0.95rem 1rem;
        transition:all .2s ease;
        font-size:15px;
        color:#0f172a;
        box-shadow:0 1px 2px rgba(15,23,42,.03);
    }

    .form-input:focus{
        outline:none;
        border-color:#6366f1;
        box-shadow:
            0 0 0 4px rgba(99,102,241,.10),
            0 4px 20px rgba(99,102,241,.08);
    }

    textarea.form-input{
        min-height:120px;
        resize:none;
    }

    .info-alert{
        border-radius:24px;
        background:linear-gradient(135deg,#eff6ff,#eef2ff);
        border:1px solid #c7d2fe;
        padding:1.5rem;
    }

    .submit-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:12px;
        background:linear-gradient(135deg,#2563eb,#4f46e5);
        color:#fff;
        padding:1rem 2rem;
        border-radius:18px;
        font-weight:700;
        transition:.25s ease;
        box-shadow:0 12px 24px rgba(79,70,229,.18);
        border:none;
    }

    .submit-btn:hover{
        transform:translateY(-2px);
        box-shadow:0 16px 30px rgba(79,70,229,.22);
    }

    .back-btn{
        display:inline-flex;
        align-items:center;
        gap:10px;
        color:#475569;
        font-weight:600;
        transition:.2s;
    }

    .back-btn:hover{
        color:#1e293b;
    }

    .error-box{
        border-radius:20px;
        background:#fef2f2;
        border:1px solid #fecaca;
        color:#b91c1c;
        padding:1rem 1.2rem;
    }

    .success-box{
        border-radius:20px;
        background:#ecfdf5;
        border:1px solid #86efac;
        color:#15803d;
        padding:1rem 1.2rem;
    }

    .sidebar-card{
        background:#fff;
        border-radius:28px;
        border:1px solid #eef2ff;
        padding:1.5rem;
        box-shadow:0 4px 20px rgba(15,23,42,.04);
    }

    .progress-track{
        width:100%;
        height:10px;
        background:#e2e8f0;
        border-radius:999px;
        overflow:hidden;
    }

    .progress-fill{
        height:100%;
        width:{{ $progress ?? 0 }}%;
        border-radius:999px;
        background:linear-gradient(90deg,#3b82f6,#6366f1);
    }

    .floating-badge{
        display:inline-flex;
        align-items:center;
        gap:8px;
        background:rgba(255,255,255,.12);
        border:1px solid rgba(255,255,255,.12);
        backdrop-filter:blur(10px);
        color:#dbeafe;
        padding:.55rem 1rem;
        border-radius:999px;
        font-size:.78rem;
        font-weight:700;
        letter-spacing:.08em;
        text-transform:uppercase;
    }

    .logo-preview{
        width:120px;
        height:120px;
        border-radius:24px;
        object-fit:cover;
        border:1px solid #e2e8f0;
        box-shadow:0 4px 12px rgba(15,23,42,.05);
    }

    .input-file{
        padding:.8rem;
    }
</style>
@endpush

@section('content')

<div class="page-wrapper py-8">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ALERT --}}
        @if(session('success'))
            <div class="success-box mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="error-box mb-6">

                @if(session('error'))
                    <div>{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <ul class="mt-2 list-disc list-inside space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

            </div>
        @endif

        {{-- HERO --}}
        <div class="hero-card p-8 lg:p-10 mb-8">

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                <div class="flex items-start gap-5">

                    <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-xl border border-white/10 flex items-center justify-center shadow-lg">
                        <i class="fas fa-building text-white text-3xl"></i>
                    </div>

                    <div>

                        <div class="floating-badge mb-4">
                            <i class="fas fa-shield-check"></i>
                            Profil Industri
                        </div>

                        <h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                            {{ $isEdit ? 'Edit Profil Lembaga' : 'Lengkapi Profil Lembaga' }}
                        </h1>

                        <p class="text-blue-100 mt-4 leading-relaxed max-w-2xl">
                            @if($isEdit)
                                Perbarui informasi lembaga Anda agar tetap akurat,
                                profesional, dan terpercaya bagi peserta magang.
                            @else
                                Lengkapi informasi lembaga Anda agar dapat membuka
                                lowongan magang dan menerima peserta magang.
                            @endif
                        </p>

                    </div>

                </div>

                {{-- PROGRESS --}}
                <div class="bg-white/10 border border-white/10 backdrop-blur-xl rounded-3xl p-6 min-w-[280px]">

                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-blue-100 font-medium">
                            Kelengkapan Profil
                        </span>

                        <span class="text-white font-bold">
                            {{ $progress ?? 0 }}%
                        </span>
                    </div>

                    <div class="progress-track">
                        <div class="progress-fill"></div>
                    </div>

                    <p class="text-sm text-blue-100 mt-4 leading-relaxed">
                        @if (($progress ?? 0) == 100)
                            Profil industri sudah lengkap dan siap digunakan.
                        @elseif (($progress ?? 0) >= 70)
                            Profil hampir lengkap.
                        @elseif (($progress ?? 0) >= 40)
                            Lengkapi profil untuk meningkatkan kredibilitas.
                        @else
                            Profil masih minim informasi.
                        @endif
                    </p>

                </div>

            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- FORM --}}
            <div class="lg:col-span-3">

                <div class="main-card">

                    <form method="POST"
                          action="{{ route('industri.profile.store') }}"
                          enctype="multipart/form-data">

                        @csrf

                        {{-- INFORMASI INDUSTRI --}}
                        <div class="section-card">

                            <div class="flex items-start gap-4 mb-8">

                                <div class="section-icon">
                                    <i class="fas fa-building"></i>
                                </div>

                                <div>
                                    <h2 class="section-title">
                                        Informasi Lembaga
                                    </h2>

                                    <p class="section-subtitle">
                                        Lengkapi informasi dasar lembaga Anda.
                                    </p>
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>
                                    <label class="form-label">
                                        Nama Lembaga
                                    </label>

                                    <input type="text"
                                           name="nama_industri"
                                           value="{{ old('nama_industri', $industri->nama_industri ?? '') }}"
                                           class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">
                                        Jenis Lembaga
                                    </label>

                                    <select name="jenis_lembaga" id="jenis_lembaga" class="form-input">
                                        <option value="">Pilih Jenis Lembaga</option>
                                        <option value="pemerintah" @selected(old('jenis_lembaga', $industri->jenis_lembaga ?? '') === 'pemerintah')>
                                            Lembaga Pemerintah
                                        </option>
                                        <option value="swasta" @selected(old('jenis_lembaga', $industri->jenis_lembaga ?? '') === 'swasta')>
                                            Lembaga Swasta/Industri
                                        </option>
                                    </select>
                                </div>

                                

                                <div>
                                    <label class="form-label">
                                        Bidang/Sektor
                                    </label>

                                    <input type="text"
                                           name="bidang_industri"
                                           value="{{ old('bidang_industri', $industri->bidang_industri ?? '') }}"
                                           class="form-input">
                                </div>

                                <div class="md:col-span-2">

                                    <label class="form-label">
                                        Logo Lembaga
                                    </label>

                                    @if(!empty($industri?->logo_industri))
                                        <div class="mb-4">
                                            <img src="{{ asset('storage/' . $industri->logo_industri) }}"
                                                 class="logo-preview">
                                        </div>
                                    @endif

                                    <input type="file"
                                           name="logo_industri"
                                           class="form-input input-file">
                                </div>

                                <div class="md:col-span-2">

                                    <label class="form-label">
                                        Deskripsi Lembaga
                                    </label>

                                    <textarea name="deskripsi_industri"
                                              class="form-input">{{ old('deskripsi_industri', $industri->deskripsi_industri ?? '') }}</textarea>

                                </div>

                            </div>

                        </div>

                        {{-- ALAMAT --}}
                        <div class="bg-gray-50 section-card section-divider">

                            <div class="flex items-start gap-4 mb-8">

                                <div class="section-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>

                                <div>
                                    <h2 class="section-title">
                                        Informasi Alamat
                                    </h2>

                                    <p class="section-subtitle">
                                        Informasi lokasi perusahaan.
                                    </p>
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div class="md:col-span-2">

                                    <label class="form-label">
                                        Alamat Industri
                                    </label>

                                    <textarea name="alamat_industri"
                                              class="form-input">{{ old('alamat_industri', $industri->alamat_industri ?? '') }}</textarea>

                                </div>

                                <div>
                                    <label class="form-label">
                                        Kota / Kabupaten
                                    </label>

                                    <input type="text"
                                           name="kota_kabupaten"
                                           value="{{ old('kota_kabupaten', $industri->kota_kabupaten ?? '') }}"
                                           class="form-input">
                                </div>

                            </div>

                        </div>

                        {{-- KONTAK --}}
                        <div class="section-card section-divider">

                            <div class="flex items-start gap-4 mb-8">

                                <div class="section-icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>

                                <div>
                                    <h2 class="section-title">
                                        Informasi Kontak
                                    </h2>

                                    <p class="section-subtitle">
                                        Informasi komunikasi perusahaan.
                                    </p>
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>

                                    <label class="form-label">
                                        Email Industri
                                    </label>

                                    <input type="email"
                                           name="email_industri"
                                           value="{{ old('email_industri', $industri->email_industri ?? '') }}"
                                           class="form-input">

                                </div>

                                <div>

                                    <label class="form-label">
                                        Nomor Telepon
                                    </label>

                                    <input type="text"
                                           name="nomor_telepon_industri"
                                           value="{{ old('nomor_telepon_industri', $industri->nomor_telepon_industri ?? '') }}"
                                           class="form-input">

                                </div>

                            </div>

                        </div>

                        {{-- LEGALITAS --}}
                        <div class="bg-gray-50 section-card section-divider">

                            <div class="flex items-start gap-4 mb-8">

                                <div class="section-icon">
                                    <i class="fas fa-file-contract"></i>
                                </div>

                                <div>
                                    <h2 class="section-title">
                                        Legalitas Perusahaan
                                    </h2>

                                    <p class="section-subtitle">
                                        Informasi legalitas perusahaan.
                                    </p>
                                </div>

                            </div>

                            <div>

                                <label class="form-label">
                                    Nomor Induk Berusaha (NIB)
                                </label>

                                <input type="text"
                                       name="nib"
                                       value="{{ old('nib', $industri->nib ?? '') }}"
                                       class="form-input">

                            </div>

                        </div>

                        {{-- ACTION --}}
                        <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex flex-col-reverse md:flex-row md:items-center md:justify-between gap-4">

                            <a href="{{ route('industri.dashboard') }}"
                               class="back-btn">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </a>

                            <button type="submit"
                                    class="submit-btn">
                                <i class="fas fa-save"></i>

                                {{ $isEdit ? 'Update Profil' : 'Simpan Profil' }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-6">

                <div class="sidebar-card sticky top-6">

                    <h3 class="text-lg font-bold text-slate-800 mb-5">
                        Informasi Profil
                    </h3>

                    <div class="space-y-5 text-sm">

                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">
                                Status Profil
                            </span>

                            <div class="w-44 text-right">

                                @if (($progress ?? 0) >= 100)
                                    <span class="font-semibold text-green-600">
                                        Lengkap
                                    </span>

                                @elseif (($progress ?? 0) >= 60)
                                    <span class="font-semibold text-yellow-600">
                                        Hampir Lengkap
                                    </span>

                                @else
                                    <span class="font-semibold text-orange-500">
                                        Belum Lengkap
                                    </span>
                                @endif

                            </div>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">
                                Status Verifikasi
                            </span>

                            <div class="w-44 text-right">

                                @if ($industri)

                                    @if ($industri->status === 'disetujui')
                                        <span class="font-semibold text-green-600">
                                            Disetujui
                                        </span>

                                    @elseif ($industri->status === 'ditolak')
                                        <span class="font-semibold text-red-600">
                                            Ditolak
                                        </span>

                                    @else
                                        <span class="font-semibold text-orange-500">
                                            Proses Verifikasi
                                        </span>
                                    @endif

                                @else
                                    <span class="font-semibold text-slate-500">
                                        Belum Membuat Profil
                                    </span>
                                @endif

                            </div>

                        </div>

                        <div class="border-t border-slate-100"></div>

                        <div class="info-alert">

                            <div class="flex items-start gap-3">

                                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-lightbulb"></i>
                                </div>

                                <div>

                                    <h4 class="font-bold text-slate-800 mb-1">
                                        Lengkapi Profil
                                    </h4>

                                    <p class="text-sm text-slate-600 leading-relaxed">
                                        Profil Lembaga yang lengkap meningkatkan
                                        kredibilitas lembaga dan lebih menarik
                                        bagi peserta magang.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection