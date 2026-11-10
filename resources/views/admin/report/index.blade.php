@extends('layouts.app')

@section('title', 'Monitoring Laporan - Sistem Magang')

@push('styles')
    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
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

        .report-table {
            min-width: 820px;
        }

        .filter-action {
            min-height: 42px;
        }

        @media (max-width: 640px) {
            .hero-title {
                font-size: 1.55rem;
                line-height: 1.3;
            }

            .panel-content {
                padding: 1rem;
            }

            .report-table th,
            .report-table td {
                padding: 0.7rem 0.65rem;
                font-size: 0.75rem;
            }

            .report-avatar {
                width: 2rem;
                height: 2rem;
                margin-right: 0.5rem;
            }

            .report-name {
                white-space: normal;
                min-width: 160px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7">
                    <h1 class="hero-title text-3xl sm:text-4xl font-bold leading-tight mb-2">Pantau Laporan Akhir Peserta Magang
                    </h1>
                    <p class="text-blue-100">Monitoring laporan akhir peserta magang</p>
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
                <div class="panel-content p-6">
                    <p class="section-label">Filter Monitoring</p>
                    <form method="GET" action="{{ route('admin.report.index') }}"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="intern_id" class="block text-sm font-medium text-gray-700 mb-2">Peserta Magang</label>
                            <select name="intern_id" id="intern_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua</option>
                                @foreach ($interns as $intern)
                                    <option value="{{ $intern->id }}"
                                        {{ request('intern_id') == $intern->id ? 'selected' : '' }}>
                                        {{ $intern->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button
                                class="filter-action flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center"
                                type="submit">
                                <i class="fas fa-filter mr-2"></i>
                                Filter
                            </button>
                            @if (request()->anyFilled(['intern_id', 'status']))
                                <a href="{{ route('admin.report.index') }}"
                                    class="filter-action bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center justify-center">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabel Laporan Akhir --}}
            <div class="panel">
                <div class="panel-header px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-file-alt mr-3"></i>
                        Data Laporan Akhir
                    </h2>
                </div>
                <div class="panel-content p-6">
                    <div class="overflow-x-auto overflow-y-auto max-h-[500px]">
                        <table class="report-table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="bg-blue-50">
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tl-lg">
                                        Nama</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Laporan</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Tanggal Upload</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">
                                        Nilai</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($reports as $report)
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if ($report->intern->photo_path)
                                                    <img src="{{ url('storage/' . $report->intern->photo_path) }}"
                                                        class="report-avatar w-10 h-10 rounded-full object-cover border-2 border-blue-200 mr-3"
                                                        alt="{{ $report->intern->name }}" loading="lazy" decoding="async">
                                                @else
                                                    <div
                                                        class="report-avatar w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 border-2 border-blue-200">
                                                        <i class="fas fa-user text-blue-600"></i>
                                                    </div>
                                                @endif
                                                <span class="report-name text-sm font-medium text-gray-900">
                                                    {{ $report->intern->name }}
                                                </span>

                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $report->file_name }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $report->submitted_at->format('d/m/Y H:i') }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div class="flex flex-col space-y-1">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if ($report->status == 'approved') bg-green-100 text-green-800
                                                @elseif($report->status == 'rejected') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                                @if ($report->needs_revision)
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                        Revisi
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if ($report->grade)
                                                <span
                                                    class="px-2 py-1 inline-flex text-sm font-bold rounded-full
                                                @if ($report->grade == 'A') bg-green-100 text-green-800
                                                @elseif($report->grade == 'B') bg-blue-100 text-blue-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ $report->grade }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            {{-- Aksi Lihat --}}
                                            <a href="{{ route('admin.report.show', $report) }}"
                                                class="text-green-600 hover:text-green-800 mr-3 transition-color"
                                                title="detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- Aksi Sertifikat --}}
                                            <a href="{{ route('admin.certificates.create', ['intern_id' => $report->intern->id]) }}"
                                                class="text-yellow-400 hover:text-yellow-300 inline-block transition-color"
                                                title="penilaian sertifikat">
                                                <i class="fas fa-certificate"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            Belum ada laporan akhir.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
@endsection
