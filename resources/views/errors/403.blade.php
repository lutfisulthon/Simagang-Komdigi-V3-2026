<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>403 - Akses Ditolak | SIMAGANG</title>
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
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-white overflow-hidden">
    <div class="relative min-h-screen flex items-center justify-center px-4 py-12">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900"></div>
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 22px 22px;"></div>

        <main class="relative z-10 w-full max-w-4xl">
            <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
                <section class="space-y-6">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 backdrop-blur-md transition hover:bg-white/10">
                        <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="SIMAGANG" class="h-7 w-7 object-contain">
                        <span class="font-etna tracking-wide">
                            <span style="color: #9d272a">SI</span><span style="color: #086bb0">MA</span><span style="color: #2dabe2">GA</span><span style="color: #efc400">NG</span>
                        </span>
                    </a>

                    <div class="space-y-3">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300/80">Akses Ditolak</p>
                        <h1 class="text-5xl font-black leading-tight sm:text-6xl">
                            <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">403</span>
                            <span class="block text-3xl sm:text-4xl mt-2 text-slate-100">Anda tidak memiliki izin untuk membuka halaman ini.</span>
                        </h1>
                        <p class="max-w-xl text-base leading-7 text-slate-300 sm:text-lg">
                            Halaman ini dibatasi oleh kebijakan akses SIMAGANG. Jika menurut Anda akses ini seharusnya tersedia, hubungi administrator sistem atau kembali ke dashboard yang sesuai dengan peran Anda.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        @auth
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">
                                    <i class="fas fa-arrow-left"></i>
                                    Ke Dashboard Admin
                                </a>
                            @elseif(auth()->user()->isMentor())
                                <a href="{{ route('mentor.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">
                                    <i class="fas fa-arrow-left"></i>
                                    Ke Dashboard Mentor
                                </a>
                            @else
                                <a href="{{ route('intern.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">
                                    <i class="fas fa-arrow-left"></i>
                                    Ke Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg shadow-blue-500/30 transition hover:bg-blue-700">
                                <i class="fas fa-right-to-bracket"></i>
                                Login
                            </a>
                        @endauth

                        <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-6 py-3 font-semibold text-slate-100 backdrop-blur-md transition hover:bg-white/10">
                            <i class="fas fa-rotate-left"></i>
                            Kembali
                        </a>
                    </div>
                </section>

                <aside class="relative">
                    <div class="rounded-[2rem] border border-white/10 bg-white/8 p-6 shadow-2xl shadow-blue-950/40 backdrop-blur-xl">
                        <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/60 p-6">
                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.25em] text-slate-400">Status</p>
                                    <p class="mt-1 text-xl font-bold text-white">Halaman Terkunci</p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-500/15 text-red-300">
                                    <i class="fas fa-shield-halved text-2xl"></i>
                                </div>
                            </div>

                            <div class="space-y-4 pt-5 text-sm text-slate-300">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-circle-info mt-1 text-blue-300"></i>
                                    <p>Halaman ini hanya bisa dibuka oleh role atau permission yang sesuai.</p>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-user-lock mt-1 text-cyan-300"></i>
                                    <p>Jika Anda admin, pastikan akun Anda sudah diberi akses dari menu manajemen admin.</p>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-bell mt-1 text-amber-300"></i>
                                    <p>Jika error muncul saat login ulang, coba refresh atau login kembali untuk memuat hak akses terbaru.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
