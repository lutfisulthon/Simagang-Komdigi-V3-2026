<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Sharing Session Looker Studio - Simagang</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/fa-subset.css') }}?v={{ @filemtime(public_path('vendor/fontawesome/fa-subset.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts-poppins.css') }}?v={{ @filemtime(public_path('css/fonts-poppins.css')) }}">
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/artikel-artikel-1.css') }}?v={{ @filemtime(public_path('css/pages/artikel-artikel-1.css')) }}">
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
        <div class="hero-content">
            <div class="hero-category">
                <i class="fas fa-chart-line"></i> Sharing Session
            </div>
            <h1 class="hero-title">
                Visualisasi Data Sederhana<br>Menggunakan <span>Looker Studio</span>
            </h1>
            <div class="hero-meta">
                <div class="hero-meta-item"><i class="fas fa-calendar-alt"></i> 07 Mei 2026</div>
                <div class="hero-meta-item"><i class="fas fa-tag"></i> Data Visualization</div>
                <div class="hero-meta-item"><i class="fas fa-building"></i> BBLSDM Komdigi</div>
            </div>
        </div>
    </header>

    <main class="article-body">
        <div class="article-container">

            <section class="step-block">
                <div class="reveal-left">
                    <div class="step-label">Sesi 01</div>
                    <div class="step-number">01</div>
                    <h3 class="step-title">Memahami Dasar Visualisasi Data</h3>
                    <p class="step-desc">
                        Peserta diperkenalkan dengan konsep dasar visualisasi data menggunakan <strong>Looker Studio</strong>. Materi difokuskan pada penyajian data yang efektif untuk mendukung analisis keputusan.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Fitur dasar Looker Studio</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Menghubungkan Google Sheets</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Membuat grafik interaktif</li>
                    </ul>
                </div>
                <div class="img-wrapper reveal-right">
                    <img src="{{ asset('storage/artikel/artikel_1.jpeg') }}" alt="Sesi 1">
                </div>
            </section>

            <section class="step-block">
                <div class="reveal-left">
                    <div class="step-label">Sesi 02</div>
                    <div class="step-number">02</div>
                    <h3 class="step-title">Praktik Dashboard Interaktif</h3>
                    <p class="step-desc">
                        Sesi praktik langsung mengubah data mentah menjadi informasi komunikatif. Peserta belajar mengatur filter dan interaksi antar elemen data.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Desain UI Dashboard</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Pengaturan Filter Data</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Analisis Chart Sederhana</li>
                    </ul>
                </div>
                <div class="img-wrapper reveal-right">
                    <img src="{{ asset('storage/artikel/artikel_1_1.png') }}" alt="Sesi 2">
                </div>
            </section>

            <section class="step-block">
                <div class="reveal-left">
                    <div class="step-label">Sesi 03</div>
                    <div class="step-number">03</div>
                    <h3 class="step-title">Literasi Data Digital</h3>
                    <p class="step-desc">
                        Mendorong kreativitas dalam penyajian informasi agar laporan kerja tampil lebih profesional, informatif, dan menarik bagi stakeholder.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Komunikasi Visual Data</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Keterampilan Digital</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Efektivitas Analisis</li>
                    </ul>
                </div>
                <div class="img-wrapper reveal-right">
                    <img src="{{ asset('storage/artikel/artikel_1_2.jpeg') }}" alt="Sesi 3">
                </div>
            </section>

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
        // Progress Bar
        const bar = document.getElementById('readProgress');
        window.addEventListener('scroll', () => {
            const total = document.body.scrollHeight - window.innerHeight;
            bar.style.width = (window.scrollY / total * 100) + '%';
        });

        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => observer.observe(el));
    </script>
</body>
</html>