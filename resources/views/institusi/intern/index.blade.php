@extends('layouts.app')

@section('title', 'Peserta Magang - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-intern-index.css') }}?v={{ @filemtime(public_path('css/pages/institusi-intern-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="hero-strip px-6 py-7 sm:px-8 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Institusi Dashboard</p>
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Monitoring Peserta Magang</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Kelola dan pantau perkembangan peserta magang.</p>
                    </div>

                    <div class="bg-white/10 border border-white/10 rounded-2xl px-4 py-4 text-white shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-100/80">Data</p>
                                <p class="text-base font-bold">{{ $interns->count() }} Peserta Magang</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                <a href="{{ route('institusi.attendance.index') }}" class="stat-tile tile-blue group">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Absensi</p>
                            <h3 class="mt-2 text-xl font-extrabold text-slate-900">Monitoring Kehadiran</h3>
                        </div>
                        <div class="tile-icon bg-gradient-to-br from-blue-500 to-blue-700 group-hover:rotate-12 transition-transform duration-300">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">Lihat dan kelola data kehadiran peserta magang.</p>
                </a>

                <a href="{{ route('institusi.logbook.index') }}" class="stat-tile tile-green group">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Logbook</p>
                            <h3 class="mt-2 text-xl font-extrabold text-slate-900">Catatan Harian</h3>
                        </div>
                        <div class="tile-icon bg-gradient-to-br from-green-500 to-emerald-700 group-hover:rotate-12 transition-transform duration-300">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">Pantau catatan harian dan aktivitas.</p>
                </a>

                <a href="{{ route('institusi.certificate.index') }}" class="stat-tile tile-purple group sm:col-span-2 md:col-span-1">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Nilai</p>
                            <h3 class="mt-2 text-xl font-extrabold text-slate-900">Sertifikat & Nilai</h3>
                        </div>
                        <div class="tile-icon bg-gradient-to-br from-purple-500 to-fuchsia-700 group-hover:rotate-12 transition-transform duration-300">
                            <i class="fas fa-certificate"></i>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">Lihat nilai laporan dan sertifikat peserta magang.</p>
                </a>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-search text-blue-200 text-base"></i>
                    <h2>Cari Peserta Magang</h2>
                </div>
                <div class="panel-body">
                    <form method="GET" action="{{ route('institusi.intern.index') }}">
                        <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
                            <div class="flex-1">
                                <label for="search" class="field-label">Nama Peserta Magang</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nama peserta magang..." class="field-input">
                            </div>

                            <div class="w-full lg:w-56">
                                <label for="status" class="field-label">Status</label>
                                <select name="status" id="status" class="field-select">
                                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="alumni" {{ request('status') == 'alumni' ? 'selected' : '' }}>Alumni</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl">
                                    <i class="fas fa-search mr-2"></i>Cari
                                </button>
                                @if (request()->filled('search') || request()->filled('status'))
                                    <a href="{{ route('institusi.intern.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-table text-blue-200 text-base"></i>
                    <h2>Data Peserta Magang</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrap">
                        <table class="w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gradient-to-r from-blue-600 to-indigo-600">
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Foto</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Nama</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider hidden sm:table-cell">Prodi</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Logbook</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Absensi</th>
                                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Mikroskill</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($interns as $intern)
                                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            @if ($intern->photo_path)
                                                <div class="w-12 h-12 sm:w-14 sm:h-14 flex-shrink-0 rounded-full overflow-hidden border-2 border-blue-200 shadow-sm ring-2 ring-blue-100 aspect-square flex items-center justify-center">
                                                    <img src="{{ url('storage/' . $intern->photo_path) }}" class="w-full h-full object-cover object-center" alt="{{ $intern->name }}" loading="lazy" decoding="async" />
                                                </div>
                                            @else
                                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-sm ring-2 ring-blue-100 flex-shrink-0 aspect-square">
                                                    <i class="fas fa-user text-white text-lg sm:text-xl"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <span class="text-blue-700 hover:text-indigo-700 font-bold transition-colors text-xs sm:text-sm md:text-base">{{ $intern->name }}</span>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            @if ($intern->is_active)
                                                <span class="status-pill bg-green-100 text-green-800 shadow-sm">Aktif</span>
                                            @else
                                                <span class="status-pill bg-slate-100 text-slate-700 shadow-sm">Alumni</span>
                                            @endif
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap hidden sm:table-cell">
                                            <div class="text-xs sm:text-sm text-slate-700 font-medium">{{ $intern->major }}</div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                            <span class="status-pill bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800">
                                                <i class="fas fa-book mr-1.5"></i>{{ $intern->logbooks_count }}
                                            </span>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                            <span class="status-pill bg-gradient-to-r from-green-100 to-green-200 text-green-800">
                                                <i class="fas fa-check-circle mr-1.5"></i>{{ $intern->attendances_count }}
                                            </span>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                            <span class="status-pill bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800">
                                                <i class="fas fa-graduation-cap mr-1.5"></i>{{ $intern->micro_skills_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4 shadow-md">
                                                    <i class="fas fa-user-slash text-5xl text-gray-300"></i>
                                                </div>
                                                <p class="text-lg font-bold text-gray-700 mb-2">Belum ada peserta magang</p>
                                                <p class="text-sm text-gray-400">Data peserta magang akan muncul di sini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($interns, 'links'))
                        <div class="mt-6">
                            {{ $interns->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
