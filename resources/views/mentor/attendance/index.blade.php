@extends('layouts.app')

@section('title', 'Absensi Anak Bimbingan - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/mentor-attendance-index.css') }}?v={{ @filemtime(public_path('css/pages/mentor-attendance-index.css')) }}">
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
                                                        alt="{{ $absentIntern->name }}" class="avatar" loading="lazy" decoding="async">
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
                                                        alt="{{ $a->intern->name }}" class="avatar" loading="lazy" decoding="async">
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
                                                        title="Klik untuk melihat full size" loading="lazy" decoding="async">
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
                                                        title="Klik untuk melihat full size" loading="lazy" decoding="async">
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
