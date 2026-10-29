<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.partials.theme-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Institusi - Simagang</title>
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #eef2ff;
            min-height: 100vh;
        }

        /* ── Page wrapper ── */
        .page-wrap {
            min-height: 100vh;
            background: linear-gradient(145deg, #e8eeff 0%, #f0f4ff 50%, #e4ecff 100%);
            padding: 2.5rem 1.25rem;
        }

        /* ── Hero banner ── */
        .hero {
            border-radius: 20px;
            background: linear-gradient(110deg, #0f2a6e 0%, #1e3a8a 40%, #3b4fd8 80%, #4f46e5 100%);
            padding: 2rem 2.5rem 2.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(15, 42, 110, 0.22);
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 240px; height: 240px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -80px; left: 30%;
            width: 320px; height: 320px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }
        .hero-inner {
            position: relative; z-index: 1;
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 1.5rem;
            flex-wrap: wrap;
        }
        .hero-left { display: flex; align-items: center; gap: 1.25rem; }
        .hero-avatar {
            width: 64px; height: 64px; border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.25);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .hero-avatar i { font-size: 26px; color: #fff; }
        .hero-eyebrow {
            font-size: 11px; font-weight: 700;
            letter-spacing: 0.22em; text-transform: uppercase;
            color: rgba(255,255,255,0.6); margin-bottom: 6px;
        }
        .hero-title { font-size: 28px; font-weight: 800; color: #fff; line-height: 1.15; }
        .hero-sub { font-size: 14px; color: rgba(255,255,255,0.72); margin-top: 6px; max-width: 380px; line-height: 1.6; }
        .btn-login-hero {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 12px;
            background: rgba(255,255,255,0.12);
            border: 1.5px solid rgba(255,255,255,0.22);
            color: #fff; font-size: 13px; font-weight: 700;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
            align-self: flex-start;
            white-space: nowrap;
        }
        .btn-login-hero:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-1px);
            color: #fff;
        }

        /* ── Step progress ── */
        .progress-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.25rem 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(30,58,138,0.05), 0 4px 18px rgba(30,58,138,0.06);
            display: flex; align-items: center; gap: 1rem;
        }
        .steps { display: flex; align-items: center; flex: 1; }
        .step-item { display: flex; align-items: center; flex: 1; }
        .step-dot {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
            border: 2px solid #e2e8f0;
            background: #fff; color: #94a3b8;
            transition: all 0.3s ease;
        }
        .step-dot.active { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        .step-dot.done { background: #16a34a; border-color: #16a34a; color: #fff; }
        .step-text { margin-left: 8px; }
        .step-num { font-size: 10px; color: #94a3b8; }
        .step-label { font-size: 12px; font-weight: 600; color: #64748b; white-space: nowrap; }
        .step-label.active { color: #1e3a8a; }
        .step-label.done { color: #16a34a; }
        .step-line {
            flex: 1; height: 2px; margin: 0 10px;
            background: #e2e8f0; border-radius: 2px;
            transition: background 0.4s;
        }
        .step-line.done { background: #16a34a; }

        /* ── Alert banners ── */
        .alert-success {
            border-radius: 14px; padding: 14px 18px; margin-bottom: 1.25rem;
            background: #f0fdf4; border: 1.5px solid #86efac;
            color: #166534; font-size: 14px; font-weight: 600;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-error {
            border-radius: 14px; padding: 14px 18px; margin-bottom: 1.25rem;
            background: #fef2f2; border: 1.5px solid #fca5a5;
            color: #991b1b; font-size: 14px;
        }

        /* ── Section card ── */
        .section-card {
            background: #fff;
            border-radius: 20px;
            margin-bottom: 1.25rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(30,58,138,0.05), 0 4px 18px rgba(30,58,138,0.06);
            transition: box-shadow 0.25s;
        }
        .section-card:focus-within {
            box-shadow: 0 4px 8px rgba(30,58,138,0.08), 0 8px 30px rgba(30,58,138,0.10);
        }
        .section-header {
            display: flex; align-items: center; gap: 14px;
            padding: 1.25rem 1.75rem;
            background: #f8faff;
            border-bottom: 1.5px solid #e8eeff;
            cursor: pointer; user-select: none;
        }
        .section-icon {
            width: 40px; height: 40px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .section-icon.blue  { background: linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
        .section-icon.indigo{ background: linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; }
        .section-icon.teal  { background: linear-gradient(135deg,#0d9488,#0f766e); color:#fff; }
        .section-header h2 { font-size: 16px; font-weight: 700; color: #1e3a8a; }
        .section-header p  { font-size: 12px; color: #64748b; margin-top: 2px; }
        .chevron { margin-left: auto; color: #94a3b8; transition: transform 0.3s; font-size: 14px; }
        .chevron.open { transform: rotate(180deg); }
        .section-body { padding: 1.75rem; }
        .section-body.collapsed { display: none; }

        /* ── Form fields ── */
        .field { margin-bottom: 1.1rem; }
        .field:last-child { margin-bottom: 0; }
        .field-label {
            display: block; font-size: 12px; font-weight: 700;
            color: #475569; text-transform: uppercase; letter-spacing: 0.07em;
            margin-bottom: 7px;
        }
        .field-label .req { color: #ef4444; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; font-size: 15px; pointer-events: none;
        }
        .has-icon input,
        .has-icon select { padding-left: 42px; }
        .field-input {
            width: 100%; padding: 11px 16px; font-size: 14px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8faff;
            color: #1f2937;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.2s;
            outline: none;
        }
        .field-input:hover:not(:focus) { border-color: #c7d2fe; background: #fff; }
        .field-input:focus { border-color: #4f46e5; background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
        .field-error { font-size: 12px; color: #dc2626; margin-top: 5px; display: flex; align-items: center; gap: 5px; }

        /* ── Jenis pill selector ── */
        .pill-group { display: flex; gap: 10px; margin-top: 2px; }
        .jenis-pill {
            flex: 1; padding: 12px 10px; border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #f8faff; cursor: pointer;
            text-align: center; font-size: 14px; font-weight: 600;
            color: #64748b;
            transition: all 0.2s;
            display: flex; flex-direction: column; align-items: center; gap: 6px;
        }
        .jenis-pill i { font-size: 22px; }
        .jenis-pill:hover { border-color: #6366f1; color: #4f46e5; background: #eef2ff; }
        .jenis-pill.selected {
            border-color: #4f46e5; border-width: 2px;
            color: #4338ca; background: #eef2ff;
        }
        .jenis-pill.selected i { color: #4f46e5; }

        /* ── Kampus extra fields ── */
        .kampus-fields { display: none; margin-top: 1.1rem; }
        .kampus-fields.show { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* ── Password strength ── */
        .strength-bar {
            height: 5px; border-radius: 3px; margin-top: 8px;
            background: #f1f5f9; overflow: hidden;
        }
        .strength-fill {
            height: 100%; width: 0; border-radius: 3px;
            transition: width 0.35s ease, background 0.35s;
        }
        .strength-fill { font-size: 11px; margin-top: 5px; font-weight: 600; }
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 46px; }
        .eye-toggle {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #94a3b8; font-size: 16px; padding: 0;
            transition: color 0.2s;
        }
        .eye-toggle:hover { color: #4f46e5; }

        /* ── Role display ── */
        .role-display {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; border-radius: 12px;
            border: 1.5px solid #e2e8f0; background: #f8faff;
            font-size: 14px; color: #475569;
        }
        .role-badge {
            font-size: 11px; font-weight: 700; padding: 3px 10px;
            border-radius: 20px; background: #eef2ff; color: #4338ca;
            letter-spacing: 0.04em;
        }

        /* ── Info hint ── */
        .info-hint {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 16px; border-radius: 12px;
            background: #eff6ff; border: 1.5px solid #bfdbfe;
            font-size: 13px; color: #1e40af; line-height: 1.55;
            margin-bottom: 1.25rem;
        }
        .info-hint i { font-size: 15px; flex-shrink: 0; margin-top: 2px; }

        /* ── Footer actions ── */
        .form-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.75rem;
            background: #f8faff;
            border-top: 1.5px solid #e8eeff;
            gap: 1rem; flex-wrap: wrap;
        }
        .btn-back {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 700; color: #64748b;
            text-decoration: none; transition: color 0.2s;
        }
        .btn-back:hover { color: #1e293b; }
        .btn-submit {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 12px 26px; border-radius: 14px;
            background: linear-gradient(110deg,#1e3a8a,#3b4fd8);
            color: #fff; font-size: 14px; font-weight: 700;
            border: none; cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(59,79,216,0.25);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(59,79,216,0.35);
        }
        .btn-submit:active { transform: scale(0.98); }

        /* ── Grid helpers ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }

        /* ── WELCOME POPUP STYLES ── */
        .welcome-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .welcome-modal.open {
            opacity: 1;
            visibility: visible;
        }
        .welcome-modal-content {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            width: min(92%, 640px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }
        .welcome-modal.open .welcome-modal-content {
            transform: scale(1);
        }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .grid-2 { grid-template-columns: 1fr; }
            .kampus-fields.show { grid-template-columns: 1fr; }
            .hero { padding: 1.5rem 1.25rem 2rem; }
            .hero-title { font-size: 22px; }
            .section-body { padding: 1.25rem; }
            .step-text { display: none; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <div style="max-width:780px; margin:0 auto;">

        {{-- ── Alerts ── --}}
        @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-circle-check"></i>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error') || $errors->any())
        <div class="alert-error">
            @if(session('error'))
                <div style="display:flex;align-items:center;gap:8px;font-weight:700;margin-bottom:4px;">
                    <i class="fas fa-circle-exclamation"></i>{{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <ul style="list-style:disc;padding-left:22px;margin-top:4px;font-size:13px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        @endif

        {{-- ── Hero ── --}}
        <div class="hero">
            <div class="hero-inner">
                <div class="hero-left">
                    <div class="hero-avatar">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <div class="hero-eyebrow">Institusi Dashboard</div>
                        <div class="hero-title">Daftar Institusi</div>
                        <div class="hero-sub">Tambahkan institusi baru untuk keperluan magang dan kerjasama dengan Komdigi.</div>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="btn-login-hero">
                    <i class="fas fa-right-to-bracket"></i> Sudah punya akun? Login
                </a>
            </div>
        </div>

        {{-- ── Step Progress ── --}}
        <div class="progress-card">
            <div class="steps">
                <div class="step-item">
                    <div class="step-dot active" id="sd1">1</div>
                    <div class="step-text">
                        <div class="step-num">Langkah 1</div>
                        <div class="step-label active" id="sl1">Informasi institusi</div>
                    </div>
                    <div class="step-line" id="line1"></div>
                </div>
                <div class="step-item">
                    <div class="step-dot" id="sd2">2</div>
                    <div class="step-text">
                        <div class="step-num">Langkah 2</div>
                        <div class="step-label" id="sl2">Informasi admin</div>
                    </div>
                    <div class="step-line" id="line2"></div>
                </div>
                <div class="step-item">
                    <div class="step-dot" id="sd3">3</div>
                    <div class="step-text">
                        <div class="step-num">Langkah 3</div>
                        <div class="step-label" id="sl3">Keamanan</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Form ── --}}
        <form method="POST" action="{{ route('institusi.store') }}" enctype="multipart/form-data" id="mainForm">
            @csrf

            {{-- ── Section 1: Informasi Institusi ── --}}
            <div class="section-card">
                <div class="section-header" onclick="toggleSection('s1')">
                    <div class="section-icon blue"><i class="fas fa-building"></i></div>
                    <div>
                        <h2>Informasi Institusi</h2>
                        <p>Data resmi sekolah atau kampus Anda</p>
                    </div>
                    <i class="fas fa-chevron-down chevron open" id="s1-chev"></i>
                </div>
                <div class="section-body" id="s1-body">

                    <div class="field">
                        <label class="field-label">Nama Institusi <span class="req">*</span></label>
                        <div class="input-wrap has-icon">
                            <i class="fas fa-school input-icon"></i>
                            <input type="text" name="nama_institusi" id="f_nama_institusi"
                                value="{{ old('nama_institusi') }}"
                                placeholder="cth. SMK Negeri 1 Jakarta"
                                class="field-input" oninput="liveProgress()">
                        </div>
                        @error('nama_institusi')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field-label">Nomor Identitas (NPSN / Kode PT) <span class="req">*</span></label>
                        <div class="input-wrap has-icon">
                            <i class="fas fa-hashtag input-icon"></i>
                            <input type="text" name="nomor_identitas" id="f_nomor_identitas"
                                value="{{ old('nomor_identitas') }}"
                                placeholder="cth. 20101234"
                                class="field-input" required oninput="liveProgress()">
                        </div>
                        @error('nomor_identitas')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field-label">Jenis Institusi <span class="req">*</span></label>
                        <input type="hidden" name="jenis_institusi" id="f_jenis_institusi" value="{{ old('jenis_institusi') }}">
                        <div class="pill-group">
                            <div class="jenis-pill {{ old('jenis_institusi')=='sekolah'?'selected':'' }}" id="pill-sekolah"
                                 onclick="selectJenis('sekolah')">
                                <i class="fas fa-school"></i>
                                Sekolah
                            </div>
                            <div class="jenis-pill {{ old('jenis_institusi')=='kampus'?'selected':'' }}" id="pill-kampus"
                                 onclick="selectJenis('kampus')">
                                <i class="fas fa-university"></i>
                                Kampus / Universitas
                            </div>
                        </div>
                        @error('jenis_institusi')
                            <div class="field-error" style="margin-top:8px;"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kampus extra fields (shown conditionally) --}}
                    <div class="kampus-fields {{ old('jenis_institusi')=='kampus'?'show':'' }}" id="kampus-fields">
                        <div class="field" style="margin-bottom:0;">
                            <label class="field-label">Fakultas</label>
                            <div class="input-wrap has-icon">
                                <i class="fas fa-book input-icon"></i>
                                <input type="text" name="fakultas" value="{{ old('fakultas') }}"
                                    placeholder="cth. Fakultas Teknik"
                                    class="field-input">
                            </div>
                        </div>
                        <div class="field" style="margin-bottom:0;">
                            <label class="field-label">Departemen</label>
                            <div class="input-wrap has-icon">
                                <i class="fas fa-sitemap input-icon"></i>
                                <input type="text" name="departemen" value="{{ old('departemen') }}"
                                    placeholder="cth. Teknik Informatika"
                                    class="field-input">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Section 2: Informasi Admin ── --}}
            <div class="section-card">
                <div class="section-header" onclick="toggleSection('s2')">
                    <div class="section-icon indigo"><i class="fas fa-user-tie"></i></div>
                    <div>
                        <h2>Informasi Pribadi Admin</h2>
                        <p>Penanggung jawab institusi</p>
                    </div>
                    <i class="fas fa-chevron-down chevron open" id="s2-chev"></i>
                </div>
                <div class="section-body" id="s2-body">

                    <div class="field">
                        <label class="field-label">Nama Lengkap <span class="req">*</span></label>
                        <div class="input-wrap has-icon">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="nama_admin" id="f_nama_admin"
                                value="{{ old('nama_admin') }}"
                                placeholder="cth. Budi Santoso"
                                class="field-input" required oninput="liveProgress()">
                        </div>
                        @error('nama_admin')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label class="field-label">Email <span class="req">*</span></label>
                            <div class="input-wrap has-icon">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" id="f_email"
                                    value="{{ old('email') }}"
                                    placeholder="admin@institusi.sch.id"
                                    class="field-input" required oninput="liveProgress()">
                            </div>
                            @error('email')
                                <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label class="field-label">Nomor Telepon <span class="req">*</span></label>
                            <div class="input-wrap has-icon">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="text" name="no_hp" id="f_no_hp"
                                    value="{{ old('no_hp') }}"
                                    placeholder="08xx-xxxx-xxxx"
                                    class="field-input" required oninput="liveProgress()">
                            </div>
                            @error('no_hp')
                                <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Section 3: Keamanan ── --}}
            <div class="section-card">
                <div class="section-header" onclick="toggleSection('s3')">
                    <div class="section-icon teal"><i class="fas fa-shield-halved"></i></div>
                    <div>
                        <h2>Keamanan & Status Akun</h2>
                        <p>Pengaturan password dan peran pengguna</p>
                    </div>
                    <i class="fas fa-chevron-down chevron open" id="s3-chev"></i>
                </div>
                <div class="section-body" id="s3-body">

                    <div class="info-hint">
                        <i class="fas fa-circle-info"></i>
                        Gunakan kombinasi huruf besar, angka, dan simbol untuk password yang lebih aman. Minimal 8 karakter.
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label class="field-label">Password <span class="req">*</span></label>
                            <div class="input-wrap pw-wrap">
                                <input type="password" name="password" id="f_password"
                                    placeholder="Minimal 8 karakter"
                                    class="field-input" required
                                    oninput="checkStrength(this.value); liveProgress()">
                                <button type="button" class="eye-toggle" id="eyeBtn" onclick="togglePw()" aria-label="Tampilkan/sembunyikan password">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="strength-hint" id="strengthHint" style="color:#94a3b8;"></div>
                            @error('password')
                                <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="field-label">Role</label>
                            <div class="role-display">
                                <i class="fas fa-shield-check" style="font-size:18px;color:#4f46e5;"></i>
                                <span style="font-size:14px;color:#374151;font-weight:600;">Admin Institusi</span>
                                <span class="role-badge" style="margin-left:auto;">Default</span>
                            </div>
                            <input type="hidden" name="role" value="Admin Institusi">
                        </div>
                    </div>

                </div>

                {{-- ── Footer Actions ── --}}
                <div class="form-footer">
                    <a href="{{ route('landing') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-floppy-disk"></i> Simpan Data
                    </button>
                </div>

            </div>

        </form>
    </div>
</div>

<!-- ── WELCOME VIDEO MODAL ── -->
<div id="videoWelcomeModal" class="welcome-modal">
    <div class="welcome-modal-content">
        
        <!-- Header / Peringatan Penting -->
        <div class="text-center mb-4">
            <div class="w-14 h-14 bg-red-50 border border-red-200 text-red-500 rounded-full flex items-center justify-center mx-auto text-xl mb-2 animate-bounce">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-lg font-extrabold text-slate-800 leading-tight">PERINGATAN PENTING!</h3>
            
            <div class="mt-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs sm:text-sm font-bold px-4 py-2.5 rounded-xl shadow-md shadow-red-500/10">
                <i class="fas fa-university mr-1.5"></i> Formulir ini wajib diisi oleh pihak KAMPUS / SEKOLAH, BUKAN oleh Siswa / Mahasiswa!
            </div>
        </div>

        <!-- Video Player Frame (PERBAIKAN URL SEMAT YOUTUBE) -->
        <div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-inner bg-slate-950 border border-slate-200">
            <iframe id="tutorialVideoFrame" class="w-full h-full" 
                    src="https://www.youtube.com/embed/GorIEj-qp6A?enablejsapi=1&rel=0" 
                    title="Panduan Alur Pendaftaran Mitra" frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen>
            </iframe>
        </div>

        <p class="text-[11px] text-slate-400 text-center mt-2.5 leading-relaxed">
            Silakan tonton video alur panduan di atas terlebih dahulu agar tidak terjadi kesalahan validasi data institusi Anda.
        </p>

        <!-- Action Button -->
        <div class="flex items-center justify-center mt-5 pt-3.5 border-t border-slate-100">
            <button type="button" onclick="closeWelcomeModal()" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-md shadow-slate-800/10 flex items-center gap-2">
                Saya Mengerti, Lewati Panduan <i class="fas fa-arrow-right text-[10px]"></i>
            </button>
        </div>

    </div>
</div>

<script>
    // ── Welcome Video Modal Logic ──────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('videoWelcomeModal');
        if (modal) {
            setTimeout(() => {
                modal.classList.add('open');
            }, 300);
        }
    });

    function closeWelcomeModal() {
        const modal = document.getElementById('videoWelcomeModal');
        const videoFrame = document.getElementById('tutorialVideoFrame');
        
        if (modal) {
            modal.classList.remove('open');
        }
        
        // Menghentikan pemutaran video secara instan saat modal ditutup
        if (videoFrame) {
            const currentSrc = videoFrame.src;
            videoFrame.src = currentSrc;
        }
    }

    // ── Jenis institusi pill selector ──────────────────────────────
    let selectedJenis = '{{ old('jenis_institusi') }}';

    function selectJenis(val) {
        selectedJenis = val;
        document.getElementById('f_jenis_institusi').value = val;
        document.querySelectorAll('.jenis-pill').forEach(p => p.classList.remove('selected'));
        document.getElementById('pill-' + val).classList.add('selected');
        const kampus = document.getElementById('kampus-fields');
        if (val === 'kampus') {
            kampus.classList.add('show');
        } else {
            kampus.classList.remove('show');
        }
        liveProgress();
    }

    if (selectedJenis) selectJenis(selectedJenis);


    // ── Toggle section collapse ────────────────────────────────────
    function toggleSection(id) {
        const body = document.getElementById(id + '-body');
        const chev = document.getElementById(id + '-chev');
        const isOpen = !body.classList.contains('collapsed');
        body.classList.toggle('collapsed', isOpen);
        chev.classList.toggle('open', !isOpen);
    }


    // ── Password visibility toggle ─────────────────────────────────
    function togglePw() {
        const pw = document.getElementById('f_password');
        const icon = document.getElementById('eyeIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            pw.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }


    // ── Password strength meter ────────────────────────────────────
    function checkStrength(val) {
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const configs = [
            { pct: '0%',   color: 'transparent', label: '',               textColor: '#94a3b8' },
            { pct: '25%',  color: '#ef4444',      label: 'Lemah',        textColor: '#dc2626' },
            { pct: '50%',  color: '#f59e0b',      label: 'Cukup',        textColor: '#b45309' },
            { pct: '75%',  color: '#3b82f6',      label: 'Kuat',         textColor: '#1d4ed8' },
            { pct: '100%', color: '#16a34a',      label: 'Sangat kuat',  textColor: '#15803d' },
        ];
        const cfg = configs[score];
        const fill = document.getElementById('strengthFill');
        const hint = document.getElementById('strengthHint');
        fill.style.width = cfg.pct;
        fill.style.background = cfg.color;
        hint.textContent = val.length ? cfg.label : '';
        hint.style.color = cfg.textColor;
    }


    // ── Live step progress indicator ──────────────────────────────
    function liveProgress() {
        const s1ok = document.getElementById('f_nama_institusi').value.trim()
                  && document.getElementById('f_nomor_identitas').value.trim()
                  && selectedJenis;
        const s2ok = document.getElementById('f_nama_admin').value.trim()
                  && document.getElementById('f_email').value.trim()
                  && document.getElementById('f_no_hp').value.trim();
        const s3ok = document.getElementById('f_password').value.length >= 8;

        setStep(1, !!s1ok);
        setStep(2, !!s2ok);
        setStep(3, !!s3ok);

        if (!s1ok) activate(1);
        else if (!s2ok) activate(2);
        else if (!s3ok) activate(3);
    }

    function setStep(n, done) {
        const dot = document.getElementById('sd' + n);
        const lbl = document.getElementById('sl' + n);
        dot.classList.remove('active', 'done');
        lbl.classList.remove('active', 'done');
        if (done) {
            dot.classList.add('done');
            dot.innerHTML = '<i class="fas fa-check" style="font-size:12px;"></i>';
            lbl.classList.add('done');
        } else {
            dot.textContent = n;
        }
        if (n < 3) {
            const line = document.getElementById('line' + n);
            line.classList.toggle('done', done);
        }
    }

    function activate(n) {
        const dot = document.getElementById('sd' + n);
        const lbl = document.getElementById('sl' + n);
        if (!dot.classList.contains('done')) {
            dot.classList.add('active');
            lbl.classList.add('active');
        }
    }
</script>
</body>
</html>