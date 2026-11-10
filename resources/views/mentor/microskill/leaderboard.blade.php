@extends('layouts.app')

@section('title', 'Leaderboard Mikro Skill - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/mentor-microskill-leaderboard.css') }}?v={{ @filemtime(public_path('css/pages/mentor-microskill-leaderboard.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── HERO HEADER ── --}}
            <div class="hero-strip shadow-xl a1">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row items-center sm:items-start gap-5">

                    {{-- Icon --}}
                    <div style="background:rgba(255,255,255,0.15);border-radius:50%;padding:3px;flex-shrink:0;">
                        <div
                            style="width:72px;height:72px;background:linear-gradient(135deg,#fbbf24,#d97706);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-trophy text-white text-2xl"></i>
                        </div>
                    </div>

                    {{-- Title --}}
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-xl font-bold text-white mb-1">Leaderboard Mikro Skill</h1>
                        <p class="text-blue-200 text-sm">Peringkat pencapaian keterampilan anak magang bimbingan Anda &mdash;
                            <span class="text-white font-semibold">{{ now()->translatedFormat('l, d F Y') }}</span>
                        </p>
                        <div class="hero-actions mt-2">
                            <a href="{{ route('mentor.dashboard') }}"
                                style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#e0e7ff;font-size:12px;font-weight:600;padding:4px 14px;border-radius:999px;text-decoration:none;transition:background .2s;"
                                onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                                <i class="fas fa-arrow-left" style="font-size:10px;"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </div>

                    {{-- Total count --}}
                    <div class="text-center sm:text-right flex-shrink-0">
                        <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Total Peserta</p>
                        <p class="text-5xl font-extrabold text-white mono">{{ $rows->total() }}</p>
                        <p class="text-blue-300 text-xs mt-0.5">anak magang</p>
                    </div>
                </div>
            </div>

            {{-- ── LEADERBOARD PANEL ── --}}
            <div class="panel a2">
                <div class="panel-header">
                    <i class="fas fa-trophy text-yellow-300 text-base"></i>
                    <h2>Peringkat Mikro Skill</h2>
                    <span
                        style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;font-size:11px;font-weight:700;padding:2px 10px;border-radius:999px;">
                        {{ $rows->total() }} peserta
                    </span>
                </div>
                <div class="panel-body">
                    <p class="sec-label">Anak magang dengan pencapaian terbaik</p>

                    @forelse($rows as $index => $row)
                        @php
                            $actualRank = $rows->firstItem() + $index;
                            $podiumClass = match (true) {
                                $actualRank === 1 => 'podium-1',
                                $actualRank === 2 => 'podium-2',
                                $actualRank === 3 => 'podium-3',
                                default => 'podium-default',
                            };
                            $rankClass = match (true) {
                                $actualRank === 1 => 'rank-1',
                                $actualRank === 2 => 'rank-2',
                                $actualRank === 3 => 'rank-3',
                                default => 'rank-n',
                            };
                            $scoreClass = match (true) {
                                $actualRank === 1 => 'score-pill score-pill-1',
                                $actualRank === 2 => 'score-pill score-pill-2',
                                $actualRank === 3 => 'score-pill score-pill-3',
                                default => 'score-pill',
                            };
                        @endphp

                        <div class="podium-item {{ $podiumClass }} {{ !$loop->first ? 'mt-3' : '' }}">

                            {{-- Rank badge --}}
                            <div class="rank-badge {{ $rankClass }}">
                                @if ($actualRank <= 3)
                                    <i class="fas fa-trophy" style="font-size:13px;"></i>
                                @else
                                    {{ $actualRank }}
                                @endif
                            </div>

                            {{-- Photo --}}
                            @if (!empty($row->photo_path))
                                <img src="{{ asset('storage/' . $row->photo_path) }}" class="avatar"
                                    alt="{{ $row->name }}"
                                    onclick="window.open('{{ asset('storage/' . $row->photo_path) }}', '_blank')"
                                    title="Klik untuk melihat full size" loading="lazy" decoding="async">
                            @else
                                <div class="avatar-placeholder">
                                    {{ strtoupper(substr($row->name, 0, 1)) }}
                                </div>
                            @endif

                            {{-- Info --}}
                            <div class="podium-info">
                                <p
                                    style="font-size:14px;font-weight:700;color:#1e293b;margin:0 0 2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $row->name }}
                                </p>
                                <p
                                    style="font-size:12px;color:#64748b;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $row->institution }}
                                </p>
                            </div>

                            {{-- Score --}}
                            <span class="{{ $scoreClass }}">
                                <i class="fas fa-star" style="font-size:10px;"></i>
                                {{ $row->total }}
                            </span>
                        </div>

                    @empty
                        <div style="text-align:center;padding:48px 24px;color:#94a3b8;">
                            <i class="fas fa-chart-line"
                                style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                            <p style="font-size:14px;font-weight:600;color:#64748b;margin-bottom:4px;">Belum ada data
                                leaderboard</p>
                            <p style="font-size:12px;color:#94a3b8;">Data akan muncul ketika anak magang mulai menyelesaikan
                                course</p>
                        </div>
                    @endforelse

                    {{-- ── Pagination ── --}}
                    @if ($rows->hasPages())
                        <div class="pagination-row">
                            <p style="font-size:12px;color:#94a3b8;">
                                Menampilkan {{ $rows->firstItem() }}–{{ $rows->lastItem() }} dari {{ $rows->total() }}
                                peserta
                            </p>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">

                                {{-- Prev --}}
                                @if ($rows->onFirstPage())
                                    <span
                                        style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#f1f5f9;border:1px solid #e8eeff;border-radius:8px;font-size:12px;font-weight:600;color:#cbd5e1;cursor:not-allowed;">
                                        <i class="fas fa-chevron-left"></i> Prev
                                    </span>
                                @else
                                    <a href="{{ $rows->previousPageUrl() }}"
                                        style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#f1f5f9;border:1px solid #e8eeff;border-radius:8px;font-size:12px;font-weight:600;color:#1e40af;text-decoration:none;transition:all .2s;"
                                        onmouseover="this.style.background='#dbeafe';this.style.borderColor='#bfdbfe';"
                                        onmouseout="this.style.background='#f1f5f9';this.style.borderColor='#e8eeff';">
                                        <i class="fas fa-chevron-left"></i> Prev
                                    </a>
                                @endif

                                {{-- Page numbers --}}
                                @foreach ($rows->getUrlRange(max(1, $rows->currentPage() - 2), min($rows->lastPage(), $rows->currentPage() + 2)) as $page => $url)
                                    @if ($page === $rows->currentPage())
                                        <button
                                            style="display:inline-flex;align-items:center;padding:6px 12px;background:#3b4fd8;border:1px solid #3b4fd8;border-radius:8px;font-size:12px;font-weight:700;color:#fff;cursor:pointer;">
                                            {{ $page }}
                                        </button>
                                    @else
                                        <a href="{{ $url }}"
                                            style="display:inline-flex;align-items:center;padding:6px 12px;background:#f1f5f9;border:1px solid #e8eeff;border-radius:8px;font-size:12px;font-weight:600;color:#1e40af;text-decoration:none;transition:all .2s;"
                                            onmouseover="this.style.background='#dbeafe';this.style.borderColor='#bfdbfe';"
                                            onmouseout="this.style.background='#f1f5f9';this.style.borderColor='#e8eeff';">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if ($rows->hasMorePages())
                                    <a href="{{ $rows->nextPageUrl() }}"
                                        style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#f1f5f9;border:1px solid #e8eeff;border-radius:8px;font-size:12px;font-weight:600;color:#1e40af;text-decoration:none;transition:all .2s;"
                                        onmouseover="this.style.background='#dbeafe';this.style.borderColor='#bfdbfe';"
                                        onmouseout="this.style.background='#f1f5f9';this.style.borderColor='#e8eeff';">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <span
                                        style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#f1f5f9;border:1px solid #e8eeff;border-radius:8px;font-size:12px;font-weight:600;color:#cbd5e1;cursor:not-allowed;">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
