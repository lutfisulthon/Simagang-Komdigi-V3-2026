@extends('layouts.app')

@section('title', 'Mikro Skill Peserta Magang - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/industri-microskill-index.css') }}?v={{ @filemtime(public_path('css/pages/industri-microskill-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="hero-strip px-6 py-7 sm:px-8 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Industri Dashboard</p>
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Mikro Skill Peserta Magang</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Pantau pencapaian dan pengembangan keterampilan
                            peserta magang.</p>
                    </div>

                    <div
                        class="bg-white/10 border border-white/10 rounded-2xl px-4 py-4 text-white shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-100/80">Data</p>
                                <p class="text-base font-bold">{{ $submissions->total() }} Submission</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="stat-tile tile-blue">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Total Submissions</p>
                            <h3 class="mt-2 text-3xl font-extrabold text-slate-900">{{ $submissions->total() }}</h3>
                        </div>
                        <div class="tile-icon bg-gradient-to-br from-blue-500 to-indigo-600">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">Seluruh submission mikro skill.</p>
                </div>

                <div class="stat-tile tile-indigo">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Unique Interns</p>
                            <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ $submissions->pluck('intern_id')->unique()->count() }}</h3>
                        </div>
                        <div class="tile-icon bg-gradient-to-br from-indigo-500 to-purple-600">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">Jumlah peserta magang.</p>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-filter text-blue-200 text-base"></i>
                    <h2>Filter Data</h2>
                </div>
                <div class="panel-body">
                    <form method="GET" action="{{ route('industri.microskill.index') }}">
                        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                            <div class="flex-1">
                                <label class="field-label">Peserta Magang</label>
                                <select name="intern_id" class="field-select">
                                    <option value="">-- Semua Peserta Magang --</option>
                                    @foreach ($interns as $intern)
                                        <option value="{{ $intern->id }}" @selected(request('intern_id') == $intern->id)>
                                            {{ $intern->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl">
                                    <i class="fas fa-search mr-2"></i>Cari
                                </button>
                                @if (request()->filled('intern_id'))
                                    <a href="{{ route('industri.microskill.index') }}"
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
                    <h2>Data Mikro Skill</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrap">
                        <table class="w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gradient-to-r from-blue-600 to-indigo-600">
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Nama</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Judul Course</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Waktu Submit</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-5 text-left text-xs font-bold text-white uppercase tracking-widest">
                                        Dokumentasi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($submissions as $s)
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-3 sm:px-6 py-3 sm:py-5 whitespace-nowrap">
                                            <div class="flex items-center gap-2 sm:gap-3">
                                                @if ($s->intern->photo_path)
                                                    <img src="{{ url('storage/' . $s->intern->photo_path) }}"
                                                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover object-center border-2 border-blue-300 shadow-md ring-2 ring-blue-100 flex-shrink-0 aspect-square"
                                                        alt="{{ $s->intern->name }}" loading="lazy" decoding="async" />
                                                @else
                                                    <div
                                                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md ring-2 ring-blue-100 flex-shrink-0 aspect-square">
                                                        <i class="fas fa-user text-white text-sm sm:text-base"></i>
                                                    </div>
                                                @endif
                                                <span
                                                    class="text-xs sm:text-sm font-semibold text-slate-900 truncate">{{ $s->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-5">
                                            <span class="text-xs sm:text-sm font-medium text-slate-700">
                                                {{ Str::limit($s->title, 40) }}
                                            </span>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-5 whitespace-nowrap">
                                            <div class="flex items-center gap-1 sm:gap-2 text-xs sm:text-sm">
                                                <span class="font-semibold text-slate-700">
                                                    {{ $s->submitted_at ? \Carbon\Carbon::parse($s->submitted_at)->format('d/m/y') : '-' }}
                                                </span>
                                                <span class="text-slate-500 text-xs hidden sm:inline">
                                                    {{ $s->submitted_at ? \Carbon\Carbon::parse($s->submitted_at)->format('H:i') : '' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-5 whitespace-nowrap">
                                            @if ($s->photo_path)
                                                @can('view', $s)
                                                    <img src="{{ route('industri.microskill.photo', basename($s->photo_path)) }}"
                                                        alt="Documentation"
                                                        class="w-10 h-10 sm:w-12 sm:h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm hover:shadow-lg transform hover:scale-110 aspect-square"
                                                        onclick="window.open('{{ route('industri.microskill.photo', basename($s->photo_path)) }}', '_blank')"
                                                        title="Klik untuk melihat full size" loading="lazy" decoding="async" />
                                                @else
                                                    <div
                                                        class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-slate-100 rounded-lg border-2 border-slate-200">
                                                        <span class="text-xs text-slate-500">Tidak ada akses</span>
                                                    </div>
                                                @endcan
                                            @else
                                                <div
                                                    class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-slate-100 rounded-lg border-2 border-slate-200">
                                                    <i class="fas fa-image text-slate-300 text-sm sm:text-lg"></i>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 sm:px-6 py-8 sm:py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div
                                                    class="w-16 h-16 sm:w-24 sm:h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-3 sm:mb-5 shadow-md">
                                                    <i class="fas fa-certificate text-3xl sm:text-5xl text-gray-300"></i>
                                                </div>
                                                <p class="text-base sm:text-lg font-bold text-gray-700 mb-1 sm:mb-2">Tidak
                                                    ada data mikro skill</p>
                                                <p class="text-xs sm:text-sm text-gray-500">Data akan muncul ketika peserta
                                                    magang telah mengerjakan mikro skill</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $submissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
