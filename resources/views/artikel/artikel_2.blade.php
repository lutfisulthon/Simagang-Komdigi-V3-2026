<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Podcast Anak Magang - Simagang</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/fa-subset.css') }}?v={{ @filemtime(public_path('vendor/fontawesome/fa-subset.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts-poppins.css') }}?v={{ @filemtime(public_path('css/fonts-poppins.css')) }}">
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/artikel-artikel-2.css') }}?v={{ @filemtime(public_path('css/pages/artikel-artikel-2.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
</head>
<body>

    <div class="read-progress" id="readProgress"></div>

    <a href="{{ url('/') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>

    <header class="article-hero">
        <div class="hero-bg-pattern"></div>
        <div class="hero-wave"></div>
        <div class="soundwave">
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span>
        </div>
        <div class="hero-float-mic"><i class="fas fa-microphone-alt"></i></div>
        <div class="hero-content">
            <div class="hero-category">
                <i class="fas fa-podcast"></i>
                Podcast Anak Magang
            </div>
            <h1 class="hero-title">
                Gen-Z dan <span>Keamanan Digital</span><br>di Era Modern
            </h1>
            <div class="hero-meta">
                <div class="hero-meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    06 Mei 2026
                </div>
                <div class="hero-divider"></div>
                <div class="hero-meta-item">
                    <i class="fas fa-headphones"></i>
                    Episode 01 · Capila 2026
                </div>
                <div class="hero-divider"></div>
                <div class="hero-meta-item">
                    <i class="fas fa-building"></i>
                    BBLSDM Komdigi Makassar
                </div>
            </div>
        </div>
    </header>

    <main class="article-body">
        <div class="article-container">

            <div class="step-block">
                <div class="reveal-left">
                    <div class="step-label"><i class="fas fa-podcast"></i> Segmen 01</div>
                    <div class="step-number">01</div>
                    <div class="step-icon-wrap"><i class="fas fa-microphone"></i></div>
                    <h3 class="step-title">Diskusi Santai Seputar Dunia Digital Gen-Z</h3>
                    <p class="step-desc">
                        Dalam podcast ini, para peserta magang berbagi pengalaman mengenai kebiasaan generasi muda dalam menggunakan teknologi digital untuk belajar, bekerja, dan berkomunikasi. Pembahasan dilakukan secara santai namun tetap informatif dan membangun.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Membahas aktivitas digital anak muda</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Diskusi ringan tentang keamanan digital</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Berbagi pengalaman menggunakan teknologi</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Meningkatkan kesadaran literasi digital</li>
                    </ul>
                </div>
                <div class="img-wrapper reveal-right">
                    <img src="{{ asset('storage/artikel/artikel_2.png') }}" alt="Podcast Anak Magang">
                    <span class="img-caption"><i class="fas fa-microphone" style="margin-right:5px"></i>Sesi Rekaman Podcast</span>
                </div>
            </div>

            <div class="step-block">
                <div class="img-wrapper reveal-left">
                    <img src="{{ asset('storage/artikel/artikel_2_1.png') }}" alt="Diskusi Podcast">
                    <span class="img-caption"><i class="fas fa-comments" style="margin-right:5px"></i>Diskusi Interaktif Peserta</span>
                </div>
                <div class="reveal-right">
                    <div class="step-label"><i class="fas fa-laptop"></i> Segmen 02</div>
                    <div class="step-number">02</div>
                    <div class="step-icon-wrap"><i class="fas fa-mobile-alt"></i></div>
                    <h3 class="step-title">Laptop atau Smartphone untuk Produktivitas?</h3>
                    <p class="step-desc">
                        Pada sesi ini, peserta membahas perbedaan penggunaan laptop dan smartphone dalam mendukung produktivitas sehari-hari. Laptop unggul untuk multitasking berat, sementara smartphone memberikan fleksibilitas yang lebih tinggi dalam mobilitas.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Membandingkan penggunaan laptop dan smartphone</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Diskusi kebiasaan belajar Gen-Z</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Mengenal pola kerja digital modern</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Berbagi pengalaman penggunaan teknologi</li>
                    </ul>
                </div>
            </div>

            <div class="step-block">
                <div class="reveal-left">
                    <div class="step-label"><i class="fas fa-shield-alt"></i> Segmen 03</div>
                    <div class="step-number">03</div>
                    <div class="step-icon-wrap"><i class="fas fa-lock"></i></div>
                    <h3 class="step-title">Pentingnya Menjaga Keamanan Data Digital</h3>
                    <p class="step-desc">
                        Podcast ini juga memberikan edukasi mengenai pentingnya menjaga keamanan akun dan data pribadi di internet. Generasi muda diajak untuk lebih bijak memahami risiko keamanan digital yang sering terjadi di lingkungan sosial media.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Menjaga privasi akun media sosial</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Menggunakan password yang kuat dan aman</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Bijak membagikan informasi pribadi</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Meningkatkan kesadaran keamanan digital</li>
                    </ul>
                </div>
                <div class="img-wrapper reveal-right">
                    <img src="{{ asset('storage/artikel/artikel_2_2.png') }}" alt="Keamanan Digital Gen-Z">
                    <span class="img-caption"><i class="fas fa-shield-alt" style="margin-right:5px"></i>Edukasi Keamanan Digital</span>
                </div>
            </div>

        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-inner">
            <div class="footer-logos">
                <img src="{{ url('storage/vendor/logo_berakhlak.png') }}" alt="BerAkhlak">
                <img src="{{ url('storage/vendor/logo_banggamelayani.png') }}" alt="Bangga Melayani">
                <img src="{{ url('storage/vendor/logo_antikorupsi.png') }}" alt="Anti Korupsi">
            </div>
            <div class="footer-copy">
                &copy; 2026 <strong>Simagang</strong> — BBLSDM Komdigi Makassar. All rights reserved.
            </div>
            <div class="footer-social">
                <a href="https://www.instagram.com/bblsdm.komdigi.makassar/" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.komdigi.go.id/" target="_blank"><i class="fas fa-globe"></i></a>
                <a href="https://www.tiktok.com/@balaikomdigimakassar" target="_blank"><i class="fab fa-tiktok"></i></a>
                <a href="https://www.youtube.com/@bblsdm.komdigi.makassar" target="_blank"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </footer>

    <script>
        const bar = document.getElementById('readProgress');
        window.addEventListener('scroll', () => {
            const total = document.body.scrollHeight - window.innerHeight;
            bar.style.width = (window.scrollY / total * 100) + '%';
        });

        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => obs.observe(el));
    </script>
</body>
</html>