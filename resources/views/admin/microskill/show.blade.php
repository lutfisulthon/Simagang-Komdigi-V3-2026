@extends('layouts.app')

@section('title', 'Daftar Peserta Micro Skill - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-microskill-show.css') }}?v={{ @filemtime(public_path('css/pages/admin-microskill-show.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- ── PROFILE HEADER ── --}}
            <div class="profile-strip anim-1">
                <div class="px-6 py-7 flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">

                    <div class="avatar-ring flex-shrink-0">
                        <div class="avatar-inner">
                            <i class="fas fa-graduation-cap text-2xl text-white"></i>
                        </div>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Micro Skill</p>
                        <h1 class="text-xl font-bold text-white mb-1">{{ $micro->judul_micro }}</h1>
                        <p class="text-blue-300 text-sm mt-1">
                            <i class="fas fa-users mr-1"></i>
                            Daftar peserta yang telah menyelesaikan micro skill ini
                        </p>
                    </div>

                    <div class="flex-shrink-0 flex flex-col sm:flex-row items-center gap-3">
                        <div class="text-center sm:text-right">
                            <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Pengumpulan</p>
                            <p class="text-4xl font-extrabold text-white mono">{{ $submissions->total() }}</p>
                        </div>
                        <a href="{{ route('admin.microskill.index') }}" class="btn-back">
                            <i class="fas fa-arrow-left text-xs"></i>
                            Kembali
                        </a>
                    </div>

                </div>
            </div>

            {{-- ── TABEL PESERTA ── --}}
            <div class="panel anim-2">
                <div class="table-header">
                    <div class="table-header-title">
                        <div class="table-icon"><i class="fas fa-list-check"></i></div>
                        <div>
                            <p class="table-title">Daftar Pengumpulan</p>
                            <p class="table-subtitle">Peserta yang telah mengirim bukti pengerjaan</p>
                        </div>
                    </div>
                    <span class="count-badge">{{ $submissions->total() }} peserta</span>
                </div>

                <div style="overflow-x:auto;border-radius:14px;border:1px solid #e8eeff;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Peserta</th>
                                <th>Institusi</th>
                                <th>Dikirim</th>
                                <th style="text-align:center;">Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                                <tr>
                                    <td>
                                        <div class="avatar-cell">
                                            @if (!empty($s->intern->photo_path))
                                                <img src="{{ url('storage/' . $s->intern->photo_path) }}"
                                                    alt="{{ $s->intern->name }}"
                                                    style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #ddd6fe;flex-shrink:0;" loading="lazy" decoding="async">
                                            @else
                                                <div class="avatar-sm-placeholder">
                                                    {{ strtoupper(substr($s->intern->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="cell-name">{{ $s->intern->name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color:#475569;">{{ $s->intern->institution }}</td>
                                    <td class="mono" style="font-size:12px;color:#64748b;">
                                        {{ $s->submitted_at ? \Carbon\Carbon::parse($s->submitted_at)->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($s->photo_path)
                                            @php
                                                $microSkillFilename = basename($s->photo_path);
                                                $microSkillUrl = URL::temporarySignedRoute(
                                                    'admin.microskill.photo',
                                                    now()->addSeconds(30),
                                                    ['filename' => $microSkillFilename],
                                                );
                                            @endphp
                                            @can('view', $s)
                                                <a href="{{ $microSkillUrl }}" target="_blank" class="bukti-btn">
                                                    <i class="fas fa-image text-xs"></i>
                                                    Lihat Bukti
                                                </a>
                                            @else
                                                <span style="color:#d1d5db;font-size:12px;">Tidak ada akses</span>
                                            @endcan
                                        @else
                                            <span style="color:#d1d5db;font-size:12px;">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding:40px;text-align:center;color:#94a3b8;">
                                        <i class="fas fa-inbox"
                                            style="font-size:2.5rem;display:block;margin-bottom:10px;color:#e2e8f0;"></i>
                                        <p style="font-size:13px;margin:0;">Belum ada peserta yang mengerjakan micro skill
                                            ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($submissions->hasPages())
                    <div style="margin-top:16px;">
                        {{ $submissions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
