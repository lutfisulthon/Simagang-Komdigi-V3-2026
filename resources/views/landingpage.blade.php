<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.theme-head', ['splash' => true])
    <title>Simagang - Sistem Manajemen Magang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        @font-face {
            font-family: 'Etna';
            src: url('/fonts/Etna-Free-Font.otf') format('opentype');
        }
        .font-etna { font-family: 'Etna', sans-serif; }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f7ff;
            color: #0f2d4a;
            margin: 0;
        }

        /* ── NAVBAR ── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid #bfdbfe;
            box-shadow: 0 1px 20px rgba(14,99,201,0.07);
        }
        .navbar-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-wrap {
            width: 36px;
            height: 36px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .logo-wrap img { width: 24px; height: 24px; object-fit: contain; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-links a {
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #4b6580;
            padding: 8px 16px;
            border-radius: 50px;
            transition: all 0.2s;
        }
        .nav-links a:hover { background: #eff6ff; color: #1d6fca; }

        /* ── DROPDOWN NAVBAR ── */
        .nav-dropdown {
            position: relative;
        }
        .nav-dropdown-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            color: #4b6580;
            padding: 8px 16px;
            border-radius: 50px;
            transition: all 0.2s;
        }
        .nav-dropdown-btn:hover {
            background: #eff6ff;
            color: #1d6fca;
        }
        .nav-dropdown-menu {
            position: absolute;
            top: 46px;
            left: 0;
            min-width: 220px;
            background: white;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(14,99,201,0.12);
            padding: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.2s ease;
            z-index: 80;
        }
        .nav-dropdown:hover .nav-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .nav-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 13px;
            border-radius: 13px;
            color: #4b6580;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }
        .nav-dropdown-menu a:hover {
            background: #eff6ff;
            color: #1d6fca;
        }
        .nav-dropdown-menu i {
            width: 18px;
            text-align: center;
            color: #1d6fca;
        }

        /* ── NAVBAR ACTIONS ── */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .visitor-counter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d6fca;
            border: 1px solid #bfdbfe;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(29,111,202,0.08);
        }
        .visitor-counter i {
            font-size: 12px;
            color: #0ea5e9;
        }
        .visitor-counter span {
            color: #0f2d4a;
        }
        .visitor-counter-mobile { display: none; }
        .visitor-counter-desktop { display: inline-flex; }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9);
            color: white;
            font-size: 14px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(14,99,201,0.3);
            transition: all 0.25s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 22px rgba(14,99,201,0.4); }
        
        .nav-toggle {
            display: none;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 1.5px solid rgba(14,99,201,0.18);
            background: white;
            color: #1d6fca;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .nav-toggle:hover { background: #eff6ff; }
        
        .mobile-menu {
            position: absolute;
            top: 70px;
            right: 1rem;
            width: min(280px, calc(100% - 2rem));
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(219, 234, 254, 0.6);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(14,99,201,0.12);
            display: none;
            flex-direction: column;
            gap: 0.65rem;
            padding: 0.85rem 0.9rem 1rem;
            z-index: 55;
        }
        .mobile-menu.open { display: flex; }
        .mobile-menu a {
            display: block;
            text-decoration: none;
            color: #0f2d4a;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 14px;
            transition: background 0.2s, color 0.2s;
        }
        .mobile-menu a:hover { background: #eff6ff; }

        /* ── MOBILE DROPDOWN AKTIVITAS ── */
        .mobile-dropdown { width: 100%; }
        .mobile-dropdown-btn {
            width: 100%;
            border: none;
            background: transparent;
            color: #0f2d4a;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .mobile-dropdown-btn:hover { background: #eff6ff; }
        .mobile-dropdown-btn-left { display: flex; align-items: center; }
        .mobile-dropdown-content {
            display: none;
            flex-direction: column;
            gap: 6px;
            margin-top: 6px;
            padding-left: 12px;
        }
        .mobile-dropdown.open .mobile-dropdown-content { display: flex; }
        .mobile-dropdown.open .mobile-dropdown-arrow { transform: rotate(180deg); }
        .mobile-dropdown-arrow { transition: transform 0.2s ease; font-size: 11px; color: #1d6fca; }
        .mobile-dropdown-content a {
            background: #f8fbff;
            color: #4b6580;
            font-size: 14px;
            padding: 11px 14px;
            border-left: 3px solid #bfdbfe;
        }

        /* ── HERO ── */
        .hero {
            position: relative;
            overflow: hidden;
            min-height: 90vh;
            display: flex;
            align-items: center;
        }
        .hero-bg-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            z-index: 0;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, rgba(8, 28, 70, 0.53) 0%, rgba(12, 52, 120, 0.70) 50%, rgba(8, 60, 100, 0.347) 100%);
            z-index: 1;
        }
        .hero-accent {
            position: absolute;
            bottom: -120px;
            left: -80px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(34,211,238,0.18) 0%, transparent 70%);
            z-index: 2;
            pointer-events: none;
            animation: pulseAccent 6s ease-in-out infinite alternate;
        }
        @keyframes pulseAccent {
            from { transform: scale(1); opacity: 0.8; }
            to   { transform: scale(1.15); opacity: 1; }
        }
        .hero-inner {
            position: relative;
            z-index: 3;
            max-width: 1280px;
            margin: 0 auto;
            padding: 5rem 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            width: 100%;
        }
        .hero-text-col { display: flex; flex-direction: column; }
        .hero-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            line-height: 1.15;
            color: white;
            margin: 0 0 1.25rem;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }
        .hero-title span { color: #7dd3fc; }
        .hero-desc {
            font-size: 1rem;
            line-height: 1.75;
            color: rgba(255,255,255,0.82);
            margin: 0;
            max-width: 480px;
            text-shadow: 0 1px 8px rgba(0,0,0,0.25);
        }
        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1.5rem;
            padding-bottom: 1.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .hero-stat-num { font-size: 1.75rem; font-weight: 800; color: white; line-height: 1; text-shadow: 0 2px 12px rgba(0,0,0,0.2); }
        .hero-stat-label { font-size: 11px; color: rgba(255,255,255,0.65); margin-top: 4px; }
        .hero-stat-divider { width: 1px; background: rgba(255,255,255,0.2); flex-shrink: 0; }
        
        .hero-btns { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 1.75rem; }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: #22d3ee;
            color: #0c2d5e;
            font-weight: 700;
            font-size: 15px;
            border-radius: 9999px;
            text-decoration: none;
            box-shadow: 0 6px 28px rgba(34,211,238,0.35);
            transition: all 0.25s;
        }
        .btn-primary:hover { background: #38bdf8; transform: translateY(-2px); box-shadow: 0 10px 36px rgba(34,211,238,0.45); }
        
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.4);
            color: white;
            font-weight: 600;
            font-size: 15px;
            border-radius: 9999px;
            text-decoration: none;
            backdrop-filter: blur(6px);
            transition: all 0.25s;
        }
        .btn-outline:hover { background: rgba(255,255,255,0.28); border-color: rgba(255,255,255,0.6); }

        .section-header { text-align: center; max-width: 640px; margin: 0 auto 3.5rem; }
        .section-eyebrow {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #1d6fca;
            background: #eff6ff;
            padding: 5px 14px;
            border-radius: 50px;
            margin-bottom: 1rem;
        }
        .section-eyebrow-alt {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #1d6fca;
            background: #c7dfff;
            padding: 5px 14px;
            border-radius: 50px;
            margin-bottom: 1rem;
        }
        .section-title { font-size: clamp(1.75rem, 3vw, 2.5rem); font-weight: 800; color: #0f2d4a; margin: 0 0 1rem; line-height: 1.2; }
        .section-desc { font-size: 1.05rem; color: #4b6580; line-height: 1.7; margin: 0; }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }

        /* ── PROCESS ── */
        .section-process { background: white; padding: 6rem 0; }
        .process-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        .process-card {
            background: #f0f7ff;
            border: 1.5px solid #bfdbfe;
            border-radius: 24px;
            padding: 2.5rem 2rem;
            position: relative;
            transition: all 0.3s;
            overflow: hidden;
        }
        .process-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #eff6ff, transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .process-card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px rgba(14,99,201,0.12); border-color: #93c5fd; }
        .process-card:hover::before { opacity: 1; }
        .process-num {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 3.5rem;
            font-weight: 900;
            color: #bfdbfe;
            line-height: 1;
        }
        .process-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 22px;
            margin-bottom: 1.5rem;
            position: relative;
            box-shadow: 0 8px 24px rgba(29,111,202,0.3);
        }
        .process-title { font-size: 1.2rem; font-weight: 700; color: #0f2d4a; margin: 0 0 0.6rem; }
        .process-desc { font-size: 0.95rem; color: #4b6580; line-height: 1.6; margin: 0; }

        .process-carousel { display: none; overflow: hidden; }
        .testi-carousel  { display: none; overflow: hidden; }
        .carousel-wrapper { display: flex; transition: transform 0.4s ease-out; }
        .carousel-item { flex: 0 0 100%; min-width: 100%; }
        .carousel-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .carousel-btn {
            width: 40px; height: 40px; border-radius: 50%;
            background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d6fca;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.3s; font-size: 13px;
        }
        .carousel-btn:hover { background: #1d6fca; color: white; border-color: #1d6fca; }
        .carousel-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .dot-wrap { display: flex; gap: 0.5rem; }
        .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #dbeafe; cursor: pointer; transition: all 0.3s;
        }
        .dot.active { background: #1d6fca; width: 24px; border-radius: 4px; }

        /* ── USAGE / TUTORIAL ── */
        .section-usage { background: #f0f7ff; padding: 6rem 0; }
        .step-block {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            padding: 3.5rem 0;
        }
        .step-block + .step-block { border-top: 1px solid #bfdbfe; }
        .step-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: #1d6fca;
            font-size: 11px; font-weight: 700; letter-spacing: 0.15em;
            text-transform: uppercase;
            padding: 5px 12px; border-radius: 50px; margin-bottom: 1rem;
        }
        .step-icon-wrap {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 20px;
            box-shadow: 0 6px 20px rgba(29,111,202,0.28);
            margin-bottom: 1.25rem;
        }
        .step-title { font-size: 1.75rem; font-weight: 800; color: #0f2d4a; margin: 0 0 1rem; }
        .step-desc { font-size: 1.05rem; color: #4b6580; line-height: 1.75; margin: 0; }
        .step-list { list-style: none; padding: 0; margin: 1.25rem 0 0; display: flex; flex-direction: column; gap: 10px; }
        .step-list li {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.95rem; color: #4b6580;
        }
        .step-list li::before {
            content: '';
            width: 22px; height: 22px; min-width: 22px;
            background: #eff6ff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%231d6fca'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l6.879-6.879a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E") center/13px no-repeat;
            border: 1.5px solid #93c5fd;
            border-radius: 50%;
        }
        .step-image {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 16px 50px rgba(14,99,201,0.12);
            border: 2px solid #bfdbfe;
            transition: transform 0.35s;
        }
        .step-image:hover { transform: scale(1.025); }

        /* ── TESTIMONIALS ── */
        .section-testimonials { background: white; padding: 6rem 0; }
        .testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        .testi-card {
            background: #f0f7ff;
            border: 1.5px solid #bfdbfe;
            border-radius: 24px;
            padding: 2rem;
            position: relative;
            transition: all 0.3s;
            display: flex; flex-direction: column;
            min-height: 320px;
        }
        .testi-card:hover { transform: translateY(-4px); box-shadow: 0 16px 44px rgba(14,99,201,0.1); }
        .testi-quote-icon { font-size: 2.5rem; color: #bfdbfe; line-height: 1; margin-bottom: 1rem; }
        .testi-stars { color: #f59e0b; font-size: 13px; margin-bottom: 0.75rem; letter-spacing: 2px; }
        .testi-text { font-size: 1rem; color: #4b6580; line-height: 1.7; margin: 0 0 1.5rem; font-style: italic; flex-grow: 1; }
        .testi-author { display: flex; align-items: center; gap: 12px; padding-top: 1.25rem; border-top: 1px solid #bfdbfe; }
        .testi-avatar {
            width: 48px; height: 48px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1d6fca, #0ea5e9); color: white; font-weight: 700; font-size: 15px;
        }
        .testi-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .testi-name { font-weight: 700; font-size: 14px; color: #0f2d4a; }
        .testi-inst { font-size: 12px; color: #64748b; margin-top: 2px; }

        /* ── PARTNERS ── */
        .section-partners { background: #f0f7ff; padding: 5rem 0; }
        .partners-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; }
        .partner-card {
            background: white;
            border: 1.5px solid #bfdbfe;
            border-radius: 20px;
            padding: 1.75rem;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s;
        }
        .partner-card img { max-height: 64px; object-fit: contain; filter: none; transition: filter 0.3s; }
        .partner-card:hover img { transform: scale(1.1); }
        .partner-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            color: #64748b;
            background: white;
            border-radius: 20px;
            border: 1.5px dashed #bfdbfe;
        }

        /* ── CTA ── */
        .section-cta { background: linear-gradient(135deg, #0c2d5e 0%, #1251a3 50%, #0891b2 100%); padding: 6rem 0; }
        .cta-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
        .cta-tag {
            display: inline-block;
            background: rgba(34,211,238,0.18);
            border: 1px solid rgba(34,211,238,0.3);
            color: #67e8f9;
            font-size: 11px; font-weight: 700; letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 5px 14px; border-radius: 50px; margin-bottom: 1rem;
        }
        .cta-title { font-size: 2.25rem; font-weight: 800; color: white; margin: 0 0 1rem; line-height: 1.2; }
        .cta-desc { font-size: 1.05rem; color: rgba(255,255,255,0.65); line-height: 1.7; margin: 0; }
        .cta-cards { display: flex; flex-direction: column; gap: 1rem; }
        .cta-card {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 20px; padding: 1.25rem 1.5rem;
            text-decoration: none; color: white;
            transition: all 0.25s; cursor: pointer;
        }
        .cta-card:hover { background: rgba(255,255,255,0.14); border-color: rgba(255,255,255,0.25); transform: translateX(4px); }
        .cta-card-left { display: flex; align-items: center; gap: 14px; }
        .cta-card-icon {
            width: 46px; height: 46px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: white; flex-shrink: 0;
        }
        .cta-card-name { font-weight: 700; font-size: 15px; }
        .cta-card-sub { font-size: 12px; color: rgba(255,255,255,0.55); margin-top: 2px; }
        .cta-arrow { font-size: 20px; opacity: 0.6; }

        /* ── FOOTER ── */
        .main-footer {
            background: linear-gradient(135deg, #10499e 100%, #0e60cc 40%, #0891b2 30%);
            color: white;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 64px;
            z-index: 1000;
            border-top: 1px solid rgba(34, 211, 238, 0.3);
            backdrop-filter: blur(10px);
            box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.3);
        }
        .footer-simple-inner {
            max-width: 1280px;
            height: 64px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            column-gap: 24px;
        }
        .footer-logos-simple {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1.5rem;
            height: 100%;
        }
        .footer-logos-simple img {
            height: 28px;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 0 5px rgba(255,255,255,0.1));
            transition: all 0.3s ease;
        }
        .footer-logos-simple img:hover {
            transform: translateY(-3px) scale(1.08);
            filter: drop-shadow(0 5px 15px rgba(34, 211, 238, 0.4));
        }
        .footer-center-area {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            height: 100%;
            white-space: nowrap;
        }
        .copyright-simple {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 13px;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.82);
            text-align: center;
            margin: 0;
        }
        .copyright-simple strong {
            color: #22d3ee;
            text-shadow: 0 0 10px rgba(34, 211, 238, 0.3);
        }
        .footer-visitor-counter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: auto;
            max-width: max-content;
            height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(125, 211, 252, 0.35);
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            flex: 0 0 auto;
            box-shadow: 0 6px 18px rgba(14, 165, 233, 0.14);
            transform: translateX(45px);
        }
        .footer-visitor-counter i { font-size: 12px; color: #7dd3fc; }
        .footer-visitor-counter span { color: #ffffff; font-weight: 900; }
        .footer-visitor-counter small { color: rgba(255, 255, 255, 0.78); font-size: 12px; font-weight: 700; }

        .social-links-simple {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            height: 100%;
            margin: 0;
            padding: 0;
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
            margin: 0;
        }
        .social-links-simple a:hover {
            background: rgba(34, 211, 238, 0.15);
            color: #22d3ee;
            border-color: #22d3ee;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(34, 211, 238, 0.2);
        }

        body { padding-bottom: 64px; }

        @media (max-width: 768px) {
            .main-footer { position: fixed; height: auto; padding: 8px 0; bottom: 0; width: 100%; z-index: 1000; }
            .footer-simple-inner {
                max-width: 100%; height: auto; margin: 0 auto; padding: 0 1rem;
                display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;
            }
            .footer-logos-simple { order: 1; justify-content: flex-start; flex-wrap: nowrap; height: auto; gap: 8px; }
            .footer-logos-simple img { height: 18px; }
            .social-links-simple { order: 2; justify-content: flex-end; height: auto; gap: 6px; }
            .social-links-simple a { width: 26px; height: 26px; font-size: 12px; }
            .footer-center-area {
                order: 3; width: 100%; height: auto; display: flex; flex-direction: column-reverse; align-items: center; gap: 6px; margin-top: 6px;
            }
            .footer-visitor-counter { padding: 4px 12px; font-size: 11px; }
            .footer-visitor-counter small { display: inline; font-size: 10px; }
            .copyright-simple { text-align: center; font-size: 9px; line-height: 1.2; color: rgba(255,255,255,0.7); }
            body { padding-bottom: 110px; }
        }

        .newsletter-form { display: flex; gap: 8px; margin-top: 1.25rem; }
        .newsletter-form input {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2);
            padding: 12px 16px; border-radius: 12px; color: white; flex-grow: 1; font-size: 14px; outline: none;
        }
        .newsletter-form input:focus { border-color: #22d3ee; background: rgba(255,255,255,0.1); }
        .newsletter-btn {
            background: #22d3ee; color: #0c2d5e; padding: 0 20px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: all 0.3s;
        }
        .newsletter-btn:hover { background: #38bdf8; transform: translateY(-2px); }

        .social-links { display: flex; gap: 12px; margin-top: 1.5rem; }
        .social-links a {
            width: 40px; height: 40px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center; border-radius: 12px; color: white; transition: 0.3s;
        }
        .social-links a:hover { background: #22d3ee; color: #0c2d5e; transform: translateY(-5px); box-shadow: 0 10px 20px rgba(34,211,238,0.2); }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 3rem; display: flex; flex-direction: column; align-items: center; gap: 2rem; }
        .partner-logos { display: flex; flex-wrap: wrap; justify-content: center; gap: 3rem; align-items: center; }
        .partner-logos img { height: 30px; opacity: 1; transition: transform 0.3s; object-fit: contain; }
        .partner-logos img:hover { transform: scale(1.1); }
        .copyright { font-size: 13px; color: rgba(255,255,255,0.4); text-align: center; }
        .copyright span { color: #22d3ee; font-weight: 600; }

        /* ── AKTIVITAS TERBARU DINAMIS ── */
        .section-news {
            padding: 6rem 0;
            background: radial-gradient(circle at top left, rgba(59,130,246,.12), transparent 32%), linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
        }
        .activity-tabs { display: inline-flex; align-items: center; gap: 10px; padding: 8px; border-radius: 999px; background: white; border: 1px solid #dbeafe; box-shadow: 0 12px 30px rgba(37,99,235,.08); }
        .activity-tab { display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; padding: 12px 22px; border-radius: 999px; background: transparent; color: #64748b; font-size: 14px; font-weight: 800; transition: all .25s ease; }
        .activity-tab.active { background: linear-gradient(135deg, #2563eb, #0ea5e9); color: white; box-shadow: 0 10px 24px rgba(37,99,235,.25); }
        .activity-panel { display: none; }
        .activity-panel.active { display: block; animation: fadeActivity .3s ease both; }
        @keyframes fadeActivity { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .activity-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .activity-card { background: white; border-radius: 28px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 12px 34px rgba(15,23,42,.07); text-decoration: none; color: inherit; transition: all .3s ease; }
        .activity-card:hover { transform: translateY(-6px); box-shadow: 0 22px 50px rgba(37,99,235,.14); }
        .activity-img-wrap { position: relative; height: 220px; overflow: hidden; background: #dbeafe; }
        .activity-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .activity-card:hover .activity-img-wrap img { transform: scale(1.08); }
        .activity-badge { position: absolute; left: 16px; top: 16px; display: inline-flex; align-items: center; gap: 7px; padding: 8px 12px; border-radius: 999px; font-size: 11px; font-weight: 900; color: white; box-shadow: 0 10px 20px rgba(0,0,0,.18); }
        .badge-article { background: #2563eb; }
        .badge-youtube { background: #ef4444; }
        .play-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: white; font-size: 26px; background: rgba(15,23,42,.22); }
        .play-overlay i { width: 64px; height: 64px; border-radius: 50%; background: rgba(239,68,68,.95); display: flex; align-items: center; justify-content: center; padding-left: 4px; box-shadow: 0 18px 34px rgba(239,68,68,.28); }
        .activity-content { padding: 22px; }
        .activity-date { display: inline-flex; align-items: center; gap: 7px; color: #64748b; font-size: 12px; font-weight: 700; margin-bottom: 12px; }
        .activity-content h3 { font-size: 18px; line-height: 1.35; color: #0f172a; font-weight: 900; margin: 0 0 10px; }
        .activity-excerpt { color: #64748b; font-size: 14px; line-height: 1.7; margin: 0; }
        .activity-empty { background: white; border: 1px dashed #bfdbfe; border-radius: 28px; padding: 60px 20px; text-align: center; color: #94a3b8; font-weight: 700; }
        .activity-empty i { font-size: 42px; margin-bottom: 14px; display: block; color: #cbd5e1; }

        @media (max-width: 1024px) { .activity-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) {
            .activity-tabs { width: 100%; justify-content: center; }
            .activity-tab { flex: 1; justify-content: center; padding: 11px 14px; font-size: 13px; }
            .activity-grid { display: flex; overflow-x: auto; gap: 16px; scroll-snap-type: x mandatory; padding-bottom: 12px; }
            .activity-card { min-width: 82%; scroll-snap-align: start; }
            .activity-img-wrap { height: 190px; }
        }

        /* ── SCROLL ANIMATION ── */
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ── RESPONSIVE GENERAL ── */
        @media (max-width: 1024px) {
            .visitor-counter-mobile { display: inline-flex; }
            .visitor-counter-desktop { display: none; }
            .navbar-actions { margin-left: auto; gap: 10px; }
            .nav-toggle { display: inline-flex; }
            .btn-login { display: none; }
            .hero { min-height: auto; }
            .hero-inner { grid-template-columns: 1fr; padding: 3.5rem 1.5rem; gap: 2.5rem; text-align: center; }
            .hero-desc { margin-left: auto; margin-right: auto; }
            .hero-stats { justify-content: center; }
            .hero-btns { justify-content: center; }
            .hero-image-wrap { justify-content: center; max-width: 520px; margin: 0 auto; order: -1; }
            .process-grid { grid-template-columns: 1fr; }
            .step-block { grid-template-columns: 1fr; gap: 2rem; padding: 2rem 0; }
            .step-block .order-swap { order: 0; }
            .step-block .step-image-wrap { order: -1; }
            .testi-grid { grid-template-columns: 1fr; }
            .partners-grid { grid-template-columns: repeat(2, 1fr); }
            .cta-inner { grid-template-columns: 1fr; }
            .nav-links { display: none; }
        }
        @media (max-width: 640px) {
            .hero-inner { padding: 2.5rem 1rem; gap: 1.5rem; }
            .hero-title { font-size: 1.8rem; }
            .hero-image-wrap { max-width: 100%; }
            .hero-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; justify-items: center; border-bottom: none; padding-bottom: 0; }
            .hero-stat-divider { display: none; }
            .partners-grid { grid-template-columns: 1fr 1fr; }
            .process-grid { display: none; }
            .process-carousel { display: block; }
            .testi-grid { display: none; }
            .testi-carousel { display: block; }
            .navbar-inner { padding: 0 1rem; gap: 8px; }
            .nav-logo { gap: 8px; min-width: 0; }
            .visitor-counter-mobile { padding: 8px 12px; font-size: 12px; }
            .nav-toggle { width: 42px; height: 42px; flex-shrink: 0; }
        }

        /* ── LOWONGAN SECTION ── */
        .section-jobs { background: #f0f7ff; padding: 5rem 0; scroll-margin-top: 90px; }
        .lowongan-scroll-wrapper { display: flex; gap: 1.5rem; overflow-x: auto; padding: 0.25rem 0.25rem 1.25rem; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: thin; scrollbar-color: #c7d2fe #eef2ff; }
        .lowongan-scroll-wrapper::-webkit-scrollbar { height: 6px; }
        .lowongan-scroll-wrapper::-webkit-scrollbar-track { background: #eef2ff; border-radius: 99px; }
        .lowongan-scroll-wrapper::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 99px; }
        .lowongan-card { position: relative; flex: 0 0 380px; min-width: 380px; scroll-snap-align: start; background: #fff; border-radius: 24px; padding: 1.4rem; border: 1px solid #e8edf5; box-shadow: 0 4px 16px rgba(15,23,42,.06); transition: .22s ease; overflow: hidden; display: flex; flex-direction: column; min-height: 220px; }
        .lowongan-card:hover { transform: translateY(-4px); box-shadow: 0 16px 36px rgba(15,23,42,.10); border-color: #c7d7f5; }
        .lowongan-card::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(99,102,241,.03), transparent); pointer-events: none; }
        .card-title { margin-top: .75rem; font-size: 1.12rem; font-weight: 800; color: #0f172a; line-height: 1.35; flex: 1; }
        .card-meta { display: flex; flex-wrap: wrap; gap: .4rem .75rem; margin-top: .55rem; }
        .meta-chip { display: inline-flex; align-items: center; gap: .25rem; font-size: .72rem; font-weight: 600; color: #64748b; }
        .meta-chip i { font-size: .62rem; color: #94a3b8; }
        .card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 1rem; padding-top: .9rem; border-top: 1px solid #f1f5f9; gap: .75rem; }
        .card-company { display: flex; align-items: center; gap: .6rem; min-width: 0; }
        .company-logo { width: 36px; height: 36px; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; background: #f8faff; flex-shrink: 0; }
        .company-logo img { width: 100%; height: 100%; object-fit: cover; }
        .company-name { font-size: .75rem; font-weight: 700; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 145px; }
        .company-sub { font-size: .65rem; color: #94a3b8; font-weight: 500; }
        .card-actions { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; }
        .btn-card { height: 34px; padding: 0 1rem; border-radius: 10px; font-size: .75rem; font-weight: 700; display: inline-flex; align-items: center; gap: .35rem; text-decoration: none; white-space: nowrap; transition: .2s; }
        .btn-detail { background: #0f172a; color: #fff; }
        .btn-detail:hover { background: #4f46e5; color: #fff; text-decoration: none; }
        .logo-wrapper { width: 78px; height: 78px; border-radius: 24px; overflow: hidden; border: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; }
        .logo-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .status-badge { display: inline-flex; align-items: center; gap: .45rem; padding: .5rem 1rem; border-radius: 999px; font-size: .75rem; font-weight: 800; letter-spacing: .3px; white-space: nowrap; flex-shrink: 0; }
        .detail-box { border-radius: 20px; background: #f8faff; border: 1px solid #eef2ff; padding: 1rem; }
        .detail-label { font-size: .72rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; }
        .detail-value { margin-top: .35rem; font-size: .95rem; font-weight: 700; color: #334155; }
        .btn-action { height: 46px; border-radius: 16px; padding: 0 1rem; font-size: .88rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; transition: .2s; text-decoration: none; }
        .btn-soft { background: #eef2ff; color: #4f46e5; }
        .btn-soft:hover { background: #4f46e5; color: #fff; }
        .jobs-footer { text-align: center; margin-top: 2.5rem; }

        @media (max-width: 640px) {
            .lowongan-card { flex: 0 0 310px; min-width: 310px; }
            .lowongan-scroll-wrapper { gap: 1rem; padding: 0.15rem 0.15rem 1rem; }
        }

        #lowonganGrid::-webkit-scrollbar { height: 4px; }
        #lowonganGrid::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        #lowonganGrid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        #lowonganGrid::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .filter-pill { background: white; color: #64748b; border-color: #e2e8f0; }
        .filter-pill:hover { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
        .filter-pill.active { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        .lowongan-card { animation: fadeUp .35s ease both; }
        .lowongan-card.hidden-card { display: none !important; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        /* ── TIM PENGEMBANG LAYOUT & STYLES ── */
        .team-card { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.07); border: 1.5px solid #e8edf5; transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
        .team-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(14,99,201,0.13); }
        .team-card-img { width: 100%; height: 220px; object-fit: cover; object-position: top center; display: block; }
        .team-card-body { padding: 1.1rem 1.2rem 1.2rem; text-align: left; }
        .team-card-name { font-weight: 700; font-size: 1.05rem; color: #0f2d4a; margin-bottom: 2px; }
        .team-card-role { font-size: 0.82rem; color: #6b7d93; margin-bottom: 0.75rem; }
        .team-card-socials { display: flex; gap: 8px; }
        .team-card-social-btn { width: 32px; height: 32px; border-radius: 50%; background: #e8f4fd; display: flex; align-items: center; justify-content: center; color: #1d6fca; font-size: 13px; transition: background 0.25s, color 0.25s; text-decoration: none; }
        .team-card-social-btn:hover { background: #1d6fca; color: #fff; }
        
        /* Redesign Latar Belakang Seksi dengan Efek Kaca Glowing & Pola Cyber Grid Premium */
        .team-tabs-section { 
            background: linear-gradient(160deg, #040c1f 0%, #071536 35%, #0d255c 70%, #040c1f 100%); 
            padding: 5rem 0; 
            position: relative; 
            overflow: hidden; 
        }
        .team-tabs-section::after { 
            content: ''; 
            position: absolute; 
            inset: 0; 
            background-image: 
                linear-gradient(rgba(34, 211, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, 0.05) 1px, transparent 1px);
            background-size: 40px 38px; 
            pointer-events: none; 
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%); 
            -webkit-mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%); 
            z-index: 1;
        }
        /* Tambahan Efek Bulatan Blur Biru Kaca Mengambang di Background */
        .team-tabs-section::before {
            content: '';
            position: absolute;
            top: 20%; left: 15%;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(29, 111, 202, 0.25) 0%, transparent 70%);
            filter: blur(40px);
            pointer-events: none;
            z-index: 1;
        }

        .team-tabs-title { font-size: clamp(2rem, 4vw, 2.8rem); font-weight: 800; color: #fff; margin: 0 0 2.5rem; letter-spacing: -0.02em; line-height: 1.15; text-align: center; position: relative; z-index: 2; }
        .team-tabs-nav { display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 2.5rem; flex-wrap: wrap; position: relative; z-index: 5; }
        .team-tab-btn { padding: 0.6rem 1.75rem; border-radius: 999px; border: 1.5px solid rgba(255,255,255,0.18); background: transparent; color: rgba(255,255,255,0.65); font-family: 'Poppins', sans-serif; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; letter-spacing: 0.01em; }
        .team-tab-btn:hover { border-color: rgba(34,211,238,0.5); color: #fff; background: rgba(34,211,238,0.08); }
        .team-tab-btn.active { background: linear-gradient(135deg, #1d6fca, #22d3ee); color: #fff; border-color: transparent; font-weight: 700; box-shadow: 0 4px 20px rgba(34,211,238,0.35); }
        
        .team-tab-panel { display: none; }
        .team-tab-panel.active { display: block; animation: fadeInPanel 0.4s ease; }
        @keyframes fadeInPanel { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        
        .team-members-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.1rem; position: relative; z-index: 2; }
        #tab-angkatan3 .team-members-grid { grid-template-columns: repeat(3, 1fr); }
        #tab-angkatan1 .team-members-grid, #tab-angkatan2 .team-members-grid { grid-template-columns: repeat(6, 1fr); gap: 0.9rem; }
        
        @media (max-width: 1280px) {
            #tab-angkatan1 .team-members-grid, #tab-angkatan2 .team-members-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 1024px) {
            .team-members-grid { grid-template-columns: repeat(3, 1fr) !important; }
        }
        @media (max-width: 768px) {
            .team-members-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }

        @media (max-width: 480px) {
            .team-carousel-wrapper { position: relative; overflow: hidden; border-radius: 18px; z-index: 2; }
            .team-members-grid { display: flex !important; flex-wrap: nowrap !important; gap: 0 !important; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform; grid-template-columns: unset !important; }
            .team-members-grid .tmember-card { flex: 0 0 100%; width: 100%; min-width: 100%; animation: none !important; display: flex; justify-content: center; aspect-ratio: unset; background: transparent; box-shadow: none; border-radius: 0; overflow: visible; }
            .team-members-grid .tmember-card .tmember-photo-wrap { position: relative; inset: unset; width: 65%; max-width: 240px; aspect-ratio: 3 / 4; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 28px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.06); }
            .team-carousel-dots { display: flex !important; justify-content: center; gap: 7px; margin-top: 1rem; }
            .team-carousel-dots .cdot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.25); border: none; padding: 0; cursor: pointer; transition: all 0.3s ease; }
            .team-carousel-dots .cdot.active { background: #22d3ee; box-shadow: 0 0 8px rgba(34,211,238,0.6); transform: scale(1.25); width: 22px; border-radius: 4px; }
            .team-carousel-progress { display: block !important; height: 2px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 0.75rem; overflow: hidden; }
            .team-carousel-progress-bar { height: 100%; background: linear-gradient(90deg, #1d6fca, #22d3ee); border-radius: 2px; transition: width 0.1s linear; }
        }

        .team-carousel-dots { display: none; }
        .team-carousel-progress { display: none; }

        .tmember-card { position: relative; border-radius: 18px; overflow: hidden; cursor: default; aspect-ratio: 3 / 4; box-shadow: 0 6px 28px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.06); transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease; animation: cardEntrance 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .tmember-card:nth-child(1) { animation-delay: 0.04s; }
        .tmember-card:nth-child(2) { animation-delay: 0.09s; }
        .tmember-card:nth-child(3) { animation-delay: 0.14s; }
        .tmember-card:nth-child(4) { animation-delay: 0.19s; }
        .tmember-card:nth-child(5) { animation-delay: 0.24s; }
        .tmember-card:nth-child(6) { animation-delay: 0.29s; }
        @keyframes cardEntrance { from { opacity: 0; transform: translateY(28px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .tmember-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 50px rgba(0,0,0,0.55), 0 0 0 1.5px rgba(34,211,238,0.35), 0 0 30px rgba(34,211,238,0.06); }
        
        .tmember-photo-wrap { position: absolute; inset: 0; width: 100%; height: 100%; overflow: hidden; }
        .tmember-photo { width: 100%; height: 100%; object-fit: cover; object-position: top center; display: block; background: transparent; transition: transform 0.4s ease-out, filter 0.3s ease-in-out; }
        
        .tmember-photo-wrap::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 40%, rgba(6,13,31,0.4) 70%, rgba(6,13,31,0.85) 100%); transition: background 0.3s ease; z-index: 2; }
        .tmember-photo-wrap::before { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #1d6fca, #22d3ee); z-index: 4; transform: scaleX(0); transform-origin: left; transition: transform 0.38s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .tmember-card:hover .tmember-photo-wrap::before { transform: scaleX(1); }
        .tmember-info { position: absolute; bottom: 0; left: 0; right: 0; padding: 0.85rem 0.9rem 1rem; z-index: 5; }
        .tmember-name { font-size: 0.88rem; font-weight: 700; color: #fff; line-height: 1.3; letter-spacing: 0.01em; text-shadow: 0 1px 6px rgba(0,0,0,0.7); }
        .tmember-meta { font-size: 0.66rem; color: rgba(255,255,255,0.72); margin-top: 4px; font-weight: 400; line-height: 1.4; display: flex; align-items: flex-start; gap: 5px; }
        .tmember-meta::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #22d3ee; flex-shrink: 0; margin-top: 3px; box-shadow: 0 0 5px #22d3ee; }
        .team-wavy-deco { position: absolute; opacity: 0.35; pointer-events: none; }

        /* ── MODAL POPUP INSTAGRAM ELEGAN ── */
        .insta-modal {
            position: fixed;
            inset: 0;
            background: rgba(7, 24, 48, 0.85);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .insta-modal.open { opacity: 1; visibility: visible; }
        .insta-modal-content {
            background: #0c1d45;
            border: 1.5px solid rgba(34, 211, 238, 0.3);
            border-radius: 24px;
            padding: 2.2rem;
            width: min(90%, 390px);
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(34, 211, 238, 0.25);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        .insta-modal.open .insta-modal-content { transform: scale(1); }
        .insta-btn-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: white !important;
            padding: 12px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            margin-top: 1.5rem;
            box-shadow: 0 4px 15px rgba(220, 39, 67, 0.4);
            transition: transform 0.2s;
        }
        .insta-btn-link:hover { transform: translateY(-2px); }

        /* ── SPESIFIKASI PREMIUM CARD TIM V3.5 ── */
        #tab-angkatan3 .tmember-card {
            background: linear-gradient(135deg, #051433 0%, #0d255c 50%, #030b21 100%);
            border: 1.5px solid rgba(34, 211, 238, 0.25);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5);
            overflow: visible !important; /* Agar bagian kepala foto transparan bisa keluar menonjol */
        }
        #tab-angkatan3 .tmember-photo-wrap {
            overflow: visible !important;
        }
        #tab-angkatan3 .tmember-photo {
            transform: translateY(0) scale(1);
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), filter 0.35s ease-in-out;
        }

        /* ── EFEK HOVER PREMIUM V3.5 (POP-OUT + INSTANT BLUR & INSTAGRAM ICON) ── */
        #tab-angkatan3 .tmember-card:hover {
            border-color: rgba(34, 211, 238, 0.6);
            box-shadow: 0 25px 55px rgba(34, 211, 238, 0.25);
        }
        #tab-angkatan3 .tmember-card:hover .tmember-photo {
            transform: translateY(-22px) scale(1.06); /* Foto bergeser naik keluar dari batas card */
            filter: blur(4px) drop-shadow(0 15px 20px rgba(0, 0, 0, 0.55)); /* Foto langsung blur */
        }

        /* Ikon Instagram Mengambang Muncul Seketika Tanpa Jeda */
        #tab-angkatan3 .tmember-insta-hover-icon {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.6);
            z-index: 10;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease-out, transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        #tab-angkatan3 .tmember-card:hover .tmember-insta-hover-icon {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        #tab-angkatan3 .tmember-insta-btn {
            width: 52px;
            height: 52px;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            box-shadow: 0 10px 22px rgba(220, 39, 67, 0.5);
        }

        /* ── TIM SIMAGANG V4 (kartu gelap + logo kampus beranimasi) ── */
        .v4-grid {
            position: relative; z-index: 2; display: grid; grid-template-columns: repeat(2, minmax(0, 285px));
            justify-content: center; gap: 22px; max-width: 600px; margin: 0 auto;
        }
        .v4-card {
            --accent: 250, 204, 21;
            position: relative; padding: 12px; border-radius: 20px;
            background: linear-gradient(165deg, #131f35 0%, #0d1628 60%, #0a1222 100%);
            border: 1px solid rgba(255, 255, 255, .09); box-shadow: 0 14px 40px rgba(0, 0, 0, .45);
            transition: transform .45s cubic-bezier(.22, 1, .36, 1), border-color .35s ease, box-shadow .35s ease;
            animation: v4CardIn .75s cubic-bezier(.16, 1, .3, 1) backwards;
        }
        .v4-card:nth-child(2) { animation-delay: .16s; }
        .v4-card:nth-child(3) { animation-delay: .26s; }
        .v4-card:nth-child(4) { animation-delay: .36s; }
        .v4-card--gold { --accent: 250, 204, 21; }
        .v4-card--red { --accent: 239, 68, 68; }
        .v4-card:hover, .v4-card:focus-within {
            transform: translateY(-6px); border-color: rgba(var(--accent), .65);
            box-shadow: 0 24px 55px rgba(0, 0, 0, .55), 0 0 0 1px rgba(var(--accent), .35), 0 0 46px rgba(var(--accent), .24);
        }
        @keyframes v4CardIn { from { opacity: 0; transform: translateY(34px) scale(.95); } to { opacity: 1; transform: none; } }

        .v4-photo {
            position: relative; overflow: hidden; aspect-ratio: 1 / 1.05; border-radius: 14px;
            background: radial-gradient(120% 90% at 50% 0%, #26364f 0%, #18233a 55%, #121b2d 100%);
            border: 1px solid rgba(255, 255, 255, .06);
        }
        .v4-photo::after {
            content: ''; position: absolute; inset: 0; pointer-events: none; z-index: 2;
            background: linear-gradient(115deg, transparent 35%, rgba(255, 255, 255, .11) 50%, transparent 65%);
            transform: translateX(-125%);
        }
        .v4-card:hover .v4-photo::after { transform: translateX(125%); transition: transform 1.1s ease; }
        .v4-photo__img {
            position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: 50% 22%;
            transition: transform .7s cubic-bezier(.22, 1, .36, 1), filter .4s ease;
        }
        .v4-card:hover .v4-photo__img { transform: scale(1.04); filter: brightness(1.08); }

        /* Logo kampus: bergerak pelan + cincin "splash" berwarna emas (Undipa) / merah (Unhas) saat kartu di-hover */
        .v4-logo {
            position: absolute; top: 10px; left: 10px; z-index: 3; width: 44px; height: 44px; display: grid; place-items: center;
            transition: left .95s cubic-bezier(.65, 0, .35, 1);
        }
        .v4-card:hover .v4-logo, .v4-card:focus-within .v4-logo {
            left: calc(100% - 54px);
            animation: v4Glide .95s cubic-bezier(.45, 0, .55, 1);
        }
        @keyframes v4Glide {
            0%, 100% { transform: translateY(0) scale(1); }
            45% { transform: translateY(-9px) scale(1.12); }
        }
        .v4-logo__img { position: relative; z-index: 2; width: 100%; height: 100%; object-fit: contain; border-radius: 50%; transition: filter .45s ease; }
        .v4-logo--round .v4-logo__img { object-fit: cover; background: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, .45); }
        .v4-logo--plain .v4-logo__img { border-radius: 0; filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .55)); }
        .v4-logo::before, .v4-logo::after {
            content: ''; position: absolute; inset: 0; border-radius: 50%; pointer-events: none; z-index: 1;
            border: 2px solid rgba(var(--accent), .95); background: radial-gradient(circle, rgba(var(--accent), .35) 0%, rgba(var(--accent), 0) 70%);
            opacity: 0;
        }
        .v4-card:hover .v4-logo::before, .v4-card:focus-within .v4-logo::before { animation: v4Splash 2.2s ease-out infinite; }
        .v4-card:hover .v4-logo::after, .v4-card:focus-within .v4-logo::after { animation: v4Splash 2.2s ease-out 1.1s infinite; }
        .v4-card:hover .v4-logo__img, .v4-card:focus-within .v4-logo__img {
            animation: v4Float 3.4s ease-in-out infinite;
            filter: drop-shadow(0 0 9px rgba(var(--accent), .9)) drop-shadow(0 0 22px rgba(var(--accent), .55));
        }
        @keyframes v4Float {
            0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
            50% { transform: translateY(-5px) rotate(5deg) scale(1.1); }
        }
        @keyframes v4Splash { 0% { transform: scale(.85); opacity: .75; } 100% { transform: scale(2.2); opacity: 0; } }

        .v4-ig {
            position: absolute; right: 10px; bottom: 10px; z-index: 3; display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 10px; border-radius: 10px; background: rgba(9, 15, 28, .88); border: 1px solid rgba(255, 255, 255, .14);
            color: #fff; font-size: 11.5px; font-weight: 700; cursor: pointer; backdrop-filter: blur(6px);
            transition: transform .3s ease, border-color .3s ease;
        }
        .v4-ig .fa-instagram { background: linear-gradient(45deg, #f09433, #dc2743, #bc1888); -webkit-background-clip: text; background-clip: text; color: transparent; font-size: 13px; }
        .v4-ig .v4-ig__out { font-size: 9px; opacity: .7; }
        .v4-card:hover .v4-ig, .v4-ig:hover { border-color: rgba(var(--accent), .7); transform: translateY(-2px); }
        .v4-ig:focus-visible { outline: 2px solid #67e8f9; outline-offset: 2px; }

        .v4-info { padding: 12px 4px 2px; text-align: left; }
        .v4-name { margin: 8px 0 4px; font-size: 1.08rem; font-weight: 700; line-height: 1.25; color: #fff; }
        .v4-name::after { content: ''; display: block; width: 0; height: 2px; margin-top: 5px; border-radius: 2px; background: rgba(var(--accent), .95); transition: width .45s cubic-bezier(.22, 1, .36, 1); }
        .v4-card:hover .v4-name::after { width: 56px; }
        .v4-campus { margin: 0; font-size: .78rem; font-weight: 700; }
        .v4-campus--gold { color: #facc15; }
        .v4-campus--red { color: #f25555; }
        .v4-program { margin: 2px 0 0; font-size: .74rem; color: #94a3b8; }

        @media (max-width: 720px) {
            .v4-grid { grid-template-columns: minmax(0, 300px); gap: 18px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .v4-card, .v4-photo__img, .v4-photo::after, .v4-logo { animation: none !important; transition: none !important; }
            .v4-card:hover .v4-logo::before, .v4-card:hover .v4-logo::after, .v4-card:hover .v4-logo__img,
            .v4-card:focus-within .v4-logo::before, .v4-card:focus-within .v4-logo::after, .v4-card:focus-within .v4-logo__img { animation: none !important; }
            .v4-card:hover .v4-logo::before { opacity: .55; }
        }
    </style>
</head>
<body>
    <x-splash-screen />

<!-- ===== NAVBAR ===== -->
<header class="navbar">
    <div class="navbar-inner">
        <a href="{{ route('landing') }}#hero" class="nav-logo">
            <div class="logo-wrap">
                <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="Komdigi">
            </div>
            <div>
                <div class="font-etna" style="font-size:20px; font-weight:900; line-height:1.1">
                    <span style="color:#9d272a">SI</span><span style="color:#086bb0">MA</span><span style="color:#2dabe2">GA</span><span style="color:#efc400">NG</span>
                </div>
                <div class="font-etna" style="font-size:10px; color:#8ea5bc; margin-top:2px">
                    Sistem Manajemen Magang
                </div>
            </div>
        </a>

        <nav class="nav-links">
            <a href="{{ route('landing') }}#hero">Beranda</a>
            <a href="{{ route('landing') }}#daftar-lowongan">Lowongan</a>

            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-btn">
                    Panduan <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
                <div class="nav-dropdown-menu">
                    <a href="{{ route('landing') }}#process"><i class="fas fa-route"></i> Alur</a>
                    <a href="{{ route('landing') }}#usage"><i class="fas fa-layer-group"></i> Fitur</a>
                </div>
            </div>

            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-btn">
                    Aktivitas <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
                <div class="nav-dropdown-menu">
                    <a href="{{ route('landing') }}#news"><i class="fas fa-newspaper"></i> Aktivitas Terbaru</a>
                    <a href="{{ route('landing') }}#sharing-session"><i class="fas fa-comments"></i> Sharing Session</a>
                </div>
            </div>

            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-btn">
                    Tentang Kami <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
                <div class="nav-dropdown-menu">
                    <a href="{{ route('landing') }}#about"><i class="fas fa-info-circle"></i> Profil/Tentang Kami</a>
                    <a href="{{ route('landing') }}#partners"><i class="fas fa-handshake"></i> Mitra</a>
                    <a href="{{ route('landing') }}#testimonials"><i class="fas fa-comment-dots"></i> Testimoni</a>
                </div>
            </div>
        </nav>

        <div class="navbar-actions">
            <x-theme-menu />
            <button type="button" class="nav-toggle" aria-label="Buka menu" aria-expanded="false">
                <i class="fas fa-bars" style="font-size:18px"></i>
            </button>

            <div class="mobile-menu" aria-hidden="true">
                <a href="{{ route('landing') }}#hero"><i class="fas fa-home" style="margin-right:8px"></i> Beranda</a>
                <a href="{{ route('landing') }}#daftar-lowongan"><i class="fas fa-briefcase" style="margin-right:8px"></i> Lowongan</a>

                <div class="mobile-dropdown" id="mobilePanduanDropdown">
                    <button type="button" class="mobile-dropdown-btn" onclick="toggleMobileDropdown('mobilePanduanDropdown')">
                        <span class="mobile-dropdown-btn-left"><i class="fas fa-book-open" style="margin-right:8px"></i> Panduan</span>
                        <i class="fas fa-chevron-down mobile-dropdown-arrow"></i>
                    </button>
                    <div class="mobile-dropdown-content">
                        <a href="{{ route('landing') }}#process"><i class="fas fa-route" style="margin-right:8px"></i> Alur</a>
                        <a href="{{ route('landing') }}#usage"><i class="fas fa-layer-group" style="margin-right:8px"></i> Fitur</a>
                    </div>
                </div>

                <div class="mobile-dropdown" id="mobileAktivitasDropdown">
                    <button type="button" class="mobile-dropdown-btn" onclick="toggleMobileDropdown('mobileAktivitasDropdown')">
                        <span class="mobile-dropdown-btn-left"><i class="fas fa-calendar-check" style="margin-right:8px"></i> Aktivitas</span>
                        <i class="fas fa-chevron-down mobile-dropdown-arrow"></i>
                    </button>
                    <div class="mobile-dropdown-content">
                        <a href="{{ route('landing') }}#news"><i class="fas fa-newspaper" style="margin-right:8px"></i> Aktivitas Terbaru</a>
                        <a href="{{ route('landing') }}#sharing-session"><i class="fas fa-comments" style="margin-right:8px"></i> Sharing Session</a>
                    </div>
                </div>

                <div class="mobile-dropdown" id="mobileTentangDropdown">
                    <button type="button" class="mobile-dropdown-btn" onclick="toggleMobileDropdown('mobileTentangDropdown')">
                        <span class="mobile-dropdown-btn-left"><i class="fas fa-users" style="margin-right:8px"></i> Tentang Kami</span>
                        <i class="fas fa-chevron-down mobile-dropdown-arrow"></i>
                    </button>
                    <div class="mobile-dropdown-content">
                        <a href="{{ route('landing') }}#about"><i class="fas fa-info-circle" style="margin-right:8px"></i> Profil/Tentang Kami</a>
                        <a href="{{ route('landing') }}#partners"><i class="fas fa-handshake" style="margin-right:8px"></i> Mitra</a>
                        <a href="{{ route('landing') }}#testimonials"><i class="fas fa-comment-dots"></i> Testimoni</a>
                    </div>
                </div>

                <a href="{{ route('login') }}" class="login-link"><i class="fas fa-sign-in-alt" style="font-size:13px; margin-right:8px"></i> Login</a>
            </div>

            <a href="{{ route('login') }}" class="btn-login"><i class="fas fa-sign-in-alt" style="font-size:13px"></i> Login</a>
        </div>
    </div>
</header>

<main>

<!-- ===== HERO ===== -->
<section id="hero" class="hero">
    <img src="{{ asset('storage/photos-landingpage/hero-bg.png') }}" alt="" class="hero-bg-image" aria-hidden="true">
    <div class="hero-overlay"></div>
    <div class="hero-accent"></div>
    <div class="hero-inner">
        <div class="hero-text-col">
            <h1 class="hero-title">Sistem Magang Terbaik untuk <span>Kampus dan Sekolah</span></h1>
            <p class="hero-desc">Nikmati pengalaman terbaik dengan mitra terpercaya. Ada Banyak Hal Besar yang Perlu dilakukan di Masa Depan. Saatnya tumbuh sebagai talenta profesional dan buktikan keahlianmu bersama BBLSDM Komdigi Makassar sekarang.</p>
            <div class="hero-stats">
                <div>
                    <div class="hero-stat-num text-center">{{ $totalPesertaAktif }}</div>
                    <div class="hero-stat-label">Peserta Magang</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div>
                    <div class="hero-stat-num text-center">11+</div>
                    <div class="hero-stat-label">Mitra pendidikan dan industri</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div>
                    <div class="hero-stat-num text-center">93.25</div>
                    <div class="hero-stat-label">Tingkat Kepuasan</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div>
                    <div class="hero-stat-num text-center">{{ number_format($visitorCount ?? 0, 0, ',', '.') }}</div>
                    <div class="hero-stat-label">Jumlah Pengunjung</div>
                </div>
            </div>
            <div class="hero-btns">
                <a href="#daftar" class="btn-primary"><i class="fas fa-rocket" style="font-size:14px"></i> Daftar Sekarang</a>
                <a href="#usage" class="btn-outline">Lihat Fitur <i class="fas fa-arrow-right" style="font-size:13px"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- ===== Daftar Lowongan Terbaru ===== -->
<section id="daftar-lowongan" class="section-jobs py-20 bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/40">
    <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <span class="inline-flex items-center gap-2 text-xs font-bold tracking-[.16em] uppercase text-blue-700 bg-blue-50 border border-blue-200 px-4 py-1.5 rounded-full mb-4">
                <i class="fas fa-briefcase text-[11px]"></i> Lowongan Terbaru
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-800 mb-4 leading-tight">Temukan Peluang Magang Terbaik</h2>
            <p class="text-slate-500 text-base max-w-2xl mx-auto leading-relaxed">Jelajahi daftar lowongan magang terbaru dari berbagai mitra pendidikan and industri kami. Temukan peluang yang sesuai dengan minat dan keahlianmu.</p>
        </div>

        @if($lowongans->isEmpty())
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white/80 p-10 text-center shadow-sm reveal">
                <i class="fas fa-briefcase text-4xl text-blue-500 mb-4"></i>
                <h3 class="text-xl font-extrabold text-slate-800 mb-2">Belum ada lowongan magang tersedia</h3>
                <p class="text-slate-500 max-w-xl mx-auto leading-relaxed">Saat ini belum ada lowongan magang yang dipublikasikan. Lowongan akan muncul di sini setelah mitra atau industri mengunggahnya.</p>
            </div>
        @else
            <div class="flex flex-wrap items-center gap-3 mb-5 reveal">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                    <input type="text" id="lowonganSearch" placeholder="Cari posisi, divisi..." class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition" oninput="filterLowongan()">
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button onclick="setStatusFilter(this,'semua')" data-status="semua" class="filter-pill active px-4 py-2 rounded-full text-xs font-bold border transition">Semua</button>
                    <button onclick="setStatusFilter(this,'dibuka')" data-status="dibuka" class="filter-pill px-4 py-2 rounded-full text-xs font-bold border transition"><i class="fas fa-circle-check mr-1"></i>Dibuka</button>
                    <button onclick="setStatusFilter(this,'ditutup')" data-status="ditutup" class="filter-pill px-4 py-2 rounded-full text-xs font-bold border transition"><i class="fas fa-circle-xmark mr-1"></i>Ditutup</button>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500">
                        <span id="statTotal" class="font-semibold text-slate-700"></span> lowongan • <span id="statDibuka" class="text-green-600 font-semibold"></span> dibuka
                    </div>
                    <button id="btnPrev" onclick="scrollCards(-1)" class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-500 shadow-sm transition disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </button>
                    <button id="btnNext" onclick="scrollCards(1)" class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-500 shadow-sm transition disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            <div id="lowonganGrid" class="flex gap-5 overflow-x-auto scroll-smooth pb-3" style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;">
                @foreach($lowongans as $lowongan)
                @php
                    $logo = optional($lowongan->industri)->logo_industri ? asset('storage/' . $lowongan->industri->logo_industri) : 'https://ui-avatars.com/api/?name=' . urlencode(optional($lowongan->industri)->nama_industri ?? 'Industri');
                    $status = $lowongan->status ?? 'dibuka';
                @endphp
                <div class="lowongan-card group flex-shrink-0 w-72" style="scroll-snap-align: start;" data-status="{{ $status }}" data-search="{{ strtolower($lowongan->judul_lowongan . ' ' . $lowongan->posisi_magang . ' ' . $lowongan->divisi) }}">
                    <div class="flex justify-end">
                        <span class="status-badge {{ $status === 'dibuka' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }} border">
                            <i class="fas {{ $status === 'dibuka' ? 'fa-check-circle' : 'fa-times-circle' }} text-[10px]"></i> {{ ucfirst($status) }}
                        </span>
                    </div>
                    <h3 class="card-title">{{ $lowongan->judul_lowongan ?? '-' }}</h3>
                    <div class="card-meta">
                        @if($lowongan->posisi_magang)
                            <span class="meta-chip"><i class="fas fa-briefcase"></i> {{ Str::limit($lowongan->posisi_magang, 20) }}</span>
                        @endif
                        @if($lowongan->divisi)
                            <span class="meta-chip"><i class="fas fa-layer-group"></i> {{ Str::limit($lowongan->divisi, 20) }}</span>
                        @endif
                        <span class="meta-chip"><i class="fas fa-users"></i> {{ $lowongan->kuota_peserta ?? 0 }} Peserta</span>
                    </div>
                    <div class="card-footer">
                        <div class="card-company">
                            <div class="company-logo">
                                <img src="{{ $logo }}" alt="{{ optional($lowongan->industri)->nama_industri ?? 'Industri' }}">
                            </div>
                            <div>
                                <p class="company-name">{{ optional($lowongan->industri)->nama_industri ?? 'BBLSDM Komdigi Makassar' }}</p>
                                <p class="company-sub">{{ optional($lowongan->created_at)->translatedFormat('d M Y') ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="{{ route('institusi.create') }}" class="btn-card btn-detail">Detail</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div id="emptyState" class="hidden text-center py-16 text-slate-400">
                <i class="fas fa-search text-4xl mb-3 block"></i>
                <p class="font-semibold text-slate-500 text-base">Tidak ada lowongan ditemukan</p>
                <p class="text-sm mt-1">Coba ubah kata kunci atau filter yang digunakan</p>
            </div>
        @endif

        @if(!$lowongans->isEmpty())
            <div class="mt-10 text-center reveal">
                <a href="{{ route('daftar_lowongan') }}" class="inline-flex items-center gap-2 text-sm font-bold px-6 py-3 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 transition">
                    Lihat Semua Lowongan <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        @endif
    </div>
</section>

<!-- ===== PROSES ===== -->
<section id="process" class="section-process">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-eyebrow">Alur Magang</span>
            <h2 class="section-title">Langkah Sederhana untuk Setiap Pengguna</h2>
            <p class="section-desc">Mulai dari pendaftaran hingga sertifikat, semua proses dipandu dengan tampilan yang mudah dipahami.</p>
        </div>

        <div class="process-grid">
            <div class="process-card reveal">
                <div class="process-num">01</div>
                <div class="process-icon"><i class="fas fa-user-plus"></i></div>
                <h3 class="process-title">Pendaftaran & Profil Peserta</h3>
                <p class="process-desc">Daftarkan diri Anda, lengkapi profil. Selanjutnya, biarkan sistem yang mengurus onboarding Anda secara cepat dan praktis.</p>
            </div>
            <div class="process-card reveal">
                <div class="process-num">02</div>
                <div class="process-icon"><i class="fas fa-calendar-check"></i></div>
                <h3 class="process-title">Manajemen Aktivitas</h3>
                <p class="process-desc">Sistem ini menyediakan fitur yang menarik untuk monitoring kehadiran, logbook harian, evaluasi, serta rekomendasi pengembangan kompetensi yang relevan.</p>
            </div>
            <div class="process-card reveal">
                <div class="process-num">03</div>
                <div class="process-icon"><i class="fas fa-award"></i></div>
                <h3 class="process-title">Laporan & Sertifikat</h3>
                <p class="process-desc">Nikmati pengalaman baru, upload laporan, terima feedback mentor, dan dapatkan sertifikat digital resmi secara otomatis.</p>
            </div>
        </div>

        <div class="process-carousel" id="process-carousel">
            <div class="carousel-wrapper" id="process-wrapper">
                <div class="carousel-item">
                    <div class="process-card">
                        <div class="process-num">01</div>
                        <div class="process-icon"><i class="fas fa-user-plus"></i></div>
                        <h3 class="process-title">Daftar & Profil</h3>
                        <p class="process-desc">Isi data peserta and pilih institusi asal dengan cepat.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="process-card">
                        <div class="process-num">02</div>
                        <div class="process-icon"><i class="fas fa-calendar-check"></i></div>
                        <h3 class="process-title">Absensi & Logbook</h3>
                        <p class="process-desc">Catat kehadiran dan aktivitas magang setiap hari secara digital.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="process-card">
                        <div class="process-num">03</div>
                        <div class="process-icon"><i class="fas fa-award"></i></div>
                        <h3 class="process-title">Laporan & Sertifikat</h3>
                        <p class="process-desc">Upload laporan akhir dan dapatkan sertifikat digital resmi.</p>
                    </div>
                </div>
            </div>
            <div class="carousel-nav">
                <button class="carousel-btn" id="process-prev"><i class="fas fa-chevron-left"></i></button>
                <div class="dot-wrap" id="process-dots">
                    <div class="dot active"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
                <button class="carousel-btn" id="process-next"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- ===== FITUR / PENGGUNAAN ===== -->
<section id="usage" class="section-usage">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-eyebrow-alt">Fitur</span>
            <h2 class="section-title">Fitur Aplikasi</h2>
            <p class="section-desc">Ikuti langkah-langkah berikut untuk memahami alur penggunaan aplikasi secara cepat dan mudah.</p>
        </div>

        <div class="step-block reveal">
            <div class="step-image-wrap flex justify-center w-full">
                <div class="relative w-full max-w-[260px] sm:max-w-[300px] mx-auto group">
                    <img src="{{ asset('storage/tutorial/frame.png') }}" alt="Phone Frame" class="relative z-10 w-full h-auto drop-shadow-2xl pointer-events-none">
                    <div class="absolute top-[2.5%] bottom-[2.5%] left-[5%] right-[5%] z-0 overflow-hidden rounded-[1.8rem] sm:rounded-[2.2rem] bg-white bg-no-repeat bg-top group-hover:bg-bottom" style="background-image: url('{{ asset('storage/tutorial/dashboard.png') }}'); background-size: 100% auto; transition: background-position 6s ease-in-out;"></div>
                </div>
            </div>
            <div>
                <div class="step-badge"><i class="fas fa-flag" style="font-size:10px"></i> Fitur 1</div>
                <div class="step-icon-wrap"><i class="fas fa-home"></i></div>
                <h3 class="step-title">Pilih Layanan Dashboard</h3>
                <p class="step-desc">Setelah login, Anda akan melihat ringkasan aktivitas magang, status absensi, and akses cepat untuk laporan serta mikro skill.</p>
                <ul class="step-list">
                    <li>Informasi hadir, izin, dan sakit secara real time</li>
                    <li>Status laporan dan mikroskill langsung terlihat</li>
                    <li>Tombol pintas untuk akses absensi dan logbook</li>
                </ul>
            </div>
        </div>

        <div class="step-block reveal">
            <div class="order-swap">
                <div class="step-badge"><i class="fas fa-flag" style="font-size:10px"></i> Fitur 2</div>
                <div class="step-icon-wrap"><i class="fas fa-calendar-check"></i></div>
                <h3 class="step-title">Kelola Absensi</h3>
                <p class="step-desc">Lakukan absensi harian sebagai bukti kehadiran selama kegiatan magang berlangsung secara akurat dan real-time.</p>
                <ul class="step-list">
                    <li>Check-in dan check-out harian secara digital</li>
                    <li>Verifikasi lokasi untuk memastikan kehadiran valid</li>
                    <li>Riwayat absensi tersimpan otomatis</li>
                </ul>
            </div>
            <div class="step-image-wrap flex justify-center w-full">
                <div class="relative w-full max-w-[260px] sm:max-w-[300px] mx-auto group">
                    <img src="{{ asset('storage/tutorial/frame.png') }}" alt="Phone Frame" class="relative z-10 w-full h-auto drop-shadow-2xl pointer-events-none">
                    <div class="absolute top-[2.5%] bottom-[2.5%] left-[5%] right-[5%] z-0 overflow-hidden rounded-[1.8rem] sm:rounded-[2.2rem] bg-white bg-no-repeat bg-top group-hover:bg-bottom" style="background-image: url('{{ asset('storage/tutorial/absen.jpeg') }}'); background-size: 100% auto; transition: background-position 6s ease-in-out;"></div>
                </div>
            </div>
        </div>

        <div class="step-block reveal">
            <div class="step-image-wrap flex justify-center w-full">
                <div class="relative w-full max-w-[260px] sm:max-w-[300px] mx-auto group">
                    <img src="{{ asset('storage/tutorial/frame.png') }}" alt="Phone Frame" class="relative z-10 w-full h-auto drop-shadow-2xl pointer-events-none">
                    <div class="absolute top-[2.5%] bottom-[2.5%] left-[5%] right-[5%] z-0 overflow-hidden rounded-[1.8rem] sm:rounded-[2.2rem] bg-white bg-no-repeat bg-top group-hover:bg-bottom" style="background-image: url('{{ asset('storage/tutorial/logbook.png') }}'); background-size: 100% auto; transition: background-position 6s ease-in-out;"></div>
                </div>
            </div>
            <div>
                <div class="step-badge"><i class="fas fa-flag" style="font-size:10px"></i> Fitur 3</div>
                <div class="step-icon-wrap"><i class="fas fa-book"></i></div>
                <h3 class="step-title">Kelola Logbook</h3>
                <p class="step-desc">Catat aktivitas pekerjaan harian untuk mendokumentasikan progres dan hasil kerja selama magang.</p>
                <ul class="step-list">
                    <li>Mencatat aktivitas kerja setiap hari</li>
                    <li>Riwayat kegiatan tersimpan rapi dan terstruktur</li>
                    <li>Dapat dipantau dan direview oleh mentor</li>
                </ul>
            </div>
        </div>

        <div class="step-block reveal">
            <div class="order-swap">
                <div class="step-badge"><i class="fas fa-flag" style="font-size:10px"></i> Fitur 4</div>
                <div class="step-icon-wrap"><i class="fas fa-file-upload"></i></div>
                <h3 class="step-title">Upload Laporan</h3>
                <p class="step-desc">Kirim laporan akhir magang sebagai bentuk hasil evaluasi dan dokumentasi kegiatan yang telah dilakukan.</p>
                <ul class="step-list">
                    <li>Upload laporan dalam berbagai format file</li>
                    <li>Status laporan dapat dipantau secara langsung</li>
                    <li>Mendapatkan feedback dan revisi dari mentor</li>
                </ul>
            </div>
            <div class="step-image-wrap flex justify-center w-full">
                <div class="relative w-full max-w-[260px] sm:max-w-[300px] mx-auto group">
                    <img src="{{ asset('storage/tutorial/frame.png') }}" alt="Phone Frame" class="relative z-10 w-full h-auto drop-shadow-2xl pointer-events-none">
                    <div class="absolute top-[2.5%] bottom-[2.5%] left-[5%] right-[5%] z-0 overflow-hidden rounded-[1.8rem] sm:rounded-[2.2rem] bg-white bg-no-repeat bg-top group-hover:bg-bottom" style="background-image: url('{{ asset('storage/tutorial/laporan.png') }}'); background-size: 100% auto; transition: background-position 6s ease-in-out;"></div>
                </div>
            </div>
        </div>

        <div class="step-block reveal">
            <div class="step-image-wrap flex justify-center w-full">
                <div class="relative w-full max-w-[260px] sm:max-w-[300px] mx-auto group">
                    <img src="{{ asset('storage/tutorial/frame.png') }}" alt="Phone Frame" class="relative z-10 w-full h-auto drop-shadow-2xl pointer-events-none">
                    <div class="absolute top-[2.5%] bottom-[2.5%] left-[5%] right-[5%] z-0 overflow-hidden rounded-[1.8rem] sm:rounded-[2.2rem] bg-white bg-no-repeat bg-top group-hover:bg-bottom" style="background-image: url('{{ asset('storage/tutorial/mikroskill.png') }}'); background-size: 100% auto; transition: background-position 6s ease-in-out;"></div>
                </div>
            </div>
            <div>
                <div class="step-badge"><i class="fas fa-flag" style="font-size:10px"></i> Fitur 5</div>
                <div class="step-icon-wrap"><i class="fas fa-award"></i></div>
                <h3 class="step-title">Mikroskill & Sertifikat</h3>
                <p class="step-desc">Lengkapi penilaian mikroskill untuk mengukur kompetensi yang diperoleh selama magang dan dapatkan sertifikat resmi.</p>
                <ul class="step-list">
                    <li>Penilaian kompetensi berdasarkan aktivitas</li>
                    <li>Validasi oleh mentor atau pembimbing</li>
                    <li>Sertifikat digital otomatis setelah selesai</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ===== TESTIMONI ===== -->
<section id="testimonials" class="section-testimonials">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-eyebrow">Testimoni</span>
            <h2 class="section-title">Apa Kata Mereka?</h2>
            <p class="section-desc">Berikut tanggapan positif dan saran konstruktif dari mereka yang telah merasakan serunya pengalaman magang bersama kami.</p>
        </div>

        <div class="testi-grid">
            @php $testimonials = $testimonials ?? []; @endphp
            @forelse($testimonials as $testimony)
                <div class="testi-card reveal">
                    <div class="testi-quote-icon">"</div>
                    <div class="testi-stars">★★★★★</div>
                    <p class="testi-text">{{ Str::limit($testimony->testimony, 150) }}</p>
                    <div class="testi-author">
                        <div class="testi-avatar">
                            <img src="{{ asset('storage/' . ($testimony->intern->photo_path ?? 'profiles/default.jpg')) }}" alt="{{ $testimony->intern->name }}">
                        </div>
                        <div>
                            <div class="testi-name">{{ $testimony->intern->name }}</div>
                            <div class="testi-inst">Mahasiswa — {{ $testimony->intern->institution ?? 'Institusi' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:2rem">
                    <p style="color:#4b6580;font-size:1.05rem;margin:0">Belum ada testimoni. Jadilah yang pertama berbagi pengalaman Anda!</p>
                </div>
            @endforelse
        </div>

        @if(count($testimonials) > 0)
        <div class="testi-carousel" id="testi-carousel">
            <div class="carousel-wrapper" id="testi-wrapper">
                @foreach($testimonials as $testimony)
                <div class="carousel-item">
                    <div class="testi-card">
                        <div class="testi-quote-icon">"</div>
                        <div class="testi-stars">★★★★★</div>
                        <p class="testi-text">{{ Str::limit($testimony->testimony, 150) }}</p>
                        <div class="testi-author">
                            <div class="testi-avatar">
                                <img src="{{ asset('storage/' . ($testimony->intern->photo_path ?? 'profiles/default.jpg')) }}" alt="{{ $testimony->intern->name }}">
                            </div>
                            <div>
                                <div class="testi-name">{{ $testimony->intern->name }}</div>
                                <div class="testi-inst">Mahasiswa — {{ $testimony->intern->institution ?? 'Institusi' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="carousel-nav">
                <button class="carousel-btn" id="testi-prev"><i class="fas fa-chevron-left"></i></button>
                <div class="dot-wrap" id="testi-dots">
                    @foreach($testimonials as $i => $t)
                    <div class="dot{{ $i === 0 ? ' active' : '' }}"></div>
                    @endforeach
                </div>
                <button class="carousel-btn" id="testi-next"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- ===== PARTNERS ===== -->
<section id="partners" class="section-partners">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-eyebrow-alt">Mitra</span>
            <h2 class="section-title">Institusi Pendidikan dan Industri yang Telah Bergabung</h2>
            <p class="section-desc">Dengan simagang, bersama kita menciptakan sistem magang yang mudah, transparan dan terkendali guna menciptakan pengalaman kerja yang seru, dinamis dan menyenangkan bagi para talenta muda.</p>
        </div>
        <div class="partners-grid">
            @php $partners = $partners ?? []; @endphp
            @forelse($partners as $partner)
                <div class="partner-card reveal">
                    <img src="{{ $partner }}" alt="Logo partner">
                </div>
            @empty
                <div class="partner-empty reveal">
                    <i class="fas fa-building" style="font-size:2rem;color:#bfdbfe;margin-bottom:0.75rem;display:block"></i>
                    <p style="margin:0;font-size:15px">Belum ada logo partner yang ditambahkan.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ===== AKTIVITAS TERBARU DINAMIS ===== -->
<section id="news" class="section-news">
    <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10 reveal">
            <div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight">Aktivitas Terbaru</h2>
                <div class="mt-3 w-24 h-1 rounded-full bg-cyan-400"></div>
            </div>
            <div class="activity-tabs">
                <button type="button" class="activity-tab active" onclick="showActivityTab('artikel', this)">
                    <i class="fas fa-newspaper"></i> Artikel
                </button>
                <button type="button" class="activity-tab" onclick="showActivityTab('youtube', this)">
                    <i class="fab fa-youtube"></i> YouTube
                </button>
            </div>
        </div>

        {{-- Artikel --}}
        <div id="activity-artikel" class="activity-panel active">
            @if(isset($articleActivities) && $articleActivities->count() > 0)
                <div class="activity-grid">
                    @foreach($articleActivities as $post)
                        <a href="{{ route('public.activity.show', $post->slug) }}" class="activity-card reveal">
                            <div class="activity-img-wrap">
                                <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}">
                                <span class="activity-badge badge-article"><i class="fas fa-newspaper"></i> Artikel</span>
                            </div>
                            <div class="activity-content">
                                <p class="activity-date">
                                    <i class="fas fa-calendar-alt"></i> {{ $post->published_at ? $post->published_at->format('d M Y') : '-' }} | 
                                    <i class="fas fa-user"></i> {{ $post->display_author }}
                                    @if($post->display_editor !== '-')
                                        | <i class="fas fa-edit"></i> {{ $post->display_editor }}
                                    @endif
                                </p>
                                <h3>{{ $post->title }}</h3>
                                <p class="activity-excerpt">{{ \Illuminate\Support\Str::limit($post->excerpt ?? strip_tags($post->content), 110) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="activity-empty reveal">
                    <i class="fas fa-newspaper"></i>
                    <p>Belum ada artikel yang dipublikasikan.</p>
                </div>
            @endif
        </div>

        {{-- YouTube --}}
        <div id="activity-youtube" class="activity-panel">
            @if(isset($youtubeActivities) && $youtubeActivities->count() > 0)
                <div class="activity-grid">
                    @foreach($youtubeActivities as $post)
                        <a href="{{ route('public.activity.show', $post->slug) }}" class="activity-card reveal">
                            <div class="activity-img-wrap">
                                <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}">
                                <span class="activity-badge badge-youtube"><i class="fab fa-youtube"></i> YouTube</span>
                                <div class="play-overlay"><i class="fas fa-play"></i></div>
                            </div>
                            <div class="activity-content">
                                <p class="activity-date">
                                    <i class="fas fa-calendar-alt"></i> {{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}
                                    @if($post->display_editor !== '-')
                                        | <i class="fas fa-edit"></i> {{ $post->display_editor }}
                                    @endif
                                </p>
                                <h3>{{ $post->title }}</h3>
                                <p class="activity-excerpt">{{ \Illuminate\Support\Str::limit($post->excerpt ?? $post->content, 110) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="activity-empty reveal">
                    <i class="fab fa-youtube"></i>
                    <p>Belum ada video YouTube yang dipublikasikan.</p>
                </div>
            @endif
        </div>

        @if(($totalArticleActivities ?? 0) > 3 || ($totalYoutubeActivities ?? 0) > 3)
            <div id="activityMoreWrapper" class="mt-10 text-center {{ ($totalArticleActivities ?? 0) > 3 ? '' : 'hidden' }}">
                <a id="activityMoreButton" href="{{ route('public.activity.index', ['type' => 'artikel']) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                    Lihat Artikel Lainnya <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        @endif
    </div>
</section>

{{-- Sharing Session Mingguan --}}
<section id="sharing-session" class="py-24 bg-[#eef6ff] relative overflow-hidden" data-dm-native>
    <div class="absolute top-0 left-0 w-72 h-72 bg-blue-200/30 rounded-full blur-3xl -translate-x-24 -translate-y-24"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-200/30 rounded-full blur-3xl translate-x-24 translate-y-24"></div>
    <div class="container mx-auto px-6 relative z-10">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-3xl font-bold text-[#020b1a]">Sharing Session</h2>
                <div class="h-1 w-20 bg-[#00f2ff] mt-2"></div>
                <p class="text-gray-500 mt-4 text-sm md:text-base">Jadwal sharing session minggu ini, {{ $weekStart->format('d M Y') }} - {{ $weekEnd->format('d M Y') }}.</p>
            </div>
        </div>

        <div class="bg-white/65 backdrop-blur-sm border border-white/70 rounded-[34px] p-5 md:p-7 shadow-[0_18px_50px_rgba(15,45,74,0.08)]">
            @if($featuredSharingSession)
                <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-[30px] items-stretch">
                    <a href="{{ route('public.sharing-session.show', $featuredSharingSession) }}" class="relative group rounded-[24px] overflow-hidden h-[500px] shadow-lg bg-gray-200 block">
                        @if($featuredSharingSession->documentation_photo_url)
                            <img src="{{ $featuredSharingSession->documentation_photo_url }}" alt="{{ $featuredSharingSession->title ?? 'Sharing Session' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-800 via-indigo-700 to-[#020b1a] flex flex-col items-center justify-center text-white">
                                <div class="w-24 h-24 rounded-3xl bg-white/15 flex items-center justify-center mb-5"><i class="fas fa-comments text-5xl"></i></div>
                                <p class="text-3xl font-bold">Sharing Session</p>
                                <p class="text-sm text-blue-100 mt-2">Dokumentasi belum tersedia</p>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#020b1a]/95 via-[#020b1a]/35 to-transparent"></div>
                        <div class="absolute top-6 right-6 opacity-0 group-hover:opacity-100 transition">
                            <span class="inline-flex items-center gap-2 bg-white/90 text-blue-700 px-4 py-2 rounded-full text-xs font-bold shadow">Lihat Detail <i class="fas fa-arrow-right"></i></span>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 p-8 md:p-10 text-white">
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-[#00f2ff] text-[#020b1a] text-[10px] font-bold mb-4 uppercase">Minggu Ini</span>
                            <h3 class="text-2xl md:text-3xl font-bold leading-tight group-hover:text-cyan-200 transition">{{ $featuredSharingSession->title ?? 'Materi Belum Diisi' }}</h3>
                            <p class="text-gray-300 mt-3 text-sm leading-relaxed max-w-xl">
                                {{ $featuredSharingSession->description ? \Illuminate\Support\Str::limit($featuredSharingSession->description, 120) : 'Deskripsi sharing session belum tersedia.' }}
                            </p>
                            <div class="flex flex-wrap gap-4 mt-4 text-sm text-white/90">
                                <span><i class="fas fa-calendar-alt mr-2"></i> {{ $featuredSharingSession->session_date->format('d M Y') }}</span>
                                <span><i class="fas fa-clock mr-2"></i> {{ $featuredSharingSession->start_time ? \Carbon\Carbon::parse($featuredSharingSession->start_time)->format('H:i') : '-' }} WITA</span>
                                <span><i class="fas fa-map-marker-alt mr-2"></i> {{ $featuredSharingSession->location ?? '-' }}</span>
                            </div>
                        </div>
                    </a>

                    <div class="flex flex-col gap-5">
                        @forelse($sideSharingSessions as $session)
                            <a href="{{ route('public.sharing-session.show', $session) }}" class="bg-white rounded-[18px] p-[15px] flex gap-5 shadow-sm hover:shadow-md border border-cyan-50 hover:border-[#00f2ff] transition group">
                                <div class="w-[120px] h-[90px] rounded-[12px] overflow-hidden bg-gray-200 shrink-0">
                                    @if($session->documentation_photo_url)
                                        <img src="{{ $session->documentation_photo_url }}" alt="{{ $session->title ?? 'Sharing Session' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-blue-700 to-indigo-500 flex items-center justify-center text-white"><i class="fas fa-comments text-3xl"></i></div>
                                    @endif
                                </div>
                                <div class="flex flex-col justify-center flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-[15px] font-bold text-[#020b1a] leading-snug mb-1 group-hover:text-blue-700 transition">{{ $session->title ?? 'Materi Belum Diisi' }}</h3>
                                        <i class="fas fa-arrow-right text-blue-500 text-xs mt-1 opacity-0 group-hover:opacity-100 transition"></i>
                                    </div>
                                    <p class="text-gray-400 text-sm leading-relaxed line-clamp-2">{{ $session->description ? \Illuminate\Support\Str::limit($session->description, 85) : 'Deskripsi sharing session belum tersedia.' }}</p>
                                    <div class="flex flex-wrap gap-3 mt-2 text-[12px] text-gray-400">
                                        <span><i class="fas fa-calendar-alt text-blue-500 mr-1"></i> {{ $session->session_date->format('d M Y') }}</span>
                                        <span><i class="fas fa-clock text-green-500 mr-1"></i> {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-' }} WITA</span>
                                        <span><i class="fas fa-map-marker-alt text-orange-500 mr-1"></i> {{ $session->location ?? '-' }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="bg-white rounded-[18px] p-8 text-gray-400 text-center shadow-sm border border-cyan-50">
                                <i class="fas fa-calendar-plus text-3xl text-gray-300 mb-3"></i>
                                <p class="font-semibold">Belum ada jadwal sharing session lain minggu ini.</p>
                            </div>
                        @endforelse

                        @if($weeklySharingSessions->count() === 1)
                            <div class="bg-white rounded-[18px] p-8 text-gray-400 text-center shadow-sm border border-cyan-50">
                                <i class="fas fa-info-circle text-3xl text-gray-300 mb-3"></i>
                                <p class="font-semibold">Baru ada 1 jadwal sharing session untuk minggu ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white rounded-[24px] p-12 text-center text-gray-500 shadow-sm border border-cyan-50">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                    <p class="font-semibold text-lg">Belum ada jadwal sharing session minggu ini.</p>
                    <p class="text-sm text-gray-400 mt-2">Jadwal akan tampil otomatis jika admin sudah membuat sharing session pada minggu berjalan.</p>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- ===== TIM PENGEMBANG TIM V3.5 ELEGAN ===== -->
<section id="about" class="team-tabs-section">
    <svg class="team-wavy-deco" style="top:80px;left:16px;width:90px;" viewBox="0 0 90 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M2 10 Q14 2 26 10 Q38 18 50 10 Q62 2 74 10 Q82 16 88 10" stroke="#22d3ee" stroke-width="2.2" stroke-linecap="round" fill="none"/>
    </svg>
    <svg class="team-wavy-deco" style="bottom:80px;right:24px;width:90px;" viewBox="0 0 90 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M2 10 Q14 2 26 10 Q38 18 50 10 Q62 2 74 10 Q82 16 88 10" stroke="#22d3ee" stroke-width="2.2" stroke-linecap="round" fill="none"/>
    </svg>
    <svg class="team-wavy-deco" style="top:28px;right:28px;width:40px;opacity:0.55;" viewBox="0 0 40 44"><polygon points="0,44 20,0 40,44" fill="none" stroke="#fbbf24" stroke-width="2.5"/></svg>

    <div class="container" style="position:relative;z-index:2;">
        <h2 class="team-tabs-title">Tim Pengembang Simagang</h2>

        <div class="team-tabs-nav">
            <button class="team-tab-btn active" data-tab="v4">Simagang v4</button>
            <button class="team-tab-btn" data-tab="angkatan3">Simagang v3.5</button>
            <button class="team-tab-btn" data-tab="angkatan1">Simagang v2</button>
            <button class="team-tab-btn" data-tab="angkatan2">Simagang v1</button>
        </div>

        <!-- ── TAB PANEL: SIMAGANG V4 (ACTIVE) ── -->
        @php
            $simagangV4 = [
                ['name' => 'Indra Pranata', 'photo' => 'profiles/indra.jpg', 'campus' => 'Universitas Dipa Makassar', 'logo' => 'partners/undipa.png',
                 'logo_style' => 'round', 'theme' => 'gold', 'instagram' => 'indrapranata369'],
                ['name' => 'Aan Syawaluddin Adi Putra', 'photo' => 'profiles/aan.jpg', 'campus' => 'Universitas Hasanuddin', 'logo' => 'partners/logo_unhas.png',
                 'logo_style' => 'plain', 'theme' => 'red', 'instagram' => 'aansyawaluddinn'],
            ];
        @endphp
        <div class="team-tab-panel active" id="tab-v4">
            <div class="v4-grid">
                @foreach ($simagangV4 as $m)
                    @php($igUrl = 'https://www.instagram.com/' . $m['instagram'])
                    <article class="v4-card v4-card--{{ $m['theme'] }}">
                        <div class="v4-photo">
                            <img class="v4-photo__img" src="{{ asset('storage/' . $m['photo']) }}" alt="Foto {{ $m['name'] }}" loading="lazy">

                            <span class="v4-logo v4-logo--{{ $m['logo_style'] }}">
                                <img class="v4-logo__img" src="{{ asset('storage/' . $m['logo']) }}" alt="Logo {{ $m['campus'] }}" loading="lazy">
                            </span>

                            <button type="button" class="v4-ig" aria-label="Buka Instagram {{ $m['name'] }}"
                                    onclick="openInstagramModal(@js($m['name']), @js($igUrl))">
                                <i class="fab fa-instagram" aria-hidden="true"></i>
                                <span>&#64;{{ $m['instagram'] }}</span>
                                <i class="fas fa-arrow-up-right-from-square v4-ig__out" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="v4-info">
                            <h3 class="v4-name">{{ $m['name'] }}</h3>
                            <p class="v4-campus v4-campus--{{ $m['theme'] }}">{{ $m['campus'] }}</p>
                            <p class="v4-program">Maganghub batch 2 | 2026</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <!-- ── TAB PANEL: SIMAGANG V3.5 / ANGKATAN 3 (BLUE ELEGAN CARD) ── -->
        <div class="team-tab-panel" id="tab-angkatan3">
            <div class="team-carousel-wrapper">
                <div class="team-members-grid max-w-4xl mx-auto !grid-cols-1 sm:!grid-cols-3 gap-5" id="carousel-grid-3">
                    
                    <!-- Anggota 1 -->
                    <div class="tmember-card cursor-pointer" onclick="openInstagramModal('Aldisar Fachreza', 'https://www.instagram.com/aldisar_22?igsh=MXRieW9zdXBuYWs3cA==')">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/aldisar.jpg') }}" alt="Aldisar Fachreza" class="tmember-photo">
                            
                            <!-- Ikon Instagram Mengambang (Tanpa Blur Lama) -->
                            <div class="tmember-insta-hover-icon">
                                <div class="tmember-insta-btn">
                                    <i class="fab fa-instagram"></i>
                                </div>
                            </div>

                            <div class="tmember-info">
                                <div class="tmember-name">Aldisar Fachreza</div>
                                <div class="tmember-meta">Universitas Negeri Makassar | TEKNIK INFORMATIKA & KOMPUTER</div>
                            </div>
                        </div>
                    </div>

                    <!-- Anggota 2 -->
                    <div class="tmember-card cursor-pointer" onclick="openInstagramModal('A. Muh. Bintang P.', 'https://www.instagram.com/bintang_palinrungi?igsh=a2k1Nm13b2pjZzc5')">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/bintang.jpg') }}" alt="A. Muh. Bintang P." class="tmember-photo">
                            
                            <!-- Ikon Instagram Mengambang (Tanpa Blur Lama) -->
                            <div class="tmember-insta-hover-icon">
                                <div class="tmember-insta-btn">
                                    <i class="fab fa-instagram"></i>
                                </div>
                            </div>

                            <div class="tmember-info">
                                <div class="tmember-name">A. Muh. Bintang P.</div>
                                <div class="tmember-meta">Universitas Negeri Makassar | TEKNIK INFORMATIKA & KOMPUTER</div>
                            </div>
                        </div>
                    </div>

                    <!-- Anggota 3 -->
                    <div class="tmember-card cursor-pointer" onclick="openInstagramModal('Muh Imtiyazzaidan Arbi', 'https://instagram.com/imtiyazzaidan')">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/arbi.jpg') }}" alt="Muh Imtiyazzaidan Arbi" class="tmember-photo">
                            
                            <!-- Ikon Instagram Mengambang (Tanpa Blur Lama) -->
                            <div class="tmember-insta-hover-icon">
                                <div class="tmember-insta-btn">
                                    <i class="fab fa-instagram"></i>
                                </div>
                            </div>

                            <div class="tmember-info">
                                <div class="tmember-name">Muh Imtiyazzaidan Arbi</div>
                                <div class="tmember-meta">Universitas Negeri Makassar | TEKNIK INFORMATIKA & KOMPUTER</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="team-carousel-dots" id="carousel-dots-3"></div>
            <div class="team-carousel-progress" id="carousel-progress-3"><div class="team-carousel-progress-bar" id="carousel-bar-3"></div></div>
        </div>

        <!-- ── TAB PANEL: SIMAGANG V2 ── -->
        <div class="team-tab-panel" id="tab-angkatan1">
            <div class="team-carousel-wrapper">
                <div class="team-members-grid" id="carousel-grid-1">
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/user1.jpeg') }}" alt="Diza Sazkia" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Diza Sazkia</div><div class="tmember-meta">Universitas Hasanuddin | Sistem Informasi</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/user2.jpg') }}" alt="Nur Fadillah" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Nur Fadillah</div><div class="tmember-meta">Universitas Hasanuddin | Sistem Informasi</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/user3.jpg') }}" alt="Resky Auliyah Kartini A" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Resky Auliyah Kartini A</div><div class="tmember-meta">Universitas Hasanuddin | Sistem Informasi</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/user4.jpeg') }}" alt="Novi Rezkiyah Azzahrah R" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Novi Rezkiyah Azzahrah R</div><div class="tmember-meta">Universitas Hasanuddin | Sistem Informasi</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/user5.png') }}" alt="An Naura Erwana Dwi P" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">An Naura Erwana Dwi P</div><div class="tmember-meta">Universitas Hasanuddin | Sistem Informasi</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ asset('storage/profiles/user6.png') }}" alt="Cholyn Sharon Enos" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Cholyn Sharon Enos</div><div class="tmember-meta">Universitas Hasanuddin | Sistem Informasi</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="team-carousel-dots" id="carousel-dots-1"></div>
            <div class="team-carousel-progress" id="carousel-progress-1"><div class="team-carousel-progress-bar" id="carousel-bar-1"></div></div>
        </div>

        <!-- ── TAB PANEL: SIMAGANG V1 ── -->
        <div class="team-tab-panel" id="tab-angkatan2">
            <div class="team-carousel-wrapper">
                <div class="team-members-grid" id="carousel-grid-2">
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ url('storage/partners/pnup.png') }}" alt="Rezki Andika" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Rezki Andika</div><div class="tmember-meta">Politeknik Negeri Ujung Pandang</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ url('storage/partners/stialan.png') }}" alt="Husnul Khatimah" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Husnul Khatimah</div><div class="tmember-meta">Politeknik STIA LAN Makassar</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ url('storage/partners/stialan.png') }}" alt="Dominikus Dikky" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Dominikus Dikky</div><div class="tmember-meta">Politeknik STIA LAN Makassar</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ url('storage/partners/stialan.png') }}" alt="Aurrely Aprilia Putri Oppusunggu" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Aurrely Aprilia Putri Oppusunggu</div><div class="tmember-meta">Politeknik STIA LAN Makassar</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ url('storage/partners/stialan.png') }}" alt="Willyan Imanuel Rari" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Willyan Imanuel Rari</div><div class="tmember-meta">Politeknik STIA LAN Makassar</div></div>
                        </div>
                    </div>
                    <div class="tmember-card">
                        <div class="tmember-photo-wrap">
                            <img src="{{ url('storage/partners/stialan.png') }}" alt="Fadhil Darmawan Jibran" class="tmember-photo">
                            <div class="tmember-info"><div class="tmember-name">Fadhil Darmawan Jibran</div><div class="tmember-meta">Politeknik STIA LAN Makassar</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="team-carousel-dots" id="carousel-dots-2"></div>
            <div class="team-carousel-progress" id="carousel-progress-2"><div class="team-carousel-progress-bar" id="carousel-bar-2"></div></div>
        </div>

    </div>
</section>

<!-- ===== MODAL POPUP INSTAGRAM SELESAI KLIK ===== -->
<div id="instagramModal" class="insta-modal" onclick="closeInstagramModal(event)">
    <div class="insta-modal-content" onclick="event.stopPropagation()">
        <h3 id="modalDeveloperName" class="text-xl font-bold text-white mb-2">Nama Developer</h3>
        <p class="text-slate-400 text-sm mb-4">Ingin melihat profil atau berinteraksi lebih lanjut?</p>
        <hr class="border-slate-700 my-3">
        <a id="modalInstagramLink" href="#" target="_blank" class="insta-btn-link">
            <i class="fab fa-instagram text-lg"></i> Kunjungi Instagram
        </a>
        <button onclick="document.getElementById('instagramModal').classList.remove('open')" class="mt-4 text-xs text-slate-500 hover:text-slate-300 block mx-auto py-1">
            Kembali
        </button>
    </div>
</div>

<!-- ===== CTA / PENDAFTARAN ===== -->
<section class="section-cta">
    <div id="daftar" class="container">
        <div class="cta-inner">
            <div class="reveal">
                <div class="cta-tag">Pendaftaran Mitra</div>
                <h2 class="cta-title">Bergabung Sebagai Mitra Simagang</h2>
                <p class="cta-desc">Simagang mendukung kerja sama antara institusi pendidikan dan industri dalam pengelolaan program magang yang lebih modern, terstruktur, dan efisien.</p>
                <div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
                    <div style="display:flex;align-items:center;gap:8px;color:rgba(255,255,255,0.65);font-size:14px">
                        <i class="fas fa-check-circle" style="color:#22d3ee;font-size:16px"></i> Gratis pendaftaran mitra
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;color:rgba(255,255,255,0.65);font-size:14px">
                        <i class="fas fa-check-circle" style="color:#22d3ee;font-size:16px"></i> Verifikasi dan dukungan admin
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;color:rgba(255,255,255,0.65);font-size:14px">
                        <i class="fas fa-check-circle" style="color:#22d3ee;font-size:16px"></i> Kolaborasi pendidikan dan industri
                    </div>
                </div>
            </div>

            <div class="cta-cards reveal">
                <a href="{{ route('institusi.create') }}" class="cta-card">
                    <div class="cta-card-left">
                        <div class="cta-card-icon" style="background:linear-gradient(135deg,#0ea5e9,#22d3ee)"><i class="fas fa-school"></i></div>
                        <div>
                            <div class="cta-card-name">Mitra Pendidikan</div>
                            <div class="cta-card-sub">Untuk sekolah, SMK, universitas, and institusi pendidikan lainnya.</div>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right cta-arrow"></i>
                </a>

                <a href="{{ route('industri.create') }}" class="cta-card">
                    <div class="cta-card-left">
                        <div class="cta-card-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24)"><i class="fas fa-building"></i></div>
                        <div>
                            <div class="cta-card-name">Mitra Industri</div>
                            <div class="cta-card-sub">Untuk perusahaan, startup, UMKM, dan instansi yang membuka lowongan magang.</div>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right cta-arrow"></i>
                </a>
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
        <div class="footer-center-area">
            <div class="copyright-simple">
                © 2026 <strong>Simagang</strong> — BBLSDM Komdigi Makassar. All rights reserved.
            </div>
            <div class="footer-visitor-counter">
                <i class="fas fa-eye"></i>
                <span>{{ number_format($visitorCount ?? 0, 0, ',', '.') }}</span>
                <small>Pengunjung</small>
            </div>
        </div>
        <div class="social-links-simple">
            <a href="https://www.instagram.com/bblsdm.komdigi.makassar/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://www.komdigi.go.id/" target="_blank" title="Website"><i class="fas fa-globe"></i></a>
            <a href="https://www.tiktok.com/@balaikomdigimakassar" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.youtube.com/@bblsdm.komdigi.makassar" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
</footer>

<script>
/* ── Scroll Reveal ── */
const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            setTimeout(() => entry.target.classList.add('visible'), entry.target.dataset.delay || 0);
            revealObs.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach((el, i) => {
    el.dataset.delay = (i % 3) * 100;
    revealObs.observe(el);
});

/* ── Active Nav ── */
const navLinks = document.querySelectorAll('.nav-links a');
const navObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            navLinks.forEach(link => {
                link.style.background = '';
                link.style.color = '';
                if (link.getAttribute('href') === '#' + entry.target.id) {
                    link.style.background = '#eff6ff';
                    link.style.color = '#1d6fca';
                }
            });
        }
    });
}, { threshold: 0.5 });
document.querySelectorAll('section[id]').forEach(s => navObs.observe(s));

/* ── Mobile Menu ── */
const toggle = document.querySelector('.nav-toggle');
const menu   = document.querySelector('.mobile-menu');
if (toggle && menu) {
    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open);
        menu.setAttribute('aria-hidden', !open);
    });
    menu.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            menu.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            menu.setAttribute('aria-hidden', 'true');
        });
    });
}

/* ── Carousel Factory ── */
function initCarousel({ wrapperId, dotsId, prevId, nextId, interval = 5000 }) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;
    const dotsWrap = document.getElementById(dotsId);
    const dots     = dotsWrap ? Array.from(dotsWrap.querySelectorAll('.dot')) : [];
    const total    = wrapper.children.length;
    let cur = 0, timer;

    const go = (n) => {
        cur = ((n % total) + total) % total;
        wrapper.style.transform = `translateX(-${cur * 100}%)`;
        dots.forEach((d, i) => d.classList.toggle('active', i === cur));
    };

    const reset = () => { clearInterval(timer); timer = setInterval(() => go(cur + 1), interval); };

    document.getElementById(prevId)?.addEventListener('click', () => { go(cur - 1); reset(); });
    document.getElementById(nextId)?.addEventListener('click', () => { go(cur + 1); reset(); });
    dots.forEach((d, i) => d.addEventListener('click', () => { go(i); reset(); }));

    let sx = 0;
    const root = wrapper.closest('.process-carousel, .testi-carousel') || wrapper.parentElement;
    root.addEventListener('touchstart', e => { sx = e.changedTouches[0].screenX; }, { passive: true });
    root.addEventListener('touchend',   e => {
        const dx = sx - e.changedTouches[0].screenX;
        if (Math.abs(dx) > 50) { go(cur + (dx > 0 ? 1 : -1)); reset(); }
    });

    go(0);
    reset();
}

initCarousel({ wrapperId:'process-wrapper', dotsId:'process-dots', prevId:'process-prev', nextId:'process-next' });
initCarousel({ wrapperId:'testi-wrapper',   dotsId:'testi-dots',   prevId:'testi-prev',   nextId:'testi-next' });

function toggleMobileAktivitas() {
    const dropdown = document.getElementById('mobileAktivitasDropdown');
    if (dropdown) dropdown.classList.toggle('open');
}

function showActivityTab(type, button) {
    document.querySelectorAll('.activity-tab').forEach(tab => tab.classList.remove('active'));
    button.classList.add('active');

    document.querySelectorAll('.activity-panel').forEach(panel => panel.classList.remove('active'));
    const targetPanel = document.getElementById('activity-' + type);
    if (targetPanel) targetPanel.classList.add('active');

    const moreWrapper = document.getElementById('activityMoreWrapper');
    const moreButton = document.getElementById('activityMoreButton');
    const hasMoreArtikel = {{ ($totalArticleActivities ?? 0) > 3 ? 'true' : 'false' }};
    const hasMoreYoutube = {{ ($totalYoutubeActivities ?? 0) > 3 ? 'true' : 'false' }};

    if (moreWrapper && moreButton) {
        if (type === 'youtube') {
            moreWrapper.classList.toggle('hidden', !hasMoreYoutube);
            moreButton.href = "{{ route('public.activity.index', ['type' => 'youtube']) }}";
            moreButton.innerHTML = 'Lihat Video Lainnya <i class="fas fa-arrow-right text-xs"></i>';
            moreButton.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'shadow-blue-500/20');
            moreButton.classList.add('bg-red-500', 'hover:bg-red-600', 'shadow-red-500/20');
        } else {
            moreWrapper.classList.toggle('hidden', !hasMoreArtikel);
            moreButton.href = "{{ route('public.activity.index', ['type' => 'artikel']) }}";
            moreButton.innerHTML = 'Lihat Artikel Lainnya <i class="fas fa-arrow-right text-xs"></i>';
            moreButton.classList.remove('bg-red-500', 'hover:bg-red-600', 'shadow-red-500/20');
            moreButton.classList.add('bg-blue-600', 'hover:bg-blue-700', 'shadow-blue-500/20');
        }
    }
}

function toggleMobileDropdown(dropdownId) {
    const targetDropdown = document.getElementById(dropdownId);
    if (!targetDropdown) return;
    document.querySelectorAll('.mobile-dropdown').forEach(dropdown => {
        if (dropdown !== targetDropdown) dropdown.classList.remove('open');
    });
    targetDropdown.classList.toggle('open');
}

/* ── Team Tabs System ── */
document.querySelectorAll('.team-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.team-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.team-tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        if (window.innerWidth <= 480) {
            const panelId = 'tab-' + btn.dataset.tab;
            const instance = carouselInstances[panelId];
            if (instance) instance.reset();
        }
    });
});

/* ── Mobile Team Carousel ── */
const carouselInstances = {};
function destroyAllCarousels() {
    Object.values(carouselInstances).forEach(instance => {
        if (instance && typeof instance.destroy === 'function') instance.destroy();
    });
    Object.keys(carouselInstances).forEach(key => delete carouselInstances[key]);
}

function initMobileCarousel(gridId, dotsId, barId) {
    const grid = document.getElementById(gridId);
    const dots = document.getElementById(dotsId);
    const bar = document.getElementById(barId);
    if (!grid || !dots || !bar) return;

    const panelEl = grid.closest('.team-tab-panel');
    const panelId = panelEl ? panelEl.id : null;
    const existing = panelId ? carouselInstances[panelId] : null;
    if (existing && existing.active) return;
    if (existing && typeof existing.destroy === 'function') existing.destroy();

    const cards = Array.from(grid.querySelectorAll('.tmember-card'));
    const total = cards.length;
    let current = 0, timer = null;
    const DURATION = 5000;
    let destroyed = false;

    dots.innerHTML = '';
    cards.forEach((_, i) => {
        const d = document.createElement('button');
        d.className = 'cdot' + (i === 0 ? ' active' : '');
        d.setAttribute('aria-label', 'Slide ' + (i + 1));
        d.addEventListener('click', () => { goTo(i); resetTimer(); });
        dots.appendChild(d);
    });

    function updateDots() { dots.querySelectorAll('.cdot').forEach((d, i) => d.classList.toggle('active', i === current)); }
    function goTo(n) {
        if (destroyed) return;
        current = ((n % total) + total) % total;
        grid.style.transform = `translateX(-${current * 100}%)`;
        updateDots();
        bar.style.transition = 'none';
        bar.style.width = '0%';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                bar.style.transition = `width ${DURATION}ms linear`;
                bar.style.width = '100%';
            });
        });
    }
    function next() { goTo(current + 1); }
    function resetTimer() { clearInterval(timer); timer = setInterval(next, DURATION); }

    let touchStartX = 0;
    const handleTouchStart = e => { touchStartX = e.changedTouches[0].screenX; };
    const handleTouchEnd = e => {
        if (destroyed) return;
        const dx = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(dx) > 40) { goTo(current + (dx > 0 ? 1 : -1)); resetTimer(); }
    };
    grid.addEventListener('touchstart', handleTouchStart, { passive: true });
    grid.addEventListener('touchend', handleTouchEnd);

    function start() { goTo(0); resetTimer(); }
    function reset() { if (!destroyed) { clearInterval(timer); start(); } }
    function destroy() {
        destroyed = true; clearInterval(timer);
        grid.removeEventListener('touchstart', handleTouchStart);
        grid.removeEventListener('touchend', handleTouchEnd);
        grid.style.transform = ''; bar.style.transition = ''; bar.style.width = '';
    }

    start();
    if (panelId) carouselInstances[panelId] = { reset, destroy, active: true };
}

function setupCarousels() {
    if (window.innerWidth <= 480) {
        initMobileCarousel('carousel-grid-3', 'carousel-dots-3', 'carousel-bar-3');
        initMobileCarousel('carousel-grid-1', 'carousel-dots-1', 'carousel-bar-1');
        initMobileCarousel('carousel-grid-2', 'carousel-dots-2', 'carousel-bar-2');
    } else {
        destroyAllCarousels();
        ['carousel-grid-3','carousel-grid-1','carousel-grid-2'].forEach(id => {
            const g = document.getElementById(id);
            if (g) g.style.transform = '';
        });
        ['carousel-dots-3','carousel-dots-1','carousel-dots-2'].forEach(id => {
            const d = document.getElementById(id);
            if (d) d.innerHTML = '';
        });
        ['carousel-bar-3','carousel-bar-1','carousel-bar-2'].forEach(id => {
            const b = document.getElementById(id);
            if (b) { b.style.transition = ''; b.style.width = ''; }
        });
    }
}

setupCarousels();
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(setupCarousels, 200);
});

/* ── Fungsi Modal Instagram Simagang v3.5 ── */
function openInstagramModal(name, url) {
    document.getElementById('modalDeveloperName').textContent = name;
    document.getElementById('modalInstagramLink').href = url;
    document.getElementById('instagramModal').classList.add('open');
}
function closeInstagramModal(event) {
    document.getElementById('instagramModal').classList.remove('open');
}
</script>

@include('chatbot')

</body>
</html>