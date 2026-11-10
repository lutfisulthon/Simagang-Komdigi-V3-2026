@extends('layouts.app')

@section('title', 'Logbook - Sistem Magang')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/pages/intern-logbook-index.css') }}?v={{ @filemtime(public_path('css/pages/intern-logbook-index.css')) }}">
    @endpush

    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="hero-strip shadow-xl a1 mb-6">
                <div class="relative z-10 px-6 py-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold leading-tight text-white mb-1">Logbook Harian</h1>
                            <p class="text-blue-200">Catat dan kelola aktivitas harianmu</p>
                        </div>
                        @if ($cekaktif)
                            <a href="{{ route('intern.logbook.create') }}" class="cta-btn">
                                <i class="fas fa-plus"></i>
                                Tambah Logbook
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics Cards (kept original logic, restyled) -->
            @if ($logbooks->count() > 0)
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 mt-6">
                    <div class="stat-tile" style="--stat-color:#3b82f6;">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">Total Logbook</p>
                                <p class="text-2xl font-extrabold mt-2">{{ $totalLogbooks ?? $logbooks->total() }}</p>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-tile" style="--stat-color:#8b5cf6;">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">LogBook Bulan Ini</p>
                                <p class="text-2xl font-extrabold mt-2">
                                    {{ $thisMonthCount ?? $logbooks->where('date', '>=', now()->startOfMonth())->count() }}
                                </p>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6);">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-tile col-span-2 lg:col-span-1" style="--stat-color:#ef4444;">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">Logbook yang harus diselesaikan</p>
                                <p class="text-2xl font-extrabold mt-2">
                                    {{ $belumDikerjakan ?? 0 }}</p>
                            </div>
                            <div class="stat-icon" style="background:linear-gradient(135deg,#dc2626,#ef4444);">
                                <i class="fas fa-hourglass-end"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Logbook Table -->
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden mt-8">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3"></i>
                        Data Logbook
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
                                        Aktivitas</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Foto</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($logbooks as $logbook)
                                    <tr class="{{ ($logbook->approval_status ?? '') === 'approved' ? 'bg-emerald-50' : 'hover:bg-blue-50' }} transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center">
                                                <span
                                                    class="text-sm font-medium text-gray-900">{{ $logbook->date->format('d/m/y') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-left">
                                            <div class="text-sm text-gray-900 max-w-md">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-1">{{ Str::limit($logbook->activity, 150) }}</div>
                                                    @if (($logbook->approval_status ?? '') === 'approved')
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Disetujui</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($logbook->photo_path)
                                                @php $filename = basename($logbook->photo_path); @endphp
                                                @can('view', $logbook)
                                                    <img src="{{ route('intern.logbook.photo', $filename) }}"
                                                        alt="Logbook photo"
                                                        class="w-12 h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm mx-auto"
                                                        onclick="window.open('{{ route('intern.logbook.photo', $filename) }}', '_blank')"
                                                        title="Klik untuk melihat full size" loading="lazy" decoding="async">
                                                @else
                                                    <span class="text-gray-400">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex gap-2 justify-center">
                                                <a href="{{ route('intern.logbook.show', $logbook) }}"
                                                    class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200 group"
                                                    title="Detail">
                                                    <svg class="w-5 h-5 text-gray-700 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                                                        <circle cx="12" cy="12" r="2.5" />
                                                    </svg>
                                                </a>
                                                <a href="{{ route('intern.logbook.edit', $logbook) }}"
                                                    class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 hover:bg-blue-200 rounded-lg transition-all duration-200 group"
                                                    title="Edit">
                                                    <svg class="w-5 h-5 text-blue-600 group-hover:scale-110 transition-transform"
                                                        fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                                    </svg>
                                                </a>
                                                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { url: '{{ route('intern.logbook.destroy', $logbook) }}' } }))"
                                                    class="inline-flex items-center justify-center w-10 h-10 bg-red-100 hover:bg-red-200 rounded-lg transition-all duration-200 group"
                                                    title="Hapus">
                                                    <svg class="w-5 h-5 text-red-600 group-hover:scale-110 transition-transform"
                                                        fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-book text-5xl mb-3 text-gray-300"></i>
                                                <p class="text-lg font-medium">Belum ada logbook.</p>
                                                <p class="text-sm mt-2">Mulai dengan membuat logbook pertama.</p>
                                                @if ($cekaktif)
                                                    <a href="{{ route('intern.logbook.create') }}"
                                                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300">
                                                        <i class="fas fa-plus mr-2"></i>Tambah Logbook
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
                    @if ($logbooks->count() > 0)
                        <div class="mt-6">{{ $logbooks->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ showDeleteModal: false, deleteUrl: '' }" @open-delete-modal.window="showDeleteModal = true; deleteUrl = $event.detail.url">
        <!-- Modal Backdrop -->
        <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" x-transition.opacity>
            <!-- Modal Content -->
            <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all" x-show="showDeleteModal" x-transition.scale.origin.bottom>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-center text-gray-600 mb-6">Apakah Anda yakin ingin menghapus logbook ini? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <form :action="deleteUrl" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-2">
                            <i class="fas fa-trash"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.body.classList.add('page-logbook');
        </script>
    @endpush

@endsection
