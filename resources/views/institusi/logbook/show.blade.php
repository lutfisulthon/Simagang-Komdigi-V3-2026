@extends('layouts.app')

@section('title', 'Detail Logbook - Sistem Magang')

@push('styles')
    <style>

        *,
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dash-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        }

        .hero-strip {
            background: linear-gradient(110deg, #1e3a8a 0%, #3b4fd8 55%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
        }

        .hero-strip::before {
            content: '';
            position: absolute;
            top: -70px;
            right: -50px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: 18%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(20, 40, 120, 0.06), 0 4px 18px rgba(20, 40, 120, 0.06);
            overflow: hidden;
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

        .panel-body {
            padding: 20px 22px;
        }

        .section-card {
            background: #f8faff;
            border: 1px solid #e8eeff;
            border-radius: 18px;
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="hero-strip px-6 py-7 sm:px-8 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Institusi Dashboard</p>
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Detail Logbook</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Informasi lengkap catatan harian peserta magang.
                        </p>
                    </div>

                    <div
                        class="bg-white/10 border border-white/10 rounded-2xl px-4 py-4 text-white shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-100/80">Tanggal</p>
                                <p class="text-base font-bold">{{ \Carbon\Carbon::parse($logbook->date)->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-user text-blue-200 text-base"></i>
                    <h2>Identitas Peserta Magang</h2>
                </div>
                <div class="panel-body">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @if ($logbook->intern->photo_path)
                            <img src="{{ url('storage/' . $logbook->intern->photo_path) }}"
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover object-center border-4 border-blue-100 shadow-md flex-shrink-0 aspect-square"
                                alt="{{ $logbook->intern->name }}" loading="lazy" decoding="async" />
                        @else
                            <div
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md flex-shrink-0 aspect-square">
                                <i class="fas fa-user text-white text-lg sm:text-2xl"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $logbook->intern->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500 truncate">{{ $logbook->intern->institution }}</p>
                        </div>
                        <div class="section-card px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Tanggal</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($logbook->date)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="lg:col-span-3 panel">
                    <div class="panel-header">
                        <i class="fas fa-tasks text-blue-200 text-base"></i>
                        <h2>Aktivitas</h2>
                    </div>
                    <div class="panel-body">
                        <div class="section-card p-5 sm:p-6">
                            <p class="text-sm sm:text-base text-slate-700 whitespace-pre-line leading-relaxed">
                                {{ $logbook->activity }}</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 panel">
                    <div class="panel-header">
                        <i class="fas fa-camera text-blue-200 text-base"></i>
                        <h2>Foto Dokumentasi</h2>
                    </div>
                    <div class="panel-body">
                        @if ($logbook->photo_path)
                            @can('view', $logbook)
                                <div class="relative group">
                                    <img src="{{ route('institusi.logbook.photo', basename($logbook->photo_path)) }}"
                                        class="w-full rounded-xl border-2 border-blue-100 shadow-md cursor-pointer hover:shadow-xl transition-all duration-300 max-h-[420px] object-cover"
                                        alt="Foto Logbook"
                                        onclick="window.open('{{ route('institusi.logbook.photo', basename($logbook->photo_path)) }}', '_blank')" loading="lazy" decoding="async" />
                                    <div
                                        class="absolute inset-0 bg-black/0 group-hover:bg-black/10 rounded-xl transition-all duration-300 flex items-center justify-center">
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <div class="bg-white rounded-full p-3 shadow-lg">
                                                <i class="fas fa-search-plus text-blue-600 text-lg"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500 text-center mt-3">Klik untuk melihat ukuran penuh</p>
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center py-10 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
                                    <p class="text-sm font-medium text-slate-500">Tidak ada akses.</p>
                                </div>
                            @endcan
                        @else
                            <div
                                class="flex flex-col items-center justify-center py-10 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
                                <i class="fas fa-image text-4xl text-slate-300 mb-3"></i>
                                <p class="text-sm font-medium text-slate-500">Tidak ada foto.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 pt-1">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 sm:px-6 py-3 text-sm sm:text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>

                <a href="{{ route('institusi.logbook.index') }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 sm:px-6 py-3 text-sm sm:text-base font-semibold text-white shadow-lg transition hover:shadow-xl">
                    <i class="fas fa-list mr-2"></i>
                    Lihat Semua Logbook
                </a>
            </div>
        </div>
    </div>
@endsection
