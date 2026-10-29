<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.partials.theme-head')
    <title>{{ $type === 'youtube' ? 'Video YouTube' : 'Artikel' }} - Simagang</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(34,211,238,.18), transparent 30%),
                linear-gradient(135deg, #eef6ff 0%, #f8fbff 100%);
            color: #0f172a;
            margin: 0;
            padding-bottom: 80px;
        }

        .activity-card {
            background: white;
            border: 1px solid #dbeafe;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .07);
            transition: all .28s ease;
            text-decoration: none;
            color: inherit;
        }

        .activity-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 54px rgba(37, 99, 235, .15);
        }

        .main-footer {
            background: linear-gradient(135deg, #10499e 100%, #0e60cc 40%, #0891b2 30%);
            color: white;
            padding: 15px 0;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            border-top: 1px solid rgba(34,211,238,0.3);
            box-shadow: 0 -10px 25px rgba(0,0,0,0.25);
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
        }

        .copyright-simple {
            flex: 1.5;
            text-align: center;
            font-size: 13px;
            color: rgba(255,255,255,.85);
        }

        .copyright-simple strong {
            color: #22d3ee;
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
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.75);
            border: 1px solid rgba(255,255,255,.12);
            transition: .25s;
            text-decoration: none;
        }

        .social-links-simple a:hover {
            color: #22d3ee;
            border-color: #22d3ee;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            body {
                padding-bottom: 125px;
            }

            .footer-simple-inner {
                flex-direction: column;
                gap: 8px;
                padding: 4px 1rem;
            }

            .footer-logos-simple,
            .social-links-simple {
                justify-content: center;
            }

            .copyright-simple {
                order: 3;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>

<header class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-blue-100">
    <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">

        <a href="{{ route('landing') }}" class="flex items-center gap-3 no-underline">
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

        <a href="{{ route('landing') }}#news"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-blue-700 font-bold text-sm hover:bg-blue-100 transition no-underline">
            <i class="fas fa-arrow-left text-xs"></i>
            Kembali
        </a>
    </div>
</header>

<main class="py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-6">

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] {{ $type === 'youtube' ? 'text-red-600' : 'text-blue-600' }} mb-3">
                    Aktivitas Simagang
                </p>

                <h1 class="text-3xl md:text-5xl font-black text-slate-900">
                    {{ $type === 'youtube' ? 'Video YouTube' : 'Artikel' }}
                </h1>

                <div class="mt-4 w-24 h-1 rounded-full {{ $type === 'youtube' ? 'bg-red-400' : 'bg-cyan-400' }}"></div>
            </div>

            <div class="inline-flex items-center gap-2 bg-white border border-blue-100 p-2 rounded-2xl shadow-sm w-fit">
                <a href="{{ route('public.activity.index', ['type' => 'artikel']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition no-underline
                   {{ $type === 'artikel' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <i class="fas fa-newspaper"></i>
                    Artikel
                </a>

                <a href="{{ route('public.activity.index', ['type' => 'youtube']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition no-underline
                   {{ $type === 'youtube' ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <i class="fab fa-youtube"></i>
                    YouTube
                </a>
            </div>
        </div>

        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach($posts as $post)
                    <a href="{{ route('public.activity.show', $post->slug) }}"
                       class="activity-card group">

                        <div class="relative h-56 bg-blue-100 overflow-hidden">
                            <img src="{{ $post->thumbnail_url }}"
                                 alt="{{ $post->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">

                            <span class="absolute left-4 top-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-black text-white {{ $post->type === 'youtube' ? 'bg-red-500' : 'bg-blue-600' }}">
                                <i class="{{ $post->type_icon }}"></i>
                                {{ $post->type_label }}
                            </span>

                            @if($post->type === 'youtube')
                                <div class="absolute inset-0 bg-black/25 flex items-center justify-center text-white">
                                    <div class="w-16 h-16 rounded-full bg-red-500 flex items-center justify-center shadow-xl">
                                        <i class="fas fa-play ml-1"></i>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="p-6">
                            <p class="text-sm font-bold text-slate-500 mb-3">
                                <i class="fas fa-calendar-alt mr-2 {{ $post->type === 'youtube' ? 'text-red-500' : 'text-blue-500' }}"></i>
                                {{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}
                                &nbsp;|&nbsp;
                                <i class="fas fa-user {{ $post->type === 'youtube' ? 'text-red-500' : 'text-blue-500' }}"></i> {{ $post->display_author }}
                                @if($post->display_editor !== '-')
                                    &nbsp;|&nbsp; <i class="fas fa-edit {{ $post->type === 'youtube' ? 'text-red-500' : 'text-blue-500' }}"></i> {{ $post->display_editor }}
                                @endif
                            </p>

                            <h2 class="text-xl font-black text-slate-900 leading-snug group-hover:text-blue-600 transition">
                                {{ \Illuminate\Support\Str::limit($post->title, 85) }}
                            </h2>

                            <p class="text-slate-500 leading-relaxed mt-3">
                                {{ \Illuminate\Support\Str::limit($post->excerpt ?? $post->content, 120) }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($posts->hasPages())
                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @endif
        @else
            <div class="rounded-[32px] bg-white border border-dashed border-blue-200 p-14 text-center text-slate-400">
                <i class="{{ $type === 'youtube' ? 'fab fa-youtube' : 'fas fa-newspaper' }} text-5xl mb-5 block"></i>
                <p class="font-black text-lg">
                    Belum ada {{ $type === 'youtube' ? 'video YouTube' : 'artikel' }} yang dipublikasikan.
                </p>
            </div>
        @endif

    </div>
</main>

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

</body>
</html>