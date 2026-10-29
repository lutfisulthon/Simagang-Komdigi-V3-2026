<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.theme-head', ['splash' => true])
    @php
        // Workspace: aktif untuk admin (super_admin/admin_full), mentor, dan intern bila route-nya terdaftar.
        // $wsMock = true selama routes/workspace-preview.php dimuat (APP_ENV=local, sebelum backend siap).
        $wsUser = auth()->user();
        $wsRole = match (true) {
            ! $wsUser => null,
            $wsUser->isSuperAdmin() || $wsUser->isAdminFull() => 'admin',
            $wsUser->isMentor() => 'mentor',
            $wsUser->isIntern() => 'intern',
            default => null,
        };
        $wsEnabled = $wsRole !== null && Route::has('workspace.index');
        $wsMock = $wsEnabled && Route::has('workspace.preview.components');
    @endphp
    <title>@yield('title', 'Sistem Manajemen Peserta Magang')</title>
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('layouts.partials.workspace-head')
    {{-- Dialog konfirmasi: aktif untuk form ber-atribut data-confirm-* (lihat public/js/confirm-dialog.js) --}}
    <link rel="stylesheet" href="{{ asset('css/confirm-dialog.css') }}?v={{ @filemtime(public_path('css/confirm-dialog.css')) }}">
    <script defer src="{{ asset('js/confirm-dialog.js') }}?v={{ @filemtime(public_path('js/confirm-dialog.js')) }}"></script>
    @stack('styles')
    <style>
        @font-face {
            font-family: 'Etna';
            src: url('/fonts/Etna-Free-Font.otf') format('opentype');
        }

        .font-etna {
            font-family: 'Etna', sans-serif;
        }

        #sidebar {
            position: relative;
            flex-shrink: 0;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: visible !important;
        }

        #sidebar-tab {
            position: absolute;
            top: 50%;
            right: -20px;
            transform: translateY(-50%);
            z-index: 20;
            width: 20px;
            height: 60px;
            background: white;
            border: 1px solid #e5e7eb;
            border-left: none;
            border-radius: 0 10px 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 3px 0 8px rgba(0, 0, 0, 0.08);
            transition: background 0.15s ease, box-shadow 0.15s ease;
        }

        #sidebar-tab:hover {
            background: #eff6ff;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.13);
        }

        #sidebar-tab-icon {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #sidebar.sidebar-collapsed {
            width: 0 !important;
            min-width: 0 !important;
        }

        #sidebar.sidebar-collapsed > *:not(#sidebar-tab) {
            opacity: 0;
            pointer-events: none;
            visibility: hidden;
        }

        #sidebar.sidebar-collapsed #sidebar-tab {
            border-left: 1px solid #e5e7eb;
            border-radius: 0 10px 10px 0;
            box-shadow: 3px 0 8px rgba(0, 0, 0, 0.1);
        }

        #sidebar.sidebar-collapsed #sidebar-tab-icon {
            transform: rotate(180deg);
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <x-splash-screen />
    <div class="flex h-screen overflow-hidden">

        <!-- ===========================
        Sidebar Desktop
        ============================ -->
        <aside id="sidebar" class="hidden lg:flex lg:flex-col lg:w-64 bg-white shadow-lg">

            <button id="sidebar-tab" onclick="toggleDesktopSidebar()" title="Buka / Tutup sidebar" aria-label="Toggle sidebar">
                <svg id="sidebar-tab-icon" xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                    fill="none" stroke="#6b7280" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>

            <!-- Logo & Brand -->
            <div class="flex flex-col items-center p-4 border-b">
                <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="Logo" class="object-contain" style="width: 60px; height: 60px"/>
                <h1 class="text-3xl font-extrabold font-etna">
                    <span class="font-etna" style="color: #9d272a">SI</span><span class="font-etna" style="color: #086bb0">MA</span><span class="font-etna" style="color: #2dabe2">GA</span><span class="font-etna" style="color: #efc400">NG</span>
                </h1>
                <p class="font-etna" style="color: #626161; font-size:10px">Sistem Manajemen Magang</p>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto py-4">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-home w-5 mr-3"></i>
                            Dashboard
                        </a>
                        @include('layouts.partials.workspace-nav-link')

                        @if(session('session_expired'))
                            <div id="session-expired-modal" class="fixed inset-0 z-[100] flex items-center justify-center px-4">
                                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
                                <div class="relative z-10 w-full max-w-xl modal-pop-in">
                                    <div class="rounded-[2rem] border border-white/10 bg-white/10 p-2 shadow-2xl shadow-slate-950/50">
                                        <div class="rounded-[1.6rem] border border-cyan-400/15 bg-slate-950/90 p-6 sm:p-8">
                                            <div class="flex items-start justify-between gap-4 border-b border-white/10 pb-5">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-300">
                                                        <i class="fas fa-clock text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs uppercase tracking-[0.35em] text-cyan-300/80">SIMAGANG</p>
                                                        <h2 class="font-etna text-lg">
                                                            <span style="color: #9d272a">SI</span><span style="color: #086bb0">MA</span><span style="color: #2dabe2">GA</span><span style="color: #efc400">NG</span>
                                                        </h2>
                                                    </div>
                                                </div>
                                                <button type="button" onclick="document.getElementById('session-expired-modal')?.remove();" class="rounded-full p-2 text-slate-300 transition hover:bg-white/10 hover:text-white" aria-label="Tutup modal">
                                                    <i class="fas fa-xmark text-lg"></i>
                                                </button>
                                            </div>
                                            <div class="mt-6 text-center">
                                                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-cyan-300/80">Page Expired</p>
                                                <p class="mt-3 text-4xl font-black leading-none text-white sm:text-5xl">419</p>
                                                <h3 class="mt-4 text-2xl font-bold text-white">Sesi sudah expired.</h3>
                                                <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-slate-300 sm:text-base">
                                                    Sesi keamanan formulir telah habis. Anda tetap berada di halaman terakhir, tinggal muat ulang halaman atau login ulang bila diperlukan.
                                                </p>
                                            </div>
                                            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                                                <button type="button" onclick="window.location.reload();" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">
                                                    <i class="fas fa-rotate-right"></i>
                                                    Muat Ulang
                                                </button>
                                                @auth
                                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                                        <a href="{{ route('admin.dashboard') }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                                            <i class="fas fa-gauge-high"></i>
                                                            Dashboard Admin
                                                        </a>
                                                    @elseif(auth()->user()->isMentor())
                                                        <a href="{{ route('mentor.dashboard') }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                                            <i class="fas fa-gauge-high"></i>
                                                            Dashboard Mentor
                                                        </a>
                                                    @else
                                                        <a href="{{ route('intern.dashboard') }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                                            <i class="fas fa-gauge-high"></i>
                                                            Dashboard
                                                        </a>
                                                    @endif
                                                @else
                                                    <a href="{{ route('login') }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                                        <i class="fas fa-right-to-bracket"></i>
                                                        Login
                                                    </a>
                                                @endauth
                                            </div>
                                            <div class="mt-5 flex items-center gap-3 rounded-2xl border border-cyan-400/15 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-100">
                                                <i class="fas fa-circle-info text-cyan-300"></i>
                                                <p>{{ session('error', 'Sesi sudah expired. Silakan muat ulang halaman.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Manajemen Pengguna --}}
                        <div x-data="{ open: {{ request()->routeIs('admin.accounts.*', 'admin.mentor.*', 'admin.intern.*', 'admin.mitra.*', 'admin.team.*') ? 'true' : 'false' }} }" class="mt-1">
                            <button type="button" @click="open = !open" class="{{ request()->routeIs('admin.accounts.*', 'admin.mentor.*', 'admin.intern.*', 'admin.mitra.*', 'admin.team.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                <span class="flex items-center">
                                    <i class="fas fa-users-cog w-5 mr-3"></i>
                                    Manajemen Pengguna
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                @if(auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.accounts.index') }}" class="{{ request()->routeIs('admin.accounts.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-user-shield w-4 mr-3 text-xs"></i>
                                    Admin
                                </a>
                                @endif
                                @can('manage_mentors')
                                <a href="{{ route('admin.mentor.index') }}" class="{{ request()->routeIs('admin.mentor.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-chalkboard-teacher w-4 mr-3 text-xs"></i>
                                    Mentor
                                </a>
                                @endcan
                                <a href="{{ route('admin.mitra.index') }}"
       class="{{ request()->routeIs('admin.mitra.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
        <i class="fas fa-handshake w-4 mr-3 text-xs"></i>
        Mitra
    </a>
                                <a href="{{ route('admin.intern.index') }}" class="{{ request()->routeIs('admin.intern.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-user-graduate w-4 mr-3 text-xs"></i>
                                    Peserta Magang
                                </a>
                                @can('manage_teams')
                                <a href="{{ route('admin.team.index') }}" class="{{ request()->routeIs('admin.team.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-users w-4 mr-3 text-xs"></i>
                                    Tim Kerja/Bagian
                                </a>
                                @endcan
                            </div>
                        </div>

                        {{-- Operasional Magang --}}
                        <div x-data="{ open: {{ request()->routeIs('admin.lowongan.*','admin.verifikasi.*','admin.pengajuan.*', 'admin.attendance.*', 'admin.logbook.*', 'admin.microskill.*', 'admin.sharing-session.*', 'admin.aktivitas.*') ? 'true' : 'false' }} }" class="mt-1">
                            <button type="button" @click="open = !open" class="{{ request()->routeIs('admin.lowongan.*','admin.pengajuan.*', 'admin.attendance.*', 'admin.logbook.*', 'admin.microskill.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                <span class="flex items-center">
                                    <i class="fas fa-briefcase w-5 mr-3"></i>
                                    Operasional Magang
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                @can('manage_lowongan')
                                <a href="{{ route('admin.lowongan.index') }}" class="{{ request()->routeIs('admin.lowongan.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-briefcase w-4 mr-3 text-xs"></i>
                                    Lowongan
                                </a>
                                @endcan
                                @can('view_verifikasi_lowongan')
                                <a href="{{ route('admin.verifikasi.index') }}" class="{{ request()->routeIs('admin.verifikasi.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-briefcase w-4 mr-3 text-xs"></i>
                                    Verifikasi Lowongan
                                </a>
                                @endcan
                                @can('manage_pengajuan')
                                <a href="{{ route('admin.pengajuan.index') }}" class="{{ request()->routeIs('admin.pengajuan.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-file-contract w-4 mr-3 text-xs"></i>
                                    Pengajuan
                                </a>
                                @endcan
                                @can('view_attendance')
                                <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-calendar-check w-4 mr-3 text-xs"></i>
                                    Absensi
                                </a>
                                @endcan
                                @can('view_logbook')
                                <a href="{{ route('admin.logbook.index') }}" class="{{ request()->routeIs('admin.logbook.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-book w-4 mr-3 text-xs"></i>
                                    Logbook
                                </a>
                                @endcan
                                <a href="{{ route('admin.microskill.index') }}" class="{{ request()->routeIs('admin.microskill.index', 'admin.microskill.create', 'admin.microskill.edit', 'admin.microskill.show') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-star w-4 mr-3 text-xs"></i>
                                    Mikro Skill
                                </a>
                                <a href="{{ route('admin.sharing-session.index') }}"
                                class="{{ request()->routeIs('admin.sharing-session.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                <i class="fas fa-comments w-4 mr-3 text-xs"></i>
                                Sharing Session
                                </a>
                                <a href="{{ route('admin.aktivitas.index') }}"
class="{{ request()->routeIs('admin.aktivitas.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
<i class="fas fa-newspaper w-4 mr-3 text-xs"></i>
Aktivitas Terbaru
</a>
                            </div>
                        </div>

                        {{-- Monitoring & Evaluasi --}}
                        <div x-data="{ open: {{ request()->routeIs('admin.monitoring.*', 'admin.report.*') ? 'true' : 'false' }} }" class="mt-1">
                            <button type="button" @click="open = !open" class="{{ request()->routeIs('admin.monitoring.*', 'admin.report.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                <span class="flex items-center">
                                    <i class="fas fa-chart-line w-5 mr-3"></i>
                                    Monitoring &amp; Evaluasi
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                <a href="{{ route('admin.monitoring.index') }}" class="{{ request()->routeIs('admin.monitoring.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-chart-line w-4 mr-3 text-xs"></i>
                                    Monitoring Peserta Magang
                                </a>
                                <a href="{{ route('admin.monitoring.industri.index') }}" class="{{ request()->routeIs('admin.monitoring.industri.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-building w-4 mr-3 text-xs"></i>
                                    Monitoring Industri
                                </a>
                                @can('view_reports')
                                <a href="{{ route('admin.report.index') }}" class="{{ request()->routeIs('admin.report.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-file-alt w-4 mr-3 text-xs"></i>
                                    Laporan
                                </a>
                                @endcan
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <div class="mt-1 border-t pt-1">
                            <a href="{{ route('admin.rag.index') }}" class="{{ request()->routeIs('admin.rag.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center px-4 py-3 text-sm font-medium">
                                <i class="fas fa-brain w-5 mr-3"></i>
                                Manajemen AI RAG
                            </a>
                        </div>
                        @endif

                    @elseif(auth()->user()->isMentor())
                        <a href="{{ route('mentor.dashboard') }}" class="{{ request()->routeIs('mentor.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-home w-5 mr-3"></i>
                            Dashboard Mentor
                        </a>
                        @include('layouts.partials.workspace-nav-link')
                        <a href="{{ route('mentor.intern.index') }}" class="{{ request()->routeIs('mentor.intern.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-users w-5 mr-3"></i>
                            Anak Bimbingan
                        </a>
                        <a href="{{ route('mentor.attendance.index') }}" class="{{ request()->routeIs('mentor.attendance.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-calendar-check w-5 mr-3"></i>
                            Absensi
                        </a>
                        <a href="{{ route('mentor.logbook.index') }}" class="{{ request()->routeIs('mentor.logbook.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-book w-5 mr-3"></i>
                            Logbook
                        </a>
                        <a href="{{ route('mentor.report.index') }}" class="{{ request()->routeIs('mentor.report.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-file-alt w-5 mr-3"></i>
                            Laporan Akhir
                        </a>
                        <a href="{{ route('mentor.microskill.index') }}" class="{{ request()->routeIs('mentor.microskill.index', 'mentor.microskill.create', 'mentor.microskill.edit', 'mentor.microskill.show') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-star w-5 mr-3"></i>
                            Mikro Skill
                        </a>

                    @elseif(auth()->user()->isInstitusi())
                        <a href="{{ route('institusi.dashboard') }}" class="{{ request()->routeIs('institusi.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-home w-5 mr-3"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('institusi.lowongan.index') }}" class="{{ request()->routeIs('institusi.lowongan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-briefcase w-5 mr-3"></i>
                            Lowongan Magang
                        </a>
                        <a href="{{ route('institusi.pengajuan.index') }}" class="{{ request()->routeIs('institusi.pengajuan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-paper-plane w-5 mr-3"></i>
                            Pengajuan Magang
                        </a>
                        <div x-data="{ open: {{ request()->routeIs('institusi.intern.*', 'institusi.attendance.*', 'institusi.logbook.*', 'institusi.microskill.*', 'institusi.certificate.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open"
                                class="{{ request()->routeIs('institusi.intern.*', 'institusi.attendance.*', 'institusi.logbook.*', 'institusi.microskill.*', 'institusi.certificate.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                <span class="flex items-center">
                                    <i class="fas fa-chart-line w-5 mr-3"></i>
                                    Monitoring
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                <a href="{{ route('institusi.intern.index') }}" class="{{ request()->routeIs('institusi.intern.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-users w-4 mr-3 text-xs"></i>
                                    Peserta Magang
                                </a>
                                <a href="{{ route('institusi.attendance.index') }}" class="{{ request()->routeIs('institusi.attendance.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-clipboard-check w-4 mr-3 text-xs"></i>
                                    Absensi
                                </a>
                                <a href="{{ route('institusi.logbook.index') }}" class="{{ request()->routeIs('institusi.logbook.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-book w-4 mr-3 text-xs"></i>
                                    Logbook
                                </a>
                                <a href="{{ route('institusi.microskill.index') }}" class="{{ request()->routeIs('institusi.microskill.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-graduation-cap w-4 mr-3 text-xs"></i>
                                    Mikro Skill
                                </a>
                                <a href="{{ route('institusi.certificate.index') }}" class="{{ request()->routeIs('institusi.certificate.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-certificate w-4 mr-3 text-xs"></i>
                                    Nilai
                                </a>
                            </div>
                        </div>

                    @elseif(auth()->user()->isIndustri())
                        <a href="{{ route('industri.dashboard') }}" class="{{ request()->routeIs('industri.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-home w-5 mr-3"></i>
                            Dashboard Industri
                        </a>
                        <a href="{{ route('industri.lowongan.index') }}" class="{{ request()->routeIs('industri.lowongan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-briefcase w-5 mr-3"></i>
                            Lowongan Magang
                        </a>
                        <a href="{{ route('industri.pengajuan.index') }}" class="{{ request()->routeIs('industri.pengajuan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-file-alt w-5 mr-3"></i>
                            Pengajuan Magang
                        </a>
                        <div x-data="{ open: {{ request()->routeIs('industri.intern.*', 'industri.attendance.*', 'industri.logbook.*', 'industri.microskill.*', 'industri.report*') ? 'true' : 'false' }} }">
                            <button @click="open = !open"
                                class="{{ request()->routeIs('industri.intern.*', 'industri.attendance.*', 'industri.logbook.*', 'industri.microskill.*', 'industri.report*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                <span class="flex items-center">
                                    <i class="fas fa-chart-line w-5 mr-3"></i>
                                    Monitoring
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                <a href="{{ route('industri.intern.index') }}" class="{{ request()->routeIs('industri.intern.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-users w-5 mr-3"></i>
                                    Peserta Magang
                                </a>
                                <a href="{{ route('industri.attendance.index') }}" class="{{ request()->routeIs('industri.attendance.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-clipboard-check w-4 mr-3 text-xs"></i>
                                    Absensi
                                </a>
                                <a href="{{ route('industri.logbook.index') }}" class="{{ request()->routeIs('industri.logbook.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-book w-4 mr-3 text-xs"></i>
                                    Logbook
                                </a>
                                <a href="{{ route('industri.microskill.index') }}" class="{{ request()->routeIs('industri.microskill.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-graduation-cap w-4 mr-3 text-xs"></i>
                                    Mikro Skill
                                </a>
                                <a href="{{ route('industri.report.index') }}" class="{{ request()->routeIs('industri.certificate.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                    <i class="fas fa-certificate w-4 mr-3 text-xs"></i>
                                    Laporan Akhir
                                </a>
                            </div>
                        </div>

                    @else
                        <a href="{{ route('intern.dashboard') }}" class="{{ request()->routeIs('intern.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-home w-5 mr-3"></i>
                            Dashboard
                        </a>
                        @include('layouts.partials.workspace-nav-link')
                        <a href="{{ route('intern.attendance.index') }}" class="{{ request()->routeIs('intern.attendance.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-calendar-check w-5 mr-3"></i>
                            Absensi
                        </a>
                        <a href="{{ route('intern.logbook.index') }}" class="{{ request()->routeIs('intern.logbook.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-book w-5 mr-3"></i>
                            Logbook
                        </a>
                        <a href="{{ route('intern.report.index') }}" class="{{ request()->routeIs('intern.report.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-file-alt w-5 mr-3"></i>
                            Laporan
                        </a>
                        <a href="{{ route('intern.microskill.index') }}" class="{{ request()->routeIs('intern.microskill.index', 'intern.microskill.create', 'intern.microskill.edit', 'intern.microskill.show') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                            <i class="fas fa-star w-5 mr-3"></i>
                            Mikro Skill
                        </a>
                        <a href="{{ route('intern.sharing-session.index') }}"
                        class="{{ request()->routeIs('intern.sharing-session.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                        <i class="fas fa-comments w-5 mr-3"></i>
                        Sharing Session
                        </a>
                    @endif
                @endauth
            </nav>

            <!-- User Info & Logout -->
            <div class="border-t p-4">
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                            </div>
                        </div>
                    @elseif(auth()->user()->isInstitusi())
                        <a href="{{ route('institusi.profile.show') }}" class="flex items-center space-x-3 mb-3 hover:bg-gray-50 rounded-lg p-2 transition-colors duration-200">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">Institusi</p>
                            </div>
                        </a>
                    @elseif(auth()->user()->isIndustri())
                        <a href="{{ route('industri.dashboard') }}" class="flex items-center space-x-3 mb-3 hover:bg-gray-50 rounded-lg p-2 transition-colors duration-200">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-building text-blue-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">Industri</p>
                            </div>
                        </a>
                    @else
                        @php
                            $profileRoute = auth()->user()->isMentor() ? route('mentor.profile.show') : route('intern.profile.show');
                            $profilePhoto = auth()->user()->isMentor() ? auth()->user()->mentor?->photo_path : auth()->user()->intern?->photo_path;
                        @endphp
                        <a href="{{ $profileRoute }}" class="flex items-center space-x-3 mb-3 hover:bg-gray-50 rounded-lg p-2 transition-colors duration-200">
                            @if($profilePhoto)
                                <img src="{{ asset('storage/' . $profilePhoto) }}" alt="Profile Photo" class="w-10 h-10 rounded-full object-cover border-2 border-blue-200">
                            @else
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->isMentor() ? 'Mentor' : 'Peserta Magang' }}</p>
                            </div>
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-md">
                            <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="w-full flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-md">
                        <i class="fas fa-sign-in-alt w-5 mr-3"></i>
                        Login
                    </a>
                @endauth
            </div>
        </aside>

        <!-- ===========================
            Main Content Area
        ============================ -->
        <div class="flex-1 flex flex-col overflow-hidden">

            @include('layouts.partials.workspace-topbar')

            <!-- Top Header (Mobile) -->
            <header class="lg:hidden bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="Logo" class="object-contain" style="width: 60px; height: 60px"/>
                    </div>
                    <div class="flex items-center gap-1">
                    <x-theme-menu />
                    @if ($wsEnabled)
                        <x-workspace.notification-bell />
                    @endif
                    <button type="button" id="mobile-menu-button" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <svg class="h-6 w-6" id="icon-menu" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6 hidden" id="icon-close" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar -->
            <div id="mobile-sidebar" class="lg:hidden hidden fixed inset-0 z-50">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75" id="mobile-sidebar-backdrop"></div>
                <div class="fixed inset-y-0 left-0 flex flex-col w-64 bg-white">
                    <div class="flex justify-between">
                        <div><p></p></div>
                        <div class="flex items-center justify-between p-4 border-b">
                            <div class="flex flex-col items-center p-4 border-b">
                                <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="Logo" class="object-contain" style="width: 60px; height: 60px"/>
                                <h1 class="text-3xl font-extrabold font-etna">
                                    <span style="color: #9d272a">SI</span><span style="color: #086bb0">MA</span><span style="color: #2dabe2">GA</span><span style="color: #efc400">NG</span>
                                </h1>
                                <p class="font-etna" style="color: #626161; font-size:10px">Sistem Manajemen Magang</p>
                            </div>
                        </div>
                        <div>
                            <button type="button" id="mobile-close-button" class="p-2 rounded-md text-gray-600 hover:text-gray-900">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Navigation -->
                    <nav class="flex-1 overflow-y-auto py-4">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-home w-5 mr-3"></i>
                                    Dashboard
                                </a>
                                @include('layouts.partials.workspace-nav-link')

                                {{-- Manajemen Pengguna --}}
                                <div x-data="{ open: {{ request()->routeIs('admin.accounts.*', 'admin.mentor.*', 'admin.intern.*', 'admin.mitra.*', 'admin.team.*') ? 'true' : 'false' }} }" class="mt-1">
                                    <button type="button" @click="open = !open" class="{{ request()->routeIs('admin.accounts.*', 'admin.mentor.*', 'admin.intern.*', 'admin.mitra.*', 'admin.team.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                        <span class="flex items-center">
                                            <i class="fas fa-users-cog w-5 mr-3"></i>
                                            Manajemen Pengguna
                                        </span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                        @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.accounts.index') }}" class="{{ request()->routeIs('admin.accounts.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-user-shield w-4 mr-3 text-xs"></i>
                                            Admin
                                        </a>
                                        @endif
                                        @can('manage_mentors')
                                        <a href="{{ route('admin.mentor.index') }}" class="{{ request()->routeIs('admin.mentor.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-chalkboard-teacher w-4 mr-3 text-xs"></i>
                                            Mentor
                                        </a>
                                        @endcan
                                        <a href="{{ route('admin.intern.index') }}" class="{{ request()->routeIs('admin.intern.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-user-graduate w-4 mr-3 text-xs"></i>
                                            Peserta Magang
                                        </a>
                                        @can('manage_teams')
                                        <a href="{{ route('admin.team.index') }}" class="{{ request()->routeIs('admin.team.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-users w-4 mr-3 text-xs"></i>
                                            Tim Kerja/Bagian
                                        </a>
                                        @endcan
                                    </div>
                                </div>

                                {{-- Operasional Magang --}}
                                <div x-data="{ open: {{ request()->routeIs('admin.lowongan.*', 'admin.verifikasi.*', 'admin.pengajuan.*', 'admin.attendance.*', 'admin.logbook.*', 'admin.microskill.*', 'admin.sharing-session.*', 'admin.aktivitas.*') ? 'true' : 'false' }} }" class="mt-1">
                                    <button type="button" @click="open = !open" class="{{ request()->routeIs('admin.pengajuan.*', 'admin.attendance.*', 'admin.logbook.*', 'admin.microskill.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                        <span class="flex items-center">
                                            <i class="fas fa-briefcase w-5 mr-3"></i>
                                            Operasional Magang
                                        </span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                        @can('manage_lowongan')
                                        <a href="{{ route('admin.lowongan.index') }}" class="{{ request()->routeIs('admin.lowongan.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-briefcase w-4 mr-3 text-xs"></i>
                                            Lowongan
                                        </a>
                                        @endcan
                                        @can('view_verifikasi_lowongan')
                                        <a href="{{ route('admin.verifikasi.index') }}" class="{{ request()->routeIs('admin.verifikasi.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-briefcase w-4 mr-3 text-xs"></i>
                                            Verifikasi Lowongan
                                        </a>
                                        @endcan
                                        @can('manage_pengajuan')
                                        <a href="{{ route('admin.pengajuan.index') }}" class="{{ request()->routeIs('admin.pengajuan.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-file-contract w-4 mr-3 text-xs"></i>
                                            Pengajuan
                                        </a>
                                        @endcan
                                        @can('view_attendance')
                                        <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-calendar-check w-4 mr-3 text-xs"></i>
                                            Absensi
                                        </a>
                                        @endcan
                                        @can('view_logbook')
                                        <a href="{{ route('admin.logbook.index') }}" class="{{ request()->routeIs('admin.logbook.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-book w-4 mr-3 text-xs"></i>
                                            Logbook
                                        </a>
                                        @endcan
                                        <a href="{{ route('admin.microskill.index') }}" class="{{ request()->routeIs('admin.microskill.index', 'admin.microskill.create', 'admin.microskill.edit', 'admin.microskill.show') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-star w-4 mr-3 text-xs"></i>
                                            Mikro Skill
                                        </a>
                                        <a href="{{ route('admin.sharing-session.index') }}"
                                        class="{{ request()->routeIs('admin.sharing-session.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                        <i class="fas fa-comments w-4 mr-3 text-xs"></i>
                                        Sharing Session
                                        </a>
                                        <a href="{{ route('admin.aktivitas.index') }}"
class="{{ request()->routeIs('admin.aktivitas.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
<i class="fas fa-newspaper w-4 mr-3 text-xs"></i>
Aktivitas Terbaru
</a>
                                    </div>
                                </div>

                                {{-- Monitoring & Evaluasi --}}
                                <div x-data="{ open: {{ request()->routeIs('admin.monitoring.*', 'admin.report.*') ? 'true' : 'false' }} }" class="mt-1">
                                    <button type="button" @click="open = !open" class="{{ request()->routeIs('admin.monitoring.*', 'admin.report.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                        <span class="flex items-center">
                                            <i class="fas fa-chart-line w-5 mr-3"></i>
                                            Monitoring &amp; Evaluasi
                                        </span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                        <a href="{{ route('admin.monitoring.index') }}" class="{{ request()->routeIs('admin.monitoring.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-chart-line w-4 mr-3 text-xs"></i>
                                            Monitoring Peserta Magang
                                        </a>
                                        <a href="{{ route('admin.monitoring.industri.index') }}" class="{{ request()->routeIs('admin.monitoring.industri.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-building w-4 mr-3 text-xs"></i>
                                            Monitoring Industri
                                        </a>
                                        @can('view_reports')
                                        <a href="{{ route('admin.report.index') }}" class="{{ request()->routeIs('admin.report.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-file-alt w-4 mr-3 text-xs"></i>
                                            Laporan
                                        </a>
                                        @endcan
                                    </div>
                                </div>

                                @if(auth()->user()->isSuperAdmin())
                                <div class="mt-1 border-t pt-1">
                                    <a href="{{ route('admin.rag.index') }}" class="{{ request()->routeIs('admin.rag.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center px-4 py-3 text-sm font-medium">
                                        <i class="fas fa-brain w-5 mr-3"></i>
                                        Manajemen AI RAG
                                    </a>
                                </div>
                                @endif

                            @elseif(auth()->user()->isMentor())
                                <a href="{{ route('mentor.dashboard') }}" class="{{ request()->routeIs('mentor.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-home w-5 mr-3"></i>
                                    Dashboard Mentor
                                </a>
                                @include('layouts.partials.workspace-nav-link')
                                <a href="{{ route('mentor.intern.index') }}" class="{{ request()->routeIs('mentor.intern.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-users w-5 mr-3"></i>
                                    Anak Bimbingan
                                </a>
                                <a href="{{ route('mentor.attendance.index') }}" class="{{ request()->routeIs('mentor.attendance.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-calendar-check w-5 mr-3"></i>
                                    Absensi
                                </a>
                                <a href="{{ route('mentor.logbook.index') }}" class="{{ request()->routeIs('mentor.logbook.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-book w-5 mr-3"></i>
                                    Logbook
                                </a>
                                <a href="{{ route('mentor.report.index') }}" class="{{ request()->routeIs('mentor.report.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-file-alt w-5 mr-3"></i>
                                    Laporan Akhir
                                </a>
                                <a href="{{ route('mentor.microskill.index') }}" class="{{ request()->routeIs('mentor.microskill.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-star w-5 mr-3"></i>
                                    Mikro Skill
                                </a>

                            @elseif(auth()->user()->isInstitusi())
                                <a href="{{ route('institusi.dashboard') }}" class="{{ request()->routeIs('institusi.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-home w-5 mr-3"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('institusi.lowongan.index') }}" class="{{ request()->routeIs('institusi.lowongan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-briefcase w-5 mr-3"></i>
                                    Lowongan Magang
                                </a>
                                <a href="{{ route('institusi.pengajuan.index') }}" class="{{ request()->routeIs('institusi.pengajuan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-paper-plane w-5 mr-3"></i>
                                    Pengajuan Magang
                                </a>
                                <div x-data="{ open: {{ request()->routeIs('institusi.intern.*', 'institusi.attendance.*', 'institusi.logbook.*', 'institusi.microskill.*', 'institusi.certificate.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="{{ request()->routeIs('institusi.intern.*', 'institusi.attendance.*', 'institusi.logbook.*', 'institusi.microskill.*', 'institusi.certificate.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                        <span class="flex items-center">
                                            <i class="fas fa-chart-line w-5 mr-3"></i>
                                            Monitoring
                                        </span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                        <a href="{{ route('institusi.intern.index') }}" class="{{ request()->routeIs('institusi.intern.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-users w-4 mr-3 text-xs"></i>
                                            Peserta Magang
                                        </a>
                                        <a href="{{ route('institusi.attendance.index') }}" class="{{ request()->routeIs('institusi.attendance.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-clipboard-check w-4 mr-3 text-xs"></i>
                                            Absensi
                                        </a>
                                        <a href="{{ route('institusi.logbook.index') }}" class="{{ request()->routeIs('institusi.logbook.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-book w-4 mr-3 text-xs"></i>
                                            Logbook
                                        </a>
                                        <a href="{{ route('institusi.microskill.index') }}" class="{{ request()->routeIs('institusi.microskill.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-graduation-cap w-4 mr-3 text-xs"></i>
                                            Mikro Skill
                                        </a>
                                        <a href="{{ route('institusi.certificate.index') }}" class="{{ request()->routeIs('institusi.certificate.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-certificate w-4 mr-3 text-xs"></i>
                                            Nilai
                                        </a>
                                    </div>
                                </div>

                            @elseif(auth()->user()->isIndustri())
                                <a href="{{ route('industri.dashboard') }}" class="{{ request()->routeIs('industri.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-home w-5 mr-3"></i>
                                    Dashboard Industri
                                </a>
                                <a href="{{ route('industri.lowongan.index') }}" class="{{ request()->routeIs('industri.lowongan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-briefcase w-5 mr-3"></i>
                                    Lowongan Magang
                                </a>
                                <a href="{{ route('industri.pengajuan.index') }}" class="{{ request()->routeIs('industri.pengajuan.index') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-file-alt w-5 mr-3"></i>
                                    Pengajuan Magang
                                </a>
                                <div x-data="{ open: {{ request()->routeIs('industri.intern.*', 'industri.attendance.*', 'industri.logbook.*', 'industri.microskill.*', 'industri.report.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="{{ request()->routeIs('industri.intern.*', 'industri.attendance.*', 'industri.logbook.*', 'industri.microskill.*', 'industri.report.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} w-full flex items-center justify-between px-4 py-3 text-sm font-medium">
                                        <span class="flex items-center">
                                            <i class="fas fa-chart-line w-5 mr-3"></i>
                                            Monitoring
                                        </span>
                                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="open" x-transition class="bg-gray-50 border-l-4 border-blue-200 ml-4">
                                        <a href="{{ route('industri.intern.index') }}" class="{{ request()->routeIs('industri.intern.index') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-users w-4 mr-3 text-xs"></i>
                                            Peserta Magang
                                        </a>
                                        <a href="{{ route('industri.attendance.index') }}" class="{{ request()->routeIs('industri.attendance.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-clipboard-check w-4 mr-3 text-xs"></i>
                                            Absensi
                                        </a>
                                        <a href="{{ route('industri.logbook.index') }}" class="{{ request()->routeIs('industri.logbook.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-book w-4 mr-3 text-xs"></i>
                                            Logbook
                                        </a>
                                        <a href="{{ route('industri.microskill.index') }}" class="{{ request()->routeIs('industri.microskill.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-graduation-cap w-4 mr-3 text-xs"></i>
                                            Mikro Skill
                                        </a>
                                        <a href="{{ route('industri.report.index') }}" class="{{ request()->routeIs('industri.report.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-500 hover:bg-gray-100' }} flex items-center px-4 py-2.5 text-sm font-medium">
                                            <i class="fas fa-certificate w-4 mr-3 text-xs"></i>
                                            Laporan Akhir
                                        </a>
                                    </div>
                                </div>

                            @else
                                <a href="{{ route('intern.dashboard') }}" class="{{ request()->routeIs('intern.dashboard') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-home w-5 mr-3"></i>
                                    Dashboard
                                </a>
                                @include('layouts.partials.workspace-nav-link')
                                <a href="{{ route('intern.attendance.index') }}" class="{{ request()->routeIs('intern.attendance.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-calendar-check w-5 mr-3"></i>
                                    Absensi
                                </a>
                                <a href="{{ route('intern.logbook.index') }}" class="{{ request()->routeIs('intern.logbook.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-book w-5 mr-3"></i>
                                    Logbook
                                </a>
                                <a href="{{ route('intern.report.index') }}" class="{{ request()->routeIs('intern.report.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-file-alt w-5 mr-3"></i>
                                    Laporan
                                </a>
                                <a href="{{ route('intern.microskill.index') }}" class="{{ request()->routeIs('intern.microskill.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600' }} flex items-center px-4 py-3 text-sm font-medium">
                                    <i class="fas fa-star w-5 mr-3"></i>
                                    Mikro Skill
                                </a>
                                <a href="{{ route('intern.sharing-session.index') }}"
                                class="{{ request()->routeIs('intern.sharing-session.*') ? 'bg-blue-50 border-r-4 border-blue-500 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }} flex items-center px-4 py-3 text-sm font-medium">
                                <i class="fas fa-comments w-5 mr-3"></i>
                                Sharing Session
                                </a>
                            @endif
                        @endauth
                    </nav>

                    <!-- Mobile User Info -->
                    <div class="border-t p-4">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                                    </div>
                                </div>
                            @elseif(auth()->user()->isInstitusi())
                                <a href="{{ route('institusi.profile.show') }}" class="flex items-center space-x-3 mb-3 hover:bg-gray-50 rounded-lg p-2 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500">Institusi</p>
                                    </div>
                                </a>
                            @else
                                <a href="{{ auth()->user()->isMentor() ? route('mentor.profile.show') : route('intern.profile.show') }}" class="flex items-center space-x-3 mb-3 hover:bg-gray-50 rounded-lg p-2 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ auth()->user()->isMentor() ? 'Mentor' : 'Peserta Magang' }}</p>
                                    </div>
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-md">
                                    <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                                    Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="w-full flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-md">
                                <i class="fas fa-sign-in-alt w-5 mr-3"></i>
                                Login
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                @if(session('success'))
                    <div class="mb-4">
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error') || $errors->any())
                    <div class="mb-4">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            @if(session('error'))
                                <span class="block sm:inline">{{ session('error') }}</span>
                            @endif
                            @if($errors->any())
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="px-4 pt-4 sm:px-6 lg:px-8">
                        <div class="flex items-start gap-4 bg-amber-50 border border-amber-300 text-amber-800 px-5 py-4 rounded-xl shadow-sm" role="alert">
                            <div class="flex-shrink-0 mt-0.5">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-amber-100">
                                    <i class="fas fa-exclamation-triangle text-amber-500 text-base"></i>
                                </span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-amber-900 text-sm mb-1">Profil Belum Dilengkapi</p>
                                <p class="text-amber-700 text-sm">{{ session('warning') }}</p>
                            </div>
                            @auth
                                @if(auth()->user()->isIndustri())
                                    <a href="{{ route('industri.profile.create') }}"
                                       class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition-colors">
                                        <i class="fas fa-user-edit"></i>
                                        Lengkapi Profil
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-4">
                        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('info') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @if ($wsEnabled)
        <x-workspace.toasts />
    @endif
    @stack('scripts')
    <script>
        (function () {
            const mobileMenuBtn = document.getElementById('mobile-menu-button');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const mobileCloseBtn = document.getElementById('mobile-close-button');
            const mobileBackdrop = document.getElementById('mobile-sidebar-backdrop');
            const iconMenu  = document.getElementById('icon-menu');
            const iconClose = document.getElementById('icon-close');

            function openMobileSidebar() {
                mobileSidebar.classList.remove('hidden');
                iconMenu.classList.add('hidden');
                iconClose.classList.remove('hidden');
            }

            function closeMobileSidebar() {
                mobileSidebar.classList.add('hidden');
                iconMenu.classList.remove('hidden');
                iconClose.classList.add('hidden');
            }

            if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', () =>
                mobileSidebar.classList.contains('hidden') ? openMobileSidebar() : closeMobileSidebar()
            );
            if (mobileCloseBtn) mobileCloseBtn.addEventListener('click', closeMobileSidebar);
            if (mobileBackdrop)  mobileBackdrop.addEventListener('click', closeMobileSidebar);

            const sidebar = document.getElementById('sidebar');
            window.toggleDesktopSidebar = function () {
                sidebar.classList.toggle('sidebar-collapsed');
            };
        })();
    </script>

</body>
</html>