@extends('layouts.app')

@section('title', 'Laporan Akhir Anak Bimbingan - Sistem Magang')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

    *, body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .mono { font-family: 'DM Mono', monospace; }

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
        top: -80px; right: -60px;
        width: 260px; height: 260px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-strip::after {
        content: '';
        position: absolute;
        bottom: -100px; left: 25%;
        width: 320px; height: 320px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
        pointer-events: none;
    }

    /* ── Stat tiles ── */
    .stat-tile {
        background: #fff;
        border-radius: 18px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(20,40,120,0.06), 0 4px 16px rgba(20,40,120,0.06);
        display: flex;
        flex-direction: column;
        gap: 12px;
        transition: transform .2s ease, box-shadow .2s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-tile::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 0 0 18px 18px;
    }
    .stat-tile:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(20,40,120,0.12); }
    .tile-green::after   { background: linear-gradient(90deg,#22c55e,#10b981); }
    .tile-yellow::after  { background: linear-gradient(90deg,#f59e0b,#f97316); }
    .tile-red::after     { background: linear-gradient(90deg,#ef4444,#dc2626); }
    .tile-orange::after  { background: linear-gradient(90deg,#f97316,#ea580c); }

    .tile-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .tile-value {
        font-size: 28px;
        font-weight: 800;
        color: #1f2937;
        font-family: 'DM Mono', monospace;
    }

    /* ── Panel ── */
    .panel {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(20,40,120,0.06), 0 4px 18px rgba(20,40,120,0.06);
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
    .panel-body { padding: 20px 22px; }

    /* ── Filter Form ── */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
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
        box-shadow: 0 0 0 3px rgba(59,79,216,0.1);
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
        box-shadow: 0 4px 12px rgba(59,79,216,0.3);
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
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-approved {
        background: #dcfce7;
        color: #15803d;
    }
    .badge-pending {
        background: #fef9c3;
        color: #92400e;
    }
    .badge-rejected {
        background: #fee2e2;
        color: #b91c1c;
    }
    .badge-revision {
        background: #ffedd5;
        color: #c2410c;
    }
    .badge-no-revision {
        background: #dcfce7;
        color: #15803d;
    }

    /* ── Grade badge ── */
    .grade-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background: linear-gradient(135deg, #3b4fd8, #3b82f6);
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 800;
        font-family: 'DM Mono', monospace;
    }
    .grade-badge .score {
        font-size: 10px;
        opacity: 0.8;
    }

    /* ── File link ── */
    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #3b4fd8;
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
        transition: all .2s ease;
    }
    .file-link:hover {
        color: #1e40af;
        text-decoration: underline;
    }
    .file-link i {
        color: #ef4444;
        font-size: 12px;
    }

    /* ── Action buttons ── */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        cursor: pointer;
        transition: all .2s ease;
        border: none;
        background: none;
        font-size: 14px;
    }
    .action-btn.grade {
        color: #22c55e;
    }
    .action-btn.grade:hover {
        background: #dcfce7;
    }
    .action-btn.view {
        color: #3b82f6;
    }
    .action-btn.view:hover {
        background: #dbeafe;
    }
    .action-btn.cert {
        color: #f59e0b;
    }
    .action-btn.cert:hover {
        background: #fef3c7;
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
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .a1 { animation: fadeUp .5s ease both; }
    .a2 { animation: fadeUp .5s .08s ease both; }
    .a3 { animation: fadeUp .5s .16s ease both; }
</style>
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
                                                    alt="{{ $r->intern->name }}" class="avatar">
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
