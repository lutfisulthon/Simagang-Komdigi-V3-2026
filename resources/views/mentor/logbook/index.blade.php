@extends('layouts.app')

@section('title', 'Logbook Anak Bimbingan - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/mentor-logbook-index.css') }}?v={{ @filemtime(public_path('css/pages/mentor-logbook-index.css')) }}">
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
            <div class="panel panel-filter a2">
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
                                                        alt="{{ $l->intern->name }}" class="avatar" loading="lazy" decoding="async">
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
                                                        title="Klik untuk melihat full size" loading="lazy" decoding="async">
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
                                            alt="{{ $l->intern->name }}" class="card-intern-avatar" loading="lazy" decoding="async">
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
                                                onclick="window.open('{{ $photoUrl }}', '_blank')" loading="lazy" decoding="async">
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
