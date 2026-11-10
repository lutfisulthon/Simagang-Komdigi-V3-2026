@extends('layouts.app')

@section('title', 'Dashboard - Sistem Magang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/institusi-dashboard.css') }}?v={{ @filemtime(public_path('css/pages/institusi-dashboard.css')) }}">
@endpush

@section('content')
@php
    $total    = $totalPengajuan ?: 1; // avoid division by zero
    $pctApproved = $totalPengajuan ? round(($pengajuanApproved / $totalPengajuan) * 100) : 0;
    $pctPending  = $totalPengajuan ? round(($pengajuanPending  / $totalPengajuan) * 100) : 0;
    $pctRejected = $totalPengajuan ? round(($pengajuanRejected / $totalPengajuan) * 100) : 0;

    // Donut circumference (r=42) = 2π×42 ≈ 263.9
    $circ = 263.9;
    $approvedDash = $pctApproved / 100 * $circ;
    $pendingDash  = $pctPending  / 100 * $circ;
    $rejectedDash = $pctRejected / 100 * $circ;

    $approvedOffset = $circ; // starts hidden, JS will animate
    $pendingOffset  = $circ;
    $rejectedOffset = $circ;

    $approvedStart = 0;
    $pendingStart  = $approvedDash;
    $rejectedStart = $approvedDash + $pendingDash;
@endphp

<div class="dash-bg py-8">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- ── PROFILE HEADER ────────────────────────── --}}
    <div class="profile-strip rounded-2xl shadow-xl anim-1">
        <div class="px-6 py-7 flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">

            {{-- Avatar --}}
            <div class="avatar-ring flex-shrink-0">
                <div class="avatar-inner">
                    <i class="fas fa-building text-2xl text-white"></i>
                </div>
            </div>

            {{-- Identity --}}
            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
                    <h1 class="text-xl font-bold text-white">{{ $institusi->user->name }}</h1>
                    {{-- <span class="status-badge badge-active self-center">
                        <span class="dot-pulse dot-green" style="width:7px;height:7px;"></span>
                        Aktif
                    </span> --}}
                </div>
                <p class="text-blue-200 font-semibold text-base">{{ $institusi->nama_institusi }}</p>
                @if ($institusi->jenis_institusi === 'kampus')
                    <p class="text-blue-300 text-sm mt-1">
                        <i class="fas fa-graduation-cap mr-1"></i>
                        {{ $institusi->fakultas }} &mdash; {{ $institusi->departemen }}
                    </p>
                @else
                    <p class="text-blue-300 text-sm mt-1">
                        <i class="fas fa-briefcase mr-1"></i>
                        {{ ucfirst($institusi->jenis_institusi) }}
                    </p>
                @endif
            </div>

            {{-- Summary right --}}
            <div class="flex-shrink-0 text-center sm:text-right">
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Total Pengajuan</p>
                <p class="text-5xl font-extrabold text-white mono">{{ $totalPengajuan }}</p>
                <p class="text-blue-300 text-xs mt-1">Keseluruhan data</p>
            </div>
        </div>
    </div>

    {{-- ── MAIN GRID ──────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Status Breakdown ─── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Status List Panel --}}
            <div class="panel anim-2">
                <p class="section-label">Rincian Status Pengajuan</p>

                <div class="space-y-2">

                    {{-- Total --}}
                    <div class="status-item" style="background:#f0f4ff; border-color:#c7d2fe;">
                        <div class="flex items-center gap-3">
                            <div style="width:36px;height:36px;background:#3b4fd8;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-layer-group text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Total Pengajuan</p>
                                <p class="text-xs text-gray-400">Semua status termasuk</p>
                            </div>
                        </div>
                        <span class="count-pill" style="background:#e0e7ff;color:#3730a3;">{{ $totalPengajuan }}</span>
                    </div>

                    <div class="divider"></div>

                    {{-- Approved --}}
                    <div class="status-item">
                        <div class="flex items-center gap-3">
                            <div style="width:36px;height:36px;background:#dcfce7;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-check-circle" style="color:#16a34a;"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Disetujui</p>
                                <div class="stat-row mt-1" style="width: 160px;">
                                    <div class="stat-bar-track">
                                        <div class="stat-bar-fill" style="background:#22c55e;" data-width="{{ $pctApproved }}"></div>
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $pctApproved }}% dari total</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- <span class="dot-pulse dot-green"></span> --}}
                            <span class="count-pill" style="background:#dcfce7;color:#15803d;">{{ $pengajuanApproved }}</span>
                        </div>
                    </div>

                    {{-- Pending --}}
                    <div class="status-item">
                        <div class="flex items-center gap-3">
                            <div style="width:36px;height:36px;background:#fef9c3;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-clock" style="color:#d97706;"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Menunggu Approval</p>
                                <div class="stat-row mt-1" style="width: 160px;">
                                    <div class="stat-bar-track">
                                        <div class="stat-bar-fill" style="background:#f59e0b;" data-width="{{ $pctPending }}"></div>
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $pctPending }}% dari total</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- <span class="dot-pulse dot-yellow"></span> --}}
                            <span class="count-pill" style="background:#fef9c3;color:#b45309;">{{ $pengajuanPending }}</span>
                        </div>
                    </div>

                    {{-- Rejected --}}
                    <div class="status-item">
                        <div class="flex items-center gap-3">
                            <div style="width:36px;height:36px;background:#fee2e2;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-times-circle" style="color:#dc2626;"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">Ditolak</p>
                                <div class="stat-row mt-1" style="width: 160px;">
                                    <div class="stat-bar-track">
                                        <div class="stat-bar-fill" style="background:#ef4444;" data-width="{{ $pctRejected }}"></div>
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $pctRejected }}% dari total</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- <span class="dot-pulse dot-red"></span> --}}
                            <span class="count-pill" style="background:#fee2e2;color:#b91c1c;">{{ $pengajuanRejected }}</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="panel anim-3">
                <p class="section-label">Aksi Cepat</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('institusi.lowongan.index') }}" class="action-btn">
                        <i class="fas fa-briefcase"></i>
                        Lihat Semua Lowongan Magang
                    </a>
                    <a href="{{ route('institusi.pengajuan.index') }}" class="action-btn">
                        <i class="fas fa-list-ul"></i>
                        Lihat Semua Pengajuan
                    </a>
                    <a href="{{ route('institusi.profile.edit') }}" class="action-btn">
                        <i class="fas fa-user-edit"></i>
                        Edit Profil Institusi
                    </a>
                    <a href="{{ route('institusi.pengajuan.index', ['status' => 'pending']) }}" class="action-btn">
                        <i class="fas fa-hourglass-half"></i>
                        Pengajuan Pending
                        @if ($pengajuanPending > 0)
                            <span style="margin-left:auto;background:#fef08a;color:#854d0e;border-radius:999px;padding:1px 8px;font-size:11px;font-weight:700;">
                                {{ $pengajuanPending }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>

        </div>

        {{-- RIGHT: Donut + Info ─── --}}
        <div class="space-y-5">

            {{-- Donut Chart --}}
            <div class="panel anim-2 flex flex-col items-center">
                <p class="section-label w-full">Distribusi Status</p>

                <div class="relative" style="width:160px;height:160px;">
                    <svg width="160" height="160" viewBox="0 0 100 100" class="donut-svg" style="transform:rotate(-90deg);">
                        {{-- Track --}}
                        <circle cx="50" cy="50" r="42" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                        {{-- Approved --}}
                        <circle id="donut-approved" cx="50" cy="50" r="42" fill="none"
                            stroke="#22c55e" stroke-width="12"
                            stroke-dasharray="{{ $circ }}"
                            stroke-dashoffset="{{ $circ }}"
                            stroke-linecap="butt"
                            style="transition: stroke-dashoffset 1.2s ease;"
                            data-dash="{{ $approvedDash }}"
                            data-offset="0"/>
                        {{-- Pending --}}
                        <circle id="donut-pending" cx="50" cy="50" r="42" fill="none"
                            stroke="#f59e0b" stroke-width="12"
                            stroke-dasharray="{{ $circ }}"
                            stroke-dashoffset="{{ $circ }}"
                            stroke-linecap="butt"
                            style="transition: stroke-dashoffset 1.2s ease 0.2s;"
                            data-dash="{{ $pendingDash }}"
                            data-start="{{ $approvedDash }}"/>
                        {{-- Rejected --}}
                        <circle id="donut-rejected" cx="50" cy="50" r="42" fill="none"
                            stroke="#ef4444" stroke-width="12"
                            stroke-dasharray="{{ $circ }}"
                            stroke-dashoffset="{{ $circ }}"
                            stroke-linecap="butt"
                            style="transition: stroke-dashoffset 1.2s ease 0.4s;"
                            data-dash="{{ $rejectedDash }}"
                            data-start="{{ $approvedDash + $pendingDash }}"/>
                    </svg>
                    {{-- Center label --}}
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <span class="mono" style="font-size:26px;font-weight:800;color:#1e3a8a;">{{ $totalPengajuan }}</span>
                        <span style="font-size:10px;color:#94a3b8;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;">Total</span>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="mt-5 space-y-2 w-full">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span style="width:10px;height:10px;background:#22c55e;border-radius:3px;display:inline-block;"></span>
                            <span class="text-gray-600 font-medium">Disetujui</span>
                        </div>
                        <span class="mono text-gray-800 font-semibold">{{ $pctApproved }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span style="width:10px;height:10px;background:#f59e0b;border-radius:3px;display:inline-block;"></span>
                            <span class="text-gray-600 font-medium">Pending</span>
                        </div>
                        <span class="mono text-gray-800 font-semibold">{{ $pctPending }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span style="width:10px;height:10px;background:#ef4444;border-radius:3px;display:inline-block;"></span>
                            <span class="text-gray-600 font-medium">Ditolak</span>
                        </div>
                        <span class="mono text-gray-800 font-semibold">{{ $pctRejected }}%</span>
                    </div>
                </div>
            </div>

            {{-- Info Panel --}}
            <div class="panel anim-4">
                <p class="section-label">Informasi Akun</p>
                <div class="space-y-3">

                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Email</p>
                        <p class="text-sm font-medium text-gray-700 truncate">{{ $institusi->user->email }}</p>
                    </div>
                    <div class="divider"></div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Jenis Institusi</p>
                        <p class="text-sm font-medium text-gray-700">{{ ucfirst($institusi->jenis_institusi) }}</p>
                    </div>
                    @if ($institusi->jenis_institusi === 'kampus')
                    <div class="divider"></div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Fakultas</p>
                        <p class="text-sm font-medium text-gray-700">{{ $institusi->fakultas }}</p>
                    </div>
                    <div class="divider"></div>
                    <div>
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Departemen</p>
                        <p class="text-sm font-medium text-gray-700">{{ $institusi->departemen }}</p>
                    </div>
                    @endif
                    <div class="divider"></div>
                </div>
            </div>

        </div>
    </div>

</div>
</div>

@push('scripts')
<script>
    // Animate progress bars
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.stat-bar-fill').forEach(el => {
                el.style.width = el.dataset.width + '%';
            });
        }, 300);

        // Donut animation
        const circ = {{ $circ }};

        const approved = document.getElementById('donut-approved');
        const pending  = document.getElementById('donut-pending');
        const rejected = document.getElementById('donut-rejected');

        setTimeout(() => {
            // Approved segment
            const aDash  = parseFloat(approved.dataset.dash);
            approved.style.strokeDasharray = `${aDash} ${circ - aDash}`;
            approved.style.strokeDashoffset = 0;

            // Pending segment - offset so it starts after approved
            const pDash  = parseFloat(pending.dataset.dash);
            const pStart = parseFloat(pending.dataset.start);
            pending.style.strokeDasharray  = `${pDash} ${circ - pDash}`;
            pending.style.strokeDashoffset = -pStart;

            // Rejected segment
            const rDash  = parseFloat(rejected.dataset.dash);
            const rStart = parseFloat(rejected.dataset.start);
            rejected.style.strokeDasharray  = `${rDash} ${circ - rDash}`;
            rejected.style.strokeDashoffset = -rStart;
        }, 400);
    });
</script>
@endpush
@endsection