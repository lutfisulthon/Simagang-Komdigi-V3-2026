<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Healthy Friday - BBLSDM Komdigi Makassar</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/fa-subset.css') }}?v={{ @filemtime(public_path('vendor/fontawesome/fa-subset.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts-poppins.css') }}?v={{ @filemtime(public_path('css/fonts-poppins.css')) }}">
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/artikel-artikel-3.css') }}?v={{ @filemtime(public_path('css/pages/artikel-artikel-3.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
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