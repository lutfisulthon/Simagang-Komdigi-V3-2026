@extends('layouts.app')

@section('title', 'Tambah Lowongan Magang')

@push('styles')
    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .mono {
            font-family: 'DM Mono', monospace;
        }

        body {
            background: #f3f7ff;
        }

        .dash-bg {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(99, 102, 241, 0.12), transparent 30%),
                linear-gradient(135deg, #eef4ff 0%, #f5f7ff 40%, #edf3ff 100%);
        }

        /* ── Hero ─────────────────────────────────────── */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            background: linear-gradient(135deg, #1e3a8a 0%, #4338ca 55%, #6366f1 100%);
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.18);
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            top: -120px;
            right: -80px;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            bottom: -180px;
            left: -80px;
        }

        .hero-icon {
            width: 88px;
            height: 88px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .floating-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            color: #dbeafe;
            padding: .45rem 1rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .hero-tip {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            min-width: 260px;
        }

        /* ── Panels ───────────────────────────────────── */
        .panel {
            background: #fff;
            border-radius: 24px;
            box-shadow:
                0 4px 20px rgba(15, 23, 42, 0.04),
                0 1px 3px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .form-section.gray {
            background: #f8fbff;
        }

        .section-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #dbeafe, #e0e7ff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4338ca;
            font-size: 19px;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .section-subtitle {
            font-size: .875rem;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.55;
        }

        /* ── Form controls ────────────────────────────── */
        .form-label {
            display: block;
            font-size: .875rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: .55rem;
        }

        .form-input {
            width: 100%;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            padding: 0.875rem 1rem;
            transition: all .2s ease;
            font-size: 14.5px;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow:
                0 0 0 4px rgba(99, 102, 241, 0.10),
                0 4px 20px rgba(99, 102, 241, 0.07);
        }

        .form-section.gray .form-input {
            background: #fff;
        }

        textarea.form-input {
            min-height: 126px;
            resize: none;
        }

        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
            cursor: pointer;
        }

        /* ── Footer actions ───────────────────────────── */
        .form-footer {
            padding: 20px 28px;
            background: #f8fbff;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-weight: 600;
            font-size: .9rem;
            transition: color .2s;
            text-decoration: none;
        }

        .back-btn:hover {
            color: #1e293b;
        }

        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #fff;
            padding: .9rem 1.8rem;
            border-radius: 14px;
            font-weight: 700;
            font-size: .9rem;
            transition: .25s ease;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.18);
            border: none;
            cursor: pointer;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.22);
        }

        /* ── Alerts ───────────────────────────────────── */
        .alert-error {
            border-radius: 18px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 1rem 1.2rem;
        }

        .alert-success {
            border-radius: 18px;
            background: #ecfdf5;
            border: 1px solid #86efac;
            color: #15803d;
            padding: 1rem 1.2rem;
        }

        /* ── Tips sidebar ─────────────────────────────── */
        .tips-card {
            background: #fff;
            border-radius: 24px;
            padding: 24px;
            box-shadow:
                0 4px 20px rgba(15, 23, 42, 0.04),
                0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .live-card {
            background: #fff;
            border-radius: 24px;
            padding: 20px;
            box-shadow:
                0 4px 20px rgba(15, 23, 42, 0.04),
                0 1px 3px rgba(15, 23, 42, 0.06);
            border: 1px solid #e8eeff;
        }

        .live-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #f8fbff;
            border: 1px solid #e9efff;
        }

        .progress-track {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0;
            border-radius: 999px;
            background: linear-gradient(120deg, #2563eb, #4f46e5);
            transition: width .35s ease;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .status-pill.aktif {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.nonaktif {
            background: #fee2e2;
            color: #991b1b;
        }

        .counter {
            margin-top: 6px;
            display: block;
            font-size: 11px;
            color: #64748b;
        }

        .tips-item {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .tips-item:not(:last-child) {
            padding-bottom: 18px;
            margin-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
        }

        .tips-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
        }

        .divider {
            border-top: 1px solid #f1f5f9;
        }

        /* ── Animations ───────────────────────────────── */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-1 {
            animation: fadeSlideUp .5s ease both;
        }
        
        .anim-2 {
            animation: fadeSlideUp .5s ease .1s both;
        }

        .anim-3 {
            animation: fadeSlideUp .5s ease .2s both;
        }

        .anim-4 {
            animation: fadeSlideUp .5s ease .3s both;
        }

        /* ── Small UI improvements */
        .panel form {
            padding: 22px;
        }

        .form-section {
            padding: 20px 22px;
        }

        .section-label {
            font-weight: 700;
            color: #64748b;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 8px;
            display: block;
        }

        .counter {
            display: inline-block;
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }

    </style>
@endpush

@section('content')

    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ALERTS --}}
            @if (session('success'))
                <div class="alert-success anim-1">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert-error anim-1">
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- HERO --}}
            <div class="hero-card p-7 anim-1">
                <div class="relative z-10 flex flex-col lg:flex-row gap-6 lg:items-center lg:justify-between">

                    <div class="flex items-start gap-5">
                        <div class="hero-icon">
                            <i class="fas fa-briefcase text-white text-3xl"></i>
                        </div>

                        <div>
                            <div class="floating-badge mb-3">
                                <i class="fas fa-plus-circle"></i>
                                Lowongan Magang
                            </div>

                            <h1 class="text-2xl lg:text-3xl font-extrabold text-white leading-tight">
                                Tambah Lowongan Magang
                            </h1>

                            <p class="text-indigo-100 mt-2 text-sm leading-relaxed max-w-lg">
                                Buat lowongan magang baru untuk menjaring peserta terbaik
                                sesuai kebutuhan perusahaan Anda.
                            </p>
                        </div>
                    </div>

                    <div class="hero-tip">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center">
                                <i class="fas fa-lightbulb text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm">Tips Lowongan</h4>
                                <p class="text-indigo-200 text-xs">Buat deskripsi yang jelas.</p>
                            </div>
                        </div>
                        <p class="text-indigo-100 text-xs leading-relaxed">
                            Lowongan dengan informasi lengkap lebih menarik dan meningkatkan jumlah pendaftar.
                        </p>
                    </div>

                </div>
            </div>

            {{-- CONTENT --}}
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                {{-- FORM --}}
                <div class="lg:col-span-3 anim-2">
                    <div class="panel">
                        <form method="POST" action="{{ route('admin.lowongan.store') }}">
                            @csrf

                            {{-- Informasi Lowongan --}}
                            <div class="form-section">
                                <div class="flex items-start gap-4 mb-7">
                                    <div class="section-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div>
                                        <h2 class="section-title">Informasi Lowongan</h2>
                                        <p class="section-subtitle">Informasi dasar mengenai lowongan magang.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2">
                                        <label class="form-label">Judul Lowongan</label>
                                        <input type="text" name="judul_lowongan" id="judul_lowongan" maxlength="255"
                                            value="{{ old('judul_lowongan') }}"
                                            placeholder="Contoh: Lowongan Magang UI/UX Designer" class="form-input">
                                    </div>

                                    <div>
                                        <label class="form-label">Posisi Magang</label>
                                        <input type="text" name="posisi_magang" id="posisi_magang" maxlength="255"
                                            value="{{ old('posisi_magang') }}" placeholder="Contoh: Frontend Developer"
                                            class="form-input">
                                    </div>

                                    <div>
                                        <label class="form-label">Tim / Bagian</label>
                                        <select name="team_id" id="team_id" class="form-input">
                                            <option value="" {{ old('team_id') == '' ? 'selected' : '' }}>Pilih Tim / Bagian</option>
                                            @if(isset($teams) && $teams->count())
                                                @foreach($teams as $team)
                                                    <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                                @endforeach
                                            @else
                                                <option disabled>Tidak ada tim terdaftar</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Detail Pekerjaan --}}
                            <div class="form-section gray">
                                <div class="flex items-start gap-4 mb-7">
                                    <div class="section-icon">
                                        <i class="fas fa-align-left"></i>
                                    </div>
                                    <div>
                                        <h2 class="section-title">Detail Pekerjaan</h2>
                                        <p class="section-subtitle">Jelaskan tugas dan kebutuhan peserta magang.</p>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label class="form-label">Deskripsi Pekerjaan</label>
                                        <textarea name="deskripsi_pekerjaan" id="deskripsi_pekerjaan"
                                            placeholder="Jelaskan tugas dan tanggung jawab peserta magang..." class="form-input">{{ old('deskripsi_pekerjaan') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="form-label">Requirements</label>
                                        <textarea name="requirements" id="requirements" placeholder="Tuliskan syarat atau kualifikasi peserta..."
                                            class="form-input">{{ old('requirements') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="form-label">Fasilitas</label>
                                        <textarea name="fasilitas" id="fasilitas" placeholder="Contoh: Sertifikat, uang transport, makan siang, laptop kerja..."
                                            class="form-input">{{ old('fasilitas') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Informasi Magang --}}
                            <div class="form-section">
                                <div class="flex items-start gap-4 mb-7">
                                    <div class="section-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <h2 class="section-title">Informasi Magang</h2>
                                        <p class="section-subtitle">Tentukan kuota, status, dan pastikan fasilitas terisi
                                            jelas.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="form-label">Kuota Peserta</label>
                                        <input type="number" name="kuota_peserta" id="kuota_peserta" min="1"
                                            value="{{ old('kuota_peserta') }}" placeholder="0" class="form-input">
                                    </div>

                                    <div>
                                        <label class="form-label">Status</label>
                                        <select name="status" id="status" class="form-input">
                                            <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>
                                                Aktif</option>
                                            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>
                                                Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="form-footer">
                                <a href="{{ route('admin.lowongan.index') }}" class="back-btn">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali
                                </a>

                                <button type="submit" class="submit-btn">
                                    <i class="fas fa-save"></i>
                                    Simpan Lowongan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <div class="anim-3">
                    <div class="live-card mb-6">
                        <p class="section-label mb-3">Ringkasan Live</p>

                        <div class="space-y-3">
                            <div class="live-row">
                                <span class="text-xs font-semibold text-slate-500">Kelengkapan</span>
                                <span class="mono text-xs font-semibold text-slate-700" id="completion_text">0%</span>
                            </div>

                            <div class="progress-track">
                                <div class="progress-fill" id="completion_bar"></div>
                            </div>

                            <div class="live-row">
                                <span class="text-xs font-semibold text-slate-500">Status</span>
                                <span class="status-pill" id="status_preview">Aktif</span>
                            </div>

                            <div class="live-row">
                                <span class="text-xs font-semibold text-slate-500">Kuota</span>
                                <span class="mono text-xs font-semibold text-slate-700" id="kuota_preview">0
                                    peserta</span>
                            </div>

                            <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3">
                                <p class="text-[11px] font-semibold text-indigo-500 uppercase tracking-wider mb-1">Judul
                                    Lowongan</p>
                                <p class="text-sm font-bold text-indigo-900 leading-snug" id="judul_preview">Belum diisi
                                </p>
                                <p class="text-xs text-indigo-700 mt-1" id="meta_preview">Posisi dan divisi akan tampil di
                                    sini</p>
                            </div>
                        </div>
                    </div>

                    <div class="tips-card sticky top-6">
                        <p class="section-label">Tips Membuat Lowongan</p>

                        <div class="tips-item">
                            <div class="tips-icon">
                                <i class="fas fa-pen"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800 text-sm mb-1">Judul yang Jelas</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">Gunakan judul lowongan yang mudah
                                    dipahami peserta.</p>
                            </div>
                        </div>

                        <div class="tips-item">
                            <div class="tips-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800 text-sm mb-1">Jelaskan Kebutuhan</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">Tuliskan requirement dengan detail agar
                                    seleksi lebih tepat.</p>
                            </div>
                        </div>

                        <div class="tips-item">
                            <div class="tips-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800 text-sm mb-1">Aktifkan Lowongan</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">Pastikan status lowongan aktif agar dapat
                                    dilihat peserta.</p>
                            </div>
                        </div>

                        <div class="tips-item">
                            <div class="tips-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800 text-sm mb-1">Tawarkan Fasilitas</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">Sebutkan fasilitas yang diterima peserta
                                    untuk menarik lebih banyak pendaftar.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fields = {
                judul: document.getElementById('judul_lowongan'),
                posisi: document.getElementById('posisi_magang'),
                divisi: document.getElementById('divisi'),
                deskripsi: document.getElementById('deskripsi_pekerjaan'),
                requirements: document.getElementById('requirements'),
                fasilitas: document.getElementById('fasilitas'),
                kuota: document.getElementById('kuota_peserta'),
                status: document.getElementById('status')
            };

            const counterEls = {
                judul: document.getElementById('judul_counter'),
                deskripsi: document.getElementById('deskripsi_counter'),
                requirements: document.getElementById('requirements_counter'),
                fasilitas: document.getElementById('fasilitas_counter')
            };

            const completionBar = document.getElementById('completion_bar');
            const completionText = document.getElementById('completion_text');
            const statusPreview = document.getElementById('status_preview');
            const kuotaPreview = document.getElementById('kuota_preview');
            const judulPreview = document.getElementById('judul_preview');
            const metaPreview = document.getElementById('meta_preview');

            const requiredForProgress = [
                fields.judul,
                fields.posisi,
                fields.divisi,
                fields.deskripsi,
                fields.requirements,
                fields.fasilitas,
                fields.kuota,
                fields.status
            ];

            function val(el) {
                return (el && el.value ? el.value.trim() : '');
            }

            function updateCounters() {
                if (counterEls.judul && fields.judul) {
                    counterEls.judul.textContent = `${fields.judul.value.length}/255`;
                }

                if (counterEls.deskripsi && fields.deskripsi) {
                    counterEls.deskripsi.textContent = `${fields.deskripsi.value.length} karakter`;
                }

                if (counterEls.requirements && fields.requirements) {
                    counterEls.requirements.textContent = `${fields.requirements.value.length} karakter`;
                }

                if (counterEls.fasilitas && fields.fasilitas) {
                    counterEls.fasilitas.textContent = `${fields.fasilitas.value.length} karakter`;
                }
            }

            function updatePreview() {
                const judul = val(fields.judul) || 'Belum diisi';
                const posisi = val(fields.posisi) || 'Posisi belum diisi';
                const divisi = val(fields.divisi) || 'Divisi belum diisi';
                const kuota = val(fields.kuota) || '0';
                const status = val(fields.status) || 'aktif';

                judulPreview.textContent = judul;
                metaPreview.textContent = `${posisi} • ${divisi}`;
                kuotaPreview.textContent = `${kuota} peserta`;
                statusPreview.textContent = status === 'aktif' ? 'Aktif' : 'Nonaktif';
                statusPreview.classList.remove('aktif', 'nonaktif');
                statusPreview.classList.add(status === 'aktif' ? 'aktif' : 'nonaktif');
            }

            function updateProgress() {
                const filled = requiredForProgress.filter(function(el) {
                    return val(el) !== '';
                }).length;

                const percent = Math.round((filled / requiredForProgress.length) * 100);
                completionBar.style.width = percent + '%';
                completionText.textContent = percent + '%';
            }

            function updateAll() {
                updateCounters();
                updatePreview();
                updateProgress();
            }

            Object.values(fields).forEach(function(field) {
                if (!field) return;
                field.addEventListener('input', updateAll);
                field.addEventListener('change', updateAll);
            });

            updateAll();
        });
    </script>
@endpush
