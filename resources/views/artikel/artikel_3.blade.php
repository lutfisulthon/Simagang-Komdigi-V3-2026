<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Healthy Friday - BBLSDM Komdigi Makassar</title>
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
            top: 0; left: 0; height: 3px;
            background: linear-gradient(90deg, #1d6fca, #0ea5e9, #22d3ee);
            z-index: 999; width: 0%; transition: width 0.1s linear;
        }

        /* ── BACK BUTTON ── */
        .btn-back {
            position: fixed;
            top: 20px; left: 20px; z-index: 100;
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(16px);
            color: #1d6fca; font-size: 13px; font-weight: 600;
            border-radius: 50px; text-decoration: none;
            box-shadow: 0 4px 20px rgba(29,111,202,0.15);
            border: 1px solid rgba(186,230,253,0.8);
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #1d6fca; color: white;
            transform: translateX(-4px);
            box-shadow: 0 6px 24px rgba(29,111,202,0.35);
        }

        /* ── HERO ── */
        .article-hero {
            position: relative;
            min-height: 520px;
            display: flex;
            align-items: flex-end;
            overflow: hidden;
            background: linear-gradient(135deg, #0f2d4a 0%, #1e40af 40%, #1d6fca 70%, #0ea5e9 100%);
        }
        .hero-bg-pattern {
            position: absolute; inset: 0;
            background-image:
                radial-gradient(circle at 10% 60%, rgba(34,211,238,0.18) 0%, transparent 45%),
                radial-gradient(circle at 80% 20%, rgba(14,165,233,0.15) 0%, transparent 40%),
                radial-gradient(circle at 50% 90%, rgba(29,111,202,0.12) 0%, transparent 40%);
        }
        .hero-dots {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .hero-hearts {
            position: absolute; inset: 0; overflow: hidden; pointer-events: none;
        }
        .heart {
            position: absolute;
            color: rgba(255,255,255,0.06);
            font-size: 2rem;
            animation: floatHeart 10s ease-in-out infinite;
        }
        .heart:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; }
        .heart:nth-child(2) { left: 30%; top: 60%; animation-delay: 2s; }
        .heart:nth-child(3) { left: 60%; top: 15%; animation-delay: 4s; }
        .heart:nth-child(4) { left: 80%; top: 50%; animation-delay: 1s; }
        .heart:nth-child(5) { left: 50%; top: 75%; animation-delay: 3s; }
        @keyframes floatHeart {
            0%, 100% { transform: translateY(0) rotate(-10deg); opacity: 0.4; }
            50% { transform: translateY(-25px) rotate(10deg); opacity: 0.8; }
        }

        .hero-content {
            position: relative; z-index: 5;
            max-width: 1280px; margin: 0 auto;
            padding: 8rem 2rem 4rem; width: 100%;
        }
        .hero-category {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(34,211,238,0.18);
            border: 1px solid rgba(34,211,238,0.4);
            color: #7dd3fc; font-size: 11px; font-weight: 700;
            letter-spacing: 0.2em; text-transform: uppercase;
            padding: 6px 16px; border-radius: 50px; margin-bottom: 1.25rem;
        }
        .hero-title {
            font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800;
            color: white; line-height: 1.15; margin: 0 0 1.25rem; max-width: 700px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.25);
        }
        .hero-title span { color: #7dd3fc; }
        .hero-meta {
            display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;
        }
        .hero-meta-item {
            display: flex; align-items: center; gap: 7px;
            color: rgba(255,255,255,0.65); font-size: 13px;
        }
        .hero-meta-item i { color: #7dd3fc; font-size: 12px; }
        .hero-divider { width: 1px; height: 16px; background: rgba(255,255,255,0.2); }

        /* ── ARTICLE BODY ── */
        .article-body { padding: 5rem 0 8rem; }
        .article-container { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }

    

        /* ── SLOGAN BANNER ── */
        .slogan-banner {
            background: linear-gradient(135deg, #0f2d4a, #1e40af);
            border-radius: 24px; padding: 2.5rem 3rem;
            text-align: center; margin: 3rem 0;
            position: relative; overflow: hidden;
        }
        .slogan-icon {
            font-size: 2.5rem; color: #7dd3fc; margin-bottom: 0.75rem;
            display: block; animation: heartbeat 1.5s ease-in-out infinite;
        }
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .slogan-text {
            font-size: clamp(1.1rem, 2.5vw, 1.5rem);
            font-weight: 700; color: white; margin: 0;
            font-style: italic; position: relative; z-index: 1;
        }
        .slogan-text em { color: #7dd3fc; font-style: normal; }

        /* ── STEP BLOCKS ── */
        .step-block {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 5rem; align-items: center; padding: 4.5rem 0;
        }
        .step-block + .step-block { border-top: 1px solid #e0f2fe; }

        .step-label {
            display: inline-flex; align-items: center; gap: 7px;
            background: #eff6ff; border: 1px solid #bae6fd;
            color: #1d6fca; font-size: 10px; font-weight: 700;
            letter-spacing: 0.18em; text-transform: uppercase;
            padding: 5px 14px; border-radius: 50px; margin-bottom: 1.2rem;
        }
        .step-number {
            font-size: 5rem; font-weight: 900; color: #e0f2fe;
            line-height: 1; margin-bottom: 0.25rem; letter-spacing: -0.05em;
        }
        .step-icon-wrap {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 22px;
            box-shadow: 0 8px 24px rgba(29,111,202,0.3);
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
            padding: 10px 14px; background: #ffffff;
            border: 1px solid #e0f2fe; border-radius: 12px; transition: all 0.2s;
        }
        .check-list li:hover { background: #f0f7ff; border-color: #7dd3fc; transform: translateX(4px); }
        .check-list li .check-icon {
            width: 24px; height: 24px; min-width: 24px;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 10px;
        }

        /* ── IMAGE WRAPPER ── */
        .img-wrapper { position: relative; }
        .img-wrapper::before {
            content: ''; position: absolute; inset: -12px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            border-radius: 32px; z-index: 0; opacity: 0.5;
        }
        .img-wrapper img {
            position: relative; z-index: 1; width: 100%;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(29,111,202,0.15);
            border: 2px solid white; transition: transform 0.4s ease; display: block;
        }
        .img-wrapper img:hover { transform: scale(1.02) translateY(-4px); }
        .img-caption {
            position: absolute; bottom: 16px; left: 50%;
            transform: translateX(-50%); z-index: 2;
            background: rgba(255,255,255,0.92); backdrop-filter: blur(10px);
            border: 1px solid rgba(125,211,252,0.6);
            border-radius: 50px; padding: 6px 16px;
            font-size: 12px; font-weight: 600; color: #1d6fca;
            white-space: nowrap; box-shadow: 0 4px 16px rgba(29,111,202,0.12);
        }

        .benefit-card {
            background: white; border: 1.5px solid #bae6fd;
            border-radius: 20px; padding: 1.75rem;
            display: flex; align-items: flex-start; gap: 1rem;
            transition: all 0.3s; position: relative; overflow: hidden;
        }
        .benefit-card::after {
            content: ''; position: absolute;
            bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #1d6fca, #0ea5e9);
            transform: scaleX(0); transition: transform 0.3s;
        }
        .benefit-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(29,111,202,0.1); border-color: #7dd3fc; }
        .benefit-card:hover::after { transform: scaleX(1); }
        .benefit-icon {
            width: 48px; height: 48px; min-width: 48px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: #1d6fca; font-size: 18px;
        }
        .benefit-title { font-size: 15px; font-weight: 700; color: #0f2d4a; margin: 0 0 0.3rem; }
        .benefit-desc { font-size: 13px; color: #64748b; line-height: 1.5; margin: 0; }

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

        /* ── ANIMATIONS ── */
        .reveal { opacity: 0; transform: translateY(32px); transition: 0.75s ease; }
        .reveal-left { opacity: 0; transform: translateX(-32px); transition: 0.75s ease; }
        .reveal-right { opacity: 0; transform: translateX(32px); transition: 0.75s ease; }
        .visible { opacity: 1; transform: translate(0); }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .step-block { grid-template-columns: 1fr; gap: 2.5rem; text-align: center; padding: 3rem 0; }
            .step-icon-wrap { margin: 0 auto 1.25rem; }
            .step-number { font-size: 3.5rem; }
            .intro-card { flex-direction: column; padding: 2rem; }
            .info-tags { justify-content: center; }
        }

        @media (max-width: 640px) {
            .hero-content { padding: 4rem 1.5rem 2rem; }
            .hero-title { font-size: 1.8rem; }
            .step-number { font-size: 2.5rem; }
            .slogan-banner { padding: 1.5rem 2rem; }
            .footer-inner { flex-direction: column; text-align: center; }
            .footer-logos { justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="read-progress" id="readProgress"></div>

    <a href="{{ url('/') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <header class="article-hero">
        <div class="hero-bg-pattern"></div>
        <div class="hero-dots"></div>
        <div class="hero-hearts">
            <i class="heart fas fa-heartbeat"></i>
            <i class="heart fas fa-heart"></i>
            <i class="heart fas fa-dumbbell"></i>
            <i class="heart fas fa-running"></i>
            <i class="heart fas fa-heartbeat"></i>
        </div>
        <div class="hero-content">
            <div class="hero-category">
                <i class="fas fa-heartbeat"></i> Kegiatan Pegawai
            </div>
            <h1 class="hero-title">
                Healthy Friday: <span>Menjaga Kebugaran</span><br>di Lingkungan Kerja
            </h1>
            <div class="hero-meta">
                <div class="hero-meta-item"><i class="fas fa-calendar-alt"></i> 30 Januari 2025</div>
                <div class="hero-divider"></div>
                <div class="hero-meta-item"><i class="fas fa-map-marker-alt"></i> Rooftop Lt. 4, Komdigi Makassar</div>
                <div class="hero-divider"></div>
                <div class="hero-meta-item"><i class="fas fa-clock"></i> 07.00 WITA</div>
            </div>
        </div>
    </header>

    <main class="article-body">
        <div class="article-container">

            {{-- <div class="intro-card reveal">
                <div class="intro-icon"><i class="fas fa-heartbeat"></i></div>
                <p class="intro-text">
                    Program rutin <strong>"Healthy Friday"</strong> BBLSDM Komdigi Makassar bertujuan untuk menjaga kesehatan fisik dan membangun semangat produktivitas seluruh civitas di lingkungan kantor. Kegiatan ini merupakan wujud nyata implementasi nilai <strong>BerAKHLAK</strong> dalam kehidupan kerja sehari-hari.
                </p>
            </div>

            <div class="slogan-banner reveal">
                <i class="slogan-icon fas fa-heartbeat"></i>
                <p class="slogan-text">"<em>Sehat, bugar, dan produktif</em> dimulai dari kita"</p>
            </div> --}}

            <div class="step-block">
                <div class="reveal-left">
                    <div class="step-label"><i class="fas fa-calendar-alt"></i> Agenda 01</div>
                    <div class="step-number">01</div>
                    <div class="step-icon-wrap"><i class="fas fa-heartbeat"></i></div>
                    <h3 class="step-title">Senam Pagi Bersama</h3>
                    <p class="step-desc">
                        Kegiatan rutin ini dipandu oleh instruktur berpengalaman. Seluruh pegawai diajak untuk bergerak aktif melalui senam aerobik dan zumba guna melepas penat sekaligus meningkatkan energi sebelum memulai pelayanan kepada masyarakat.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Meningkatkan kebugaran jasmani</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Dipandu instruktur profesional</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Diikuti oleh seluruh jajaran pegawai</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Membangun pola hidup sehat di kantor</li>
                    </ul>
                </div>
                <div class="img-wrapper reveal-right">
                    <img src="{{ asset('storage/artikel/artikel_3.png') }}" alt="Flyer Healthy Friday">
                    <span class="img-caption"><i class="fas fa-image" style="margin-right:5px"></i>Flyer Healthy Friday</span>
                </div>
            </div>

            <div class="step-block">
                <div class="img-wrapper reveal-left">
                    <img src="{{ asset('storage/artikel/artikel_3_1.png') }}" alt="Pelaksanaan Senam Pagi">
                    <span class="img-caption"><i class="fas fa-camera" style="margin-right:5px"></i>Pelaksanaan Senam Pagi</span>
                </div>
                <div class="reveal-right">
                    <div class="step-label"><i class="fas fa-map-marker-alt"></i> Agenda 02</div>
                    <div class="step-number">02</div>
                    <div class="step-icon-wrap"><i class="fas fa-users"></i></div>
                    <h3 class="step-title">Kompak dengan Dress Code Unik</h3>
                    <p class="step-desc">
                        Dilaksanakan pada Jumat pagi pukul 07.00 WITA bertempat di <strong>Rooftop Lt. 4</strong> Kantor BBLSDM Komdigi Makassar. Para peserta tampil semangat dengan dress code bertema khusus, menciptakan suasana yang ceria dan penuh kebersamaan.
                    </p>
                </div>
            </div>

            <div class="step-block">
                <div class="reveal-left">
                    <div class="step-label"><i class="fas fa-fist-raised"></i> Agenda 03</div>
                    <div class="step-number">03</div>
                    <div class="step-icon-wrap"><i class="fas fa-smile-beam"></i></div>
                    <h3 class="step-title">Sehat, Bugar, dan Produktif</h3>
                    <p class="step-desc">
                        Program ini bukan sekadar olahraga, melainkan bentuk implementasi nilai BerAKHLAK untuk memberikan performa kerja terbaik bagi masyarakat. Dengan tubuh yang sehat, semangat kerja dan kreativitas pegawai pun turut meningkat.
                    </p>
                    <ul class="check-list">
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Meningkatkan fokus dan konsentrasi kerja</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Mengurangi tingkat stres kerja</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Mempererat tali silaturahmi</li>
                        <li><span class="check-icon"><i class="fas fa-check"></i></span> Mendukung budaya kerja positif</li>
                    </ul>
                </div>
                <div class="img-wrapper reveal-right">
                    <img src="{{ asset('storage/artikel/artikel_3_2.png') }}" alt="Vibe Positif Pegawai">
                    <span class="img-caption"><i class="fas fa-camera" style="margin-right:5px"></i>Vibe Positif Pegawai</span>
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