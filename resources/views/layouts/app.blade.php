<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.theme-head', ['splash' => true])
    @php
        // Workspace: aktif untuk admin (super_admin/admin_full), mentor, dan intern bila route-nya terdaftar.
        $wsUser = auth()->user();
        $wsRole = match (true) {
            ! $wsUser => null,
            $wsUser->isSuperAdmin() || $wsUser->isAdminFull() => 'admin',
            $wsUser->isMentor() => 'mentor',
            $wsUser->isIntern() => 'intern',
            default => null,
        };
        $wsEnabled = $wsRole !== null && Route::has('workspace.index');

        // Warna & identitas halaman (selaras warna menu sidebar): lihat layouts/partials/page-meta.php
        $pageMeta = null;
        if ($wsUser) {
            foreach (require resource_path('views/layouts/partials/page-meta.php') as $pattern => $meta) {
                if (request()->routeIs(...explode(',', $pattern))) { $pageMeta = $meta; break; }
            }
        }
    @endphp
    <title>@yield('title', 'Sistem Manajemen Peserta Magang')</title>
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}?v={{ @filemtime(public_path('css/app-shell.css')) }}">
    @auth
        <link rel="stylesheet" href="{{ asset('css/page-ui.css') }}?v={{ @filemtime(public_path('css/page-ui.css')) }}">
    @endauth
    @include('layouts.partials.workspace-head')
    {{-- Dialog konfirmasi: aktif untuk form ber-atribut data-confirm-* (lihat public/js/confirm-dialog.js) --}}
    <link rel="stylesheet" href="{{ asset('css/confirm-dialog.css') }}?v={{ @filemtime(public_path('css/confirm-dialog.css')) }}">
    <script defer src="{{ asset('js/confirm-dialog.js') }}?v={{ @filemtime(public_path('js/confirm-dialog.js')) }}"></script>
    {{-- Filter langsung: form GET otomatis terkirim saat isian berubah (lihat public/js/live-filter.js) --}}
    <script defer src="{{ asset('js/live-filter.js') }}?v={{ @filemtime(public_path('js/live-filter.js')) }}"></script>
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

            @include('layouts.partials.sidebar-brand')

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto py-4">
                @auth
                    @if(auth()->user()->isAdmin())
                        @include('layouts.partials.admin-nav')

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



                    @elseif(auth()->user()->isMentor())
                        @include('layouts.partials.mentor-nav')

                    @elseif(auth()->user()->isInstitusi())
                        @include('layouts.partials.institusi-nav')

                    @elseif(auth()->user()->isIndustri())
                        @include('layouts.partials.industri-nav')

                    @else
                        @include('layouts.partials.intern-nav')
                    @endif
                @endauth
            </nav>

            @include('layouts.partials.sidebar-footer')
        </aside>

        <!-- ===========================
            Main Content Area
        ============================ -->
        <div class="flex-1 flex flex-col overflow-hidden">

            @include('layouts.partials.workspace-topbar')

            <!-- Top Header (Mobile) -->
            <header class="tb-m lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="tb-m__brand">
                        <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="Logo">
                        <b class="font-etna"><span style="color: #9d272a">SI</span><span style="color: #086bb0">MA</span><span style="color: #2dabe2">GA</span><span style="color: #efc400">NG</span></b>
                    </div>
                    <div class="flex items-center gap-1">
                        <x-theme-menu />
                        @if ($wsEnabled)
                            <x-workspace.notification-bell />
                        @endif
                        <button type="button" id="mobile-menu-button" class="p-2 rounded-full text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" aria-label="Buka menu">
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
                    <div class="sb-drawer__head">
                        @include('layouts.partials.sidebar-brand')
                        <button type="button" id="mobile-close-button" class="sb-drawer__close" aria-label="Tutup menu">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Navigation -->
                    <nav class="flex-1 overflow-y-auto py-4">
                        @auth
                            @if(auth()->user()->isAdmin())
                                @include('layouts.partials.admin-nav')


                            @elseif(auth()->user()->isMentor())
                                @include('layouts.partials.mentor-nav')

                            @elseif(auth()->user()->isInstitusi())
                                @include('layouts.partials.institusi-nav')

                            @elseif(auth()->user()->isIndustri())
                                @include('layouts.partials.industri-nav')

                            @else
                                @include('layouts.partials.intern-nav')
                            @endif
                        @endauth
                    </nav>

                    @include('layouts.partials.sidebar-footer')
                </div>
            </div>

            <!-- Main Content -->
            <main class="relative flex-1 overflow-y-auto"
                  @if ($pageMeta) data-tone="{{ $pageMeta[0] }}" @if ($pageMeta[2]) data-hero-icon="{{ $pageMeta[2] }}" style="--pt-icon: '{{ $pageMeta[2] }}'" @endif @endif>
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
        // Judul top bar cadangan: bila title halaman generik, pakai <h1> halaman.
        document.addEventListener('DOMContentLoaded', function () {
            var tb = document.querySelector('[data-title-fallback]');
            var h1 = document.querySelector('main h1');
            if (tb && h1 && h1.textContent.trim()) tb.textContent = h1.textContent.trim();
        });
    </script>
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