@extends('layouts.app')

@section('title', 'Logbook Peserta Magang - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/industri-logbook-index.css') }}?v={{ @filemtime(public_path('css/pages/industri-logbook-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="hero-strip px-6 py-7 sm:px-8 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Industri Dashboard</p>
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Logbook Peserta Magang</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Pantau dan kelola catatan harian aktivitas peserta
                            magang.</p>
                    </div>

                    <div
                        class="bg-white/10 border border-white/10 rounded-2xl px-4 py-4 text-white shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-100/80">Data</p>
                                <p class="text-base font-bold">{{ $logbooks->total() }} Logbook</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-filter text-blue-200 text-base"></i>
                    <h2>Filter Data</h2>
                </div>
                <div class="panel-body">
                    <form method="GET" action="{{ route('industri.logbook.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div>
                                <label class="field-label">Peserta Magang</label>
                                <select name="intern_id" class="field-select">
                                    <option value="">Semua</option>
                                    @foreach ($interns as $intern)
                                        <option value="{{ $intern->id }}" @selected(request('intern_id') == $intern->id)>{{ $intern->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="field-label">Dari Tanggal</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                    class="field-input" />
                            </div>

                            <div>
                                <label class="field-label">Hingga Tanggal</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="field-input" />
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="inline-flex flex-1 items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl">
                                    <i class="fas fa-search mr-2"></i>Filter
                                </button>
                                @if (request()->anyFilled(['intern_id', 'date_from', 'date_to']))
                                    <a href="{{ route('industri.logbook.index') }}"
                                        class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
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
                    <h2>Data Logbook</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrap">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gradient-to-r from-blue-600 to-indigo-600">
                                    <th
                                        class="px-4 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Tanggal</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Nama</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Aktivitas</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Foto</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logbooks as $l)
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse($l->date)->format('d/m/y') }}</span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2 sm:gap-3">
                                                @if ($l->intern->photo_path)
                                                    <img src="{{ url('storage/' . $l->intern->photo_path) }}"
                                                        class="w-10 h-10 rounded-full object-cover object-center border-2 border-blue-200 shadow-sm flex-shrink-0 aspect-square"
                                                        alt="{{ $l->intern->name }}" loading="lazy" decoding="async" />
                                                @else
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-sm flex-shrink-0 aspect-square">
                                                        <i class="fas fa-user text-white text-xs"></i>
                                                    </div>
                                                @endif
                                                <span
                                                    class="text-sm font-semibold text-slate-900 truncate">{{ $l->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4">
                                            <div class="text-sm text-slate-600 max-w-md truncate">{{ $l->activity }}</div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            @if ($l->photo_path)
                                                @can('view', $l)
                                                    <img src="{{ route('industri.logbook.photo', basename($l->photo_path)) }}"
                                                        alt="Logbook Photo"
                                                        class="w-12 h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm hover:shadow-lg"
                                                        onclick="window.open('{{ route('industri.logbook.photo', basename($l->photo_path)) }}', '_blank')"
                                                        title="Klik untuk melihat full size" loading="lazy" decoding="async" />
                                                @else
                                                    <span class="text-slate-400 text-sm">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span class="text-slate-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('industri.logbook.show', $l) }}"
                                                class="inline-flex items-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:shadow-md">
                                                <i class="fas fa-eye mr-2"></i>Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div
                                                    class="w-16 h-16 sm:w-24 sm:h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-3 sm:mb-5 shadow-md">
                                                    <i class="fas fa-book-open text-3xl sm:text-5xl text-gray-300"></i>
                                                </div>
                                                <p class="text-base sm:text-lg font-bold text-gray-700 mb-1 sm:mb-2">Tidak
                                                    ada data logbook</p>
                                                <p class="text-xs sm:text-sm text-gray-500">Coba ubah filter untuk melihat
                                                    data lainnya</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $logbooks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
