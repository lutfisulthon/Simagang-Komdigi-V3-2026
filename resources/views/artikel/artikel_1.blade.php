<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Sharing Session Looker Studio - Simagang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f7ff;
            color: #0f2d4a;
            margin: 0;
            overflow-x: hidden;
        }

        /* ── PROGRESS BAR ── */
        .read-progress {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            background: linear-gradient(90deg, #1d6fca, #0ea5e9, #22d3ee);
            z-index: 999;
            width: 0%;
            transition: width 0.1s linear;
        }

        /* ── BACK BUTTON ── */
        .btn-back {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(16px);
            color: #1d6fca;
            font-size: 13px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(14,99,201,0.18);
            border: 1px solid rgba(191,219,254,0.8);
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #1d6fca;
            color: white;
            transform: translateX(-4px);
        }

        /* ── HERO ── */
        .article-hero {
            position: relative;
            min-height: 520px;
            display: flex;
            align-items: flex-end;
            overflow: hidden;
            background: linear-gradient(135deg, #0c2d5e 0%, #1251a3 50%, #0891b2 100%);
        }
        .hero-bg-pattern {
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(34,211,238,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(29,111,202,0.2) 0%, transparent 40%);
        }
        .hero-content {
            position: relative;
            z-index: 5;
            max-width: 1280px;
            margin: 0 auto;
            padding: 8rem 1.5rem 4rem;
            width: 100%;
        }
        .hero-category {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(34,211,238,0.15);
            border: 1px solid rgba(34,211,238,0.4);
            color: #22d3ee;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 1.25rem;
        }
        .hero-title {
            font-size: clamp(1.75rem, 5vw, 3rem);
            font-weight: 800;
            color: white;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 20px rgba(0,0,0,0.2);
        }
        .hero-title span { color: #7dd3fc; }
        .hero-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 7px;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
        }

        /* ── ARTICLE BODY ── */
        .article-body {
            padding: 4rem 0 6rem;
        }
        .article-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* ── STEP BLOCKS ── */
        .step-block {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            padding: 4rem 0;
            border-bottom: 1px solid #dbeafe;
        }
        .step-block:last-child { border-bottom: none; }

        .step-label {
            display: inline-flex;
            background: #eff6ff;
            color: #1d6fca;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 50px;
            margin-bottom: 1rem;
        }
        .step-number {
            font-size: clamp(3rem, 8vw, 5rem);
            font-weight: 900;
            color: #dbeafe;
            line-height: 1;
        }
        .step-title {
            font-size: clamp(1.4rem, 3vw, 1.8rem);
            font-weight: 800;
            color: #0f2d4a;
            margin-bottom: 1rem;
        }
        .step-desc {
            font-size: 1rem;
            color: #4b6580;
            line-height: 1.7;
        }

        /* ── CHECKLIST ── */
        .check-list {
            list-style: none;
            padding: 0;
            margin-top: 1.5rem;
            display: grid;
            gap: 10px;
        }
        .check-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: #4b6580;
            padding: 10px 14px;
            background: white;
            border: 1px solid #e0eeff;
            border-radius: 12px;
        }
        .check-icon {
            width: 20px; height: 20px;
            background: #1d6fca;
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; flex-shrink: 0;
        }

        /* ── IMAGE WRAPPER ── */
        .img-wrapper {
            position: relative;
            width: 100%;
        }
        .img-wrapper img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 45px rgba(14,99,201,0.1);
            border: 4px solid white;
        }

        /* ── FOOTER ── */
        .main-footer {
            background: linear-gradient(135deg, #0c2d5e 0%, #1251a3 50%, #0891b2 100%);
            color: white; padding: 24px 0;
            border-top: 1px solid rgba(34,211,238,0.25);
        }
        .footer-inner {
            display: flex; justify-content: space-between; align-items: center;
            max-width: 1280px; margin: 0 auto; padding: 0 2rem;
            gap: 1.5rem; flex-wrap: wrap;
        }
        .footer-logos { display: flex; gap: 1.5rem; align-items: center; }
        .footer-logos img { height: 28px; object-fit: contain; }
        .footer-copy { font-size: 13px; color: rgba(255,255,255,0.75); }
        .footer-social { display: flex; gap: 10px; }
        .footer-social a {
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px; background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            color: white; text-decoration: none; font-size: 14px; transition: all 0.25s;
        }
        .footer-social a:hover { background: rgba(34,211,238,0.2); border-color: rgba(34,211,238,0.4); transform: translateY(-2px); }


        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .step-block {
                grid-template-columns: 1fr; 
                gap: 2.5rem;
            }
            .step-block {
                display: flex;
                flex-direction: column-reverse;
            }
            .step-block:nth-child(even) {
                flex-direction: column-reverse;
            }
            .hero-content { padding-top: 10rem; }
        }

        @media (max-width: 640px) {
            .btn-back { top: 12px; left: 12px; padding: 8px 16px; }
            .hero-meta-item { font-size: 12px; }
            .hero-title { margin-bottom: 2rem; }
            .article-body { padding: 2rem 0; }
            .footer-inner { flex-direction: column; text-align: center; }
            .footer-logos { justify-content: center; }
        }

        /* ── ANIMATIONS ── */
        .reveal, .reveal-left, .reveal-right {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease-out;
        }
        .reveal.visible, .reveal-left.visible, .reveal-right.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
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