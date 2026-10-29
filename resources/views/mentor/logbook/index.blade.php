@extends('layouts.app')

@section('title', 'Logbook Anak Bimbingan - Sistem Magang')

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
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
            border: 1px solid #e8eeff;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
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

        thead th:nth-child(1) {
            width: 120px;
        }

        thead th:nth-child(2) {
            width: 180px;
        }

        thead th:nth-child(3) {
            width: auto;
        }

        thead th:nth-child(4) {
            width: 72px;
        }

        thead th:nth-child(5) {
            width: 150px;
        }

        tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #e8eeff;
            font-size: 13px;
            color: #374151;
            vertical-align: top;
        }

        tbody tr {
            transition: background .15s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        /* ── Avatar ── */
        .avatar-cell {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            flex-shrink: 0;
        }

        /* ── Activity text ── */
        .activity-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-break: break-word;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.55;
        }

        .activity-cell {
            width: 100%;
            max-width: 100%;
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

        /* ── Action buttons ── */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s ease;
            white-space: nowrap;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-action i {
            font-size: 11px;
        }

        .action-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .action-inline form {
            margin: 0;
        }

        .btn-approve {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s ease;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.24);
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(34, 197, 94, 0.35);
        }

        .btn-approve i {
            font-size: 13px;
        }

        .btn-approved {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #dcfce7;
            color: #15803d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            border: 1px solid #bbf7d0;
        }

        .btn-approved i {
            font-size: 13px;
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

        /* ── MOBILE RESPONSIVE ── */
        @media (max-width: 768px) {

            /* Hide desktop table on mobile */
            .table-wrapper {
                display: none;
            }

            /* Show mobile cards layout */
            .logbook-cards {
                display: grid;
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .logbook-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 16px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }

            .card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 2px solid #f3f4f6;
            }

            .card-date {
                font-size: 12px;
                font-weight: 600;
                color: #3b82f6;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .card-intern {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 12px;
            }

            .card-intern-avatar {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #e5e7eb;
                flex-shrink: 0;
            }

            .card-intern-name {
                font-weight: 600;
                font-size: 14px;
                color: #1f2937;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                flex: 1;
            }

            .card-activity {
                margin-bottom: 12px;
            }

            .card-activity-label {
                font-size: 11px;
                font-weight: 700;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 6px;
                display: block;
            }

            .card-activity-text {
                font-size: 13px;
                color: #4b5563;
                line-height: 1.5;
                word-break: break-word;
            }

            .card-photo {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 12px;
                padding: 10px;
                background: #f9fafb;
                border-radius: 8px;
            }

            .card-photo-label {
                font-size: 11px;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .card-photo-thumbnail {
                width: 40px;
                height: 40px;
                border-radius: 6px;
                object-fit: cover;
                border: 1px solid #dbeafe;
                cursor: pointer;
                transition: all .2s ease;
            }

            .card-photo-thumbnail:active {
                border-color: #3b82f6;
                transform: scale(1.05);
            }

            .card-photo-empty {
                font-size: 11px;
                color: #d1d5db;
                font-weight: 500;
            }

            .card-actions {
                display: flex;
                gap: 8px;
                padding-top: 12px;
                border-top: 2px solid #f3f4f6;
            }

            .card-action-btn {
                flex: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 10px 12px;
                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                transition: all .2s ease;
                white-space: nowrap;
            }

            .card-action-btn:active {
                transform: scale(0.98);
            }

            .card-action-btn i {
                font-size: 12px;
            }
        }

        /* ── TABLET VIEW ── */
        @media (min-width: 769px) and (max-width: 1024px) {
            thead th:nth-child(1) {
                width: 100px;
            }

            thead th:nth-child(2) {
                width: 140px;
            }

            thead th:nth-child(3) {
                width: auto;
            }

            thead th:nth-child(4) {
                width: 50px;
            }

            thead th:nth-child(5) {
                width: 140px;
            }
        }

        /* ── DESKTOP VIEW ── */
        @media (min-width: 1025px) {
            .logbook-cards {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── HERO HEADER ──────────────────────────────── --}}
            <div class="hero-strip shadow-xl a1">
                <div class="relative z-10 px-6 py-8">
                    <h1 class="text-2xl font-bold text-white mb-1">Logbook Anak Bimbingan</h1>
                    <p class="text-blue-200 text-sm">Pantau dan kelola catatan harian aktivitas anak magang Anda</p>
                </div>
            </div>

            {{-- ── FILTER PANEL ────────────────────────────── --}}
            <div class="panel a2">
                <div class="panel-header">
                    <i class="fas fa-sliders-h text-blue-200"></i>
                    <h2>Filter Data</h2>
                </div>
                <div class="panel-body">
                    <form method="GET" action="{{ route('mentor.logbook.index') }}">
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

                            <div class="filter-actions">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                @if (request()->anyFilled(['intern_id', 'date_from', 'date_to']))
                                    <a href="{{ route('mentor.logbook.index') }}" class="btn-clear">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── LOGBOOK TABLE PANEL ──────────────────────– --}}
            <div class="panel a3">
                <div class="panel-header">
                    <i class="fas fa-book text-blue-200"></i>
                    <h2>Data Logbook</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">Tanggal</th>
                                    <th style="min-width: 150px;">Nama</th>
                                    <th style="min-width: 250px;">Aktivitas</th>
                                    <th style="min-width: 50px;">Foto</th>
                                    <th style="min-width: 145px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logbooks as $l)
                                    <tr>
                                        <td>
                                            <span style="font-size: 12px; color: #6b7280;">
                                                {{ \Carbon\Carbon::parse($l->date)->translatedFormat('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="avatar-cell">
                                                @if ($l->intern->photo_path)
                                                    <img src="{{ url('storage/' . $l->intern->photo_path) }}"
                                                        alt="{{ $l->intern->name }}" class="avatar">
                                                @else
                                                    <div class="avatar"
                                                        style="background: linear-gradient(135deg,#3b82f6,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:12px;">
                                                        {{ strtoupper(substr($l->intern->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span
                                                    style="font-weight: 600; font-size: 13px; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $l->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td class="activity-cell">
                                            <span class="activity-text" title="{{ $l->activity }}">
                                                {{ $l->activity }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($l->photo_path)
                                                @php
                                                    $photoUrl = URL::temporarySignedRoute(
                                                        'mentor.logbook.photo',
                                                        now()->addMinutes(5),
                                                        ['filename' => basename($l->photo_path)],
                                                    );
                                                @endphp
                                                @can('view', $l)
                                                    <img src="{{ $photoUrl }}" alt="Logbook Photo" class="photo-thumbnail"
                                                        onclick="window.open('{{ $photoUrl }}', '_blank')"
                                                        title="Klik untuk melihat full size">
                                                @else
                                                    <span style="color:#d1d5db; font-size:12px;">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span style="color:#d1d5db; font-size:12px;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-inline">
                                                <a href="{{ route('mentor.logbook.show', $l) }}" class="btn-action">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>

                                                @if ($l->approval_status === 'approved')
                                                    <span class="btn-approved" title="Sudah disetujui">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                @else
                                                    <form method="POST"
                                                        action="{{ route('mentor.logbook.approve', $l) }}"
                                                        data-confirm-title="Setujui logbook ini?"
                                                        data-confirm-message="Status logbook akan ditandai Disetujui. Setelah disetujui, status tidak dapat diubah lagi dari halaman ini."
                                                        data-confirm-subject="{{ $l->intern->name }} · {{ \Carbon\Carbon::parse($l->date)->translatedFormat('d M Y') }}"
                                                        data-confirm-note="{{ \Illuminate\Support\Str::limit($l->activity, 160) }}"
                                                        data-confirm-ok="Ya, setujui"
                                                        data-confirm-busy="Menyetujui…"
                                                        data-confirm-tone="success"
                                                        data-confirm-icon="fa-thumbs-up">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="approved">

                                                        <button type="submit" class="btn-approve" title="Approve Logbook">
                                                            <i class="fas fa-thumbs-up"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-book-open"></i>
                                                <p class="font-semibold">Tidak ada data logbook</p>
                                                <p style="font-size:12px;">Coba ubah filter untuk melihat data lainnya</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ── MOBILE CARDS VIEW ──────────────────── --}}
                    <div class="logbook-cards">
                        @forelse($logbooks as $l)
                            <div class="logbook-card">
                                <div class="card-header">
                                    <span
                                        class="card-date">{{ \Carbon\Carbon::parse($l->date)->translatedFormat('d M Y') }}</span>
                                </div>

                                <div class="card-intern">
                                    @if ($l->intern->photo_path)
                                        <img src="{{ url('storage/' . $l->intern->photo_path) }}"
                                            alt="{{ $l->intern->name }}" class="card-intern-avatar">
                                    @else
                                        <div class="card-intern-avatar"
                                            style="background: linear-gradient(135deg,#3b82f6,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:12px;">
                                            {{ strtoupper(substr($l->intern->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="card-intern-name">{{ $l->intern->name }}</span>
                                </div>

                                <div class="card-activity">
                                    <span class="card-activity-label">Aktivitas</span>
                                    <div class="card-activity-text">{{ $l->activity }}</div>
                                </div>

                                <div class="card-photo">
                                    <span class="card-photo-label">Foto:</span>
                                    @if ($l->photo_path)
                                        @php
                                            $photoUrl = URL::temporarySignedRoute(
                                                'mentor.logbook.photo',
                                                now()->addMinutes(5),
                                                ['filename' => basename($l->photo_path)],
                                            );
                                        @endphp
                                        @can('view', $l)
                                            <img src="{{ $photoUrl }}" alt="Logbook Photo" class="card-photo-thumbnail"
                                                onclick="window.open('{{ $photoUrl }}', '_blank')">
                                        @else
                                            <span class="card-photo-empty">Tidak ada akses</span>
                                        @endcan
                                    @else
                                        <span class="card-photo-empty">—</span>
                                    @endif
                                </div>

                                <div class="card-actions">
                                    <a href="{{ route('mentor.logbook.show', $l) }}" class="card-action-btn">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>

                                    @if ($l->approval_status === 'approved')
                                        <span class="card-action-btn"
                                            style="background:#dcfce7; color:#15803d; box-shadow:none;">
                                            <i class="fas fa-check"></i> Approved
                                        </span>
                                    @else
                                        <form method="POST"
                                            action="{{ route('mentor.logbook.approve', $l) }}"
                                            style="flex:1; margin:0;"
                                            data-confirm-title="Setujui logbook ini?"
                                            data-confirm-message="Status logbook akan ditandai Disetujui. Setelah disetujui, status tidak dapat diubah lagi dari halaman ini."
                                            data-confirm-subject="{{ $l->intern->name }} · {{ \Carbon\Carbon::parse($l->date)->translatedFormat('d M Y') }}"
                                            data-confirm-note="{{ \Illuminate\Support\Str::limit($l->activity, 160) }}"
                                            data-confirm-ok="Ya, setujui"
                                            data-confirm-busy="Menyetujui…"
                                            data-confirm-tone="success"
                                            data-confirm-icon="fa-thumbs-up">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">

                                            <button type="submit" class="card-action-btn"
                                                style="width:100%; background:linear-gradient(135deg,#16a34a,#22c55e); color:white;">
                                                <i class="fas fa-thumbs-up"></i> Approve
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-book-open"></i>
                                <p style="font-weight: 600; margin-top: 8px;">Belum ada data logbook.</p>
                                <p>Logbook akan tampil setelah anak magang menambahkan catatan harian.</p>
                            </div>
                        @endforelse
                    </div>

                    @if (method_exists($logbooks, 'links'))
                        <div style="margin-top: 20px;">
                            {{ $logbooks->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
