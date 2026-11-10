@extends('layouts.app')

@section('title', 'Lowongan Magang')

@push('styles')
<style>

    *{
        font-family:'Plus Jakarta Sans',sans-serif;
    }

    .page-bg{
        min-height:100vh;
        background:linear-gradient(135deg,#eef4ff 0%,#f8fbff 45%,#edf2ff 100%);
    }

    .hero-card{
        background:linear-gradient(135deg,#1e3a8a 0%,#4338ca 55%,#6366f1 100%);
        border-radius:28px;
        overflow:hidden;
        position:relative;
        box-shadow:0 15px 40px rgba(30,58,138,.18);
    }

    .hero-card::before{
        content:'';
        position:absolute;
        width:260px;
        height:260px;
        border-radius:999px;
        background:rgba(255,255,255,.06);
        top:-120px;
        right:-80px;
    }

    .stat-card{
        background:#fff;
        border-radius:22px;
        padding:1.5rem;
        box-shadow:0 4px 20px rgba(15,23,42,.05);
        border:1px solid #eef2ff;
    }

    .panel{
        background:#fff;
        border-radius:24px;
        overflow:hidden;
        border:1px solid #eef2ff;
        box-shadow:0 4px 20px rgba(15,23,42,.05);
    }

    .panel-header{
        background:linear-gradient(90deg,#1e3a8a,#4338ca);
        padding:1rem 1.5rem;
        color:#fff;
    }

    .panel-body{
        padding: 20px 22px;
    }

    .field-input,
    .field-select{
        width:100%;
        height:52px;
        border-radius:16px;
        border:1px solid #dbe4ff;
        padding:0 1rem;
        transition:.2s;
    }

    .field-input:focus,
    .field-select:focus{
        outline:none;
        border-color:#6366f1;
        box-shadow:0 0 0 4px rgba(99,102,241,.12);
    }

    .status-pill{
        display:inline-flex;
        align-items:center;
        padding:6px 14px;
        border-radius:999px;
        font-size:.75rem;
        font-weight:700;
    }
</style>
@endpush

@section('content')

<div class="page-bg py-8">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- HERO --}}
        <div class="hero-card px-8 py-8">

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                <div class="text-white max-w-2xl">

                    <h1 class="text-3xl sm:text-4xl font-extrabold">
                        Lowongan Magang
                    </h1>

                    <p class="mt-3 text-blue-100 leading-relaxed">
                        Kelola seluruh lowongan magang perusahaanmu
                        dengan lebih mudah.
                    </p>

                </div>

                <a href="{{ route('industri.lowongan.create') }}"
                   class="inline-flex items-center justify-center bg-white text-blue-700 font-semibold px-6 py-3 rounded-2xl shadow-lg hover:bg-blue-50 transition">

                    <i class="fas fa-plus mr-2"></i>
                    Tambah Lowongan

                </a>

            </div>

        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <div class="stat-card">
                <p class="text-sm text-slate-500">
                    Total Lowongan
                </p>

                <h2 class="mt-2 text-3xl font-extrabold text-slate-800">
                    {{ $totalLowongan }}
                </h2>
            </div>

            <div class="stat-card">
                <p class="text-sm text-slate-500">
                    Lowongan Dibuka
                </p>

                <h2 class="mt-2 text-3xl font-extrabold text-green-600">
                    {{ $lowonganDibuka }}
                </h2>
            </div>

            <div class="stat-card">
                <p class="text-sm text-slate-500">
                    Lowongan Ditutup
                </p>

                <h2 class="mt-2 text-3xl font-extrabold text-red-600">
                    {{ $lowonganDitutup }}
                </h2>
            </div>

        </div>

        {{-- FILTER --}}
        <div class="panel">

            <div class="p-6">

                <form method="GET">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                        <div class="lg:col-span-4">

                            <label class="text-sm font-semibold text-slate-700 mb-2 block">
                                Cari Lowongan
                            </label>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari judul, posisi, atau divisi..."
                                   class="field-input">

                        </div>

                        <div class="lg:col-span-3">

                            <label class="text-sm font-semibold text-slate-700 mb-2 block">
                                Status
                            </label>

                            <select name="status" class="field-select">

                                <option value="">
                                    Semua
                                </option>

                                <option value="dibuka"
                                    {{ request('status') == 'dibuka' ? 'selected' : '' }}>
                                    Dibuka
                                </option>

                                <option value="ditutup"
                                    {{ request('status') == 'ditutup' ? 'selected' : '' }}>
                                    Ditutup
                                </option>

                            </select>

                        </div>

                        <div class="lg:col-span-3">

                            <label class="text-sm font-semibold text-slate-700 mb-2 block">
                                Verifikasi
                            </label>

                            <select name="status_verifikasi" class="field-select">

                                <option value="">
                                    Semua
                                </option>

                                <option value="dibuka"
                                    {{ request('status_verifikasi') == 'disetujui' ? 'selected' : '' }}>
                                    Disetujui
                                </option>

                                <option value="ditutup"
                                    {{ request('status_verifikasi') == 'ditolak' ? 'selected' : '' }}>
                                    Ditolak
                                </option>
                            
                                <option value="ditutup"
                                    {{ request('status_verifikasi') == 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>

                            </select>

                        </div>

                        <div class="lg:col-span-2 flex items-end gap-2">

                            <button type="submit"
                                    class="flex-1 h-[52px] rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow-lg hover:opacity-90 transition inline-flex items-center justify-center">

                                <i class="fas fa-search mr-2"></i>
                                Cari

                            </button>

                            @if (request()->filled('search') || request()->filled('status') || request()->filled('date'))

                                <a href="{{ route('industri.lowongan.index') }}"
                                class="h-[52px] w-[52px] rounded-2xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 inline-flex items-center justify-center shadow-sm">

                                    <i class="fas fa-rotate-left"></i>

                                </a>

                            @endif

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="panel">

            <div class="panel-header flex items-center justify-between">

                <div class="flex items-center gap-2">
                    <i class="fas fa-briefcase"></i>

                    <h2 class="font-bold">
                        Data Lowongan
                    </h2>
                </div>

                <span class="bg-white/10 px-3 py-1 rounded-full text-xs font-semibold">
                    {{ $totalLowonganFilter }} Data
                </span>

            </div>

            <div class="panel-body overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-blue-50">

                        <tr>

                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-700 rounded-tl-xl">
                                Judul
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-700">
                                Posisi
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-700">
                                Divisi
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-700">
                                Status
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-700">
                                Verifikasi
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-700 rounded-tr-xl">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse($lowongans as $lowongan)

                            @php
                                $statusClass = $lowongan->status == 'dibuka'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700';
                                
                                $verifikasiClass = match ($lowongan->status_verifikasi) {
                                    'disetujui' => 'bg-green-100 text-green-700',
                                    'ditolak' => 'bg-red-100 text-red-700',
                                    default => 'bg-yellow-100 text-yellow-700',
                                };

                                $verifikasiLabel = match ($lowongan->status_verifikasi) {
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    default => 'Pending',
                                };
                                    
                            @endphp

                            <tr class="hover:bg-slate-50 transition">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-medium text-slate-700">
                                        {{ $lowongan->judul_lowongan }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm text-slate-600">
                                        {{ $lowongan->posisi_magang }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm text-slate-600">
                                        {{ $lowongan->divisi }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">

                                    <span class="status-pill {{ $statusClass }}">
                                        {{ ucfirst($lowongan->status) }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">

                                    <span class="status-pill {{ $verifikasiClass }}">
                                        {{ $verifikasiLabel }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">

                                    <div class="flex items-center justify-center gap-2">

                                        <a href="{{ route('industri.lowongan.show', $lowongan->id) }}"
                                           class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center hover:bg-green-600 hover:text-white transition">

                                            <i class="fas fa-eye"></i>

                                        </a>

                                        <a href="{{ route('industri.lowongan.edit', $lowongan->id) }}"
                                           class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition">

                                            <i class="fas fa-pen"></i>

                                        </a>

                                        <form action="{{ route('industri.lowongan.destroy', $lowongan->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus lowongan ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition">

                                                <i class="fas fa-trash"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-6 py-16 text-center">

                                    <div class="flex flex-col items-center text-slate-500">

                                        <i class="fas fa-briefcase text-5xl text-slate-300 mb-4"></i>

                                        <h3 class="font-semibold text-lg">
                                            Belum Ada Lowongan
                                        </h3>

                                        <p class="text-sm mt-1">
                                            Mulai buat lowongan magang pertama Anda.
                                        </p>

                                        <a href="{{ route('industri.lowongan.create') }}"
                                           class="mt-5 inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-semibold transition">

                                            <i class="fas fa-plus mr-2"></i>
                                            Tambah Lowongan

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="p-6 border-t border-slate-100">
                {{ $lowongans->links() }}
            </div>

        </div>

    </div>

</div>

@endsection