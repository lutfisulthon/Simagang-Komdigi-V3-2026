@extends('layouts.app')

@section('title', 'Detail Industri - ' . $industri->nama_industri)

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
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -80px; left: 30%;
            width: 300px; height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
        }

        .panel-header-blue {
            background: linear-gradient(to right, #2563eb, #4f46e5);
            padding: 1rem 1.5rem;
        }

        .input-main {
            width: 100%;
            padding: 0.6rem 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 0.6rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all .15s ease;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: #1f2937;
            background: #fff;
        }

        .input-main:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        select.input-main {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 34px;
        }

        .btn-filter {
            background: linear-gradient(to right, #2563eb, #4338ca);
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 0.5rem 1.25rem;
            border-radius: 0.6rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .15s ease;
            white-space: nowrap;
        }

        .btn-filter:hover {
            box-shadow: 0 4px 14px rgba(59, 79, 216, 0.3);
            transform: translateY(-1px);
        }

        .btn-reset {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #2563eb;
            font-weight: 700;
            font-size: 13px;
            padding: 0.5rem 0.75rem;
            border-radius: 0.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all .15s ease;
        }

        .btn-reset:hover {
            background: #dbeafe;
            color: #1d4ed8;
            text-decoration: none;
        }

        .breadcrumb-link {
            color: #3b82f6;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color .15s;
        }

        .breadcrumb-link:hover { color: #1d4ed8; text-decoration: none; }

        /* Stat cards */
        .stat-card {
            border-radius: 16px;
            padding: 20px;
            text-align: center;
        }

        /* Data table */
        .data-table {
            min-width: 800px;
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead tr { background: #eff6ff; }

        .data-table th {
            padding: 0.75rem 1rem;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #1e3a8a;
            text-align: left;
            white-space: nowrap;
        }

        .data-table th:first-child { border-radius: 10px 0 0 10px; }
        .data-table th:last-child  { border-radius: 0 10px 10px 0; text-align: center; }

        .data-table td {
            padding: 0.85rem 1rem;
            font-size: 13px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tbody tr { transition: background 0.15s; }
        .data-table tbody tr:hover { background: #eff6ff; }
        .data-table tbody tr:last-child td { border-bottom: none; }

        /* Avatar cell */
        .avatar-cell { display: flex; align-items: center; gap: 10px; }

        .avatar-sm {
            width: 38px; height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #bfdbfe;
            flex-shrink: 0;
        }

        .avatar-sm-placeholder {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: #dbeafe;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
            font-weight: 700;
            color: #2563eb;
        }

        /* Pills */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .pill-green  { background: #dcfce7; color: #15803d; }
        .pill-orange { background: #ffedd5; color: #c2410c; }

        /* Progress bar */
        .progress-track {
            width: 80px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin: 4px auto 0;
        }

        .progress-fill {
            height: 100%;
            border-radius: 999px;
        }

        .count-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1e40af;
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .empty-state i { font-size: 2.5rem; display: block; margin-bottom: 10px; color: #e2e8f0; }
        .empty-state p { font-size: 13px; margin: 0; }

        @media (max-width: 768px) {
            .hero-title { font-size: 1.5rem; }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        {{-- Breadcrumb --}}
                        <div class="flex items-center gap-2 mb-3">
                            <a href="{{ route('admin.monitoring.industri.index') }}"
                               class="text-blue-200 hover:text-white text-sm font-medium transition-colors">
                                <i class="fas fa-building mr-1"></i>Monitoring Industri
                            </a>
                            <i class="fas fa-chevron-right text-blue-300 text-xs"></i>
                            <span class="text-white text-sm font-semibold">{{ $industri->nama_industri }}</span>
                        </div>
                        <h1 class="hero-title text-4xl font-bold mb-1">{{ $industri->nama_industri }}</h1>
                        @if($industri->alamat)
                            <p class="text-blue-100 text-sm">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $industri->alamat }}
                            </p>
                        @endif
                    </div>

                    {{-- Stat summary --}}
                    <div class="flex gap-3 flex-shrink-0">
                        <div class="text-center px-4 py-3 rounded-xl"
                             style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);">
                            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                            <p class="text-blue-200 text-xs font-semibold mt-0.5">Total</p>
                        </div>
                        <div class="text-center px-4 py-3 rounded-xl"
                             style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.25);">
                            <p class="text-2xl font-bold text-green-300">{{ $stats['active'] }}</p>
                            <p class="text-green-200 text-xs font-semibold mt-0.5">Aktif</p>
                        </div>
                        <div class="text-center px-4 py-3 rounded-xl"
                             style="background:rgba(251,146,60,0.15);border:1px solid rgba(251,146,60,0.25);">
                            <p class="text-2xl font-bold text-orange-300">{{ $stats['alumni'] }}</p>
                            <p class="text-orange-200 text-xs font-semibold mt-0.5">Alumni</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── FILTER PANEL ── --}}
            <div class="panel mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-filter mr-3"></i>Filter Data
                    </h2>
                </div>
                <form method="GET"
                      action="{{ route('admin.monitoring.industri.show', $industri) }}"
                      class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-2">Cari Nama Peserta</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="input-main" placeholder="Ketik nama peserta..." />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">Status</label>
                        <select name="status" class="input-main">
                            <option value="">Semua Status</option>
                            <option value="active"  {{ request('status') === 'active'  ? 'selected' : '' }}>Aktif</option>
                            <option value="alumni"  {{ request('status') === 'alumni'  ? 'selected' : '' }}>Alumni</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 btn-filter">
                            <i class="fas fa-filter"></i>Filter
                        </button>
                        @if(request()->anyFilled(['search', 'status']))
                            <a href="{{ route('admin.monitoring.industri.show', $industri) }}" class="btn-reset">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ── TABEL PESERTA ── --}}
            <div class="panel overflow-hidden">
                <div class="panel-header-blue flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-3"></i>Daftar Peserta Magang
                    </h2>
                    <span class="count-badge">{{ $interns->total() }} peserta</span>
                </div>
                <div class="p-6">
                    <div class="table-responsive" style="overflow-x:auto;overflow-y:auto;max-height:600px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Peserta</th>
                                    <th style="text-align:center;">Status</th>
                                    <th style="text-align:center;">Kehadiran</th>
                                    <th style="text-align:center;">Logbook</th>
                                    <th style="text-align:center;">Microskill</th>
                                    <th style="text-align:center;">Periode</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($interns as $intern)
                                    <tr>
                                        {{-- Nama & Institusi --}}
                                        <td>
                                            <div class="avatar-cell">
                                                @if($intern->photo_path)
                                                    <img src="{{ asset('storage/' . $intern->photo_path) }}"
                                                        alt="{{ $intern->name }}" class="avatar-sm" loading="lazy" decoding="async">
                                                @else
                                                    <div class="avatar-sm-placeholder">
                                                        {{ strtoupper(substr($intern->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">{{ $intern->name }}</p>
                                                    <p class="text-xs text-gray-400">{{ $intern->institution }}</p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Status --}}
                                        <td style="text-align:center;">
                                            @if($intern->is_active)
                                                <span class="pill pill-green">
                                                    <i class="fas fa-circle" style="font-size:6px;"></i>Aktif
                                                </span>
                                            @else
                                                <span class="pill pill-orange">
                                                    <i class="fas fa-circle" style="font-size:6px;"></i>Alumni
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Kehadiran --}}
                                        <td style="text-align:center;">
                                            <p class="font-semibold text-gray-700 text-sm">{{ $intern->attendance_pct }}%</p>
                                            <div class="progress-track">
                                                <div class="progress-fill"
                                                     style="width:{{ $intern->attendance_pct }}%;
                                                            background:{{ $intern->attendance_pct >= 80 ? '#22c55e' : ($intern->attendance_pct >= 60 ? '#f59e0b' : '#ef4444') }};">
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Logbook --}}
                                        <td style="text-align:center;">
                                            <span class="font-semibold text-gray-700">{{ $intern->total_logbooks }}</span>
                                        </td>

                                        {{-- Microskill --}}
                                        <td style="text-align:center;">
                                            <span class="font-semibold text-gray-700">{{ $intern->total_microskill }}</span>
                                        </td>

                                        {{-- Periode --}}
                                        <td style="text-align:center;">
                                            <p class="text-xs text-gray-600 font-medium">
                                                {{ \Carbon\Carbon::parse($intern->start_date)->format('d M Y') }}
                                            </p>
                                            <p class="text-xs text-gray-300">s/d</p>
                                            <p class="text-xs text-gray-600 font-medium">
                                                {{ \Carbon\Carbon::parse($intern->end_date)->format('d M Y') }}
                                            </p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-user-graduate"></i>
                                                <p>Belum ada peserta magang di industri ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($interns->hasPages())
                        <div class="mt-6">{{ $interns->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection