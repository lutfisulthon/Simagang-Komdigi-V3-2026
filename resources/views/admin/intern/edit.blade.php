@extends('layouts.app')

@section('title', 'Edit Peserta Magang - Sistem Magang')

@push('styles')
<style>

    * { font-family: 'Plus Jakarta Sans', sans-serif; }
    .mono { font-family: 'DM Mono', monospace; }
    body { background: #f0f4ff; }

    .dash-bg {
        background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        min-height: 100vh;
    }

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
    .avatar-photo {
        width: 80px; height: 80px;
        border-radius: 9999px;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.4);
    }

    .panel {
        background: #fff;
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(30,58,138,0.06), 0 4px 20px rgba(30,58,138,0.06);
    }

    .section-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 18px;
    }

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
    .section-icon.red    { background: linear-gradient(135deg, #ef4444, #dc2626); }

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
    .form-control.readonly:hover { border-color: #e2e8f0; background: #f1f5f9; }

    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 40px;
    }

    .form-error {
        font-size: 12px;
        color: #ef4444;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .photo-current {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        border-radius: 14px;
        background: #f0f4ff;
        border: 1.5px solid #e0e7ff;
        margin-bottom: 16px;
    }
    .photo-current img {
        width: 64px; height: 64px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #c7d2fe;
        flex-shrink: 0;
    }
    .photo-current-info p {
        font-size: 13px;
        font-weight: 700;
        color: #3730a3;
        margin: 0 0 2px;
    }
    .photo-current-info span {
        font-size: 11px;
        color: #94a3b8;
    }

    .photo-dropzone {
        border: 2px dashed #c7d2fe;
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
        background: #f8faff;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
    }
    .photo-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
        z-index: 2;
    }
    .photo-dropzone.changed {
        border-color: #22c55e;
        background: #f0fdf4;
    }
    .photo-dropzone.changed i { color: #22c55e !important; }

    .hint-text {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

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
        width: 100%;
    }
    .toggle-wrap:hover { background: #e8eeff; border-color: #a5b4fc; }
    .toggle-wrap input[type="checkbox"] {
        width: 18px; height: 18px;
        accent-color: #6366f1;
        cursor: pointer;
        flex-shrink: 0;
    }
    .toggle-wrap span {
        font-size: 14px;
        font-weight: 600;
        color: #3730a3;
    }

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
    .btn-submit:active { transform: translateY(0); }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 100;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: #fff;
        border-radius: 20px;
        padding: 28px;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        animation: modalIn 0.25s ease both;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.93) translateY(10px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .anim-1 { animation: fadeSlideUp 0.5s ease both; }
    .anim-2 { animation: fadeSlideUp 0.5s ease 0.1s both; }
    .anim-3 { animation: fadeSlideUp 0.5s ease 0.2s both; }
    .anim-4 { animation: fadeSlideUp 0.5s ease 0.3s both; }
    .anim-5 { animation: fadeSlideUp 0.5s ease 0.4s both; }

    @media (max-width: 768px) {
        .md-grid-2 { grid-template-columns: 1fr !important; }
        .md-col-span-2 { grid-column: span 1 !important; }
        .panel { padding: 18px; }
    }
    @media (max-width: 640px) {
        .avatar-inner { width: 60px; height: 60px; }
        .avatar-photo { width: 60px; height: 60px; }
    }
</style>
@endpush

@section('content')
<div class="dash-bg py-8">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

    {{-- ── PROFILE HEADER ── --}}
    <div class="profile-strip anim-1">
        <div class="px-6 py-7 flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">

            @if($intern->photo_path)
                <div class="avatar-ring flex-shrink-0">
                    <img src="{{ url('storage/' . $intern->photo_path) }}"
                        alt="{{ $intern->name }}"
                        class="avatar-photo" loading="lazy" decoding="async">
                </div>
            @else
                <div class="avatar-ring flex-shrink-0">
                    <div class="avatar-inner">
                        <i class="fas fa-user-edit text-2xl text-white"></i>
                    </div>
                </div>
            @endif

            <div class="flex-1 text-center sm:text-left">
                <h1 class="text-xl font-bold text-white mb-1">Edit Data Peserta</h1>
                <p class="text-blue-200 font-semibold text-base">{{ $intern->name }}</p>
                <p class="text-blue-300 text-sm mt-1">
                    <i class="fas fa-university mr-1"></i>{{ $intern->institution }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ \Carbon\Carbon::parse($intern->start_date)->locale('id')->translatedFormat('d F Y') }}
                    –
                    {{ \Carbon\Carbon::parse($intern->end_date)->locale('id')->translatedFormat('d F Y') }}
                </p>
            </div>

            <div class="flex-shrink-0 flex flex-col items-center sm:items-end gap-3">
                @if($intern->is_active)
                    <span style="background:rgba(34,197,94,0.2);color:#86efac;border:1px solid rgba(134,239,172,0.3);padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;">
                        <i class="fas fa-circle text-xs mr-1"></i> Aktif
                    </span>
                @else
                    <span style="background:rgba(239,68,68,0.2);color:#fca5a5;border:1px solid rgba(252,165,165,0.3);padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;">
                        <i class="fas fa-circle text-xs mr-1"></i> Tidak Aktif
                    </span>
                @endif
                <a href="{{ route('admin.intern.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                        bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-all duration-200">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ── FORM ── --}}
    <form id="edit-intern-form" method="POST" action="{{ route('admin.intern.update', $intern) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

        {{-- ─ SECTION 1: Informasi Pribadi ─ --}}
        <div class="panel anim-2 mb-5">
            <p class="section-label">Bagian 1 dari 5</p>
            <div class="section-header">
                <div class="section-icon blue"><i class="fas fa-user"></i></div>
                <div class="section-header-text">
                    <h3>Informasi Pribadi</h3>
                    <p>Data diri dan kontak Peserta Magang</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label" for="name">Nama Lengkap <span class="req">*</span></label>
                    <input type="text" name="name" id="name"
                        value="{{ old('name', $intern->name) }}" required
                        class="form-control" placeholder="Nama lengkap peserta">
                    @error('name')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="email">Email <span class="req">*</span></label>
                    <input type="email" name="email" id="email"
                        value="{{ old('email', $intern->user->email) }}" required
                        class="form-control" placeholder="contoh@email.com">
                    @error('email')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="gender">Jenis Kelamin <span class="req">*</span></label>
                    <select name="gender" id="gender" required class="form-control">
                        <option value="">— Pilih —</option>
                        <option value="Laki-laki" {{ old('gender', $intern->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan"  {{ old('gender', $intern->gender) == 'Perempuan'  ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="phone">Nomor Telepon</label>
                    <input type="text" name="phone" id="phone"
                        value="{{ old('phone', $intern->phone) }}"
                        class="form-control" placeholder="08xxxxxxxxxx">
                    @error('phone')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ─ SECTION 2: Informasi Akademik ─ --}}
        <div class="panel anim-3 mb-5">
            <p class="section-label">Bagian 2 dari 5</p>
            <div class="section-header">
                <div class="section-icon violet"><i class="fas fa-graduation-cap"></i></div>
                <div class="section-header-text">
                    <h3>Informasi Akademik</h3>
                    <p>Data pendidikan dan institusi asal</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label" for="education_level">Jenjang Pendidikan <span class="req">*</span></label>
                    <select name="education_level" id="education_level" required class="form-control">
                        <option value="">— Pilih —</option>
                        <option value="SMA/SMK" {{ old('education_level', $intern->education_level) == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                        <option value="S1/D4"   {{ old('education_level', $intern->education_level) == 'S1/D4'   ? 'selected' : '' }}>S1/D4</option>
                    </select>
                    @error('education_level')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="major">Jurusan / Program Studi</label>
                    <input type="text" name="major" id="major"
                        value="{{ old('major', $intern->major) }}"
                        class="form-control" placeholder="cth: Teknik Informatika">
                    @error('major')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div style="grid-column:span 2;" class="md-col-span-2">
                    <label class="form-label" for="institution">Institusi <span class="req">*</span></label>
                    <input type="text" name="institution" id="institution"
                            value="{{ old('institution', $intern->institution) }}" required
                            class="form-control" placeholder="Nama sekolah / universitas">
                    @error('institution')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div style="grid-column:span 2;" class="md-col-span-2">
                    <label class="form-label" for="purpose">Keperluan Magang</label>
                    <select name="purpose" id="purpose" class="form-control">
                        <option value="">— Pilih Keperluan (Opsional) —</option>
                        @foreach(['Magang','KKN Profesi','PKL','Praktek Industri','Magang Industri','Guru Magang Industri','Job on Training'] as $p)
                            <option value="{{ $p }}" {{ old('purpose', $intern->purpose) == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('purpose')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ─ SECTION 3: Penempatan ─ --}}
        <div class="panel anim-3 mb-5">
            <p class="section-label">Bagian 3 dari 5</p>
            <div class="section-header">
                <div class="section-icon green"><i class="fas fa-users"></i></div>
                <div class="section-header-text">
                    <h3>Penempatan</h3>
                    <p>Mentor dan tim yang ditugaskan untuk peserta ini</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label" for="mentorSelect">Mentor</label>
                    <select name="mentor_id" id="mentorSelect" class="form-control">
                        <option value="">— Pilih Mentor (Opsional) —</option>
                        @foreach($mentors as $mentor)
                            <option value="{{ $mentor->id }}"
                                    data-team="{{ $mentor->team?->name ?? 'Belum masuk dalam tim' }}"
                                    {{ old('mentor_id', $intern->mentor_id) == $mentor->id ? 'selected' : '' }}>
                                {{ $mentor->name }}{{ $mentor->position ? ' – '.$mentor->position : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('mentor_id')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Tim Penempatan</label>
                    <input type="text" id="teamDisplay" readonly
                            value="{{ $intern->mentor?->team?->name ?? 'Belum masuk dalam tim' }}"
                            class="form-control readonly">
                    <div id="teamPill" class="info-pill" style="{{ $intern->mentor?->team ? '' : 'display:none;' }}">
                        <i class="fas fa-layer-group text-xs"></i>
                        <span id="teamPillText">{{ $intern->mentor?->team?->name ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─ SECTION 4: Foto & Periode ─ --}}
        <div class="panel anim-4 mb-5">
            <p class="section-label">Bagian 4 dari 5</p>
            <div class="section-header">
                <div class="section-icon amber"><i class="fas fa-calendar-alt"></i></div>
                <div class="section-header-text">
                    <h3>Foto & Periode Magang</h3>
                    <p>Update foto profil dan rentang waktu magang</p>
                </div>
            </div>

            @if($intern->photo_path)
                <div class="photo-current">
                    <img src="{{ url('storage/' . $intern->photo_path) }}" alt="Foto {{ $intern->name }}" loading="lazy" decoding="async">
                    <div class="photo-current-info">
                        <p><i class="fas fa-image mr-1"></i> Foto profil tersimpan</p>
                        <span>Upload foto baru di bawah untuk menggantinya</span>
                    </div>
                </div>
            @endif

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">

                <div style="grid-column:span 2;" class="md-col-span-2">
                    <label class="form-label">
                        {{ $intern->photo_path ? 'Ganti Pass Foto' : 'Pass Foto' }}
                        @if(!$intern->photo_path)<span class="req">*</span>@endif
                    </label>
                    <div class="photo-dropzone" id="photoDropzone">
                        <input type="file" name="photo" id="photoInput" accept="image/*"
                            {{ !$intern->photo_path ? 'required' : '' }}>
                        <i class="fas fa-cloud-upload-alt text-3xl mb-3" id="photoIcon"
                            style="color:#6366f1;display:block;"></i>
                        <p id="photoLabel" style="font-size:13px;font-weight:600;color:#6366f1;margin:0;">
                            Klik atau seret foto ke sini
                        </p>
                        <p style="font-size:11px;color:#94a3b8;margin-top:4px;">PNG, JPG, JPEG — maks. 2MB</p>
                    </div>
                    @error('photo')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="start_date">Tanggal Masuk <span class="req">*</span></label>
                    <input type="date" name="start_date" id="start_date"
                        value="{{ old('start_date', $intern->start_date->format('Y-m-d')) }}" required
                        class="form-control">
                    @error('start_date')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="end_date">Tanggal Keluar <span class="req">*</span></label>
                    <input type="date" name="end_date" id="end_date"
                        value="{{ old('end_date', $intern->end_date->format('Y-m-d')) }}" required
                        class="form-control">
                    @error('end_date')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ─ SECTION 5: Keamanan & Status ─ --}}
        <div class="panel anim-4 mb-5">
            <p class="section-label">Bagian 5 dari 5</p>
            <div class="section-header">
                <div class="section-icon red"><i class="fas fa-shield-alt"></i></div>
                <div class="section-header-text">
                    <h3>Keamanan & Status</h3>
                    <p>Ubah password dan atur status keaktifan peserta</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label" for="password">Password Baru</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="password"
                            class="form-control" placeholder="Kosongkan jika tidak ingin mengubah"
                            style="padding-right:44px;">
                        <button type="button" id="togglePwd"
                                style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;font-size:15px;padding:0;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <p class="hint-text"><i class="fas fa-info-circle text-xs"></i> Kosongkan jika tidak ingin mengubah password. Min. 8 karakter.</p>
                    @error('password')<p class="form-error"><i class="fas fa-exclamation-circle text-xs"></i>{{ $message }}</p>@enderror
                </div>

                <div style="display:flex;align-items:flex-end;">
                    <div style="width:100%;">
                        <label class="form-label">Status Peserta</label>
                        <label class="toggle-wrap" for="is_active">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $intern->is_active) ? 'checked' : '' }}>
                            <span><i class="fas fa-circle-check mr-2" style="color:#22c55e;"></i>Tandai sebagai Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- SECTION: Hard Skill dan Soft Skill --}}
        <div class="panel anim-4 mb-5">
            <p class="section-label">Hard Skill dan Soft Skill</p>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;" class="md-grid-2">
                <div>
                    <label class="form-label">Hard Skill</label>
                    <textarea name="hard_skill" class="form-control" placeholder="Masukkan hard skill" rows="3">{{ old('hard_skill', $intern->hard_skill) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Soft Skill</label>
                    <textarea name="soft_skill" class="form-control" placeholder="Masukkan soft skill" rows="3">{{ old('soft_skill', $intern->soft_skill) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ─ FOOTER ACTIONS ─ --}}
        <div class="panel anim-5" style="padding:20px 28px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
                <a href="{{ route('admin.intern.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Batal & Kembali
                </a>
                <button type="button" id="btn-submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>

    </form>

</div>
</div>

{{-- ── CONFIRMATION MODAL ── --}}
<div id="confirm-modal" class="modal-overlay">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:center;width:52px;height:52px;margin:0 auto 16px;background:#eff6ff;border-radius:50%;">
            <i class="fas fa-save" style="color:#3b4fd8;font-size:20px;"></i>
        </div>
        <h3 style="text-align:center;font-size:18px;font-weight:700;color:#1e3a8a;margin:0 0 8px;">Konfirmasi Simpan</h3>
        <p style="text-align:center;color:#64748b;font-size:14px;margin:0 0 24px;">Apakah Anda yakin ingin menyimpan perubahan ini? Perubahan akan langsung diterapkan.</p>
        <div style="display:flex;gap:12px;">
            <button id="btn-cancel" type="button"
                style="flex:1;padding:12px;border-radius:12px;background:#f1f5f9;border:1.5px solid #e2e8f0;font-size:13px;font-weight:600;color:#475569;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;">
                <i class="fas fa-times mr-1"></i> Batal
            </button>
            <button id="btn-confirm" type="button"
                style="flex:1;padding:12px;border-radius:12px;background:linear-gradient(110deg,#1e3a8a,#3b4fd8);border:none;font-size:13px;font-weight:700;color:#fff;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;">
                <i class="fas fa-save mr-1"></i> Ya, Simpan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('confirm-modal');
    const form  = document.getElementById('edit-intern-form');

    // Buka modal saat tombol submit diklik
    document.getElementById('btn-submit').addEventListener('click', function () {
        modal.classList.add('active');
    });

    // Tutup modal - tombol batal
    document.getElementById('btn-cancel').addEventListener('click', function () {
        modal.classList.remove('active');
    });

    // Konfirmasi - submit form
    document.getElementById('btn-confirm').addEventListener('click', function () {
        modal.classList.remove('active');
        form.submit();
    });

    // Tutup modal - klik backdrop
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('active');
    });

    // Mentor → Tim display
    const mentorSelect = document.getElementById('mentorSelect');
    const teamDisplay  = document.getElementById('teamDisplay');
    const teamPill     = document.getElementById('teamPill');
    const teamPillText = document.getElementById('teamPillText');

    function updateTeam() {
        const opt = mentorSelect.options[mentorSelect.selectedIndex];
        if (!mentorSelect.value) {
            teamDisplay.value = 'Belum masuk dalam tim';
            teamPill.style.display = 'none';
            return;
        }
        const teamName = opt.getAttribute('data-team') || 'Belum masuk dalam tim';
        teamDisplay.value        = teamName;
        teamPillText.textContent = teamName;
        teamPill.style.display   = 'inline-flex';
    }

    mentorSelect.addEventListener('change', updateTeam);
    updateTeam();

    // Photo dropzone
    const photoInput    = document.getElementById('photoInput');
    const photoLabel    = document.getElementById('photoLabel');
    const photoDropzone = document.getElementById('photoDropzone');

    photoInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            photoLabel.textContent = '✓ ' + this.files[0].name;
            photoDropzone.classList.add('changed');
        }
    });

    // Password toggle
    const togglePwd = document.getElementById('togglePwd');
    const pwdInput  = document.getElementById('password');
    const eyeIcon   = document.getElementById('eyeIcon');

    togglePwd.addEventListener('click', function () {
        const isText = pwdInput.type === 'text';
        pwdInput.type     = isText ? 'password' : 'text';
        eyeIcon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

});
</script>
@endpush
@endsection