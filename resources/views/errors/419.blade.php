<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>419 - Page Expired | SIMAGANG</title>
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Etna';
            src: url('/fonts/Etna-Free-Font.otf') format('opentype');
        }

        .font-etna {
            font-family: 'Etna', sans-serif;
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: translateY(24px) scale(0.94);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .animate-pop-in {
            animation: popIn 420ms ease-out both;
        }
    </style>
</head>
<body class="min-h-screen overflow-hidden bg-slate-950 text-white">
    <div class="relative flex min-h-screen items-center justify-center px-4 py-10">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900"></div>
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 22px 22px;"></div>
        <div class="absolute -top-28 left-1/4 h-72 w-72 rounded-full bg-blue-500/15 blur-3xl"></div>
        <div class="absolute -bottom-28 right-1/4 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

        <main class="relative z-10 w-full max-w-2xl animate-pop-in">
            <section class="rounded-[2rem] border border-white/10 bg-white/8 p-6 shadow-2xl shadow-blue-950/40 backdrop-blur-2xl sm:p-8">
                <div class="flex items-start justify-between gap-4 border-b border-white/10 pb-5">
                    <div class="flex items-center gap-3">
                        <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="SIMAGANG" class="h-11 w-11 object-contain">
                        <div>
                            <p class="text-xs uppercase tracking-[0.35em] text-cyan-300/80">SIMAGANG</p>
                            <p class="font-etna text-lg">
                                <span style="color: #9d272a">SI</span><span style="color: #086bb0">MA</span><span style="color: #2dabe2">GA</span><span style="color: #efc400">NG</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-300">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>

                <div class="mt-6 rounded-[1.5rem] border border-white/10 bg-slate-950/65 p-6 sm:p-7">
                    <div class="mb-5 flex items-center gap-3 text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300/80">
                        <span class="h-px flex-1 bg-gradient-to-r from-transparent via-cyan-400/60 to-transparent"></span>
                        <span>Page Expired</span>
                        <span class="h-px flex-1 bg-gradient-to-r from-transparent via-cyan-400/60 to-transparent"></span>
                    </div>

                    <div class="space-y-3 text-center">
                        <p class="text-5xl font-black leading-none sm:text-6xl">
                            <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">419</span>
                        </p>
                        <h1 class="text-2xl font-bold text-white sm:text-3xl">Sesi atau token formulir sudah kedaluwarsa.</h1>
                        <p class="mx-auto max-w-xl text-sm leading-7 text-slate-300 sm:text-base">
                            Hal ini biasanya terjadi karena halaman terlalu lama dibiarkan terbuka atau token keamanan sudah tidak valid. Silakan muat ulang halaman dan kirim ulang formulir Anda.
                        </p>
                    </div>

                    <div class="mt-7 grid gap-3 sm:grid-cols-2">
                        <button type="button" onclick="window.location.reload();" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">
                            <i class="fas fa-rotate-right"></i>
                            Muat Ulang
                        </button>

                        @auth
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                    <i class="fas fa-gauge-high"></i>
                                    Dashboard Admin
                                </a>
                            @elseif(auth()->user()->isMentor())
                                <a href="{{ route('mentor.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                    <i class="fas fa-gauge-high"></i>
                                    Dashboard Mentor
                                </a>
                            @else
                                <a href="{{ route('intern.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                    <i class="fas fa-gauge-high"></i>
                                    Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3.5 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                                <i class="fas fa-right-to-bracket"></i>
                                Login
                            </a>
                        @endauth
                    </div>

                    <div class="mt-5 flex items-center gap-3 rounded-2xl border border-cyan-400/15 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-100">
                        <i class="fas fa-circle-info text-cyan-300"></i>
                        <p>Jika error ini muncul setelah login atau submit form, refresh biasanya cukup untuk membuat token baru.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
