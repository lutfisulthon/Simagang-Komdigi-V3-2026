<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('layouts.partials.theme-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Institusi - Simagang</title>
    <link rel="shortcut icon" href="{{ url('storage/vendor/icon-komdigi.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/fa-subset.css') }}?v={{ @filemtime(public_path('vendor/fontawesome/fa-subset.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pages/daftarinstitusi.css') }}?v={{ @filemtime(public_path('css/pages/daftarinstitusi.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
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