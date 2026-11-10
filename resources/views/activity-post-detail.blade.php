<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.partials.theme-head')
    <title>{{ $activityPost->title }} - Simagang</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/fa-subset.css') }}?v={{ @filemtime(public_path('vendor/fontawesome/fa-subset.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts-poppins.css') }}?v={{ @filemtime(public_path('css/fonts-poppins.css')) }}">

    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">

    <link rel="stylesheet" href="{{ asset('css/pages/activity-post-detail.css') }}?v={{ @filemtime(public_path('css/pages/activity-post-detail.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
</head>

<body class="detail-bg min-h-screen">

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
            <a href="{{ route('landing') }}#news"
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
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 py-12 md:py-16">

            <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_.8fr] gap-8 items-stretch">

                <div class="relative rounded-[34px] overflow-hidden min-h-[430px] md:min-h-[560px] shadow-2xl bg-blue-900">
                    @if($activityPost->type === 'youtube')
                        @if($activityPost->youtube_embed_url)
                            <iframe src="{{ $activityPost->youtube_embed_url }}"
                                    class="w-full h-full min-h-[430px] md:min-h-[560px]"
                                    frameborder="0"
                                    allowfullscreen>
                            </iframe>
                        @else
                            <div class="w-full h-full min-h-[430px] md:min-h-[560px] bg-gradient-to-br from-red-700 via-slate-900 to-black flex flex-col items-center justify-center text-white">
                                <div class="w-28 h-28 rounded-3xl bg-white/15 flex items-center justify-center mb-5">
                                    <i class="fab fa-youtube text-6xl"></i>
                                </div>

                                <p class="text-3xl font-bold">
                                    Link YouTube belum valid
                                </p>

                                <p class="text-sm text-red-100 mt-2">
                                    Silakan periksa link video pada admin.
                                </p>
                            </div>
                        @endif
                    @else
                        <img src="{{ $activityPost->thumbnail_url }}"
                             alt="{{ $activityPost->title }}"
                             class="w-full h-full object-cover">
                    @endif

                    @if($activityPost->type !== 'youtube')
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/30 to-transparent"></div>

                        <div class="absolute left-0 right-0 bottom-0 p-7 md:p-10 text-white">
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-cyan-400 text-slate-950 text-xs font-extrabold uppercase mb-4">
                                {{ $activityPost->type_label }}
                            </span>

                            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight max-w-3xl">
                                {{ $activityPost->title }}
                            </h1>

                            <div class="flex flex-wrap gap-4 mt-5 text-sm text-white/90">
                                <span>
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    {{ $activityPost->published_at ? $activityPost->published_at->format('d M Y') : '-' }}
                                </span>

                                @if($activityPost->type !== 'youtube')

<span>
    <i class="fas fa-user mr-2"></i>
    {{ $activityPost->display_author }}
</span>

@endif

                                @if($activityPost->display_editor !== '-')
                                <span>
                                    <i class="fas fa-edit mr-2"></i>
                                    Ed: {{ $activityPost->display_editor }}
                                </span>
                                @endif

                                <span>
                                    <i class="{{ $activityPost->type_icon }} mr-2"></i>
                                    {{ $activityPost->type_label }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="glass-card rounded-[34px] p-6 md:p-8 shadow-xl">
                    <div class="mb-7">
                        <p class="text-sm font-bold uppercase tracking-widest {{ $activityPost->type === 'youtube' ? 'text-red-600' : 'text-blue-600' }}">
                            {{ $activityPost->type_label }}
                        </p>

                        <h2 class="text-2xl font-extrabold text-slate-900 mt-2">
                            Informasi Konten
                        </h2>
                    </div>

                    <div class="space-y-5">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl {{ $activityPost->type === 'youtube' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center shrink-0">
                                <i class="{{ $activityPost->type_icon }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Jenis Konten</p>
                                <p class="text-sm text-gray-500">{{ $activityPost->type_label }}</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Tanggal Publikasi</p>
                                <p class="text-sm text-gray-500">
                                    {{ $activityPost->published_at ? $activityPost->published_at->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        @if($activityPost->type !== 'youtube')

<div class="flex gap-4">

    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex 
    items-center justify-center shrink-0">

        <i class="fas fa-user"></i>

    </div>


    <div>

        <p class="text-sm font-bold text-slate-800">
            Penulis
        </p>


        <p class="text-sm text-gray-500">
            {{ $activityPost->display_author }}
        </p>


    </div>

</div>

@endif

                        @if($activityPost->display_editor !== '-')
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Editor</p>
                                <p class="text-sm text-gray-500">
                                    {{ $activityPost->display_editor }}
                                </p>
                            </div>
                        </div>
                        @endif

                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-heading"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Judul</p>
                                <p class="text-sm text-gray-500 leading-relaxed">
                                    {{ $activityPost->title }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($activityPost->excerpt)
                        <div class="mt-8 p-5 rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white">
                            <p class="text-sm text-blue-100">
                                Ringkasan
                            </p>
                            <p class="font-semibold mt-2 leading-relaxed">
                                {{ $activityPost->excerpt }}
                            </p>
                        </div>
                    @endif

                    @php
                        $shareTitle = $activityPost->title ?? 'Aktivitas Simagang';
                        $shareDate = $activityPost->published_at ? $activityPost->published_at->format('d M Y') : '-';
                        $shareType = $activityPost->type_label ?? 'Aktivitas';
                        $shareUrl = url()->current();

                        $shareText = "Yuk baca konten terbaru Simagang!\n\n"
                            . "Judul: " . $shareTitle . "\n"
                            . "Tipe: " . $shareType . "\n"
                            . "Tanggal: " . $shareDate . "\n\n"
                            . "Buka detail:\n" . $shareUrl;

                        $whatsappShareUrl = 'https://wa.me/?text=' . rawurlencode($shareText);
                    @endphp

                    <div class="share-box">
                        <div class="share-title">
                            <i class="fas fa-share-alt"></i>
                            Bagikan Konten
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
                                    onclick="copyActivityPostLink()">
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
                                Isi Konten
                            </p>
                            <h2 class="text-2xl font-extrabold text-slate-900">
                                {{ $activityPost->type === 'youtube' ? 'Deskripsi Video' : 'Artikel Lengkap' }}
                            </h2>
                        </div>
                    </div>

                    @if($activityPost->type === 'artikel')
                        @if($activityPost->content)
                            <div class="article-content">
                                {!! nl2br(e($activityPost->content)) !!}
                            </div>
                        @else
                            <div class="rounded-3xl border border-dashed border-gray-200 bg-white/70 p-8 text-center text-gray-400">
                                <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                                <p class="font-semibold">
                                    Isi artikel belum tersedia.
                                </p>
                            </div>
                        @endif
                    @else
                        @if($activityPost->content || $activityPost->excerpt)
                            <div class="text-gray-600 leading-relaxed whitespace-pre-line text-base">
                                {{ $activityPost->content ?: $activityPost->excerpt }}
                            </div>
                        @else
                            <div class="rounded-3xl border border-dashed border-gray-200 bg-white/70 p-8 text-center text-gray-400">
                                <i class="fab fa-youtube text-4xl text-gray-300 mb-4"></i>
                                <p class="font-semibold">
                                    Deskripsi video belum tersedia.
                                </p>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="glass-card rounded-[34px] p-7 shadow-lg">
                    <div class="mb-6">
                        <p class="text-sm font-bold uppercase tracking-widest text-blue-600">
                            Konten Terkait
                        </p>
                        <h2 class="text-2xl font-extrabold text-slate-900 mt-1">
                            {{ $activityPost->type_label }} Lainnya
                        </h2>
                    </div>

                    <div class="space-y-4">
                        @forelse($relatedPosts as $post)
                            <a href="{{ route('public.activity.show', $post->slug) }}"
                               class="flex gap-4 bg-white rounded-3xl p-4 hover:shadow-md transition border border-blue-50 group">

                                <div class="w-24 h-20 rounded-2xl overflow-hidden bg-blue-100 shrink-0 relative">
                                    <img src="{{ $post->thumbnail_url }}"
                                         alt="{{ $post->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition">

                                    @if($post->type === 'youtube')
                                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center text-white">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <div class="inline-flex items-center gap-1 text-[10px] font-black uppercase {{ $post->type === 'youtube' ? 'text-red-600' : 'text-blue-600' }}">
                                        <i class="{{ $post->type_icon }}"></i>
                                        {{ $post->type_label }}
                                    </div>

                                    <h3 class="font-bold text-slate-900 leading-snug line-clamp-2 mt-1">
                                        {{ $post->title }}
                                    </h3>

                                    <p class="text-xs text-gray-400 mt-2">
                                        <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                                        {{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-3xl border border-dashed border-gray-200 bg-white/70 p-8 text-center text-gray-400">
                                <i class="fas fa-newspaper text-3xl text-gray-300 mb-3"></i>
                                <p class="font-semibold">
                                    Belum ada konten terkait.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </section>
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

<script>
    function copyActivityPostLink() {
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