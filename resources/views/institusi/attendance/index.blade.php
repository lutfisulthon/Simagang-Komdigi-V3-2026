@extends('layouts.app')

@section('title', 'Absensi Anak Bimbingan - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-attendance-index.css') }}?v={{ @filemtime(public_path('css/pages/institusi-attendance-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="hero-strip px-6 py-7 sm:px-8 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Institusi Dashboard</p>
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Absensi Peserta Magang</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Pantau dan kelola data kehadiran peserta magang.</p>
                    </div>

                    <div
                        class="bg-white/10 border border-white/10 rounded-2xl px-4 py-4 text-white shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-100/80">Data</p>
                                <p class="text-base font-bold">{{ $todayAbsentInterns->count() }} Absen</p>
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
                    <form method="GET" action="{{ route('institusi.attendance.index') }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-end">

                            <div>
                                <label class="field-label">Peserta Magang</label>

                                <select name="intern_id" class="field-select">
                                    <option value="">Semua</option>

                                    @foreach ($interns as $intern)
                                        <option value="{{ $intern->id }}"
                                            @selected(request('intern_id') == $intern->id)>

                                            {{ $intern->name }}

                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="field-label">Dari Tanggal</label>

                                <input
                                    type="date"
                                    name="date_from"
                                    value="{{ request('date_from') }}"
                                    class="field-input" />
                            </div>

                            <div>
                                <label class="field-label">Hingga Tanggal</label>

                                <input
                                    type="date"
                                    name="date_to"
                                    value="{{ request('date_to') }}"
                                    class="field-input" />
                            </div>

                            <div>
                                <label class="field-label">Status</label>

                                <select name="status" class="field-select">

                                    <option value="">Semua</option>

                                    <option value="hadir" @selected(request('status') === 'hadir')>
                                        Hadir
                                    </option>

                                    <option value="izin" @selected(request('status') === 'izin')>
                                        Izin
                                    </option>

                                    <option value="sakit" @selected(request('status') === 'sakit')>
                                        Sakit
                                    </option>

                                    <option value="alfa" @selected(request('status') === 'alfa')>
                                        Tidak Hadir
                                    </option>

                                </select>
                            </div>

                            {{-- TOMBOL --}}
                            <div class="flex items-end gap-2 h-full">

                                <button
                                    type="submit"
                                    class="inline-flex h-[54px] flex-1 items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl">

                                    <i class="fas fa-search mr-2"></i>
                                    Filter

                                </button>

                                @if (request()->anyFilled(['intern_id', 'date_from', 'date_to', 'status']))

                                    <a
                                        href="{{ route('institusi.attendance.index') }}"
                                        class="inline-flex h-[54px] w-[54px] items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">

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
                    <h2>Data Absensi</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrap">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gradient-to-r from-blue-600 to-indigo-600">
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                        Tanggal</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                        Nama</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                        Status</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                        Check In</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                        Check Out</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                        Foto In</th>
                                    <th
                                        class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                                        Foto Out</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($todayAbsentInterns as $absentIntern)
                                    <tr class="bg-red-50">
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="flex items-center text-xs sm:text-sm text-slate-700">
                                                <i class="fas fa-calendar mr-1 sm:mr-2 text-slate-400 text-xs"></i>
                                                <span>{{ \Carbon\Carbon::parse($todayWita)->format('d/m/y') }}</span>
                                                <span class="ml-1 text-xs text-slate-400">(Hari ini)</span>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if ($absentIntern->photo_path)
                                                    <img src="{{ url('storage/' . $absentIntern->photo_path) }}"
                                                        class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover border-2 border-blue-200 mr-2 sm:mr-3 flex-shrink-0"
                                                        alt="{{ $absentIntern->name }}" loading="lazy" decoding="async" />
                                                @else
                                                    <div
                                                        class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0">
                                                        <i class="fas fa-user text-white text-xs"></i>
                                                    </div>
                                                @endif
                                                <span
                                                    class="text-xs sm:text-sm font-medium text-slate-900 truncate max-w-[100px] sm:max-w-none">{{ $absentIntern->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <span class="status-pill bg-red-100 text-red-800">Tidak Hadir</span>
                                        </td>
                                        <td colspan="4"
                                            class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-400 italic">
                                            Belum melakukan absensi hari ini
                                        </td>
                                    </tr>
                                @endforeach

                                @forelse($attendances as $a)
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="flex items-center text-xs sm:text-sm text-slate-700">
                                                <i class="fas fa-calendar mr-1 sm:mr-2 text-slate-400 text-xs"></i>
                                                <span>{{ \Carbon\Carbon::parse($a->date)->format('d/m/y') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if ($a->intern->photo_path)
                                                    <img src="{{ url('storage/' . $a->intern->photo_path) }}"
                                                        class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover border-2 border-blue-200 mr-2 sm:mr-3 flex-shrink-0"
                                                        alt="{{ $a->intern->name }}" loading="lazy" decoding="async" />
                                                @else
                                                    <div
                                                        class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0">
                                                        <i class="fas fa-user text-white text-xs"></i>
                                                    </div>
                                                @endif
                                                <span
                                                    class="text-xs sm:text-sm font-medium text-slate-900 truncate max-w-[100px] sm:max-w-none">{{ $a->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <span
                                                class="status-pill
                                                @if ($a->status == 'hadir') bg-green-100 text-green-800
                                                @elseif($a->status == 'izin') bg-yellow-100 text-yellow-800
                                                @elseif($a->status == 'sakit') bg-orange-100 text-orange-800
                                                @else bg-red-100 text-red-800 @endif">
                                                @if ($a->status == 'alfa')
                                                    Tidak Hadir
                                                @else
                                                    {{ ucfirst($a->status) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="flex items-center text-xs sm:text-sm text-slate-600">
                                                <i class="fas fa-sign-in-alt mr-1 sm:mr-2 text-green-500 text-xs"></i>
                                                {{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '-' }}
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="flex items-center text-xs sm:text-sm text-slate-600">
                                                <i class="fas fa-sign-out-alt mr-1 sm:mr-2 text-red-500 text-xs"></i>
                                                {{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '-' }}
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            @if ($a->photo_path)
                                                @php
                                                    $photoUrl = route('institusi.attendance.photo', [
                                                        'filename' => basename($a->photo_path),
                                                    ]);
                                                @endphp
                                                <img src="{{ $photoUrl }}" alt="Check In"
                                                    class="w-10 h-10 sm:w-12 sm:h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm hover:shadow-md"
                                                    onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                    title="Klik untuk melihat full size" loading="lazy" decoding="async" />
                                            @else
                                                <span class="text-slate-400 text-xs sm:text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            @if ($a->photo_checkout)
                                                @php
                                                    $photoCheckoutUrl = route('institusi.attendance.photo', [
                                                        'filename' => basename($a->photo_checkout),
                                                    ]);
                                                @endphp
                                                <img src="{{ $photoCheckoutUrl }}" alt="Check Out"
                                                    class="w-10 h-10 sm:w-12 sm:h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm hover:shadow-md"
                                                    onclick="window.open('{{ $photoCheckoutUrl }}', '_blank')"
                                                    title="Klik untuk melihat full size" loading="lazy" decoding="async" />
                                            @else
                                                <span class="text-slate-400 text-xs sm:text-sm">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 sm:px-6 py-6 sm:py-8 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div
                                                    class="w-16 h-16 sm:w-24 sm:h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-3 sm:mb-5 shadow-md">
                                                    <i class="fas fa-inbox text-4xl sm:text-5xl text-gray-300"></i>
                                                </div>
                                                <p class="text-sm sm:text-lg font-bold text-gray-700 mb-1 sm:mb-2">Tidak
                                                    ada data absensi</p>
                                                <p class="text-xs sm:text-sm text-gray-500">Coba ubah filter untuk melihat
                                                    data lainnya</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-users text-blue-200 text-base"></i>
                    <h2>Riwayat Absensi</h2>
                </div>

                <div class="panel-body">
                    <div class="table-wrap">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gradient-to-r from-blue-600 to-indigo-600">
                                    <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                        Nama
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                        Persentase Kehadiran
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                        Status
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($internStatistics as $intern)

                                    <tr class="hover:bg-blue-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">

                                                @if ($intern->photo_path)
                                                    <img
                                                        src="{{ url('storage/' . $intern->photo_path) }}"
                                                        class="w-10 h-10 rounded-full object-cover border-2 border-blue-200 mr-3" loading="lazy" decoding="async">
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white mr-3">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                @endif

                                                <span class="font-medium text-slate-800">
                                                    {{ $intern->name }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex justify-center items-center gap-3">

                                                <div class="w-40 bg-gray-200 rounded-full h-3">
                                                    <div
                                                        class="h-3 rounded-full
                                                        @if ($intern->attendance_percentage >= 70)
                                                            bg-green-500
                                                        @elseif($intern->attendance_percentage >= 50)
                                                            bg-yellow-500
                                                        @else
                                                            bg-red-500
                                                        @endif"
                                                        style="width: {{ $intern->attendance_percentage }}%">
                                                    </div>
                                                </div>

                                                <span class="text-sm font-semibold">
                                                    {{ $intern->attendance_percentage }}%
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap flex justify-center">

                                            @if ($intern->attendance_percentage >= 70)
                                                <span class="status-pill bg-green-100 text-green-800">
                                                    Baik
                                                </span>
                                            @elseif($intern->attendance_percentage >= 50)
                                                <span class="status-pill bg-yellow-100 text-yellow-800">
                                                    Perlu Perhatian
                                                </span>
                                            @else
                                                <span class="status-pill bg-red-100 text-red-800">
                                                    Rendah
                                                </span>
                                            @endif

                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-center">

                                            <a href="{{ route('institusi.attendance.show', ['intern' => $intern->id]) }}"
                                                class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">

                                                <i class="fas fa-eye mr-2"></i>
                                                Lihat Riwayat
                                            </a>

                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            Tidak ada data anak magang
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
