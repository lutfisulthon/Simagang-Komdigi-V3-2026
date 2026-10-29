<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Podcast Anak Magang - Simagang</title>
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

        /* ── READ PROGRESS ── */
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
            top: 20px; left: 20px;
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
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #1d6fca;
            color: white;
            transform: translateX(-4px);
            box-shadow: 0 6px 24px rgba(14,99,201,0.35);
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
            position: absolute; inset: 0;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(34,211,238,0.15) 0%, transparent 45%),
                radial-gradient(circle at 85% 25%, rgba(29,111,202,0.15) 0%, transparent 40%),
                radial-gradient(circle at 55% 85%, rgba(34,211,238,0.1) 0%, transparent 40%);
        }
        .hero-wave {
            position: absolute; inset: 0;
            background-image: repeating-linear-gradient(
                -45deg,
                rgba(255,255,255,0.015) 0px,
                rgba(255,255,255,0.015) 1px,
                transparent 1px,
                transparent 20px
            );
        }
        .soundwave {
            position: absolute;
            bottom: 0; right: 0;
            display: flex;
            align-items: flex-end;
            gap: 5px;
            padding: 0 60px 30px 0;
            opacity: 0.1;
        }
        .soundwave span {
            display: block;
            width: 6px;
            background: white;
            border-radius: 3px;
            animation: wave 1.2s ease-in-out infinite;
        }
        .soundwave span:nth-child(1) { height: 20px; animation-delay: 0s; }
        .soundwave span:nth-child(2) { height: 50px; animation-delay: 0.1s; }
        .soundwave span:nth-child(3) { height: 80px; animation-delay: 0.2s; }
        .soundwave span:nth-child(4) { height: 120px; animation-delay: 0.3s; }
        .soundwave span:nth-child(5) { height: 90px; animation-delay: 0.4s; }
        .soundwave span:nth-child(6) { height: 60px; animation-delay: 0.5s; }
        .soundwave span:nth-child(7) { height: 40px; animation-delay: 0.6s; }
        .soundwave span:nth-child(8) { height: 70px; animation-delay: 0.7s; }
        .soundwave span:nth-child(9) { height: 100px; animation-delay: 0.8s; }
        .soundwave span:nth-child(10) { height: 65px; animation-delay: 0.9s; }
        .soundwave span:nth-child(11) { height: 35px; animation-delay: 1.0s; }
        .soundwave span:nth-child(12) { height: 20px; animation-delay: 1.1s; }
        
        @keyframes wave {
            0%, 100% { transform: scaleY(0.6); }
            50% { transform: scaleY(1); }
        }

        .hero-float-mic {
            position: absolute;
            top: 50%; right: 8%;
            transform: translateY(-50%);
            font-size: 10rem;
            color: rgba(255,255,255,0.04);
            line-height: 1;
        }

        .hero-content {
            position: relative;
            z-index: 5;
            max-width: 1280px;
            margin: 0 auto;
            padding: 8rem 2rem 4rem;
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
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 1.25rem;
        }
        .hero-title {
            font-size: clamp(1.8rem, 4vw, 3rem);
            font-weight: 800;
            color: white;
            line-height: 1.15;
            margin: 0 0 1.25rem;
            max-width: 700px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.25);
        }
        .hero-title span { color: #7dd3fc; }
        .hero-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .hero-meta-item {
            display: flex; align-items: center; gap: 7px;
            color: rgba(255,255,255,0.7); font-size: 13px;
        }
        .hero-meta-item i { color: #22d3ee; font-size: 12px; }
        .hero-divider { width: 1px; height: 16px; background: rgba(255,255,255,0.2); }

        /* ── ARTICLE BODY ── */
        .article-body { background: #f0f7ff; padding: 5rem 0 8rem; }
        .article-container { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }

        /* ── STEP BLOCKS ── */
        .step-block {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
            padding: 4.5rem 0;
        }
        .step-block + .step-block { border-top: 1px solid #dbeafe; }

        .step-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d6fca;
            font-size: 10px; font-weight: 700; letter-spacing: 0.18em;
            text-transform: uppercase;
            padding: 5px 14px; border-radius: 50px; margin-bottom: 1.2rem;
        }
        .step-number {
            font-size: 5rem; font-weight: 900; color: #dbeafe;
            line-height: 1; margin-bottom: 0.25rem; letter-spacing: -0.05em;
        }
        .step-icon-wrap {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 22px;
            box-shadow: 0 8px 24px rgba(14,99,201,0.2);
            margin-bottom: 1.25rem;
        }
        .step-title {
            font-size: 1.65rem; font-weight: 800; color: #0f2d4a;
            margin: 0 0 1rem; line-height: 1.25;
        }
        .step-desc { font-size: 1rem; color: #4b6580; line-height: 1.8; margin: 0; }

        /* ── CHECKLIST ── */
        .check-list {
            list-style: none; padding: 0; margin: 1.5rem 0 0;
            display: flex; flex-direction: column; gap: 10px;
        }
        .check-list li {
            display: flex; align-items: center; gap: 12px;
            font-size: 0.92rem; color: #4b6580;
            padding: 10px 14px;
            background: #f8fbff;
            border: 1px solid #e0eeff;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .check-list li:hover { background: #eff6ff; border-color: #93c5fd; transform: translateX(4px); }
        .check-list li .check-icon {
            width: 24px; height: 24px; min-width: 24px;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 10px;
        }

        /* ── IMAGE WRAPPER ── */
        .img-wrapper { position: relative; }
        .img-wrapper::before {
            content: '';
            position: absolute; inset: -12px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-radius: 32px; z-index: 0; opacity: 0.5;
        }
        .img-wrapper img {
            position: relative; z-index: 1; width: 100%;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(14,99,201,0.15);
            border: 2px solid white;
            transition: transform 0.4s ease; display: block;
        }
        .img-wrapper img:hover { transform: scale(1.02) translateY(-4px); }
        .img-caption {
            position: absolute; bottom: 16px; left: 50%;
            transform: translateX(-50%); z-index: 2;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(191,219,254,0.6);
            border-radius: 50px; padding: 6px 16px;
            font-size: 12px; font-weight: 600; color: #1d6fca;
            white-space: nowrap;
            box-shadow: 0 4px 16px rgba(14,99,201,0.12);
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
                gap: 3rem; 
                text-align: center; 
                padding: 3rem 0;
            }
            /* Menyesuaikan arah flex agar gambar selalu di atas pada mobile */
            .step-block { display: flex; flex-direction: column-reverse; }
            /* Khusus blok genap (yang tadinya gambar di kiri), tetap gambar di atas */
            .step-block:nth-child(even) { flex-direction: column-reverse; }
            
            .step-icon-wrap { margin: 0 auto 1.25rem; }
            .step-number { font-size: 3.5rem; }
            .check-list { align-items: center; }
            .check-list li { width: 100%; max-width: 500px; text-align: left; }
            .hero-float-mic { font-size: 6rem; right: 5%; opacity: 0.02; }
        }

        @media (max-width: 768px) {
            .hero-content { padding: 7rem 1.5rem 3rem; }
            .hero-title { font-size: 1.8rem; }
            .hero-meta { gap: 0.75rem; }
            .hero-divider { display: none; }
            .article-body { padding: 3rem 0 6rem; }
            .btn-back { top: 14px; left: 14px; padding: 8px 16px; font-size: 12px; }
            .footer-inner { flex-direction: column; text-align: center; }
            .footer-logos, .footer-social { justify-content: center; }
            .soundwave { display: none; }
        }

        /* ── ANIMATIONS ── */
        .reveal { opacity: 0; transform: translateY(32px); transition: opacity 0.75s ease, transform 0.75s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-left { opacity: 0; transform: translateX(-32px); transition: opacity 0.75s ease, transform 0.75s ease; }
        .reveal-left.visible { opacity: 1; transform: translateX(0); }
        .reveal-right { opacity: 0; transform: translateX(32px); transition: opacity 0.75s ease, transform 0.75s ease; }
        .reveal-right.visible { opacity: 1; transform: translateX(0); }
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