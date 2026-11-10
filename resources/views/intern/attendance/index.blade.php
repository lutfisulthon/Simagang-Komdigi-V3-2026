@extends('layouts.app')

@section('title', 'Absensi - Sistem Magang')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/intern-attendance-index.css') }}?v={{ @filemtime(public_path('css/pages/intern-attendance-index.css')) }}">
@endpush

    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="hero-strip shadow-xl a1 mb-6">
                <div class="relative z-10 px-6 py-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold leading-tight text-white mb-1">Riwayat Absensi</h1>
                            <p class="text-blue-200">Pantau dan kelola absensi harianmu</p>
                        </div>
                        @if ($cekaktif)
                            <a href="{{ route('intern.attendance.create') }}" class="cta-btn">
                                <i class="fas fa-plus"></i>
                                Absensi Baru
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Statistics Cards (Optional - untuk informasi tambahan) -->
            @if ($attendances->count() > 0 || $todayVirtualAbsent)
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mt-6 stat-grid">
                    <!-- Total Hadir -->
                    <div class="stat-tile" style="--stat-color:#22c55e;">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Kehadiran</p>
                            <p class="stat-value">{{ $totalHadir }}</p>
                        </div>
                    </div>

                    <!-- Total Izin -->
                    <div class="stat-tile" style="--stat-color:#f59e0b;">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Izin</p>
                            <p class="stat-value">{{ $totalIzin }}</p>
                        </div>
                    </div>

                    <!-- Total Sakit -->
                    <div class="stat-tile" style="--stat-color:#ef5350;">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#ef5350,#e53935);">
                            <i class="fas fa-calendar-minus"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Sakit</p>
                            <p class="stat-value">{{ $totalSakit }}</p>
                        </div>
                    </div>

                    <!-- Total Tidak Hadir -->
                    <div class="stat-tile" style="--stat-color:#f97316;">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#f97316,#ea580c);">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Tidak Hadir</p>
                            <p class="stat-value">{{ $totalTidakHadir + ($todayVirtualAbsent ? 1 : 0) }}</p>
                        </div>
                    </div>

                </div>
            @endif

            <!-- Attendance Table -->
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden mt-8">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-clipboard-list mr-3"></i>
                        Riwayat Absensi
                    </h2>
                </div>
                <div class="p-6">
                    <div
                        class="overflow-x-auto overflow-y-auto max-h-[500px] scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tl-lg">
                                        Tanggal</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Check In</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Foto Check In</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Check Out</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Foto Check Out</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Keterangan</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">
                                        Status Dokumen</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                {{-- Baris virtual hari ini: belum absen di hari kerja --}}
                                @if ($cekaktif && $todayVirtualAbsent)
                                    <tr class="bg-red-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ \Carbon\Carbon::parse($todayWita)->format('d/m/Y') }}
                                            </span>
                                            <span class="ml-1 text-xs text-gray-400">(Hari ini)</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Tidak Hadir
                                            </span>
                                        </td>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-400 italic">
                                            Belum melakukan absensi hari ini
                                        </td>
                                    </tr>
                                @endif
                                @forelse($attendances as $attendance)
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $attendance->date->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if ($attendance->status == 'hadir') bg-green-100 text-green-800
                                            @elseif($attendance->status == 'izin') bg-yellow-100 text-yellow-800
                                            @elseif($attendance->status == 'sakit') bg-orange-100 text-orange-800
                                            @else bg-red-100 text-red-800 @endif">
                                                @if ($attendance->status == 'alfa')
                                                    Tidak Hadir
                                                @else
                                                    {{ ucfirst($attendance->status) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                            @if ($attendance->check_in)
                                                <div class="text-sm text-gray-600">
                                                    {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($attendance->photo_path)
                                                @php
                                                    $filename = basename($attendance->photo_path);
                                                    $photoUrl = route('intern.attendance.photo', [
                                                        'filename' => $filename,
                                                    ]);
                                                @endphp
                                                <img src="{{ $photoUrl }}" alt="Check In"
                                                    class="w-12 h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm mx-auto"
                                                    onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                    title="Klik untuk melihat full size" loading="lazy" decoding="async">
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                            @if ($attendance->check_out)
                                                <div class="text-sm text-gray-600">
                                                    {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($attendance->photo_checkout)
                                                @php
                                                    $filename = basename($attendance->photo_checkout);
                                                    $photoUrl = route('intern.attendance.photo', [
                                                        'filename' => $filename,
                                                    ]);
                                                @endphp
                                                <img src="{{ $photoUrl }}" alt="Check Out"
                                                    class="w-12 h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm mx-auto"
                                                    onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                    title="Klik untuk melihat full size" loading="lazy" decoding="async">
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 text-center">
                                            @if ($attendance->note)
                                                <div class="max-w-xs truncate mx-auto text-center"
                                                    title="{{ $attendance->note }}">
                                                    {{ $attendance->note }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($attendance->document_status)
                                                <span
                                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if ($attendance->document_status == 'approved') bg-green-100 text-green-800
                                                @elseif($attendance->document_status == 'rejected') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                    @if ($attendance->document_status == 'approved')
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                    @elseif($attendance->document_status == 'rejected')
                                                        <i class="fas fa-times-circle mr-1"></i>
                                                    @else
                                                        <i class="fas fa-clock mr-1"></i>
                                                    @endif
                                                    {{ ucfirst($attendance->document_status) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-inbox text-5xl mb-3 text-gray-300"></i>
                                                <p class="text-lg font-medium">Belum ada data absensi.</p>
                                                <p class="text-sm mt-2">Mulai dengan membuat absensi baru.</p>
                                                @if ($cekaktif)
                                                    <a href="{{ route('intern.attendance.create') }}"
                                                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300">
                                                        <i class="fas fa-plus mr-2"></i>Buat Absensi
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($attendances->count() > 0)
                        <div class="mt-6">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>document.body.classList.add('page-attendance');</script>
@endpush

@endsection
