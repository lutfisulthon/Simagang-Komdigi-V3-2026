@extends('layouts.app')

@section('title', 'Detail Anak Magang - Sistem Magang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/industri-intern-show.css') }}?v={{ @filemtime(public_path('css/pages/industri-intern-show.css')) }}">
@endpush

@section('content')
<div class="dash-bg py-8">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

    {{-- ── PROFILE HEADER ── --}}
    <div class="profile-strip anim-1">
        <div class="px-6 py-7 flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">

            {{-- Foto peserta ── --}}
            @if($intern->photo_path)
                <div style="background:linear-gradient(135deg,#60a5fa,#818cf8);padding:3px;border-radius:9999px;display:inline-flex;flex-shrink:0;">
                    <img src="{{ url('storage/'.$intern->photo_path) }}"
                        alt="{{ $intern->name }}"
                        style="width:80px;height:80px;border-radius:9999px;object-fit:cover;" loading="lazy" decoding="async">
                </div>
            @else
                <div style="background:linear-gradient(135deg,#60a5fa,#818cf8);padding:3px;border-radius:9999px;display:inline-flex;flex-shrink:0;">
                    <div style="background:linear-gradient(135deg,#3b82f6,#6366f1);border-radius:9999px;width:80px;height:80px;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:2rem;font-weight:800;color:#fff;">
                            {{ strtoupper(substr($intern->name, 0, 1)) }}
                        </span>
                    </div>
                </div>
            @endif

            {{-- Identity ── --}}
            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                    <h1 class="text-xl font-bold text-white">{{ $intern->name }}</h1>
                    @if($intern->is_active)
                        <span style="background:rgba(34,197,94,0.2);color:#86efac;border:1px solid rgba(134,239,172,0.3);padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;">
                            <i class="fas fa-circle text-xs mr-1"></i>Aktif
                        </span>
                    @else
                        <span style="background:rgba(239,68,68,0.2);color:#fca5a5;border:1px solid rgba(252,165,165,0.3);padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;">
                            <i class="fas fa-circle text-xs mr-1"></i>Tidak Aktif
                        </span>
                    @endif
                </div>
                <p class="text-blue-200 font-semibold text-sm">{{ $intern->institution }}</p>
                <p class="text-blue-300 text-xs mt-1">
                    <i class="fas fa-graduation-cap mr-1"></i>{{ $intern->education_level }} · {{ $intern->major ?? 'Jurusan tidak diisi' }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ $intern->start_date->locale('id')->translatedFormat('d F Y') }} – {{ $intern->end_date->locale('id')->translatedFormat('d F Y') }}
                </p>
                @if($intern->team)
                    <p class="text-blue-300 text-xs mt-1">
                        <i class="fas fa-layer-group mr-1"></i>Tim: {{ $intern->team->name ?? $intern->team }}
                    </p>
                @endif
            </div>

            {{-- Actions ── --}}
            <div class="flex-shrink-0 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('industri.intern.edit', $intern) }}" class="edit-btn">
                    <i class="fas fa-edit text-xs"></i>
                    Edit Data
                </a>
                <a href="{{ route('industri.intern.index') }}" class="edit-btn">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ── STAT TILES ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 anim-2">
        <div class="stat-tile" style="--stat-color:#22c55e;">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-label">Hadir</p>
                    <p class="stat-value">{{ $stats['total_hadir'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
        <div class="stat-tile" style="--stat-color:#f59e0b;">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-label">Izin</p>
                    <p class="stat-value">{{ $stats['total_izin'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <i class="fas fa-calendar-times"></i>
                </div>
            </div>
        </div>
        <div class="stat-tile" style="--stat-color:#ef5350;">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-label">Sakit</p>
                    <p class="stat-value">{{ $stats['total_sakit'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#ef5350,#e53935);">
                    <i class="fas fa-heartbeat"></i>
                </div>
            </div>
        </div>
        <div class="stat-tile" style="--stat-color:#3b82f6;">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-label">Logbook</p>
                    <p class="stat-value">{{ $stats['total_logbooks'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MAIN GRID ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: Info Profil ── --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- Informasi Pribadi ── --}}
            <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon blue"><i class="fas fa-user"></i></div>
                    <div class="section-header-text">
                        <h3>Informasi Pribadi</h3>
                        <p>Data diri peserta</p>
                    </div>
                </div>

                <div class="info-item">
                    <p class="info-label">Nama Lengkap</p>
                    <p class="info-value">{{ $intern->name }}</p>
                </div>
                <div class="info-item">
                    <p class="info-label">Email</p>
                    <p class="info-value mono" style="font-size:12px;word-break:break-all;">{{ $intern->user->email }}</p>
                </div>
                <div class="info-item">
                    <p class="info-label">Jenis Kelamin</p>
                    <p class="info-value">{{ $intern->gender }}</p>
                </div>
                <div class="info-item">
                    <p class="info-label">Telepon</p>
                    <p class="info-value mono" style="font-size:13px;">{{ $intern->phone ?? '—' }}</p>
                </div>
                <div class="info-item">
                    <p class="info-label">Jenjang Pendidikan</p>
                    <p class="info-value">{{ $intern->education_level }}</p>
                </div>
                <div class="info-item">
                    <p class="info-label">Jurusan</p>
                    <p class="info-value">{{ $intern->major ?? '—' }}</p>
                </div>
                <div class="info-item">
                    <p class="info-label">Institusi</p>
                    <p class="info-value">{{ $intern->institution }}</p>
                </div>
            </div>

            {{-- Penempatan ── --}}
            {{-- <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon green"><i class="fas fa-users"></i></div>
                    <div class="section-header-text">
                        <h3>Penempatan</h3>
                        <p>Mentor & tim</p>
                    </div>
                </div>

                <div class="info-item">
                    <p class="info-label">Mentor</p>
                    <p class="info-value">{{ $intern->mentor?->name ?? 'Belum ada mentor' }}</p>
                </div>
                <div class="info-item">
                    <p class="info-label">Tim</p>
                    @if($intern->team)
                        <span class="badge badge-blue" style="margin-top:2px;">
                            <i class="fas fa-layer-group text-xs"></i>
                            {{ $intern->team->name ?? $intern->team }}
                        </span>
                    @else
                        <p class="info-value" style="color:#94a3b8;">Belum ada tim</p>
                    @endif
                </div>
                <div class="info-item">
                    <p class="info-label">Periode Magang</p>
                    <p class="info-value mono" style="font-size:12px;">
                        {{ $intern->start_date->locale('id')->translatedFormat('d F Y') }}<br>
                        <span style="color:#94a3b8;font-size:10px;">s/d</span><br>
                        {{ $intern->end_date->locale('id')->translatedFormat('d F Y') }}
                    </p>
                </div>
                <div class="info-item">
                    <p class="info-label">Status</p>
                    @if($intern->is_active)
                        <span class="badge badge-green" style="margin-top:2px;">
                            <i class="fas fa-circle" style="font-size:6px;"></i> Aktif
                        </span>
                    @else
                        <span class="badge badge-red" style="margin-top:2px;">
                            <i class="fas fa-circle" style="font-size:6px;"></i> Tidak Aktif
                        </span>
                    @endif
                </div>
            </div> --}}

            {{-- Soft Skill & Hard Skill ── side by side --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="panel anim-3">
                    <div class="section-header">
                        <div class="section-icon violet"><i class="fas fa-comments"></i></div>
                        <div class="section-header-text">
                            <h3>Soft Skill</h3>
                            <p>Keterampilan sosial</p>
                        </div>
                    </div>
                    @if($intern->soft_skill)
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @foreach(explode(',', $intern->soft_skill) as $skill)
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;background:#f3f0ff;color:#6d28d9;border:1px solid #e9d5ff;">
                                    <i class="fas fa-star" style="font-size:7px;color:#a78bfa;"></i>
                                    {{ trim($skill) }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="info-value" style="color:#94a3b8;">—</p>
                    @endif
                </div>

                <div class="panel anim-3">
                    <div class="section-header">
                        <div class="section-icon amber"><i class="fas fa-code"></i></div>
                        <div class="section-header-text">
                            <h3>Hard Skill</h3>
                            <p>Keterampilan teknis</p>
                        </div>
                    </div>
                    @if($intern->hard_skill)
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @foreach(explode(',', $intern->hard_skill) as $skill)
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;background:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                                    <i class="fas fa-cog" style="font-size:7px;color:#fbbf24;"></i>
                                    {{ trim($skill) }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="info-value" style="color:#94a3b8;">—</p>
                    @endif
                </div>
            </div>

        </div>

        {{-- RIGHT: Absensi + Logbook ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Riwayat Absensi ── --}}
            <div class="panel anim-4">
                <div class="section-header">
                    <div class="section-icon indigo"><i class="fas fa-clipboard-list"></i></div>
                    <div class="section-header-text">
                        <h3>Riwayat Absensi</h3>
                        <p>Rekap kehadiran peserta</p>
                    </div>
                </div>

                <div style="overflow-x:auto;overflow-y:auto;max-height:340px;border-radius:12px;border:1px solid #e8eeff;">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($intern->attendances as $attendance)
                                <tr>
                                    <td class="mono" style="font-size:12px;">
                                        {{ $attendance->date->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($attendance->status) {
                                                'hadir' => 'badge-green',
                                                'izin'  => 'badge-amber',
                                                'sakit' => 'badge badge-red',
                                                default => 'badge-gray',
                                            };
                                            $badgeIcon = match($attendance->status) {
                                                'hadir' => 'fa-check-circle',
                                                'izin'  => 'fa-clipboard',
                                                'sakit' => 'fa-heartbeat',
                                                default => 'fa-times-circle',
                                            };
                                            $badgeText = match($attendance->status) {
                                                'hadir' => 'Hadir',
                                                'izin'  => 'Izin',
                                                'sakit' => 'Sakit',
                                                default => 'Alfa',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            <i class="fas {{ $badgeIcon }} text-xs"></i>
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                    <td class="mono" style="font-size:12px;">
                                        {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '—' }}
                                    </td>
                                    <td class="mono" style="font-size:12px;">
                                        {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding:32px;text-align:center;color:#94a3b8;">
                                        <i class="fas fa-inbox text-3xl mb-2" style="display:block;color:#e2e8f0;"></i>
                                        Belum ada riwayat absensi
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Logbook ── --}}
            <div class="panel anim-4">
                <div class="section-header">
                    <div class="section-icon violet"><i class="fas fa-book-open"></i></div>
                    <div class="section-header-text">
                        <h3>Logbook Terakhir</h3>
                        <p>Aktivitas harian peserta</p>
                    </div>
                </div>

                <div class="space-y-2" style="max-height:320px;overflow-y:auto;padding-right:4px;">
                    @forelse($intern->logbooks as $logbook)
                        <div class="logbook-item">
                            <p class="logbook-date">
                                <i class="fas fa-calendar-day mr-1"></i>
                                {{ $logbook->date->locale('id')->translatedFormat('d F Y') }}
                            </p>
                            <p class="logbook-text">{{ $logbook->activity }}</p>
                        </div>
                    @empty
                        <div style="padding:32px;text-align:center;color:#94a3b8;">
                            <i class="fas fa-book text-3xl mb-2" style="display:block;color:#e2e8f0;"></i>
                            <p style="font-size:13px;margin:0;">Belum ada logbook</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Laporan Akhir ── --}}
            @if($intern->finalReport)
                <div class="panel anim-5">
                    <div class="section-header">
                        <div class="section-icon amber"><i class="fas fa-file-alt"></i></div>
                        <div class="section-header-text">
                            <h3>Laporan Akhir</h3>
                            <p>Dokumen penutup magang</p>
                        </div>
                    </div>
                    <div class="report-card">
                        <div class="report-card-info">
                            <p class="report-name">
                                <i class="fas fa-paperclip mr-1" style="color:#6366f1;"></i>
                                {{ $intern->finalReport->file_name }}
                            </p>
                            <p class="report-date">
                                <i class="fas fa-clock mr-1"></i>
                                Dikirim {{ $intern->finalReport->submitted_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                        <a href="{{ route('admin.report.show', $intern->finalReport) }}" class="cta-btn">
                            Lihat Laporan
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ── FOOTER BACK ── --}}
    <div class="anim-5" style="padding-bottom:8px;">
        <a href="{{ route('industri.intern.index') }}" class="btn-back">
            <i class="fas fa-arrow-left text-xs"></i>
            Kembali ke Daftar Anak Magang
        </a>
    </div>

</div>
</div>
@endsection