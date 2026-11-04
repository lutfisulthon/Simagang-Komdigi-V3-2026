@extends('layouts.app')

@section('title', 'Monitoring Bulanan - Sistem Magang')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&display=swap');

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .mono {
            font-family: 'DM Mono', monospace;
        }

        .dash-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        }

        .hero-strip {
            background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 50%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
            color: #fff;
        }

        .hero-strip::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: 30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
            border: 1px solid #dbeafe;
        }

        .panel-header {
            background: linear-gradient(110deg, #1e3a8a 0%, #3b4fd8 100%);
            color: #fff;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 14px;
        }

        .stat-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;          /* ← garis ikut terbawa */
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(59, 79, 216, 0.16);
        }

        .table-main {
            min-width: 980px;
        }

        .chart-wrap {
            position: relative;
            height: 320px;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.6rem;
                line-height: 1.3;
            }

            .panel-body,
            .filter-body {
                padding: 1rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                width: 100%;
            }

            .table-main th,
            .table-main td {
                padding: 0.7rem 0.65rem;
                font-size: 0.75rem;
            }

            .chart-wrap {
                height: 260px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7">
                    <h1 class="hero-title text-3xl sm:text-4xl font-bold leading-tight mb-2">Monitoring Bulanan</h1>
                    <p class="text-blue-100">Pantau dan kelola data peserta magang secara bulanan</p>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="panel mb-8">
                <div class="panel-header px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-filter mr-3"></i>
                        Filter Data
                    </h2>
                </div>
                <div class="filter-body p-6">
                    <p class="section-label">Filter Monitoring</p>
                    <form method="GET" action="{{ route('admin.monitoring.index') }}">
                        <div class="filter-grid grid grid-cols-1 md:grid-cols-5 gap-4 items-end">

                            <!-- Bulan -->
                            <div>
                                <label for="month" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Bulan</label>
                                <input type="month" name="month" id="month" value="{{ $selectedMonthYm }}"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status Peserta Magang</label>
                                <select name="status" id="status"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <option value="all">Semua</option>
                                    <option value="masuk"          {{ $selectedStatus === 'masuk'          ? 'selected' : '' }}>Masuk</option>
                                    <option value="aktif"          {{ $selectedStatus === 'aktif'          ? 'selected' : '' }}>Aktif</option>
                                    <option value="akan_pelepasan" {{ $selectedStatus === 'akan_pelepasan' ? 'selected' : '' }}>Akan Pelepasan</option>
                                </select>
                            </div>

                            <!-- Mentor -->
                            <div>
                                <label for="mentor_id" class="block text-sm font-semibold text-gray-700 mb-2">Mentor</label>
                                <select name="mentor_id" id="mentor_id"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <option value="">Semua Mentor</option>
                                    @foreach ($mentors as $mentor)
                                        <option value="{{ $mentor->id }}" {{ $selectedMentor == $mentor->id ? 'selected' : '' }}>
                                            {{ $mentor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Kampus -->
                            <div>
                                <label for="institution" class="block text-sm font-semibold text-gray-700 mb-2">Kampus</label>
                                <select name="institution" id="institution"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                    <option value="">Semua Kampus</option>
                                    @foreach ($institutions as $inst)
                                        <option value="{{ $inst }}" {{ request('institution') === $inst ? 'selected' : '' }}>
                                            {{ $inst }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="filter-actions flex gap-3">
                                <button type="submit"
                                    class="flex-1 h-[42px] bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                @if (request()->anyFilled(['month', 'status', 'mentor_id', 'institution']))
                                    <a href="{{ route('admin.monitoring.index') }}"
                                        class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded-lg transition duration-200">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!-- Card 1: Masuk -->
                <div class="stat-card group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">
                                    Masuk {{ \Carbon\Carbon::parse($selectedMonth)->locale('id')->translatedFormat('F Y') }}
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900">{{ $masukCount }}</h3>
                            </div>
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-plus text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                </div>

                <!-- Card 2: Keluar -->
                <div class="stat-card group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">
                                    Keluar {{ \Carbon\Carbon::parse($selectedMonth)->locale('id')->translatedFormat('F Y') }}
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900">{{ $keluarCount }}</h3>
                            </div>
                            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-minus text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-red-500 to-rose-600"></div>
                </div>

                <!-- Card 3: Total Aktif -->
                <div class="stat-card group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">
                                    Total Aktif {{ \Carbon\Carbon::parse($selectedMonth)->locale('id')->translatedFormat('F Y') }}
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900">{{ $aktifCount }}</h3>
                            </div>
                            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-users text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-green-500 to-emerald-600"></div>
                </div>

                <!-- Card 4: Pelepasan -->
                <div class="stat-card group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-1">
                                    Pelepasan {{ \Carbon\Carbon::parse($selectedMonth)->locale('id')->translatedFormat('F Y') }}
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900">{{ $internsPelepasan->count() }}</h3>
                            </div>
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-graduation-cap text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-orange-500 to-orange-600"></div>
                </div>

            </div>

            <!-- Filtered Results Table -->
            {{-- Workspace: rekap tugas per tim --}}
            <x-workspace.team-recap class="mb-8" />

            <div class="panel mb-8">
                <div class="panel-header px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            @if ($selectedStatus === 'masuk')
                                <i class="fas fa-user-plus mr-3"></i>
                            @elseif($selectedStatus === 'aktif')
                                <i class="fas fa-users mr-3"></i>
                            @elseif($selectedStatus === 'akan_pelepasan')
                                <i class="fas fa-clock mr-3"></i>
                            @elseif($selectedStatus === 'pelepasan')
                                <i class="fas fa-graduation-cap mr-3"></i>
                            @else
                                <i class="fas fa-search mr-3"></i>
                            @endif
                            Peserta Magang
                            @if ($selectedStatus !== 'all')
                                <span class="ml-2 text-sm font-normal text-blue-100">
                                    (Status:
                                    @if ($selectedStatus === 'masuk') Masuk
                                    @elseif($selectedStatus === 'aktif') Aktif
                                    @elseif($selectedStatus === 'akan_pelepasan') Akan Pelepasan
                                    @elseif($selectedStatus === 'pelepasan') Pelepasan
                                    @endif
                                    )
                                </span>
                            @endif
                            @if ($selectedMentor)
                                <span class="ml-2 text-sm font-normal text-blue-100">(Mentor: Terpilih)</span>
                            @endif
                        </h2>

                        <form method="GET" action="{{ route('admin.monitoring.export') }}">
                            <input type="hidden" name="month"        value="{{ request('month') }}">
                            <input type="hidden" name="status"       value="{{ request('status') }}">
                            <input type="hidden" name="mentor_id"    value="{{ request('mentor_id') }}">
                            <input type="hidden" name="institution"  value="{{ request('institution') }}">
                            <input type="hidden" name="final_project" value="{{ request('final_project') }}">
                            <button type="submit"
                                class="bg-white text-blue-600 hover:bg-blue-50 font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 whitespace-nowrap">
                                <i class="fas fa-download mr-2"></i>Export Excel
                            </button>
                        </form>
                    </div>
                </div>
                <div class="panel-body p-6">
                    <div class="overflow-x-auto">
                        <table class="table-main min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tl-lg">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Kampus</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Mentor</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Tugas</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Tgl Mulai</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Tgl Rencana Pelepasan</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @if ($activeInterns->count() > 0)
                                    @foreach ($activeInterns as $index => $intern)
                                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $activeInterns->firstItem() + $index }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $intern->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($intern->is_active)
                                                    @if (\Carbon\Carbon::now()->toDateString() <= $intern->end_date &&
                                                         $intern->end_date <= \Carbon\Carbon::now()->addDays(30)->toDateString())
                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Akan Pelepasan
                                                        </span>
                                                    @else
                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Aktif
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Pelepasan
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $intern->institution }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $intern->mentor ? $intern->mentor->name : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                @php($progress = $taskProgress[$intern->id] ?? null)
                                                @if ($progress)
                                                    <span class="font-semibold text-gray-900">{{ $progress['done'] }}/{{ $progress['total'] }}</span> selesai
                                                    @if ($progress['overdue'] > 0)
                                                        <span class="ml-1 px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $progress['overdue'] }} terlambat</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ \Carbon\Carbon::parse($intern->start_date)->locale('id')->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $intern->end_date ? \Carbon\Carbon::parse($intern->end_date)->locale('id')->translatedFormat('d F Y') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2 flex gap-2">
                                                <a href="{{ route('admin.intern.show', $intern->id) }}"
                                                    class="text-blue-600 hover:text-blue-900 inline-block transition-colors"
                                                    title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($intern->is_active)
                                                    <button type="button"
                                                        onclick="window.dispatchEvent(new CustomEvent('open-release-modal', { detail: { id: {{ $intern->id }}, name: '{{ addslashes($intern->name) }}' } }))"
                                                        class="text-orange-600 hover:text-orange-900 inline-block transition-colors"
                                                        title="Tandai sebagai pelepasan">
                                                        <i class="fas fa-graduation-cap"></i>
                                                    </button>
                                                    <form id="form-release-{{ $intern->id }}"
                                                        action="{{ route('admin.monitoring.mark-released', $intern->id) }}"
                                                        method="POST" class="hidden">
                                                        @csrf
                                                    </form>
                                                @else
                                                    <button type="button"
                                                        onclick="window.dispatchEvent(new CustomEvent('open-active-modal', { detail: { id: {{ $intern->id }}, name: '{{ addslashes($intern->name) }}' } }))"
                                                        class="text-green-600 hover:text-green-900 inline-block transition-colors"
                                                        title="Kembalikan menjadi aktif">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <form id="form-active-{{ $intern->id }}"
                                                        action="{{ route('admin.monitoring.mark-active', $intern->id) }}"
                                                        method="POST" class="hidden">
                                                        @csrf
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-inbox text-5xl mb-3 text-gray-300"></i>
                                                <p class="text-lg">Tidak ada data yang sesuai dengan filter Anda.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if ($activeInterns->count() > 0)
                        <div class="mt-6">{{ $activeInterns->links() }}</div>
                    @endif
                </div>
            </div>

            <!-- Data Alumni -->
            <div class="panel mb-8">
                <div class="bg-gradient-to-r from-gray-600 to-gray-800 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-user-graduate mr-3"></i>
                            Data Alumni
                        </h2>
                        <form method="GET" action="{{ route('admin.monitoring.export') }}">
                            <input type="hidden" name="month"       value="{{ request('month') }}">
                            <input type="hidden" name="status"      value="pelepasan">
                            <input type="hidden" name="mentor_id"   value="{{ request('mentor_id') }}">
                            <input type="hidden" name="institution" value="{{ request('institution') }}">
                            <button type="submit"
                                class="bg-white text-gray-800 hover:bg-gray-100 font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 whitespace-nowrap">
                                <i class="fas fa-download mr-2"></i>Export Alumni
                            </button>
                        </form>
                    </div>
                </div>
                <div class="panel-body p-6">
                    <div class="overflow-x-auto">
                        <table class="table-main min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider rounded-tl-lg">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Kampus</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Mentor</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Tgl Mulai</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Tgl Rencana Pelepasan</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider rounded-tr-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @if ($alumniInterns->count() > 0)
                                    @foreach ($alumniInterns as $index => $intern)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $alumniInterns->firstItem() + $index }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $intern->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ $intern->institution }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $intern->mentor ? $intern->mentor->name : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ \Carbon\Carbon::parse($intern->start_date)->locale('id')->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $intern->end_date ? \Carbon\Carbon::parse($intern->end_date)->locale('id')->translatedFormat('d F Y') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2 flex gap-2">
                                                <a href="{{ route('admin.intern.show', $intern->id) }}"
                                                    class="text-blue-600 hover:text-blue-900 inline-block transition-colors"
                                                    title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button"
                                                    onclick="window.dispatchEvent(new CustomEvent('open-active-modal', { detail: { id: {{ $intern->id }}, name: '{{ addslashes($intern->name) }}' } }))"
                                                    class="text-green-600 hover:text-green-900 inline-block transition-colors"
                                                    title="Kembalikan menjadi aktif">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <form id="form-active-{{ $intern->id }}"
                                                    action="{{ route('admin.monitoring.mark-active', $intern->id) }}"
                                                    method="POST" class="hidden">
                                                    @csrf
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-box-open text-5xl mb-3 text-gray-300"></i>
                                                <p class="text-lg">Belum ada alumni untuk filter saat ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if ($alumniInterns->count() > 0)
                        <div class="mt-6">{{ $alumniInterns->links() }}</div>
                    @endif
                </div>
            </div>

            <!-- Chart Section -->
            <div class="panel mb-8">
                <div class="panel-header px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-chart-line mr-3"></i>
                        Statistik 12 Bulan Terakhir
                    </h2>
                </div>
                <div class="panel-body p-6">
                    <div class="chart-wrap">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Institutions Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Top Institutions All Time -->
                <div class="panel">
                    <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-trophy mr-3"></i>
                            Top 10 Institusi (Semua Waktu)
                        </h2>
                    </div>
                    <div class="p-6">
                        @if ($topInstitutions->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topInstitutions as $index => $inst)
                                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl hover:shadow-md transition-all duration-300 border-2 border-yellow-100">
                                        <div class="flex items-center">
                                            <div class="relative">
                                                <span class="w-10 h-10 rounded-full bg-gradient-to-br
                                                    @if ($index == 0) from-yellow-400 to-yellow-600
                                                    @elseif($index == 1) from-gray-300 to-gray-500
                                                    @elseif($index == 2) from-orange-400 to-orange-600
                                                    @else from-orange-100 to-orange-500 @endif
                                                    text-white flex items-center justify-center font-bold text-lg shadow-lg mr-4">
                                                    {{ $index + 1 }}
                                                </span>
                                                @if ($index < 3)
                                                    <i class="fas fa-crown absolute -top-2 -right-1 text-yellow-500 text-xs"></i>
                                                @endif
                                            </div>
                                            <span class="font-semibold text-gray-900">{{ $inst->institution }}</span>
                                        </div>
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                            <i class="fas fa-users mr-1"></i>{{ $inst->total }} orang
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                                <i class="fas fa-chart-line text-5xl mb-3 text-gray-300"></i>
                                <p class="text-sm">Belum ada data.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Top Institutions This Month -->
                <div class="panel">
                    <div class="panel-header px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-star mr-3"></i>
                            Top Institusi
                            {{ \Carbon\Carbon::parse($selectedMonth)->locale('id')->translatedFormat('F Y') }}
                        </h2>
                    </div>
                    <div class="p-6">
                        @if ($topInstitutionsThisMonth->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topInstitutionsThisMonth as $index => $inst)
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl hover:shadow-md transition-all duration-300 border-2 border-blue-100">
                                        <div class="flex items-center">
                                            <span class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-lg mr-4">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="font-semibold text-gray-900">{{ $inst->institution }}</span>
                                        </div>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                            <i class="fas fa-users mr-1"></i>{{ $inst->total }} orang
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                                <i class="fas fa-chart-line text-5xl mb-3 text-gray-300"></i>
                                <p class="text-sm">Tidak ada data untuk bulan ini.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyData = @json($monthlyStats);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(item => item.month),
                    datasets: [{
                            label: 'Masuk',
                            data: monthlyData.map(item => item.masuk),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Keluar',
                            data: monthlyData.map(item => item.keluar),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Aktif',
                            data: monthlyData.map(item => item.aktif),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                            text: 'Tren Masuk, Keluar, dan Aktif Per Bulan'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });


            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => { alert.style.display = 'none'; }, 500);
                }, 5000);
            });
        </script>
    @endpush

    {{-- ── RELEASE MODAL ── --}}
    <div x-data="{ showReleaseModal: false, releaseId: '', releaseName: '' }"
        @open-release-modal.window="showReleaseModal = true; releaseId = $event.detail.id; releaseName = $event.detail.name">
        <div x-show="showReleaseModal" style="display:none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm"
            x-transition.opacity>
            <div @click.away="showReleaseModal = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                x-show="showReleaseModal" x-transition.scale.origin.bottom>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full mb-4">
                    <i class="fas fa-graduation-cap text-orange-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Pelepasan</h3>
                <p class="text-center text-gray-600 mb-6">
                    Yakin ingin menandai <strong x-text="releaseName"></strong> sebagai pelepasan?
                </p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showReleaseModal = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="button"
                            @click="document.getElementById('form-release-' + releaseId).submit()"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-graduation-cap"></i> Iya
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ACTIVE MODAL ── --}}
    <div x-data="{ showActiveModal: false, activeId: '', activeName: '' }"
        @open-active-modal.window="showActiveModal = true; activeId = $event.detail.id; activeName = $event.detail.name">
        <div x-show="showActiveModal" style="display:none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm"
            x-transition.opacity>
            <div @click.away="showActiveModal = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                x-show="showActiveModal" x-transition.scale.origin.bottom>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full mb-4">
                    <i class="fas fa-undo text-green-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Kembalikan ke Aktif</h3>
                <p class="text-center text-gray-600 mb-6">
                    Yakin ingin mengembalikan <strong x-text="activeName"></strong> menjadi aktif?
                </p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showActiveModal = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="button"
                            @click="document.getElementById('form-active-' + activeId).submit()"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-undo"></i> Ya, Aktifkan
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection