@extends('layouts.app')

@section('title', 'Absensi Anak Bimbingan - Sistem Magang')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

        *,
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .mono {
            font-family: 'DM Mono', monospace;
        }

        /* ── Page background ── */
        .dash-bg {
            min-height: 100vh;
            background: #f1f5ff;
        }

        /* ── Header strip ── */
        .hero-strip {
            background: linear-gradient(110deg, #0f2878 0%, #2d3ecb 55%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .hero-strip::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -60px;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: 25%;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        /* ── Panel ── */
        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(20, 40, 120, 0.06), 0 4px 18px rgba(20, 40, 120, 0.06);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .panel-header {
            background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 100%);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-header h2 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.01em;
            margin: 0;
        }

        .panel-body {
            padding: 20px 22px;
        }

        /* ── Filter Form ── */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-label i {
            font-size: 12px;
        }

        .filter-input,
        .filter-select {
            padding: 10px 12px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13px;
            font-family: inherit;
            background: #fff;
            transition: all .2s ease;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #3b4fd8;
            box-shadow: 0 0 0 3px rgba(59, 79, 216, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .btn-filter {
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b4fd8, #3b82f6);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 79, 216, 0.3);
        }

        .btn-clear {
            padding: 10px 14px;
            background: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s ease;
        }

        .btn-clear:hover {
            background: #d1d5db;
        }

        /* ── Table ── */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f0f4ff;
        }

        thead th {
            padding: 12px 14px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #3b4fd8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }

        tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #e8eeff;
            font-size: 13px;
            color: #374151;
        }

        tbody tr {
            transition: background .15s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr.row-absent {
            background: #fff5f5;
        }

        tbody tr.row-absent:hover {
            background: #fef2f2;
        }

        /* ── Date display ── */
        .date-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #374151;
            font-size: 13px;
        }

        .date-cell i {
            color: #9ca3af;
            font-size: 12px;
            flex-shrink: 0;
        }

        /* ── Avatar ── */
        .avatar-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            flex-shrink: 0;
        }

        /* ── Status badges ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-hadir {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-izin {
            background: #fef9c3;
            color: #92400e;
        }

        .badge-sakit {
            background: #ffedd5;
            color: #c2410c;
        }

        .badge-alfa,
        .badge-absent {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ── Time display ── */
        .time-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            font-weight: 600;
        }

        .time-cell i {
            font-size: 11px;
            flex-shrink: 0;
        }

        /* ── Photo preview ── */
        .photo-thumbnail {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #dbeafe;
            cursor: pointer;
            transition: all .2s ease;
            flex-shrink: 0;
        }

        .photo-thumbnail:hover {
            border-color: #3b82f6;
            transform: scale(1.1);
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 13px;
            margin: 4px 0;
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .a1 {
            animation: fadeUp .5s ease both;
        }

        .a2 {
            animation: fadeUp .5s .08s ease both;
        }

        .a3 {
            animation: fadeUp .5s .16s ease both;
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── HERO HEADER ──────────────────────────────── --}}
            <div class="hero-strip shadow-xl a1">
                <div class="relative z-10 px-6 py-8">
                    <h1 class="text-2xl font-bold text-white mb-1">Absensi Anak Bimbingan</h1>
                    <p class="text-blue-200 text-sm">Pantau dan kelola data kehadiran anak magang Anda</p>
                </div>
            </div>

            {{-- ── FILTER PANEL ────────────────────────────── --}}
            <div class="panel panel-filter a2">
                <div class="panel-header">
                    <i class="fas fa-sliders-h text-blue-200"></i>
                    <h2>Filter Data</h2>
                </div>
                <div class="panel-body">
                    <form method="GET" action="{{ route('mentor.attendance.index') }}">
                        <div class="filter-grid">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-user"></i> Anak Magang
                                </label>
                                <select name="intern_id" class="filter-select">
                                    <option value="">Semua</option>
                                    @foreach ($interns as $intern)
                                        <option value="{{ $intern->id }}" @selected(request('intern_id') == $intern->id)>
                                            {{ $intern->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-calendar-alt"></i> Dari Tanggal
                                </label>
                                <input type="date" name="date_from" class="filter-input"
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-calendar-check"></i> Hingga Tanggal
                                </label>
                                <input type="date" name="date_to" class="filter-input" value="{{ request('date_to') }}">
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-info-circle"></i> Status
                                </label>
                                <select name="status" class="filter-select">
                                    <option value="">Semua</option>
                                    <option value="hadir" @selected(request('status') === 'hadir')>Hadir</option>
                                    <option value="izin" @selected(request('status') === 'izin')>Izin</option>
                                    <option value="sakit" @selected(request('status') === 'sakit')>Sakit</option>
                                    <option value="alfa" @selected(request('status') === 'alfa')>Tidak Hadir</option>
                                </select>
                            </div>

                            <div class="filter-actions">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                @if (request()->anyFilled(['intern_id', 'date_from', 'date_to', 'status']))
                                    <a href="{{ route('mentor.attendance.index') }}" class="btn-clear">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── ATTENDANCE TABLE PANEL ──────────────────– --}}
            <div class="panel a3">
                <div class="panel-header">
                    <i class="fas fa-clipboard-check text-blue-200"></i>
                    <h2>Data Absensi</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Foto In</th>
                                    <th>Foto Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Belum absen hari ini --}}
                                @foreach ($todayAbsentInterns as $absentIntern)
                                    <tr class="row-absent">
                                        <td>
                                            <div class="date-cell">
                                                <i class="fas fa-calendar"></i>
                                                <span>{{ \Carbon\Carbon::parse($todayWita)->translatedFormat('d M Y') }}</span>
                                                <span style="color:#9ca3af; font-size:11px;">(Hari ini)</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="avatar-cell">
                                                @if ($absentIntern->photo_path)
                                                    <img src="{{ url('storage/' . $absentIntern->photo_path) }}"
                                                        alt="{{ $absentIntern->name }}" class="avatar">
                                                @else
                                                    <div class="avatar"
                                                        style="background: linear-gradient(135deg,#3b82f6,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:12px;">
                                                        {{ strtoupper(substr($absentIntern->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span>{{ $absentIntern->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge badge-absent">
                                                <i class="fas fa-times-circle"></i> Belum Absen
                                            </span>
                                        </td>
                                        <td colspan="4" style="color:#9ca3af; font-size:12px; font-style:italic;">
                                            Belum melakukan absensi hari ini
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Data absensi --}}
                                @forelse($attendances as $a)
                                    <tr>
                                        <td>
                                            <div class="date-cell">
                                                <i class="fas fa-calendar"></i>
                                                {{ \Carbon\Carbon::parse($a->date)->translatedFormat('d M Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="avatar-cell">
                                                @if ($a->intern->photo_path)
                                                    <img src="{{ url('storage/' . $a->intern->photo_path) }}"
                                                        alt="{{ $a->intern->name }}" class="avatar">
                                                @else
                                                    <div class="avatar"
                                                        style="background: linear-gradient(135deg,#3b82f6,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:12px;">
                                                        {{ strtoupper(substr($a->intern->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span>{{ $a->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="status-badge @if ($a->status == 'hadir') badge-hadir
                                            @elseif($a->status == 'izin') badge-izin
                                            @elseif($a->status == 'sakit') badge-sakit
                                            @else badge-alfa @endif">
                                                @if ($a->status == 'hadir')
                                                    <i class="fas fa-check-circle"></i> Hadir
                                                @elseif($a->status == 'izin')
                                                    <i class="fas fa-notes-medical"></i> Izin
                                                @elseif($a->status == 'sakit')
                                                    <i class="fas fa-notes-medical"></i> Sakit
                                                @else
                                                    <i class="fas fa-times-circle"></i> Tidak Hadir
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <div class="time-cell">
                                                @if ($a->check_in)
                                                    <i class="fas fa-sign-in-alt" style="color: #22c55e;"></i>
                                                    {{ \Carbon\Carbon::parse($a->check_in)->format('H:i') }}
                                                @else
                                                    <span style="color:#d1d5db;">—</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="time-cell">
                                                @if ($a->check_out)
                                                    <i class="fas fa-sign-out-alt" style="color: #ef4444;"></i>
                                                    {{ \Carbon\Carbon::parse($a->check_out)->format('H:i') }}
                                                @else
                                                    <span style="color:#d1d5db;">—</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($a->photo_path)
                                                @php
                                                    $photoUrl = route('mentor.attendance.photo', [
                                                        'filename' => basename($a->photo_path),
                                                    ]);
                                                @endphp
                                                @can('view', $a)
                                                    <img src="{{ $photoUrl }}" alt="Check In" class="photo-thumbnail"
                                                        onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                        title="Klik untuk melihat full size">
                                                @else
                                                    <span class="text-muted" style="font-size:12px;">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span style="color:#d1d5db; font-size:12px;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($a->photo_checkout)
                                                @php
                                                    $photoOutUrl = route('mentor.attendance.photo', [
                                                        'filename' => basename($a->photo_checkout),
                                                    ]);
                                                @endphp
                                                @can('view', $a)
                                                    <img src="{{ $photoOutUrl }}" alt="Check Out" class="photo-thumbnail"
                                                        onclick="window.open('{{ $photoOutUrl }}', '_blank')"
                                                        title="Klik untuk melihat full size">
                                                @else
                                                    <span class="text-muted" style="font-size:12px;">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span style="color:#d1d5db; font-size:12px;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p class="font-semibold">Tidak ada data absensi</p>
                                                <p style="font-size:12px;">Coba ubah filter untuk melihat data lainnya</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($attendances, 'links'))
                        <div style="margin-top: 20px;">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
