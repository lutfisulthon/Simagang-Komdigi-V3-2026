 @extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/industri-pengajuan-index.css') }}?v={{ @filemtime(public_path('css/pages/industri-pengajuan-index.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7">
                    <div>
                        <h1 class="hero-title text-3xl sm:text-4xl font-bold leading-tight mb-2">
                            Pengajuan Peserta Magang
                        </h1>
                        <p class="text-blue-100">Catat dan kelola pengajuan peserta magang Anda</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards (Optional - untuk informasi tambahan) -->
            {{-- @if ($pengajuans->count() > 0) --}}
            {{-- Stats Cards --}}
            <div class="stats-shell mb-8">

                @php
                    $stats = [
                        [
                            'label' => 'Total Pengajuan',
                            'value' => $totalPengajuan,
                            'bg' => 'bg-blue-500',
                            'icon' => 'fa-folder-open',
                        ],
                        [
                            'label' => 'Disetujui',
                            'value' => $totalDiterima,
                            'bg' => 'bg-emerald-500',
                            'icon' => 'fa-check-circle',
                        ],
                        [
                            'label' => 'Menunggu Approval',
                            'value' => $totalMenunggu,
                            'bg' => 'bg-amber-500',
                            'icon' => 'fa-clock',
                        ],
                        [
                            'label' => 'Revisi',
                            'value' => $totalRevisi,
                            'bg' => 'bg-orange-500',
                            'icon' => 'fa-exclamation-circle',
                        ],
                        [
                            'label' => 'Ditolak', 
                            'value' => $totalDitolak, 
                            'bg' => 'bg-red-500', 
                            'icon' => 'fa-times-circle'
                        ],
                    ];
                @endphp

                @foreach ($stats as $stat)
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="p-4 sm:p-6 flex items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1 truncate">{{ $stat['label'] }}</p>
                                <p class="text-2xl font-black text-gray-800 truncate">{{ $stat['value'] }}</p>
                            </div>
                            <div
                                class="flex-shrink-0 w-12 h-12 sm:w-16 sm:h-16 {{ $stat['bg'] }} rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                <i class="fas {{ $stat['icon'] }} text-white text-xl sm:text-2xl"></i>
                            </div>
                        </div>
                        <div class="h-1 {{ $stat['bg'] }}"></div>
                    </div>
                @endforeach

            </div>

            <div class="panel mb-6 border-t-4 border-blue-500">
                <div class="panel-content p-6">
                    <h2 class="text-lg font-semibold text-blue-900 mb-1 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-600"></i>Filter & Pencarian
                    </h2>
                    <p class="section-label">Filter Monitoring</p>
                    <form method="GET" action="{{ route('industri.pengajuan.index') }}"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-blue-900 mb-1">Cari Nomor
                                Surat</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Cari nomor surat..."
                                class="w-full px-3 py-2 border border-blue-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-blue-900 mb-1">Filter Status</label>
                            <select name="status" id="status"
                                class="w-full px-3 py-2 border border-blue-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                                </option>
                                <option value="revised" {{ request('status') == 'revised' ? 'selected' : '' }}>Revised
                                </option>
                            </select>
                        </div>
                        <!-- status filter removed; inactive interns shown in separate table below -->
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-200 flex-1">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                            @if (request()->anyFilled(['search', 'status']))
                                <a href="{{ route('industri.pengajuan.index') }}"
                                    class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded-lg transition duration-200">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pengajuan Magang Table -->
            <div class="panel border border-blue-100">
                <div class="panel-header px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3"></i>
                        Data Pengajuan Magang
                    </h2>
                </div>
                <div class="panel-content p-6">
                    <div class="overflow-x-auto overflow-y-auto max-h-[500px]">
                        <table class="pengajuan-table min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tl-lg">
                                        Nomor Surat</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Institusi</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Tanggal Pengajuan</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($pengajuanTabel as $pengajuan)
                                    <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm text-gray-800 font-bold">
                                                {{ $pengajuan->no_surat }}
                                            </p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-building text-sm"></i>
                                                </div>
                                                <p class="text-sm text-gray-700 font-semibold">
                                                    {{ $pengajuan->institusi->nama_institusi }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <p class="text-sm text-gray-600 font-medium text-center">
                                                    {{ $pengajuan->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-left">
                                            <div class="flex flex-col space-y-1 items-center">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if ($pengajuan->status == 'approved') bg-green-100 text-green-800 
                                                @elseif($pengajuan->status == 'rejected') bg-red-100 text-red-800 
                                                @elseif($pengajuan->status == 'revised') bg-orange-100 text-orange-800 
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($pengajuan->status) }}
                                                </span>
                                                {{-- @if ($pengajuan->needs_revision)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                    Revisi
                                                </span>
                                            @endif --}}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex gap-2 justify-center">
                                                <a href="{{ route('industri.pengajuan.show', $pengajuan->id) }}"
                                                    class="inline-flex items-center justify-center w-10 h-10 bg-green-100 hover:bg-green-600 rounded-lg transition-all duration-200 group"
                                                    title="Lihat">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {{-- <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-delete-modal-pengajuan', { detail: { url: '{{ route('industri.pengajuan.destroy', $pengajuan->id) }}' } }))"
                                                    class="inline-flex items-center justify-center w-10 h-10 bg-red-100 hover:bg-red-200 rounded-lg transition-all duration-200 group"
                                                    title="Hapus">
                                                    <svg class="w-5 h-5 text-red-600 group-hover:scale-110 transition-transform"
                                                        fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                                                    </svg>
                                                </button> --}}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-book text-5xl mb-3 text-gray-300"></i>
                                                <p class="text-lg font-medium">Belum ada Pengajuan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- <!-- Pagination -->
                @if ($logbooks->count() > 0)
                    <div class="mt-6">
                        {{ $logbooks->links() }}
                    </div>
                @endif --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
                        <div x-data="{ showDeleteModal: false, deleteUrl: '' }" @open-delete-modal-pengajuan.window="showDeleteModal = true; deleteUrl = $event.detail.url">
                            <!-- Modal Backdrop -->
                            <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" x-transition.opacity>
                                <!-- Modal Content -->
                                <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all" x-show="showDeleteModal" x-transition.scale.origin.bottom>
                                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
                                    <p class="text-center text-gray-600 mb-6">Apakah Anda yakin ingin menghapus pengajuan ini? Tindakan ini tidak dapat dibatalkan.</p>
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
@endsection
