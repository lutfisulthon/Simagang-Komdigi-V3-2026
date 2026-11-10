@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')

@push('styles')
    <style>
        .field-input,
        .field-select {
            width: 100%;
            border-radius: 14px;
            border: 1px solid #dbe3f0;
            background: #fff;
            padding: 0.9rem 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
    </style>
    
@endpush

<div class="min-h-screen bg-slate-50 py-8">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-700 to-indigo-700 rounded-3xl p-8 text-white shadow-xl">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                <div class="flex items-center gap-4">

                    @if ($intern->photo_path)
                        <img
                            src="{{ url('storage/' . $intern->photo_path) }}"
                            class="w-20 h-20 rounded-2xl object-cover border-4 border-white/30" loading="lazy" decoding="async">
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-user text-3xl"></i>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-blue-100 uppercase tracking-widest">
                            Riwayat Absensi
                        </p>

                        <h1 class="text-3xl font-extrabold mt-1">
                            {{ $intern->name }}
                        </h1>
                    </div>

                </div>

                <div class="bg-white/10 rounded-2xl px-6 py-4 backdrop-blur-sm">

                    <p class="text-sm text-blue-100">
                        Persentase Kehadiran
                    </p>

                    <h2 class="text-4xl font-black mt-1">
                        {{ $attendancePercentage }}%
                    </h2>

                </div>

            </div>

        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">

            <form method="GET">

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-end">

                    {{-- DARI TANGGAL --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Dari Tanggal
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="field-input">
                    </div>

                    {{-- SAMPAI TANGGAL --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Sampai Tanggal
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            class="field-input">
                    </div>

                    {{-- STATUS --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Status
                        </label>

                        <select
                            name="status"
                            class="field-select">

                            <option value="">Semua</option>

                            <option value="hadir" @selected(request('status') == 'hadir')>
                                Hadir
                            </option>

                            <option value="izin" @selected(request('status') == 'izin')>
                                Izin
                            </option>

                            <option value="sakit" @selected(request('status') == 'sakit')>
                                Sakit
                            </option>

                            <option value="alfa" @selected(request('status') == 'alfa')>
                                Tidak Hadir
                            </option>

                        </select>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="flex items-end gap-2 h-full">

                        <button
                            type="submit"
                            class="inline-flex h-[54px] flex-1 items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl hover:scale-[1.01]">

                            <i class="fas fa-search mr-2"></i>
                            Filter

                        </button>

                        @if(request()->filled('date_from') || request()->filled('date_to') || request()->filled('status'))

                            <a
                                href="{{ route('industri.attendance.show', $intern->id) }}"
                                class="inline-flex h-[54px] w-[54px] items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 text-slate-700 transition hover:bg-slate-200">

                                <i class="fas fa-rotate-left"></i>

                            </a>

                        @endif

                    </div>

                </div>

            </form>

        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600">

                        <tr>

                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                Tanggal
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                Status
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                Check In
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                Check Out
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">
                                Foto
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($attendances as $a)

                            <tr class="hover:bg-blue-50 transition">

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ \Carbon\Carbon::parse($a->date)->translatedFormat('d/m/y') }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">

                                    <span
                                        class="inline-flex px-3 py-1 rounded-full text-xs font-bold

                                        @if ($a->status == 'hadir')
                                            bg-green-100 text-green-700
                                        @elseif($a->status == 'izin')
                                            bg-yellow-100 text-yellow-700
                                        @elseif($a->status == 'sakit')
                                            bg-orange-100 text-orange-700
                                        @else
                                            bg-red-100 text-red-700
                                        @endif">

                                        @if ($a->status == 'alfa')
                                            Tidak Hadir
                                        @else
                                            {{ ucfirst($a->status) }}
                                        @endif

                                    </span>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ $a->check_in
                                        ? \Carbon\Carbon::parse($a->check_in)->format('H:i')
                                        : '-' }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    {{ $a->check_out
                                        ? \Carbon\Carbon::parse($a->check_out)->format('H:i')
                                        : '-' }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap flex justify-center">

                                    @if ($a->photo_path)

                                        @php
                                            $photoUrl = route('industri.attendance.photo', [
                                                'filename' => basename($a->photo_path),
                                            ]);
                                        @endphp

                                        <img
                                            src="{{ $photoUrl }}"
                                            onclick="window.open('{{ $photoUrl }}', '_blank')"
                                            class="w-14 h-14 rounded-xl object-cover border cursor-pointer hover:scale-105 transition" loading="lazy" decoding="async">

                                    @else

                                        <span class="text-slate-400">
                                            -
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">

                                    Tidak ada riwayat absensi

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- PAGINATION --}}
        <div>
            {{ $attendances->links() }}
        </div>

    </div>

</div>

@endsection