@extends('layouts.app')

@section('title', 'Dashboard Mentor - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/mentor-dashboard.css') }}?v={{ @filemtime(public_path('css/pages/mentor-dashboard.css')) }}">
@endpush

@section('content')
    @php
        if (!isset($alumni)) {
            $alumniCollection = $interns->filter(function ($i) {
                $s = strtolower($i->status ?? '');
                return in_array($s, ['tidak aktif', 'inactive', 'pelepasan', 'released', 'alumni']);
            });
        } else {
            $alumniCollection = $alumni;
        }
        $alumniCount = is_countable($alumniCollection) ? $alumniCollection->count() : 0;
        $hadirCount = $todayAttendances->where('status', 'hadir')->count();
        $izinCount = $todayAttendances->whereIn('status', ['izin', 'sakit'])->count();
        $alfaCount = $todayAttendances->where('status', 'alfa')->count() + $todayAbsentInterns->count();
        $internsTotal = $interns->count();
        $hadirPct = $internsTotal ? round(($hadirCount / $internsTotal) * 100) : 0;
    @endphp

    <div class="mdash py-7">
        <div class="mdash-in max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ── BANNER + STATISTIK ── --}}
            <div class="mdash-sec" data-dm-native style="display:flex;flex-direction:column;gap:20px;">

                <div class="m-hero a1">
                    <div class="m-hero__avatar"><i class="fas fa-chalkboard-teacher"></i></div>

                    <div class="m-hero__main">
                        <span class="m-chip"><i class="fas fa-circle"></i> Aktif Membimbing</span>
                        <h1>{{ $mentor?->name ?? auth()->user()->name }}</h1>
                        <p>
                            Ringkasan bimbingan hari ini &mdash;
                            <strong>{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('l, d F Y') }}</strong>
                        </p>
                        @if (isset($todayLogbooksPending) && count($todayLogbooksPending) > 0)
                            <div class="m-alert">
                                <i class="fas fa-book"></i>
                                <span>
                                    Ada {{ count($todayLogbooksPending) }} logbook perlu direview hari ini.
                                    <a href="{{ route('mentor.logbook.index') }}">Lihat sekarang</a>
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="m-meter">
                        <small>Kehadiran Hari Ini</small>
                        <b class="mono">{{ $hadirPct }}<span>%</span></b>
                        <em>{{ $hadirCount }} / {{ $internsTotal }} hadir</em>
                        <div class="m-bar"><div id="hero-bar"></div></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 a2">
                    <div class="m-tile tone-blue">
                        <div class="m-tile__ico"><i class="fas fa-users"></i></div>
                        <p class="m-tile__lbl m-0" style="margin:0;">Anak Magang</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $internsTotal }}</p>
                    </div>

                    <div class="m-tile tone-green">
                        <div class="m-tile__ico"><i class="fas fa-calendar-check"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Hadir Hari Ini</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $hadirCount }}</p>
                        <div class="att-bar-track"><div class="att-bar-fill" data-w="{{ $hadirPct }}"></div></div>
                    </div>

                    <div class="m-tile tone-amber">
                        <div class="m-tile__ico"><i class="fas fa-calendar-times"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Izin / Sakit</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $izinCount }}</p>
                        <div class="att-bar-track"><div class="att-bar-fill" data-w="{{ $internsTotal ? round(($izinCount / $internsTotal) * 100) : 0 }}"></div></div>
                    </div>

                    <div class="m-tile tone-slate">
                        <div class="m-tile__ico"><i class="fas fa-user-times"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Tidak Hadir</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $alfaCount }}</p>
                        <div class="att-bar-track"><div class="att-bar-fill" data-w="{{ $internsTotal ? round(($alfaCount / $internsTotal) * 100) : 0 }}"></div></div>
                    </div>

                    <div class="m-tile tone-violet">
                        <div class="m-tile__ico"><i class="fas fa-graduation-cap"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Mikro Skill Hari Ini</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $microTodayTotal }}</p>
                        <p class="m-tile__note" style="margin:0;">Total hari ini</p>
                    </div>

                    <div class="m-tile tone-rose">
                        <div class="m-tile__ico"><i class="fas fa-user-graduate"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Alumni</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $alumniCount }}</p>
                    </div>
                </div>
            </div>

            {{-- ── WORKSPACE: Perlu Review & Tugas Terlambat (hanya tampil bila modul Workspace aktif) ── --}}
            <x-workspace.dashboard-widgets role="mentor" class="a2" />

            {{-- ── DAFTAR, PERINGKAT, ABSENSI, LOGBOOK ── --}}
            <div class="mdash-sec" data-dm-native style="display:flex;flex-direction:column;gap:20px;">

                <div class="m-cols">

                    {{-- KIRI: daftar anak magang --}}
                    <div class="m-card a3">
                        <div class="m-card__head">
                            <div class="m-ico tone-blue"><i class="fas fa-users-cog"></i></div>
                            <div>
                                <h2 class="m-title">Daftar Anak Magang</h2>
                                <p class="m-sub">Peserta yang Anda bimbing</p>
                            </div>
                            <span class="m-badge">{{ $internsTotal }} orang</span>
                        </div>
                        <div class="m-card__body">
                            <div class="m-seg">
                                <button class="tab-btn active" onclick="switchTab('aktif', this)">
                                    <i class="fas fa-user-check mr-1"></i> Aktif ({{ $internsTotal }})
                                </button>
                                <button class="tab-btn" onclick="switchTab('alumni', this)">
                                    <i class="fas fa-user-graduate mr-1"></i> Alumni ({{ $alumniCount }})
                                </button>
                            </div>

                            {{-- Tab aktif --}}
                            <div id="tab-aktif" class="tab-content active">
                                @php
                                    $internsList = collect($interns);
                                    $internsPerPage = 8;
                                    $internsCurrentPage = request('interns_page', 1);
                                    $internsSliced = $internsList->slice(
                                        ($internsCurrentPage - 1) * $internsPerPage,
                                        $internsPerPage,
                                    );
                                    $internsTotalPages = ceil($internsList->count() / $internsPerPage);
                                @endphp
                                <div class="intern-list">
                                    @forelse($internsSliced as $intern)
                                        <div class="intern-row">
                                            <div class="avatar-sm">{{ strtoupper(substr($intern->name, 0, 1)) }}</div>
                                            <div class="flex-1 min-w-0">
                                                <p class="m-name">{{ $intern->name }}</p>
                                                <p class="m-meta">{{ $intern->institution }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <span class="mini-pill tone-blue"><i class="fas fa-calendar-alt"></i> {{ $intern->attendances_count }}</span>
                                                <span class="mini-pill tone-violet"><i class="fas fa-book"></i> {{ $intern->micro_skills_count }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="m-empty">
                                            <i class="fas fa-user-slash"></i>
                                            <p>Belum ada anak magang.</p>
                                        </div>
                                    @endforelse
                                </div>
                                @if ($internsList->count() > $internsPerPage)
                                    <div class="m-pager">
                                        <span>
                                            Menampilkan
                                            {{ ($internsCurrentPage - 1) * $internsPerPage + 1 }}–{{ min($internsCurrentPage * $internsPerPage, $internsList->count()) }}
                                            dari {{ $internsList->count() }}
                                        </span>
                                        <div>
                                            @if ($internsCurrentPage > 1)
                                                <a href="?interns_page=1" class="pg"><i class="fas fa-chevron-left"></i> Awal</a>
                                            @endif
                                            @for ($i = max(1, $internsCurrentPage - 1); $i <= min($internsTotalPages, $internsCurrentPage + 1); $i++)
                                                @if ($i === $internsCurrentPage)
                                                    <button class="pg is-on">{{ $i }}</button>
                                                @else
                                                    <a href="?interns_page={{ $i }}" class="pg">{{ $i }}</a>
                                                @endif
                                            @endfor
                                            @if ($internsCurrentPage < $internsTotalPages)
                                                <a href="?interns_page={{ $internsTotalPages }}" class="pg">Akhir <i class="fas fa-chevron-right"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Tab alumni --}}
                            <div id="tab-alumni" class="tab-content">
                                @php
                                    $alumniList = collect($alumniCollection);
                                    $alumniPerPage = 8;
                                    $alumniCurrentPage = request('alumni_page', 1);
                                    $alumniSliced = $alumniList->slice(
                                        ($alumniCurrentPage - 1) * $alumniPerPage,
                                        $alumniPerPage,
                                    );
                                    $alumniTotalPages = ceil($alumniList->count() / $alumniPerPage);
                                @endphp
                                <div class="intern-list">
                                    @forelse($alumniSliced as $a)
                                        <div class="intern-row is-alumni">
                                            <div class="avatar-sm is-rose">{{ strtoupper(substr($a->name, 0, 1)) }}</div>
                                            <div class="flex-1 min-w-0">
                                                <p class="m-name">{{ $a->name }}</p>
                                                <p class="m-meta">{{ $a->institution }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <span class="mini-pill tone-blue"><i class="fas fa-calendar-alt"></i> {{ $a->attendances_count ?? 0 }}</span>
                                                <span class="mini-pill tone-violet"><i class="fas fa-book"></i> {{ $a->micro_skills_count ?? 0 }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="m-empty">
                                            <i class="fas fa-inbox"></i>
                                            <p>Belum ada data alumni.</p>
                                        </div>
                                    @endforelse
                                </div>
                                @if ($alumniList->count() > $alumniPerPage)
                                    <div class="m-pager">
                                        <span>
                                            Menampilkan
                                            {{ ($alumniCurrentPage - 1) * $alumniPerPage + 1 }}–{{ min($alumniCurrentPage * $alumniPerPage, $alumniList->count()) }}
                                            dari {{ $alumniList->count() }}
                                        </span>
                                        <div>
                                            @if ($alumniCurrentPage > 1)
                                                <a href="?alumni_page=1" class="pg"><i class="fas fa-chevron-left"></i> Awal</a>
                                            @endif
                                            @for ($i = max(1, $alumniCurrentPage - 1); $i <= min($alumniTotalPages, $alumniCurrentPage + 1); $i++)
                                                @if ($i === $alumniCurrentPage)
                                                    <button class="pg is-on">{{ $i }}</button>
                                                @else
                                                    <a href="?alumni_page={{ $i }}" class="pg">{{ $i }}</a>
                                                @endif
                                            @endfor
                                            @if ($alumniCurrentPage < $alumniTotalPages)
                                                <a href="?alumni_page={{ $alumniTotalPages }}" class="pg">Akhir <i class="fas fa-chevron-right"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: peringkat + donut --}}
                    <div class="m-stack">

                        <div class="m-card a3">
                            <div class="m-card__head">
                                <div class="m-ico tone-amber"><i class="fas fa-trophy"></i></div>
                                <div>
                                    <h2 class="m-title">Top Mikro Skill</h2>
                                    <p class="m-sub">Top 3 bimbingan</p>
                                </div>
                            </div>
                            <div class="m-card__body">
                                @if (isset($topMicroSkills) && count($topMicroSkills))
                                    <div style="display:flex;flex-direction:column;gap:10px;">
                                        @foreach ($topMicroSkills->take(3) as $index => $row)
                                            <div class="podium-item {{ ['tone-amber', 'tone-slate', 'tone-rose'][$index] ?? 'tone-slate' }}">
                                                <div class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                                                @if (!empty($row['photo_path']))
                                                    <img src="{{ url('storage/' . $row['photo_path']) }}" class="m-photo" alt="{{ $row['name'] }}" loading="lazy" decoding="async">
                                                @else
                                                    <div class="avatar-sm">{{ strtoupper(substr($row['name'], 0, 1)) }}</div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <p class="m-name">{{ $row['name'] }}</p>
                                                    <p class="m-meta">{{ $row['institution'] }}</p>
                                                </div>
                                                <span class="m-score"><i class="fas fa-star"></i> {{ $row['total'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="m-empty">
                                        <i class="fas fa-chart-line"></i>
                                        <p>Belum ada data.</p>
                                    </div>
                                @endif
                                <a href="{{ route('mentor.microskill.leaderboard') }}" class="m-btn">
                                    Lihat Selengkapnya <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        @php
                            $circ = 188.5;
                            $hadirDash = $internsTotal ? ($hadirCount / $internsTotal) * $circ : 0;
                            $izinDash = $internsTotal ? ($izinCount / $internsTotal) * $circ : 0;
                            $alfaDash = $internsTotal ? ($alfaCount / $internsTotal) * $circ : 0;
                        @endphp
                        <div class="m-card a4">
                            <div class="m-card__head">
                                <div class="m-ico tone-green"><i class="fas fa-chart-pie"></i></div>
                                <div>
                                    <h2 class="m-title">Distribusi Kehadiran</h2>
                                    <p class="m-sub">Hari ini</p>
                                </div>
                            </div>
                            <div class="m-card__body">
                                <div class="m-donut">
                                    <svg width="120" height="120" viewBox="0 0 80 80">
                                        <circle class="track" cx="40" cy="40" r="30" />
                                        <circle id="d-hadir" cx="40" cy="40" r="30" fill="none" stroke="#22c55e"
                                            stroke-width="10" stroke-dasharray="{{ $circ }}"
                                            stroke-dashoffset="{{ $circ }}"
                                            style="transition:stroke-dashoffset 1.2s ease;" data-dash="{{ $hadirDash }}"
                                            data-start="0" />
                                        <circle id="d-izin" cx="40" cy="40" r="30" fill="none" stroke="#f59e0b"
                                            stroke-width="10" stroke-dasharray="{{ $circ }}"
                                            stroke-dashoffset="{{ $circ }}"
                                            style="transition:stroke-dashoffset 1.2s ease .15s;"
                                            data-dash="{{ $izinDash }}" data-start="{{ $hadirDash }}" />
                                        <circle id="d-alfa" cx="40" cy="40" r="30" fill="none" stroke="#ef4444"
                                            stroke-width="10" stroke-dasharray="{{ $circ }}"
                                            stroke-dashoffset="{{ $circ }}"
                                            style="transition:stroke-dashoffset 1.2s ease .3s;"
                                            data-dash="{{ $alfaDash }}" data-start="{{ $hadirDash + $izinDash }}" />
                                    </svg>
                                    <div class="m-donut__c">
                                        <b class="mono">{{ $hadirPct }}%</b>
                                        <small>Hadir</small>
                                    </div>
                                </div>
                                <div class="m-legend">
                                    <div><span><i style="background:#22c55e;"></i> Hadir</span><b class="mono">{{ $hadirCount }}</b></div>
                                    <div><span><i style="background:#f59e0b;"></i> Izin/Sakit</span><b class="mono">{{ $izinCount }}</b></div>
                                    <div><span><i style="background:#ef4444;"></i> Tidak Hadir</span><b class="mono">{{ $alfaCount }}</b></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── ABSENSI HARI INI ── --}}
                <div class="m-card a5">
                    <div class="m-card__head" style="padding-bottom:14px;">
                        <div class="m-ico tone-blue"><i class="fas fa-clipboard-check"></i></div>
                        <div>
                            <h2 class="m-title">Absensi Hari Ini</h2>
                            <p class="m-sub">Kehadiran seluruh anak bimbingan</p>
                        </div>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="m-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Foto In</th>
                                    <th>Check Out</th>
                                    <th>Foto Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hadirExists = false; @endphp

                                @forelse($todayAttendances->where('status', 'hadir') as $attendance)
                                    @php $hadirExists = true; @endphp
                                    <tr class="att-tr">
                                        <td>
                                            <div class="m-who">
                                                <div class="avatar-sm">{{ strtoupper(substr($attendance->intern->name, 0, 1)) }}</div>
                                                <span>{{ $attendance->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="s-badge tone-green"><i class="fas fa-check-circle"></i> Hadir</span></td>
                                        <td class="m-time">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_path)
                                                @php $photoUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_path)]); @endphp
                                                @can('view', $attendance)
                                                    <img src="{{ $photoUrl }}" alt="In" class="m-thumb" onclick="window.open('{{ $photoUrl }}', '_blank')" loading="lazy" decoding="async">
                                                @else
                                                    <span class="m-dash">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                        <td class="m-time">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_checkout)
                                                @php $photoOutUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_checkout)]); @endphp
                                                @can('view', $attendance)
                                                    <img src="{{ $photoOutUrl }}" alt="Out" class="m-thumb" onclick="window.open('{{ $photoOutUrl }}', '_blank')" loading="lazy" decoding="async">
                                                @else
                                                    <span class="m-dash">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse

                                @forelse($todayAttendances->whereIn('status', ['izin', 'sakit', 'alfa']) as $attendance)
                                    <tr class="att-tr">
                                        <td>
                                            <div class="m-who">
                                                <div class="avatar-sm">{{ strtoupper(substr($attendance->intern->name, 0, 1)) }}</div>
                                                <span>{{ $attendance->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="s-badge @if ($attendance->status == 'izin') tone-amber @elseif($attendance->status == 'sakit') tone-violet @else tone-rose @endif">
                                                @if (in_array($attendance->status, ['izin', 'sakit']))
                                                    <i class="fas fa-notes-medical"></i>
                                                @else
                                                    <i class="fas fa-times-circle"></i>
                                                @endif
                                                {{ $attendance->status == 'alfa' ? 'Tidak Hadir' : ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td class="m-time">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_path)
                                                @php $photoUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_path)]); @endphp
                                                <img src="{{ $photoUrl }}" alt="In" class="m-thumb" onclick="window.open('{{ $photoUrl }}', '_blank')" loading="lazy" decoding="async">
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                        <td class="m-time">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_checkout)
                                                @php $photoOutUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_checkout)]); @endphp
                                                <img src="{{ $photoOutUrl }}" alt="Out" class="m-thumb" onclick="window.open('{{ $photoOutUrl }}', '_blank')" loading="lazy" decoding="async">
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse

                                @forelse($todayAbsentInterns as $absentIntern)
                                    <tr class="att-tr att-tr-absent">
                                        <td>
                                            <div class="m-who">
                                                <div class="avatar-sm is-rose">{{ strtoupper(substr($absentIntern->name, 0, 1)) }}</div>
                                                <span>{{ $absentIntern->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="s-badge tone-rose"><i class="fas fa-times-circle"></i> Belum Absen</span></td>
                                        <td colspan="4" class="m-dash" style="font-style:italic;">Belum melakukan absensi hari ini</td>
                                    </tr>
                                @empty
                                    @if (!$hadirExists)
                                        <tr>
                                            <td colspan="6">
                                                <div class="m-empty">
                                                    <i class="fas fa-inbox"></i>
                                                    <p>Belum ada data absensi hari ini.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── LOGBOOK PERLU REVIEW ── --}}
                <div class="m-card a5">
                    <div class="m-card__head">
                        <div class="m-ico tone-violet"><i class="fas fa-book"></i></div>
                        <div>
                            <h2 class="m-title">Logbook Hari Ini Perlu Di-Review</h2>
                            <p class="m-sub">Tinjau dan setujui logbook anak bimbingan</p>
                        </div>
                        <span class="m-badge">{{ count($todayLogbooksPending ?? []) }} logbook</span>
                    </div>
                    <div class="m-card__body">
                        @if ($todayLogbooksPending && count($todayLogbooksPending) > 0)
                            <div class="m-logs">
                                @foreach ($todayLogbooksPending as $logbook)
                                    @php
                                        $isApproved = $logbook->approval_status === 'approved';
                                        $isPending = !$isApproved;
                                    @endphp
                                    <div class="logbook-card">
                                        <div class="logbook-card__head">
                                            <div class="logbook-card__who">
                                                <div class="avatar-sm">{{ strtoupper(substr($logbook->intern->name, 0, 1)) }}</div>
                                                <div style="min-width:0;">
                                                    <p class="m-name">{{ $logbook->intern->name }}</p>
                                                    <p class="m-meta">{{ $logbook->intern->institution }}</p>
                                                </div>
                                            </div>
                                            @if ($isApproved)
                                                <span class="s-badge tone-green"><i class="fas fa-check-circle"></i> Approved</span>
                                            @else
                                                <span class="s-badge tone-amber"><i class="fas fa-clock"></i> Pending</span>
                                            @endif
                                        </div>

                                        <div class="logbook-card__body">
                                            <p class="m-label" style="margin-bottom:5px;">Aktivitas</p>
                                            <p class="logbook-card__act">{{ $logbook->activity }}</p>
                                            <div class="logbook-card__row">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>{{ \Carbon\Carbon::parse($logbook->date)->locale('id')->translatedFormat('d M Y') }}</span>
                                            </div>
                                            @if ($logbook->photo_path)
                                                <div class="logbook-card__row is-link">
                                                    <i class="fas fa-image"></i> Ada dokumentasi foto
                                                </div>
                                            @endif
                                        </div>

                                        <div class="logbook-card__foot">
                                            <a href="{{ route('mentor.logbook.show', $logbook->id) }}" class="m-act is-soft">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                            @if ($isPending)
                                                <button type="button" class="m-act is-ok" onclick="openApprovalModal({{ $logbook->id }})">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="m-empty" style="padding:44px 24px;">
                                <i class="fas fa-inbox"></i>
                                <p style="font-size:14px;font-weight:600;">Belum ada logbook yang perlu direview hari ini.</p>
                                <p>Logbook akan muncul di sini ketika anak magang mengupload logbook baru atau jika ada yang belum disetujui.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── APPROVAL MODAL ── --}}
    {{-- Wrapper uses min-height trick so fixed-like overlay works in normal flow --}}
    <div id="approvalModalOverlay"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
        <div
            style="background:#fff;border-radius:18px;border:1px solid #e6ebf3;box-shadow:0 24px 60px rgba(15,23,42,.28);max-width:480px;width:100%;max-height:90vh;overflow-y:auto;">
            <div
                style="background:#eef4ff;border-bottom:1px solid #d6e4ff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;border-radius:18px 18px 0 0;">
                <div>
                    <p
                        style="color:#3b5b94;font-size:11px;text-transform:uppercase;letter-spacing:.06em;margin:0;font-weight:700;">
                        Persetujuan Logbook</p>
                    <h3 style="color:#0f172a;font-size:17px;font-weight:800;margin:6px 0 0;">Setujui Logbook</h3>
                </div>
                <button onclick="closeApprovalModal()"
                    style="background:#fff;border:1px solid #d6e4ff;color:#475569;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;"
                    onmouseover="this.style.background='#f1f6ff';"
                    onmouseout="this.style.background='#fff';">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="padding:22px 24px;">
                <form id="approvalForm" style="display:flex;flex-direction:column;gap:14px;">
                    <input type="hidden" id="logbookId" name="logbook_id">
                    @csrf
                    <div>
                        <label
                            style="display:block;font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">
                            Catatan Approval (Opsional)
                        </label>
                        <textarea name="note" id="approvalNote"
                            style="width:100%;padding:11px;border:1px solid #dfe5ef;border-radius:10px;font-size:13px;font-family:inherit;resize:vertical;min-height:90px;outline:none;transition:border-color .2s;"
                            placeholder="Tambahkan catatan atau feedback untuk anak magang..." onfocus="this.style.borderColor='#2563eb';"
                            onblur="this.style.borderColor='#dfe5ef';"></textarea>
                    </div>
                    <div style="display:flex;gap:10px;padding-top:10px;border-top:1px solid #e5e7eb;">
                        <button type="button" onclick="closeApprovalModal()"
                            style="flex:1;padding:11px;background:#f1f5f9;color:#475569;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:13px;"
                            onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
                            Batal
                        </button>
                        <button type="submit"
                            style="flex:1;padding:11px;background:#16a34a;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:13px;"
                            onmouseover="this.style.background='#15803d';" onmouseout="this.style.background='#16a34a';">
                            <i class="fas fa-check" style="margin-right:5px;"></i> Setujui Logbook
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="logbookAlertOverlay"
        style="display:none;position:fixed;inset:0;background:rgba(26,26,46,0.75);z-index:10000;align-items:center;justify-content:center;padding:24px;">
        <div style="background:#fff;border-radius:20px;max-width:420px;width:100%;overflow:hidden;border:1px solid #e6ebf3;box-shadow:0 24px 60px rgba(15,23,42,.28);">

            {{-- Header berwarna --}}
            <div style="background:#fff1f2;padding:24px 24px 36px;position:relative;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                    <div style="width:52px;height:52px;border-radius:14px;background:#ffe4e6;border:1px solid #fecdd3;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-book-open" style="color:#e11d48;font-size:22px;"></i>
                    </div>
                    <button onclick="closeLogbookAlert()"
                        style="width:28px;height:28px;border-radius:8px;background:#fff;border:1px solid #fecdd3;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#be123c;font-size:14px;">
                        &times;
                    </button>
                </div>
                <p style="margin:14px 0 2px;font-size:18px;font-weight:800;color:#0f172a;">Logbook Baru Hari Ini</p>
                <p style="margin:0;font-size:13px;color:#9f1239;">{{ now()->isoFormat('dddd, D MMMM YYYY') }} &mdash; review diperlukan</p>
                <div style="position:absolute;bottom:-1px;left:0;right:0;height:18px;background:#fff;border-radius:20px 20px 0 0;"></div>
            </div>

            {{-- Body --}}
            <div style="padding:20px 24px 24px;">
                <div style="display:flex;gap:10px;margin-bottom:18px;">
                    <div style="flex:1;border-radius:12px;padding:12px 14px;border:1px solid #e5e7eb;background:#f9fafb;">
                        <p style="font-size:11px;color:#9ca3af;margin:0 0 4px;text-transform:uppercase;letter-spacing:.04em;">Menunggu review</p>
                        <p style="font-size:22px;font-weight:800;color:#e11d48;margin:0;">{{ count($todayLogbooksPending ?? []) }}</p>
                    </div>
                    <div style="flex:1;border-radius:12px;padding:12px 14px;border:1px solid #e5e7eb;background:#f9fafb;">
                        <p style="font-size:11px;color:#9ca3af;margin:0 0 4px;text-transform:uppercase;letter-spacing:.04em;">Batas waktu</p>
                        <p style="font-size:22px;font-weight:800;color:#c2410c;margin:0;">Hari ini</p>
                    </div>
                </div>

                <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#fff7ed;border-radius:12px;border:1px solid #fed7aa;margin-bottom:18px;">
                    <i class="fas fa-info-circle" style="color:#c2410c;font-size:16px;flex-shrink:0;margin-top:2px;"></i>
                    <p style="font-size:13px;color:#9a3412;margin:0;line-height:1.6;">Mahasiswa menunggu feedback kamu. Tinjau logbook sekarang agar progres mereka tidak terhambat.</p>
                </div>

                <div style="display:flex;gap:8px;">
                    <a href="{{ route('mentor.logbook.index') }}"
                        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 18px;background:#e11d48;color:#fff;border-radius:12px;box-shadow:0 8px 18px -8px rgba(225,29,72,.6);text-decoration:none;font-weight:700;font-size:14px;">
                        <i class="fas fa-arrow-right" style="font-size:14px;"></i>
                        Tinjau sekarang
                    </a>
                    <button type="button" onclick="closeLogbookAlert()"
                        style="padding:11px 16px;background:transparent;color:#6b7280;border:1px solid #e5e7eb;border-radius:12px;font-weight:600;font-size:14px;cursor:pointer;">
                        Nanti
                    </button>
                </div>
            </div>

        </div>
    </div>
    @push('scripts')
        <script>
            // ── Animate bars & donut on load ──
            window.addEventListener('load', function() {
                // Hero bar
                document.getElementById('hero-bar').style.width = '{{ $hadirPct }}%';

                // Stat tile bars
                document.querySelectorAll('.att-bar-fill').forEach(function(el) {
                    el.style.width = (el.dataset.w || 0) + '%';
                });

                // Donut segments
                const circ = {{ $circ }};
                ['hadir', 'izin', 'alfa'].forEach(function(id) {
                    const el = document.getElementById('d-' + id);
                    if (!el) return;
                    const dash = parseFloat(el.dataset.dash) || 0;
                    const start = parseFloat(el.dataset.start) || 0;
                    el.style.strokeDashoffset = circ - dash;
                    el.style.strokeDasharray = dash + ' ' + (circ - dash);
                    el.style.transform = 'rotate(' + ((start / circ) * 360) + 'deg)';
                    el.style.transformOrigin = '40px 40px';
                });
            });

            // ── Tab switcher ──
            function switchTab(name, btn) {
                document.querySelectorAll('.tab-content').forEach(function(el) {
                    el.classList.remove('active');
                });
                document.querySelectorAll('.tab-btn').forEach(function(el) {
                    el.classList.remove('active');
                });
                document.getElementById('tab-' + name).classList.add('active');
                btn.classList.add('active');
            }

            // ── Logbook alert popup ──
            function openLogbookAlert() {
                const el = document.getElementById('logbookAlertOverlay');
                if (!el) return;
                el.style.display = 'flex';
            }

            function closeLogbookAlert() {
                const el = document.getElementById('logbookAlertOverlay');
                if (!el) return;
                el.style.display = 'none';
            }

            window.addEventListener('load', function() {
                const todayCount = {{ count($todayLogbooksPending ?? []) }};
                if (todayCount && todayCount > 0) {
                    setTimeout(openLogbookAlert, 700);
                }
            });

            // ── Approval modal ──
            function openApprovalModal(logbookId) {
                document.getElementById('logbookId').value = logbookId;
                document.getElementById('approvalNote').value = '';
                const overlay = document.getElementById('approvalModalOverlay');
                overlay.style.display = 'flex';
            }

            function closeApprovalModal() {
                document.getElementById('approvalModalOverlay').style.display = 'none';
            }

            document.getElementById('approvalModalOverlay').addEventListener('click', function(e) {
                if (e.target === this) closeApprovalModal();
            });

            document.getElementById('approvalForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const logbookId = document.getElementById('logbookId').value;
                const note = document.getElementById('approvalNote').value;
                const form = new FormData();
                form.append('status', 'approved');
                form.append('note', note);
                form.append('_token', document.querySelector('input[name="_token"]').value);
                form.append('_method', 'PUT');

                fetch('/mentor/logbook/' + logbookId + '/approve', {
                        method: 'POST',
                        body: form
                    })
                    .then(function(res) {
                        if (res.ok) {
                            alert('Logbook berhasil disetujui!');
                            window.location.reload();
                        } else {
                            alert('Terjadi kesalahan saat menyimpan approval.');
                        }
                    })
                    .catch(function(err) {
                        console.error('Error:', err);
                        alert('Terjadi kesalahan saat mengirim data.');
                    });
            });
        </script>
    @endpush
@endsection
