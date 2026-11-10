@extends('layouts.app')

@section('title', 'Tambah Peserta Magang - Sistem Magang')

@push('styles')
<style>

    * { font-family: 'Plus Jakarta Sans', sans-serif; }
    .mono { font-family: 'DM Mono', monospace; }
    body { background: #f0f4ff; }

    .dash-bg {
        background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        min-height: 100vh;
    }

    /* ── Profile Strip (identical to dashboard) ── */
    .profile-strip {
        background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 50%, #4f46e5 100%);
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
    }
    .profile-strip::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .profile-strip::after {
        content: '';
        position: absolute;
        bottom: -80px; left: 30%;
        width: 300px; height: 300px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }

    /* ── Avatar ring ── */
    .avatar-ring {
        background: linear-gradient(135deg, #60a5fa, #818cf8);
        padding: 3px;
        border-radius: 9999px;
        display: inline-flex;
        flex-shrink: 0;
    }
    .avatar-inner {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        border-radius: 9999px;
        width: 80px; height: 80px;
        display: flex; align-items: center; justify-content: center;
    }

    /* ── Panel (identical to dashboard) ── */
    .panel {
        background: #fff;
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(30,58,138,0.06), 0 4px 20px rgba(30,58,138,0.06);
    }

    /* ── Section label (identical to dashboard) ── */
    .section-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 18px;
    }

    /* ── Section header with icon ── */
    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .section-icon {
        width: 40px; height: 40px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        color: white;
        flex-shrink: 0;
    }
    .section-icon.blue   { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .section-icon.indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .section-icon.violet { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .section-icon.green  { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .section-icon.amber  { background: linear-gradient(135deg, #f59e0b, #d97706); }

    .section-header-text h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1e3a8a;
        margin: 0;
    }
    .section-header-text p {
        font-size: 12px;
        color: #94a3b8;
        margin: 0;
    }

    /* ── Form inputs ── */
    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 6px;
    }
    .form-label .req { color: #ef4444; }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        background: #f8faff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 14px;
        color: #1f2937;
        transition: all 0.2s ease;
        outline: none;
        box-sizing: border-box;
    }
    .form-control:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .form-control:hover:not(:focus) {
        border-color: #c7d2fe;
        background: #fff;
    }
    .form-control.readonly {
        background: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
        border-color: #e2e8f0;
    }
    .form-control.readonly:hover {
        border-color: #e2e8f0;
        background: #f1f5f9;
    }
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 40px;
    }

    /* ── Error message ── */
    .form-error {
        font-size: 12px;
        color: #ef4444;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* ── Panel divider ── */
    .panel-divider {
        height: 1px;
        background: linear-gradient(90deg, #e8eeff 0%, #e2e8f0 50%, #e8eeff 100%);
        margin: 24px 0;
    }

    /* ── Checkbox toggle ── */
    .toggle-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 14px;
        background: #f0f4ff;
        border: 1.5px solid #e0e7ff;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .toggle-wrap:hover {
        background: #e8eeff;
        border-color: #a5b4fc;
    }
    .toggle-wrap input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #6366f1;
        cursor: pointer;
    }
    .toggle-wrap span {
        font-size: 14px;
        font-weight: 600;
        color: #3730a3;
    }

    /* ── Action buttons ── */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 12px;
        background: #f0f4ff;
        border: 1.5px solid #e0e7ff;
        color: #3b4fd8;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-back:hover {
        background: #e8eeff;
        border-color: #a5b4fc;
        transform: translateY(-1px);
        color: #3b4fd8;
        text-decoration: none;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        border-radius: 12px;
        background: linear-gradient(110deg, #1e3a8a, #3b4fd8);
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        letter-spacing: 0.02em;
    }
    .btn-submit:hover {
        box-shadow: 0 6px 20px rgba(59,79,216,0.35);
        transform: translateY(-2px);
    }
    .btn-submit:active {
        transform: translateY(0);
    }

    /* ── Progress steps ── */
    .steps-bar {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 28px;
    }
    .step-item {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    .step-circle {
        width: 30px; height: 30px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
        background: #e0e7ff;
        color: #6366f1;
    }
    .step-circle.active {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: #fff;
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
    }
    .step-label {
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
    }
    .step-label.active { color: #3730a3; }
    .step-connector {
        flex: 1;
        height: 2px;
        background: #e0e7ff;
        margin: 0 8px;
        border-radius: 999px;
    }

    /* ── Info pill (shows team name) ── */
    .info-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 999px;
        background: #ede9fe;
        color: #6d28d9;
        font-size: 12px;
        font-weight: 700;
        margin-top: 8px;
    }

    /* ── Animations (identical to dashboard) ── */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .anim-1 { animation: fadeSlideUp 0.5s ease both; }
    .anim-2 { animation: fadeSlideUp 0.5s ease 0.1s both; }
    .anim-3 { animation: fadeSlideUp 0.5s ease 0.2s both; }
    .anim-4 { animation: fadeSlideUp 0.5s ease 0.3s both; }
    .anim-5 { animation: fadeSlideUp 0.5s ease 0.4s both; }

    /* ── Responsive ── */
    @media (max-width: 640px) {
        .avatar-inner { width: 60px; height: 60px; }
        .panel { padding: 18px; }
        .steps-bar { display: none; }
        .grid-cols-2 { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 768px) {
        .md-grid-2 { grid-template-columns: 1fr !important; }
        .md-col-span-2 { grid-column: span 1 !important; }
    }
</style>
@endpush

@section('content')
<div class="dash-bg py-8">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

    {{-- ── PROFILE HEADER (mirrors dashboard profile-strip) ── --}}
    <div class="profile-strip anim-1">
        <div class="px-6 py-7 flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">
            {{-- Avatar --}}
            <div class="avatar-ring flex-shrink-0">
                <div class="avatar-inner">
                    <i class="fas fa-user-plus text-2xl text-white"></i>
                </div>
            </div>
            {{-- Identity --}}
            <div class="flex-1 text-center sm:text-left">
                <h1 class="text-xl font-bold text-white mb-1">Tambah Peserta Magang</h1>
                <p class="text-blue-300 text-sm mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Isi seluruh data berikut untuk mendaftarkan peserta magang baru
                </p>
            </div>
            {{-- Back button in header --}}
            <div class="flex-shrink-0">
                <a href="{{ route('industri.intern.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                        bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-all duration-200">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ── FORM ── --}}
    <form method="POST" action="{{ route('industri.intern.store') }}" enctype="multipart/form-data">
    @csrf

        {{-- ─ SECTION 1: Pilih Calon ─ --}}
        <div class="panel anim-2 mb-5">
            <p class="section-label">Langkah 1 dari 5</p>
            <div class="section-header">
                <div class="section-icon blue"><i class="fas fa-search"></i></div>
                <div class="section-header-text">
                    <h3>Pilih Calon Peserta Magang</h3>
                    <p>Pilih dari daftar calon yang sudah mengajukan permohonan</p>
                </div>
            </div>

            <div>
                <label class="form-label" for="calonSelect">
                    Nama Calon Peserta Magang <span class="req">*</span>
                </label>
                <select id="calonSelect" name="name" class="form-control">
                    <option value="">— Pilih Calon Peserta Magang —</option>
                    @foreach($calonMagang as $c)
                        <option value="{{ $c->nama }}"
                            data-id="{{ $c->id }}"
                            data-name="{{ $c->nama }}"
                            data-email="{{ $c->email }}"
                            data-gender="{{ $c->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}"
                            data-phone="{{ $c->no_telp }}"
                            data-soft-skill="{{ $c->soft_skill }}"
                            data-hard-skill="{{ $c->hard_skill }}"
                            @if ($c->pengajuan->institusi->jenis_institusi == 'sekolah')
                                data-education="SMA/SMK"
                            @elseif ($c->pengajuan->institusi->jenis_institusi == 'kampus')
                                data-education="S1/D4"
                            @endif
                            data-major="{{ $c->jurusan }}"
                            data-institution="{{ $c->pengajuan->institusi->nama_institusi }}"
                            data-purpose="{{ $c->pengajuan->keperluan }}"
                            data-team="{{ $c->pengajuan->lowongan->divisi ?? '' }}"
                            data-startdate="{{ $c->pengajuan->start_date }}"
                            data-enddate="{{ $c->pengajuan->end_date }}"
                        >{{ $c->nama }}</option>
                    @endforeach
                </select>
                @error('name')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
            </div>

            <input type="hidden" name="pengajuan_detail_id" id="detailIdInput">

            {{-- Auto-fill notice --}}
            <div id="autofillNotice" style="display:none;margin-top:14px;padding:12px 16px;border-radius:12px;background:#f0fdf4;border:1.5px solid #bbf7d0;color:#15803d;font-size:13px;font-weight:600;">
                <i class="fas fa-check-circle mr-2"></i>
                Data otomatis terisi dari pengajuan. Periksa kembali sebelum menyimpan.
            </div>
        </div>

        {{-- ─ SECTION 2: Informasi Pribadi ─ --}}
        <div class="panel anim-3 mb-5">
            <p class="section-label">Langkah 2 dari 5</p>
            <div class="section-header">
                <div class="section-icon indigo"><i class="fas fa-user"></i></div>
                <div class="section-header-text">
                    <h3>Informasi Pribadi</h3>
                    <p>Data diri dan kontak Peserta Magang</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label" for="emailInput">Email <span class="req">*</span></label>
                    <input type="email" id="emailInput" name="email" value="{{ old('email') }}" required
                            class="form-control" placeholder="contoh@email.com">
                    @error('email')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="phoneInput">Nomor Telepon</label>
                    <input type="text" id="phoneInput" name="phone" value="{{ old('phone') }}"
                        class="form-control" placeholder="08xxxxxxxxxx">
                    @error('phone')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="genderInput">Jenis Kelamin <span class="req">*</span></label>
                    <select name="gender" required id="genderInput" class="form-control">
                        <option value="">— Pilih —</option>
                        <option value="Laki-laki" {{ old('gender')=='Laki-laki'?'selected':'' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender')=='Perempuan'?'selected':'' }}>Perempuan</option>
                    </select>
                    @error('gender')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ─ SECTION 3: Informasi Akademik ─ --}}
        <div class="panel anim-3 mb-5">
            <p class="section-label">Langkah 3 dari 5</p>
            <div class="section-header">
                <div class="section-icon violet"><i class="fas fa-graduation-cap"></i></div>
                <div class="section-header-text">
                    <h3>Informasi Akademik</h3>
                    <p>Data pendidikan dan institusi asal</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label" for="educationInput">Jenjang Pendidikan <span class="req">*</span></label>
                    <select name="education_level" required id="educationInput" class="form-control">
                        <option value="">— Pilih —</option>
                        <option value="SMA/SMK" {{ old('education_level')=='SMA/SMK'?'selected':'' }}>SMA/SMK</option>
                        <option value="S1/D4" {{ old('education_level')=='S1/D4'?'selected':'' }}>S1/D4</option>
                    </select>
                    @error('education_level')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="majorInput">Jurusan / Program Studi</label>
                    <input type="text" name="major" value="{{ old('major') }}" id="majorInput"
                            class="form-control" placeholder="cth: Teknik Informatika">
                    @error('major')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div style="grid-column: span 2;" class="md-col-span-2">
                    <label class="form-label" for="institutionInput">Institusi <span class="req">*</span></label>
                    <input type="text" name="institution" value="{{ old('institution') }}" id="institutionInput" required
                            class="form-control" placeholder="Nama sekolah / universitas">
                    @error('institution')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div style="grid-column: span 2;" class="md-col-span-2">
                    <label class="form-label" for="purposeInput">Keperluan Magang</label>
                    <select name="purpose" id="purposeInput" class="form-control">
                        <option value="">— Pilih Keperluan —</option>
                        @foreach(['Magang','KKN Profesi','PKL','Praktek Industri','Magang Industri','Guru Magang Industri','Job on Training'] as $p)
                            <option value="{{ $p }}" {{ old('purpose')==$p?'selected':'' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('purpose')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ─ SECTION 4: Penempatan ─ --}}
        {{-- <div class="panel anim-4 mb-5">
            <p class="section-label">Langkah 4 dari 5</p>
            <div class="section-header">
                <div class="section-icon green"><i class="fas fa-users"></i></div>
                <div class="section-header-text">
                    <h3>Penempatan</h3>
                    <p>Mentor dan tim yang ditugaskan untuk peserta ini</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label" for="mentorSelect">Mentor <span class="req">*</span></label>
                    <select name="mentor_id" id="mentorSelect" required class="form-control">
                        <option value="">— Pilih Mentor —</option>
                        @foreach($mentors as $mentor)
                            <option value="{{ $mentor->id }}"
                                    data-team-id="{{ $mentor->team_id }}"
                                    data-team="{{ $mentor->team?->name ?? 'Belum masuk dalam tim' }}"
                                    {{ old('mentor_id') == $mentor->id ? 'selected' : '' }}>
                                {{ $mentor->name }}{{ $mentor->position ? ' – '.$mentor->position : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('mentor_id')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Tim Penempatan</label>
                    <input type="text" id="teamDisplay" readonly
                            value="Pilih mentor terlebih dahulu"
                            class="form-control readonly">
                    <div id="teamPill" style="display:none;" class="info-pill">
                        <i class="fas fa-layer-group text-xs"></i>
                        <span id="teamPillText"></span>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- ─ SECTION 5: Foto, Periode & Keamanan ─ --}}
        <div class="panel anim-4 mb-5">
            <p class="section-label">Langkah 5 dari 5</p>
            <div class="section-header">
                <div class="section-icon amber"><i class="fas fa-calendar-alt"></i></div>
                <div class="section-header-text">
                    <h3>Foto, Periode & Keamanan</h3>
                    <p>Upload foto, tentukan periode magang, dan atur akses login</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">

                {{-- Pass Foto --}}
                <div style="grid-column: span 2;" class="md-col-span-2">
                    <label class="form-label">Pass Foto <span class="req">*</span></label>
                    <div style="position:relative;">
                        <input type="file" name="photo" id="photoInput" accept="image/*" required
                                style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;z-index:2;">
                        <div id="photoDropzone" style="
                            border: 2px dashed #c7d2fe;
                            border-radius: 14px;
                            padding: 28px 20px;
                            text-align: center;
                            background: #f8faff;
                            transition: all 0.2s ease;
                            cursor: pointer;
                        ">
                            <i class="fas fa-cloud-upload-alt text-3xl mb-3" style="color:#6366f1;display:block;"></i>
                            <p id="photoLabel" style="font-size:13px;font-weight:600;color:#6366f1;margin:0;">Klik atau seret foto ke sini</p>
                            <p style="font-size:11px;color:#94a3b8;margin-top:4px;">PNG, JPG, JPEG — maks. 5MB</p>
                        </div>
                    </div>
                    @error('photo')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal Masuk --}}
                <div>
                    <label class="form-label" for="startDateInput">Tanggal Masuk <span class="req">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required id="startDateInput"
                            class="form-control">
                    @error('start_date')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal Keluar --}}
                <div>
                    <label class="form-label" for="endDateInput">Tanggal Keluar <span class="req">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required id="endDateInput"
                            class="form-control">
                    @error('end_date')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="form-label" for="passwordInput">Password Login <span class="req">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="passwordInput" required
                            class="form-control" placeholder="Min. 8 karakter"
                            style="padding-right:44px;">
                        <button type="button" id="togglePwd"
                                style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;font-size:15px;padding:0;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                {{-- Status Aktif --}}
                <div style="display:flex;align-items:flex-end;">
                    <div style="width:100%;">
                        <label class="form-label">Status Peserta</label>
                        <label class="toggle-wrap" for="isActiveCheck">
                            <input type="checkbox" name="is_active" id="isActiveCheck" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <span><i class="fas fa-circle-check mr-2" style="color:#22c55e;"></i>Tandai sebagai Aktif</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- SECTION: Hard Skill dan Soft Skill --}}
        <div class="panel anim-2 mb-5">
            <p class="section-label">Hard Skill dan Soft Skill</p>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label">Hard Skill</label>
                    <textarea id="hardSkillDisplay" name="hard_skill" readonly class="form-control readonly" placeholder="Pilih calon untuk melihat hard skill" rows="3"></textarea>
                </div>
                <div>
                    <label class="form-label">Soft Skill</label>
                    <textarea id="softSkillDisplay" name="soft_skill" readonly class="form-control readonly" placeholder="Pilih calon untuk melihat soft skill" rows="3"></textarea>
                </div>
            </div>
        </div>

        {{-- ─ FOOTER ACTIONS ─ --}}
        <div class="panel anim-5" style="padding:20px 28px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
                <a href="{{ route('industri.intern.index') }}" class="btn-back">
                     <i class="fas fa-arrow-left text-xs"></i>
                     Batal & Kembali
                </a>
                <button type="submit" class="btn-submit">
                     <i class="fas fa-save"></i>
                     Simpan Data Peserta
                </button>
            </div>
        </div>

    </form>

</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Referensi elemen ── */
    const calonSelect       = document.getElementById('calonSelect');
    const autofillNotice    = document.getElementById('autofillNotice');
    const softSkillDisplay  = document.getElementById('softSkillDisplay');
    const hardSkillDisplay  = document.getElementById('hardSkillDisplay');

    /* ── Calon → auto-fill semua field ── */
    calonSelect.addEventListener('change', function () {
        const sel = this.options[this.selectedIndex];
        if (!this.value) return;

        document.getElementById('detailIdInput').value    = sel.dataset.id          || '';
        document.getElementById('emailInput').value       = sel.dataset.email        || '';
        document.getElementById('phoneInput').value       = sel.dataset.phone        || '';
        document.getElementById('majorInput').value       = sel.dataset.major        || '';
        document.getElementById('institutionInput').value = sel.dataset.institution  || '';
        document.getElementById('startDateInput').value   = sel.dataset.startdate    || '';
        document.getElementById('endDateInput').value     = sel.dataset.enddate      || '';

        // Gender
        const gender = (sel.dataset.gender || '').trim();
        [...document.getElementById('genderInput').options].forEach(o => {
            o.selected = o.value === gender;
        });

        // Keperluan
        const purpose = (sel.dataset.purpose || '').trim();
        [...document.getElementById('purposeInput').options].forEach(o => {
            o.selected = o.value === purpose;
        });

        // Jenjang pendidikan
        document.getElementById('educationInput').value = sel.dataset.education || '';

        // Hard skill & Soft skill
        softSkillDisplay.value = sel.dataset.softSkill || '';
        hardSkillDisplay.value = sel.dataset.hardSkill || '';

        autofillNotice.style.display = 'block';
    });

    /* ── Photo dropzone label ── */
    const photoInput = document.getElementById('photoInput');
    const photoLabel = document.getElementById('photoLabel');
    const dropzone   = document.getElementById('photoDropzone');

    photoInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            photoLabel.textContent = '✓ ' + this.files[0].name;
            dropzone.style.borderColor = '#22c55e';
            dropzone.style.background  = '#f0fdf4';
            dropzone.querySelector('i').style.color = '#22c55e';
        }
    });

    /* ── Password toggle ── */
    const togglePwd = document.getElementById('togglePwd');
    const pwdInput  = document.getElementById('passwordInput');
    const eyeIcon   = document.getElementById('eyeIcon');

    togglePwd.addEventListener('click', () => {
        const isText = pwdInput.type === 'text';
        pwdInput.type = isText ? 'password' : 'text';
        eyeIcon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

});
</script>
@endpush
@endsection