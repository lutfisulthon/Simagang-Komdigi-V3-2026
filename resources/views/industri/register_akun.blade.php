<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.partials.theme-head')
    <title>Pendaftaran Industri - Simagang</title>
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/fa-subset.css') }}?v={{ @filemtime(public_path('vendor/fontawesome/fa-subset.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pages/industri-register-akun.css') }}?v={{ @filemtime(public_path('css/pages/industri-register-akun.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
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