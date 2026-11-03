@extends('layouts.app')

@section('title', 'Detail Anak Magang - Sistem Magang')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-blue-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                Detail Anak Magang
            </h1>
            <p class="text-gray-600">Informasi lengkap dan aktivitas anak magang</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden mb-6">
            <div class="p-8">
                <div class="flex flex-col md:flex-row items-start space-y-6 md:space-y-0 md:space-x-8">
                    <!-- Photo -->
                    <div class="flex-shrink-0">
                        @if($intern->photo_path)
                            <img src="{{ url('storage/'.$intern->photo_path) }}" 
                                 class="w-32 h-32 object-cover rounded-2xl border-4 border-blue-200 shadow-lg" 
                                 alt="{{ $intern->name }}" />
                        @else
                            <div class="w-32 h-32 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white shadow-lg border-4 border-blue-200">
                                <i class="fas fa-user text-5xl"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex-1">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $intern->name }}</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-university text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Institusi</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $intern->institution }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-graduation-cap text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Jenjang</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $intern->education_level }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-book-open text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Jurusan</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $intern->major ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-phone text-yellow-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Nomor Telepon</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $intern->phone ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-calendar-alt text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Periode</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $intern->start_date->format('d M Y') }} - {{ $intern->end_date->format('d M Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-{{ $intern->is_active ? 'green' : 'red' }}-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-circle text-{{ $intern->is_active ? 'green' : 'red' }}-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $intern->is_active ? 'Aktif' : 'Tidak aktif' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 mb-1">Total Absensi</p>
                                <h3 class="text-3xl font-bold text-blue-900">{{ $intern->attendances()->count() }}</h3>
                            </div>
                            <div class="w-14 h-14 bg-blue-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-check text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 mb-1">Total Logbook</p>
                                <h3 class="text-3xl font-bold text-green-900">{{ $intern->logbooks()->count() }}</h3>
                            </div>
                            <div class="w-14 h-14 bg-green-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-book text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600 mb-1">Laporan Akhir</p>
                                <h3 class="text-3xl font-bold text-purple-900">{{ $intern->finalReport ? '1' : '0' }}</h3>
                            </div>
                            <div class="w-14 h-14 bg-purple-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-file-alt text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tugas di Workspace (hanya bila modul Workspace aktif) --}}
        <x-workspace.intern-tasks :intern-id="$intern->id" class="mb-6" />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-md border border-blue-100 overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-clipboard-check mr-3"></i>
                        Absensi Terbaru
                    </h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="px-4 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">In</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Out</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($intern->attendances as $a)
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($a->date)->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize
                                                @if($a->status == 'hadir') bg-green-100 text-green-800
                                                @elseif($a->status == 'izin') bg-yellow-100 text-yellow-800
                                                @elseif($a->status == 'sakit') bg-red-100 text-red-800
                                                @else bg-gray-200 text-gray-800
                                                @endif">
                                                {{ $a->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                                                <p class="text-sm">Tidak ada data.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-green-100 overflow-hidden">
                <div class="bg-green-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-book mr-3"></i>
                        Logbook Terbaru
                    </h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-green-50">
                                    <th class="px-4 py-3 text-left text-xs font-bold text-green-900 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-green-900 uppercase tracking-wider">Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($intern->logbooks as $l)
                                    <tr class="hover:bg-green-50 transition-colors duration-150">
                                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($l->date)->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $l->activity }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-6 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                                                <p class="text-sm">Tidak ada data.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md border border-purple-100 overflow-hidden">
            <div class="bg-purple-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-file-alt mr-3"></i>
                    Laporan Akhir
                </h2>
            </div>
            <div class="p-6">
                @if($intern->finalReport)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-file-pdf text-purple-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">File</p>
                                <a class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline break-all" 
                                   target="_blank" 
                                   href="{{ url('storage/'.$intern->finalReport->file_path) }}">
                                    {{ $intern->finalReport->file_name }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Status</p>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize
                                    @if($intern->finalReport->status == 'approved') bg-green-100 text-green-800
                                    @elseif($intern->finalReport->status == 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $intern->finalReport->status }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-star text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Nilai</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $intern->finalReport->grade ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Perlu Revisi</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $intern->finalReport->needs_revision ? 'Ya' : 'Tidak' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-clock text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Dikirim</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $intern->finalReport->submitted_at ? \Carbon\Carbon::parse($intern->finalReport->submitted_at)->format('d M Y H:i') : '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start md:col-span-2 lg:col-span-1">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-sticky-note text-gray-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Catatan Admin</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $intern->finalReport->admin_note ?? '-' }}</p>
                            </div>
                        </div>

                        @if($intern->finalReport->include_task_recap)
                            <div class="flex items-start md:col-span-2 lg:col-span-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-tasks text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Rekap Tugas Workspace</p>
                                    <a href="{{ route('workspace.recap', ['intern_id' => $intern->id]) }}" target="_blank" rel="noopener"
                                       class="text-sm font-semibold text-indigo-600 hover:underline">Lihat rekap tugas selesai</a>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                        <i class="fas fa-file-excel text-5xl mb-3 text-gray-300"></i>
                        <p class="text-sm font-medium">Belum ada laporan akhir.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection