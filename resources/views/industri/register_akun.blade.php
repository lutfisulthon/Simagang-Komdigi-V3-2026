<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.partials.theme-head')
    <title>Pendaftaran Industri - Simagang</title>
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(145deg, #e8eeff 0%, #f0f4ff 50%, #e4ecff 100%);
        }

        /* ── Page wrapper ── */
        .page-wrap {
            min-height: 100vh;
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
            box-shadow: 0 12px 40px rgba(15,42,110,0.22);
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
            align-self: flex-start; white-space: nowrap;
        }
        .btn-login-hero:hover { background: rgba(255,255,255,0.22); transform: translateY(-1px); color: #fff; }

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
        .step-dot.done   { background: #16a34a; border-color: #16a34a; color: #fff; }
        .step-text { margin-left: 8px; }
        .step-num { font-size: 10px; color: #94a3b8; }
        .step-lbl { font-size: 12px; font-weight: 600; color: #64748b; white-space: nowrap; }
        .step-lbl.active { color: #1e3a8a; }
        .step-lbl.done   { color: #16a34a; }
        .step-line {
            flex: 1; height: 2px; margin: 0 10px;
            background: #e2e8f0; border-radius: 2px;
            transition: background 0.4s;
        }
        .step-line.done { background: #16a34a; }

        /* ── Alerts ── */
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

        /* ── Section cards ── */
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
            font-size: 17px; flex-shrink: 0;
        }
        .section-icon.blue   { background: linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
        .section-icon.violet { background: linear-gradient(135deg,#8b5cf6,#6d28d9); color:#fff; }
        .section-header h2 { font-size: 16px; font-weight: 700; color: #1e3a8a; }
        .section-header p  { font-size: 12px; color: #64748b; margin-top: 2px; }
        .chevron { margin-left: auto; color: #94a3b8; transition: transform 0.3s; font-size: 14px; }
        .chevron.open { transform: rotate(180deg); }
        .section-body { padding: 1.75rem; }
        .section-body.collapsed { display: none; }

        /* ── Fields ── */
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
        .has-icon input { padding-left: 42px; }
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

        /* ── Password ── */
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
        .strength-bar {
            height: 5px; border-radius: 3px; margin-top: 8px;
            background: #f1f5f9; overflow: hidden;
        }
        .strength-fill {
            height: 100%; width: 0; border-radius: 3px;
            transition: width 0.35s ease, background 0.35s;
        }
        .strength-hint { font-size: 11px; margin-top: 5px; font-weight: 600; }

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
            letter-spacing: 0.04em; margin-left: auto;
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

        /* ── Requirements checklist ── */
        .req-list { list-style: none; padding: 0; margin: 10px 0 0; display: flex; flex-wrap: wrap; gap: 6px 14px; }
        .req-list li {
            font-size: 12px; color: #94a3b8;
            display: flex; align-items: center; gap: 5px;
            transition: color 0.25s;
        }
        .req-list li.met { color: #16a34a; }
        .req-list li i { font-size: 11px; }

        /* ── Grid ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }

        /* ── Footer ── */
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
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(59,79,216,0.35); }
        .btn-submit:active { transform: scale(0.98); }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .grid-2 { grid-template-columns: 1fr; }
            .hero { padding: 1.5rem 1.25rem 2rem; }
            .hero-title { font-size: 22px; }
            .section-body { padding: 1.25rem; }
            .step-text { display: none; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <div style="max-width:720px; margin:0 auto;">

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
                        <i class="fas fa-industry"></i>
                    </div>
                    <div>
                        <div class="hero-eyebrow">Industri Dashboard</div>
                        <div class="hero-title">Daftar Industri</div>
                        <div class="hero-sub">Tambahkan industri baru untuk keperluan magang dan kerjasama dengan Komdigi.</div>
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
                        <div class="step-lbl active" id="sl1">Informasi akun</div>
                    </div>
                    <div class="step-line" id="line1"></div>
                </div>
                <div class="step-item">
                    <div class="step-dot" id="sd2">2</div>
                    <div class="step-text">
                        <div class="step-num">Langkah 2</div>
                        <div class="step-lbl" id="sl2">Keamanan</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Form ── --}}
        <form method="POST" action="{{ route('industri.store') }}" enctype="multipart/form-data" id="mainForm">
            @csrf

            {{-- ── Section 1: Informasi Akun ── --}}
            <div class="section-card">
                <div class="section-header" onclick="toggleSection('s1')">
                    <div class="section-icon blue"><i class="fas fa-user-tie"></i></div>
                    <div>
                        <h2>Informasi Akun Industri</h2>
                        <p>Lengkapi profil dan data akun perusahaan Anda</p>
                    </div>
                    <i class="fas fa-chevron-down chevron open" id="s1-chev"></i>
                </div>
                <div class="section-body" id="s1-body">

                    <div class="field">
                        <label class="field-label">Nama <span class="req">*</span></label>
                        <div class="input-wrap has-icon">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="nama_admin" id="f_nama"
                                value="{{ old('nama_admin') }}"
                                placeholder="cth. Budi Santoso"
                                class="field-input" required oninput="liveProgress()">
                        </div>
                        @error('nama_admin')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field-label">Email <span class="req">*</span></label>
                        <div class="input-wrap has-icon">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="f_email"
                                value="{{ old('email') }}"
                                placeholder="admin@perusahaan.com"
                                class="field-input" required oninput="liveProgress()">
                        </div>
                        @error('email')
                            <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- ── Section 2: Keamanan ── --}}
            <div class="section-card">
                <div class="section-header" onclick="toggleSection('s2')">
                    <div class="section-icon violet"><i class="fas fa-shield-halved"></i></div>
                    <div>
                        <h2>Keamanan & Status</h2>
                        <p>Pengaturan password dan peran pengguna</p>
                    </div>
                    <i class="fas fa-chevron-down chevron open" id="s2-chev"></i>
                </div>
                <div class="section-body" id="s2-body">

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
                                <button type="button" class="eye-toggle" onclick="togglePw()" aria-label="Tampilkan/sembunyikan password">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="strength-hint" id="strengthHint" style="color:#94a3b8;"></div>
                            <ul class="req-list">
                                <li id="r-len"><i class="fas fa-circle-dot"></i> Min. 8 karakter</li>
                                <li id="r-upper"><i class="fas fa-circle-dot"></i> Huruf besar</li>
                                <li id="r-num"><i class="fas fa-circle-dot"></i> Angka</li>
                                <li id="r-sym"><i class="fas fa-circle-dot"></i> Simbol</li>
                            </ul>
                            @error('password')
                                <div class="field-error"><i class="fas fa-triangle-exclamation"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="field-label">Role</label>
                            <div class="role-display">
                                <i class="fas fa-shield-check" style="font-size:18px;color:#4f46e5;"></i>
                                <span style="font-size:14px;color:#374151;font-weight:600;">Admin Industri</span>
                                <span class="role-badge">Default</span>
                            </div>
                            <input type="hidden" name="role" value="Admin Industri">
                        </div>
                    </div>

                </div>

                {{-- ── Footer inside last card ── --}}
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

<script>
    // ── Toggle section collapse ────────────────────────────────────
    function toggleSection(id) {
        const body = document.getElementById(id + '-body');
        const chev = document.getElementById(id + '-chev');
        const isOpen = !body.classList.contains('collapsed');
        body.classList.toggle('collapsed', isOpen);
        chev.classList.toggle('open', !isOpen);
    }

    // ── Password visibility ────────────────────────────────────────
    function togglePw() {
        const pw = document.getElementById('f_password');
        const icon = document.getElementById('eyeIcon');
        pw.type = pw.type === 'password' ? 'text' : 'password';
        icon.className = pw.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
    }

    // ── Password strength ──────────────────────────────────────────
    function checkStrength(val) {
        const rules = {
            'r-len':   val.length >= 8,
            'r-upper': /[A-Z]/.test(val),
            'r-num':   /[0-9]/.test(val),
            'r-sym':   /[^A-Za-z0-9]/.test(val),
        };
        let score = Object.values(rules).filter(Boolean).length;
        Object.entries(rules).forEach(([id, met]) => {
            const el = document.getElementById(id);
            el.classList.toggle('met', met);
            el.querySelector('i').className = met ? 'fas fa-circle-check' : 'fas fa-circle-dot';
        });
        const configs = [
            { pct:'0%',   color:'transparent', label:'',             textColor:'#94a3b8' },
            { pct:'25%',  color:'#ef4444',     label:'Lemah',        textColor:'#dc2626' },
            { pct:'50%',  color:'#f59e0b',     label:'Cukup',        textColor:'#b45309' },
            { pct:'75%',  color:'#3b82f6',     label:'Kuat',         textColor:'#1d4ed8' },
            { pct:'100%', color:'#16a34a',     label:'Sangat kuat',  textColor:'#15803d' },
        ];
        const cfg = configs[score];
        document.getElementById('strengthFill').style.cssText = `width:${cfg.pct};background:${cfg.color}`;
        const hint = document.getElementById('strengthHint');
        hint.textContent = val.length ? cfg.label : '';
        hint.style.color = cfg.textColor;
    }

    // ── Live step progress ─────────────────────────────────────────
    function liveProgress() {
        const s1ok = document.getElementById('f_nama').value.trim()
                  && document.getElementById('f_email').value.trim();
        const s2ok = document.getElementById('f_password').value.length >= 8;
        setStep(1, !!s1ok);
        setStep(2, !!s2ok);
        if (!s1ok) activate(1);
        else if (!s2ok) activate(2);
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
        if (n < 2) {
            document.getElementById('line' + n).classList.toggle('done', done);
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