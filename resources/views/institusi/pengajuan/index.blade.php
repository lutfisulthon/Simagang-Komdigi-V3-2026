@extends('layouts.app')

@section('title', 'Pengajuan Magang - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-pengajuan-index.css') }}?v={{ @filemtime(public_path('css/pages/institusi-pengajuan-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Hero Strip --}}
            <div class="hero-strip px-5 sm:px-8 py-6 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Pengajuan Magang</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Semua pengajuan magang bisa dipantau dan dikelola dengan lebih mudah.</p>
                    </div>
                </div>
            </div>

            {{-- Stat Tiles --}}
            @php
                $stats = [
                    ['label' => 'Total Pengajuan', 'value' => $totalPengajuan,   'tile' => 'tile-blue',   'iconBg' => 'linear-gradient(135deg,#3b82f6,#6366f1)', 'icon' => 'fa-calendar-check'],
                    ['label' => 'Disetujui',        'value' => $pengajuanApproved,'tile' => 'tile-green',  'iconBg' => 'linear-gradient(135deg,#22c55e,#10b981)', 'icon' => 'fa-circle-check'],
                    ['label' => 'Diproses',         'value' => $pengajuanPending, 'tile' => 'tile-yellow', 'iconBg' => 'linear-gradient(135deg,#fef3c7,#facc15)', 'icon' => 'fa-hourglass-half'],
                    ['label' => 'Revisi',           'value' => $pengajuanRevised, 'tile' => 'tile-orange', 'iconBg' => 'linear-gradient(135deg,#fb923c,#f97316)', 'icon' => 'fa-pen-to-square'],
                    ['label' => 'Ditolak',          'value' => $pengajuanRejected,'tile' => 'tile-red',    'iconBg' => 'linear-gradient(135deg,#f43f5e,#e11d48)', 'icon' => 'fa-circle-xmark'],
                ];
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 sm:gap-6">
                @foreach ($stats as $stat)
                    <div class="stat-tile {{ $stat['tile'] }}">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-gray-500 leading-tight">{{ $stat['label'] }}</p>
                            <div class="tile-icon" style="background:{{ $stat['iconBg'] }};">
                                <i class="fas {{ $stat['icon'] }}"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Filter --}}
            <div class="panel">
                <div class="panel-body">
                    <form method="GET" action="{{ route('institusi.pengajuan.index') }}">
                        <div class="filter-box">
                            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">

                                <div class="xl:col-span-5">
                                    <label for="search" class="field-label">Nomor Pengajuan</label>
                                    <div class="relative">
                                        <i class="fas fa-search filter-search-icon"></i>
                                        <input type="text" name="search" id="search"
                                            value="{{ request('search') }}"
                                            placeholder="Cari nomor pengajuan..."
                                            class="field-input filter-input-icon">
                                    </div>
                                </div>

                                <div class="xl:col-span-3">
                                    <label for="status" class="field-label">Status</label>
                                    <select name="status" id="status" class="field-select">
                                        <option value="">Semua Status</option>
                                        <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                                        <option value="approved"  {{ request('status') == 'approved'  ? 'selected' : '' }}>Disetujui</option>
                                        <option value="rejected"  {{ request('status') == 'rejected'  ? 'selected' : '' }}>Ditolak</option>
                                        <option value="revised"   {{ request('status') == 'revised'   ? 'selected' : '' }}>Revisi</option>
                                    </select>
                                </div>

                                <div class="xl:col-span-2">
                                    <label for="date" class="field-label">Tanggal</label>
                                    <input type="date" name="date" id="date"
                                        value="{{ request('date') }}"
                                        class="field-input">
                                </div>

                                <div class="xl:col-span-2 flex items-end gap-2">
                                    <button type="submit"
                                        class="filter-btn flex-1 inline-flex items-center justify-center bg-gradient-to-r from-blue-600 to-indigo-600 px-5 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
                                        <i class="fas fa-search mr-2"></i>Cari
                                    </button>

                                    @if (request()->filled('search') || request()->filled('status') || request()->filled('date'))
                                        <a href="{{ route('institusi.pengajuan.index') }}"
                                            class="filter-btn inline-flex w-[52px] items-center justify-center border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                                            <i class="fas fa-rotate-left"></i>
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-list text-blue-200 text-base"></i>
                    <h2>Data Pengajuan Magang</h2>
                    <span class="ml-auto rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                        {{ $totalPengajuanFilter }} data
                    </span>
                </div>

                <div class="panel-body">
                    <div class="overflow-x-auto overflow-y-auto max-h-[500px]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tl-lg">Nomor Surat</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">Tanggal Pengajuan</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($pengajuans as $pengajuan)
                                    @php
                                        $statusClass = match ($pengajuan->status) {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'revised'  => 'bg-orange-100 text-orange-800',
                                            default    => 'bg-yellow-100 text-yellow-800',
                                        };
                                    @endphp
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-medium text-slate-700">{{ $pengajuan->no_surat }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <p class="text-sm text-slate-600">{{ $pengajuan->created_at->format('d/m/y') }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="status-pill {{ $statusClass }}">{{ ucfirst($pengajuan->status) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex gap-2 justify-center">
                                                {{-- Lihat --}}
                                                <a href="{{ route('institusi.pengajuan.show', $pengajuan->id) }}"
                                                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 text-green-700 transition-all duration-200 hover:bg-green-600 hover:text-white"
                                                    title="Lihat">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Edit --}}
                                                @if ($pengajuan->status === 'revised')
                                                    <a href="{{ route('institusi.pengajuan.edit', $pengajuan->id) }}"
                                                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 text-blue-600 transition-all duration-200 hover:bg-blue-200"
                                                        title="Edit">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                                        </svg>
                                                    </a>
                                                @else
                                                    <button type="button"
                                                        onclick="alert('Pengajuan hanya bisa diedit ketika status revisi.')"
                                                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed"
                                                        title="Edit tidak tersedia untuk status ini">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                                        </svg>
                                                    </button>
                                                @endif

                                                {{-- Hapus --}}
                                                <button type="button"
                                                    onclick="window.dispatchEvent(new CustomEvent('open-delete-modal-pengajuan', { detail: { url: '{{ route('institusi.pengajuan.destroy', $pengajuan->id) }}' } }))"
                                                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-600 transition-all duration-200 hover:bg-red-200"
                                                    title="Hapus">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
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
                                                <p class="text-lg font-medium">Belum ada pengajuan.</p>
                                                <p class="text-sm mt-2">Mulai dengan membuat pengajuan pertama Anda.</p>
                                                {{-- <a href="#"
                                                    class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300">
                                                    <i class="fas fa-plus mr-2"></i>Tambah Pengajuan
                                                </a> --}}
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
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-data="{ showDeleteModal: false, deleteUrl: '' }"
        @open-delete-modal-pengajuan.window="showDeleteModal = true; deleteUrl = $event.detail.url">

        <div x-show="showDeleteModal"
            style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm"
            x-transition.opacity>

            <div @click.away="showDeleteModal = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all"
                x-show="showDeleteModal"
                x-transition.scale.origin.bottom>

                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>

                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-center text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus pengajuan ini? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex justify-center gap-3">
                    <button type="button" @click="showDeleteModal = false"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <form :action="deleteUrl" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-2">
                            <i class="fas fa-trash"></i> Ya, Hapus
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

@endsection