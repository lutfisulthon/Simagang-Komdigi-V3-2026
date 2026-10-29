<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>{{ $sharingSession->title ?? 'Detail Sharing Session' }} - Simagang</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #eef6ff;
            color: #0f2d4a;
            margin: 0;
            padding-bottom: 60px;
        }

        .glass-card {
            background: rgba(255,255,255,0.78);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.75);
        }

        .detail-bg {
            background:
                radial-gradient(circle at top left, rgba(34,211,238,.28), transparent 32%),
                radial-gradient(circle at bottom right, rgba(37,99,235,.22), transparent 35%),
                linear-gradient(135deg, #eef6ff 0%, #f8fbff 100%);
        }

        /* ── SHARE BUTTONS ── */
        .share-box {
            margin-top: 1.5rem;
            padding: 1.25rem;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(191, 219, 254, 0.95);
            box-shadow: 0 12px 28px rgba(14, 99, 201, 0.08);
        }

        .share-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f2d4a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .share-title i {
            color: #2563eb;
        }

        .share-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .share-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            border-radius: 16px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            transition: all 0.25s ease;
        }

        .share-whatsapp {
            background: #22c55e;
            color: white;
            box-shadow: 0 10px 22px rgba(34, 197, 94, 0.22);
        }

        .share-whatsapp:hover {
            background: #16a34a;
            transform: translateY(-2px);
            color: white;
        }

        .share-copy {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .share-copy:hover {
            background: #dbeafe;
            transform: translateY(-2px);
        }

        .copy-alert {
            display: none;
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 14px;
            background: #ecfdf5;
            color: #047857;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .copy-alert.show {
            display: block;
        }

        @media (max-width: 480px) {
            .share-actions {
                grid-template-columns: 1fr;
            }
        }

        /* ── FOOTER ── */
        .main-footer {
            background: linear-gradient(135deg, #10499e 100%, #0e60cc 40%, #0891b2 30%);
            color: white;
            padding: 15px 0;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            border-top: 1px solid rgba(34, 211, 238, 0.3);
            backdrop-filter: blur(10px);
            box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.3);
        }

        .footer-simple-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-logos-simple,
        .social-links-simple {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .footer-logos-simple {
            gap: 1.5rem;
        }

        .footer-logos-simple img {
            height: 28px;
            object-fit: contain;
            filter: drop-shadow(0 0 5px rgba(255,255,255,0.1));
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .footer-logos-simple img:hover {
            transform: translateY(-5px) scale(1.1);
            filter: drop-shadow(0 5px 15px rgba(34, 211, 238, 0.4));
        }

        .copyright-simple {
            flex: 1.5;
            text-align: center;
            font-size: 13px;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.8);
        }

        .copyright-simple strong {
            color: #22d3ee;
            text-shadow: 0 0 10px rgba(34, 211, 238, 0.3);
        }

        .social-links-simple {
            justify-content: flex-end;
            gap: 12px;
        }

        .social-links-simple a {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .social-links-simple a:hover {
            background: rgba(34, 211, 238, 0.15);
            color: #22d3ee;
            border-color: #22d3ee;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(34, 211, 238, 0.2);
        }

        @media (max-width: 768px) {
            .main-footer {
                padding: 10px 0;
            }

            .footer-simple-inner {
                flex-direction: column;
                gap: 8px;
                padding: 4px 1rem;
            }

            .copyright-simple {
                order: 3;
                font-size: 11px;
            }

            .footer-logos-simple {
                order: 1;
                justify-content: center;
            }

            .social-links-simple {
                order: 2;
                justify-content: center;
            }

            body {
                padding-bottom: 110px;
            }
        }
    </style>
</head>

<body class="detail-bg min-h-screen">

    {{-- Navbar sederhana --}}
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center">
                    <img src="{{ url('storage/vendor/logo_komdigi.png') }}"
                         alt="Komdigi"
                         class="w-7 h-7 object-contain">
                </div>

                <div>
                    <div class="font-extrabold text-lg leading-none">
                        <span class="text-red-700">SI</span><span class="text-blue-700">MA</span><span class="text-cyan-500">GA</span><span class="text-yellow-500">NG</span>
                    </div>
                    <div class="text-[10px] text-gray-400 mt-1">
                        Sistem Manajemen Magang
                    </div>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}#sharing-session"
                   class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-blue-700 font-semibold text-sm hover:bg-blue-100 transition">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali
                </a>

                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 text-white font-semibold text-sm shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition">
                    <i class="fas fa-sign-in-alt text-xs"></i>
                    Login
                </a>
            </div>
        </div>
    </header>

    <main>
        {{-- Hero Detail --}}
        <section class="relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 py-12 md:py-16">

                <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_.8fr] gap-8 items-stretch">

                    {{-- Foto besar --}}
                    <div class="relative rounded-[34px] overflow-hidden min-h-[430px] md:min-h-[560px] shadow-2xl bg-blue-900">
                        @if($sharingSession->documentation_photo_url)
                            <img src="{{ $sharingSession->documentation_photo_url }}"
                                 alt="{{ $sharingSession->title ?? 'Sharing Session' }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-800 via-indigo-700 to-slate-950 flex flex-col items-center justify-center text-white">
                                <div class="w-28 h-28 rounded-3xl bg-white/15 flex items-center justify-center mb-5">
                                    <i class="fas fa-comments text-6xl"></i>
                                </div>

                                <p class="text-3xl font-bold">
                                    Sharing Session
                                </p>

                                <p class="text-sm text-blue-100 mt-2">
                                    Dokumentasi belum tersedia
                                </p>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/30 to-transparent"></div>

                        <div class="absolute left-0 right-0 bottom-0 p-7 md:p-10 text-white">
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-cyan-400 text-slate-950 text-xs font-extrabold uppercase mb-4">
                                Detail Sharing Session
                            </span>

                            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight max-w-3xl">
                                {{ $sharingSession->title ?? 'Materi Belum Diisi' }}
                            </h1>

                            <div class="flex flex-wrap gap-4 mt-5 text-sm text-white/90">
                                <span>
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    {{ $sharingSession->session_date->format('d M Y') }}
                                </span>

                                <span>
                                    <i class="fas fa-clock mr-2"></i>
                                    {{ $sharingSession->start_time ? \Carbon\Carbon::parse($sharingSession->start_time)->format('H:i') : '-' }}
                                    WITA
                                </span>

                                <span>
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    {{ $sharingSession->location ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Info kanan --}}
                    <div class="glass-card rounded-[34px] p-6 md:p-8 shadow-xl">
                        <div class="mb-7">
                            <p class="text-sm font-bold uppercase tracking-widest text-blue-600">
                                Informasi Kegiatan
                            </p>

                            <h2 class="text-2xl font-extrabold text-slate-900 mt-2">
                                Ringkasan Sharing Session
                            </h2>
                        </div>

                        <div class="space-y-5">
                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Tanggal</p>
                                    <p class="text-sm text-gray-500">{{ $sharingSession->session_date->format('d M Y') }}</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Waktu</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $sharingSession->start_time ? \Carbon\Carbon::parse($sharingSession->start_time)->format('H:i') : '-' }}
                                        WITA - Selesai
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Lokasi</p>
                                    <p class="text-sm text-gray-500">{{ $sharingSession->location ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Narasumber</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $sharingSession->speakerUser?->name ?? $sharingSession->speaker ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Moderator</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $sharingSession->moderatorUser?->name ?? $sharingSession->moderator ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 p-5 rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                            <p class="text-sm text-blue-100">
                                Periode minggu ini
                            </p>
                            <p class="font-bold mt-1">
                                {{ $weekStart->format('d M Y') }} - {{ $weekEnd->format('d M Y') }}
                            </p>
                        </div>

                        @php
                            $shareTitle = $sharingSession->title ?? 'Sharing Session Simagang';

                            $shareDate = $sharingSession->session_date
                                ? $sharingSession->session_date->format('d M Y')
                                : '-';

                            $shareTime = $sharingSession->start_time
                                ? \Carbon\Carbon::parse($sharingSession->start_time)->format('H:i') . ' WITA'
                                : '-';

                            $shareLocation = $sharingSession->location ?? '-';

                            $shareUrl = url()->current();

                            $shareText = "Yuk ikuti Sharing Session Simagang!\n\n"
                                . "Judul: " . $shareTitle . "\n"
                                . "Tanggal: " . $shareDate . "\n"
                                . "Waktu: " . $shareTime . "\n"
                                . "Lokasi: " . $shareLocation . "\n\n"
                                . "Detail kegiatan:\n" . $shareUrl;

                            $whatsappShareUrl = 'https://wa.me/?text=' . rawurlencode($shareText);
                        @endphp

                        <div class="share-box">
                            <div class="share-title">
                                <i class="fas fa-share-alt"></i>
                                Bagikan Kegiatan
                            </div>

                            <div class="share-actions">
                                <a href="{{ $whatsappShareUrl }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="share-btn share-whatsapp">
                                    <i class="fab fa-whatsapp"></i>
                                    WhatsApp
                                </a>

                                <button type="button"
                                        class="share-btn share-copy"
                                        onclick="copySharingSessionLink()">
                                    <i class="fas fa-link"></i>
                                    Salin Link
                                </button>
                            </div>

                            <div id="copyAlert" class="copy-alert">
                                <i class="fas fa-check-circle"></i>
                                Link berhasil disalin.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Deskripsi --}}
        <section class="pb-16">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_.8fr] gap-8">

                    <div class="glass-card rounded-[34px] p-7 md:p-9 shadow-lg">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-14 h-14 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                                <i class="fas fa-align-left text-xl"></i>
                            </div>

                            <div>
                                <p class="text-sm font-bold uppercase tracking-widest text-blue-600">
                                    Deskripsi
                                </p>
                                <h2 class="text-2xl font-extrabold text-slate-900">
                                    Tentang Sharing Session Ini
                                </h2>
                            </div>
                        </div>

                        @if($sharingSession->description)
                            <div class="text-gray-600 leading-relaxed whitespace-pre-line text-base">
                                {{ $sharingSession->description }}
                            </div>
                        @else
                            <div class="rounded-3xl border border-dashed border-gray-200 bg-white/70 p-8 text-center text-gray-400">
                                <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                                <p class="font-semibold">
                                    Deskripsi sharing session belum tersedia.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Jadwal lain --}}
                    <div class="glass-card rounded-[34px] p-7 shadow-lg">
                        <div class="mb-6">
                            <p class="text-sm font-bold uppercase tracking-widest text-blue-600">
                                Jadwal Lain
                            </p>
                            <h2 class="text-2xl font-extrabold text-slate-900 mt-1">
                                Minggu Ini
                            </h2>
                        </div>

                        <div class="space-y-4">
                            @forelse($otherSharingSessions as $session)
                                <a href="{{ route('public.sharing-session.show', $session) }}"
                                   class="flex gap-4 bg-white rounded-3xl p-4 hover:shadow-md transition border border-blue-50 group">

                                    <div class="w-24 h-20 rounded-2xl overflow-hidden bg-blue-100 shrink-0">
                                        @if($session->documentation_photo_url)
                                            <img src="{{ $session->documentation_photo_url }}"
                                                 alt="{{ $session->title ?? 'Sharing Session' }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-700 to-indigo-500 text-white flex items-center justify-center">
                                                <i class="fas fa-comments text-2xl"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="font-bold text-slate-900 leading-snug line-clamp-2">
                                            {{ $session->title ?? 'Materi Belum Diisi' }}
                                        </h3>

                                        <p class="text-xs text-gray-400 mt-2">
                                            <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                                            {{ $session->session_date->format('d M Y') }}
                                            <span class="mx-1">•</span>
                                            <i class="fas fa-clock text-green-500 mr-1"></i>
                                            {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-' }}
                                            WITA
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-3xl border border-dashed border-gray-200 bg-white/70 p-8 text-center text-gray-400">
                                    <i class="fas fa-calendar-plus text-3xl text-gray-300 mb-3"></i>
                                    <p class="font-semibold">
                                        Belum ada jadwal lain minggu ini.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="main-footer">
        <div class="footer-simple-inner">
            <div class="footer-logos-simple">
                <img src="{{ url('storage/vendor/logo_berakhlak.png') }}" alt="BerAkhlak">
                <img src="{{ url('storage/vendor/logo_banggamelayani.png') }}" alt="Bangga Melayani">
                <img src="{{ url('storage/vendor/logo_antikorupsi.png') }}" alt="Anti Korupsi">
            </div>

            <div class="copyright-simple">
                &copy; 2026 <strong>Simagang</strong> — BBLSDM Komdigi Makassar. All rights reserved.
            </div>

            <div class="social-links-simple">
                <a href="https://www.instagram.com/bblsdm.komdigi.makassar/" target="_blank" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>

                <a href="https://www.komdigi.go.id/" target="_blank" title="Website">
                    <i class="fas fa-globe"></i>
                </a>

                <a href="https://www.tiktok.com/@balaikomdigimakassar" target="_blank" title="TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>

                <a href="https://www.youtube.com/@bblsdm.komdigi.makassar" target="_blank" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </footer>

    <script>
        function copySharingSessionLink() {
            const url = window.location.href;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function () {
                    showCopyAlert();
                }).catch(function () {
                    fallbackCopyText(url);
                });
            } else {
                fallbackCopyText(url);
            }
        }

        function fallbackCopyText(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';

            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                document.execCommand('copy');
                showCopyAlert();
            } catch (err) {
                alert('Link belum bisa disalin otomatis. Silakan salin dari address bar browser.');
            }

            document.body.removeChild(textarea);
        }

        function showCopyAlert() {
            const alertBox = document.getElementById('copyAlert');

            if (!alertBox) return;

            alertBox.classList.add('show');

            setTimeout(function () {
                alertBox.classList.remove('show');
            }, 2200);
        }
    </script>

</body>
</html>