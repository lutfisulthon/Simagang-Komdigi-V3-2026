@extends('layouts.app')

@section('title', 'Laporan Akhir Anak Bimbingan - Sistem Magang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/mentor-report-index.css') }}?v={{ @filemtime(public_path('css/pages/mentor-report-index.css')) }}">
@endpush

@section('content')
<div class="dash-bg py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ── HERO HEADER ──────────────────────────────── --}}
        <div class="hero-strip shadow-xl a1">
            <div class="relative z-10 px-6 py-8">
                <h1 class="text-2xl font-bold text-white mb-1">Laporan Akhir Anak Bimbingan</h1>
                <p class="text-blue-200 text-sm">Kelola dan nilai laporan akhir serta sertifikat anak magang Anda</p>
            </div>
        </div>

        {{-- ── STAT TILES ────────────────────────────────── --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px;" class="a2">
            <div class="stat-tile tile-green">
                <p class="tile-label">Approved</p>
                <p class="tile-value">{{ $reports->where('status', 'approved')->count() }}</p>
            </div>

            <div class="stat-tile tile-yellow">
                <p class="tile-label">Pending</p>
                <p class="tile-value">{{ $reports->where('status', 'pending')->count() }}</p>
            </div>

            <div class="stat-tile tile-red">
                <p class="tile-label">Rejected</p>
                <p class="tile-value">{{ $reports->where('status', 'rejected')->count() }}</p>
            </div>

            <div class="stat-tile tile-orange">
                <p class="tile-label">Perlu Revisi</p>
                <p class="tile-value">{{ $reports->where('needs_revision', true)->count() }}</p>
            </div>
        </div>

        {{-- ── FILTER PANEL ────────────────────────────── --}}
        <div class="panel panel-filter a2">
            <div class="panel-header">
                <i class="fas fa-sliders-h text-blue-200"></i>
                <h2>Filter Data</h2>
            </div>
            <div class="panel-body">
                <form method="GET" action="{{ route('mentor.report.index') }}">
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
                                <i class="fas fa-info-circle"></i> Status
                            </label>
                            <select name="status" class="filter-select">
                                <option value="">Semua</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-exclamation-triangle"></i> Revisi?
                            </label>
                            <select name="needs_revision" class="filter-select">
                                <option value="">Semua</option>
                                <option value="1" @selected(request('needs_revision') === '1')>Ya</option>
                                <option value="0" @selected(request('needs_revision') === '0')>Tidak</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            @if (request()->anyFilled(['intern_id', 'status', 'needs_revision']))
                                <a href="{{ route('mentor.report.index') }}" class="btn-clear">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── REPORT TABLE PANEL ──────────────────────– --}}
        <div class="panel a3">
            <div class="panel-header">
                <i class="fas fa-file-alt text-blue-200"></i>
                <h2>Data Laporan Akhir</h2>
            </div>
            <div class="panel-body">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="min-width: 120px;">Nama</th>
                                <th style="min-width: 130px;">File</th>
                                <th style="min-width: 100px;">Status</th>
                                <th style="min-width: 80px;">Nilai</th>
                                <th style="min-width: 80px;">Revisi</th>
                                <th style="min-width: 140px;">Dikirim</th>
                                <th style="min-width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $r)
                                <tr>
                                    <td>
                                        <div class="avatar-cell">
                                            @if ($r->intern->photo_path)
                                                <img src="{{ url('storage/' . $r->intern->photo_path) }}"
                                                    alt="{{ $r->intern->name }}" class="avatar" loading="lazy" decoding="async">
                                            @else
                                                <div class="avatar" style="background: linear-gradient(135deg,#3b82f6,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:11px;">
                                                    {{ strtoupper(substr($r->intern->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span style="font-weight: 600; font-size: 12px;">{{ $r->intern->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('download', ['path' => $r->file_path]) }}" target="_blank" class="file-link">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>{{ Str::limit($r->file_name ?? 'File', 20) }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="status-badge @if ($r->status == 'approved') badge-approved
                                            @elseif($r->status == 'rejected') badge-rejected
                                            @else badge-pending @endif">
                                            @if ($r->status == 'approved')
                                                <i class="fas fa-check-circle"></i> Approved
                                            @elseif($r->status == 'rejected')
                                                <i class="fas fa-times-circle"></i> Rejected
                                            @else
                                                <i class="fas fa-hourglass-half"></i> Pending
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if ($r->grade)
                                            <div class="grade-badge">
                                                {{ $r->grade }}
                                                @if ($r->score)
                                                    <span class="score">({{ $r->score }})</span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: #9ca3af; font-size: 12px;">Belum</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge @if ($r->needs_revision) badge-revision @else badge-no-revision @endif">
                                            {{ $r->needs_revision ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px; color: #6b7280;">
                                            {{ $r->submitted_at ? \Carbon\Carbon::parse($r->submitted_at)->translatedFormat('d M Y H:i') : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 4px;">
                                            @if (!$r->grade)
                                                <a href="{{ route('mentor.report.show', $r) }}"
                                                    class="action-btn grade"
                                                    title="Beri nilai laporan akhir">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('mentor.report.show', $r) }}"
                                                    class="action-btn view"
                                                    title="Lihat nilai laporan akhir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif

                                            <a href="{{ route('mentor.certificates.create', ['intern_id' => $r->intern->id]) }}"
                                                class="action-btn cert"
                                                title="Penilaian sertifikat">
                                                <i class="fas fa-certificate"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fas fa-file-excel"></i>
                                            <p class="font-semibold">Tidak ada data laporan akhir</p>
                                            <p style="font-size:12px;">Data akan muncul ketika anak magang submit laporan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($reports, 'links'))
                    <div style="margin-top: 20px;">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
