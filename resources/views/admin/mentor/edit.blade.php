@extends('layouts.app')

@section('title', 'Edit Mentor - Sistem Manajemen Magang')

@push('styles')
    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dash-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        }

        /* ── Hero Strip ── */
        .hero-strip {
            background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 50%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
            color: #fff;
        }

        .hero-strip::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: 30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        /* ── Panel ── */
        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
        }

        /* ── Panel header bar ── */
        .panel-header-blue {
            background: linear-gradient(to right, #2563eb, #4f46e5);
            padding: 1rem 1.5rem;
        }

        /* ── Input ── */
        .input-main {
            width: 100%;
            padding: 0.6rem 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 0.6rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all .15s ease;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: #1f2937;
            background: #fff;
            box-sizing: border-box;
        }

        .input-main:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        select.input-main {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 34px;
        }

        /* ── Tombol ── */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.55rem 1.25rem;
            border-radius: 0.6rem;
            background: rgba(255, 255, 255, 0.15);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all .2s ease;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-submit {
            background: linear-gradient(to right, #2563eb, #4338ca);
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 0.6rem 1.75rem;
            border-radius: 0.6rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .15s ease;
            white-space: nowrap;
        }

        .btn-submit:hover {
            box-shadow: 0 4px 14px rgba(59, 79, 216, 0.3);
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #2563eb;
            font-weight: 700;
            font-size: 13px;
            padding: 0.6rem 1.25rem;
            border-radius: 0.6rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all .15s ease;
        }

        .btn-cancel:hover {
            background: #dbeafe;
            color: #1d4ed8;
            text-decoration: none;
        }

        /* ── Section divider ── */
        .form-section-inner {
            padding: 1.75rem 2rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-section-inner:last-of-type {
            border-bottom: none;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ── Error & hint ── */
        .field-error {
            margin-top: 6px;
            font-size: 12px;
            color: #b91c1c;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .field-hint {
            margin-top: 5px;
            font-size: 12px;
            color: #94a3b8;
        }

        /* ── Checkbox toggle row ── */
        .toggle-row {
            display: flex;
            align-items: center;
            height: 42px;
            background: #eff6ff;
            border-radius: 0.6rem;
            padding: 0 1rem;
            border: 1px solid #bfdbfe;
            cursor: pointer;
            gap: 10px;
        }

        .toggle-row input[type="checkbox"] {
            width: 17px;
            height: 17px;
            accent-color: #2563eb;
            cursor: pointer;
        }

        .toggle-row span {
            font-size: 13px;
            font-weight: 600;
            color: #1e40af;
        }

        /* ── Form footer ── */
        .form-footer {
            background: #f8fafc;
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #f1f5f9;
        }

        /* ── Modal ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 100;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: modalIn 0.25s ease both;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.93) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            margin: 0 auto 16px;
            background: #eff6ff;
            border-radius: 50%;
        }

        .modal-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0 0 8px;
        }

        .modal-desc {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin: 0 0 24px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .modal-btn-cancel {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            background: #f1f5f9;
            border: 1.5px solid #e2e8f0;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all .15s ease;
        }

        .modal-btn-cancel:hover {
            background: #e2e8f0;
        }

        .modal-btn-confirm {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            background: linear-gradient(110deg, #1e3a8a, #3b4fd8);
            border: none;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all .15s ease;
        }

        .modal-btn-confirm:hover {
            box-shadow: 0 4px 14px rgba(59, 79, 216, 0.35);
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .hero-title { font-size: 1.5rem; }
            .form-section-inner { padding: 1.25rem 1rem; }
            .form-footer { flex-direction: column-reverse; }
            .form-footer a,
            .form-footer button { width: 100%; justify-content: center; }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-6">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="hero-title text-4xl font-bold mb-1">Edit Mentor</h1>
                        <p class="text-blue-100">Perbarui informasi mentor di sini</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.mentor.index') }}" class="btn-back">
                            <i class="fas fa-arrow-left text-sm"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── FORM PANEL ── --}}
            <div class="panel">
                <div class="panel-header-blue flex items-center gap-3">
                    <i class="fas fa-user-edit text-white text-lg"></i>
                    <h2 class="text-xl font-bold text-white">Formulir Edit Mentor</h2>
                </div>

                <form id="edit-mentor-form" method="POST" action="{{ route('admin.mentor.update', $mentor) }}">
                    @csrf
                    @method('PUT')

                    {{-- ── SEKSI 1: Informasi Pribadi ── --}}
                    <div class="form-section-inner">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="section-icon">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-blue-900">Informasi Pribadi</h3>
                                <p class="text-xs text-gray-500">Data diri dan kontak mentor</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    value="{{ old('name', $mentor->name) }}"
                                    required class="input-main"
                                    placeholder="Masukkan nama lengkap">
                                @error('name')
                                    <p class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email', $mentor->email) }}"
                                    class="input-main"
                                    placeholder="contoh@email.com">
                                @error('email')
                                    <p class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jabatan
                                </label>
                                <input type="text" name="position" id="position"
                                    value="{{ old('position', $mentor->position) }}"
                                    class="input-main"
                                    placeholder="Masukkan jabatan">
                                @error('position')
                                    <p class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Telepon
                                </label>
                                <input type="text" name="phone" id="phone"
                                    value="{{ old('phone', $mentor->phone) }}"
                                    class="input-main"
                                    placeholder="08xxxxxxxxxx">
                                @error('phone')
                                    <p class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── SEKSI 2: Penempatan ── --}}
                    <div class="form-section-inner">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="section-icon">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-blue-900">Penempatan</h3>
                                <p class="text-xs text-gray-500">Tim yang ditugaskan</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Pilih Tim <span class="text-red-500">*</span>
                            </label>
                            <select name="team_id" id="mentorSelect" required class="input-main">
                                <option value="">Pilih Tim</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}"
                                        data-team="{{ $team->name }}"
                                        {{ old('team_id', $mentor->team_id) == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('team_id')
                                <p class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ── SEKSI 3: Keamanan & Status ── --}}
                    <div class="form-section-inner">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="section-icon">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-blue-900">Keamanan & Status</h3>
                                <p class="text-xs text-gray-500">Pengaturan password dan status aktif</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Password Baru
                                </label>
                                <input type="password" name="password" id="password"
                                    class="input-main"
                                    placeholder="Kosongkan jika tidak ingin mengubah">
                                <p class="field-hint">Minimal 8 karakter</p>
                                @error('password')
                                    <p class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Status Aktif</label>
                                <label for="is_active" class="toggle-row">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $mentor->is_active) ? 'checked' : '' }}>
                                    <span>Tandai sebagai aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── FOOTER AKSI ── --}}
                    <div class="form-footer">
                        <a href="{{ route('admin.mentor.index') }}" class="btn-cancel">
                            <i class="fas fa-times"></i>
                            Batal
                        </a>
                        {{-- Tombol ini membuka modal, bukan langsung submit --}}
                        <button type="button" id="btn-open-modal" class="btn-submit">
                            <i class="fas fa-save"></i>
                            Update Data
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    {{-- ── CONFIRMATION MODAL ── --}}
    <div id="confirm-modal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-wrap">
                <i class="fas fa-save" style="color:#3b4fd8;font-size:20px;"></i>
            </div>
            <h3 class="modal-title">Konfirmasi Simpan</h3>
            <p class="modal-desc">Apakah Anda yakin ingin menyimpan perubahan ini? Perubahan akan langsung diterapkan.</p>
            <div class="modal-actions">
                <button id="btn-modal-cancel" type="button" class="modal-btn-cancel">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button id="btn-modal-confirm" type="button" class="modal-btn-confirm">
                    <i class="fas fa-save"></i> Ya, Simpan
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal   = document.getElementById('confirm-modal');
    const form    = document.getElementById('edit-mentor-form');

    // Buka modal saat tombol "Update Data" diklik
    document.getElementById('btn-open-modal').addEventListener('click', function () {
        modal.classList.add('active');
    });

    // Tutup modal – tombol batal dalam modal
    document.getElementById('btn-modal-cancel').addEventListener('click', function () {
        modal.classList.remove('active');
    });

    // Konfirmasi – submit form
    document.getElementById('btn-modal-confirm').addEventListener('click', function () {
        modal.classList.remove('active');
        form.submit();
    });

    // Tutup modal – klik backdrop
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('active');
    });

    // Tutup modal – tekan Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });

});
</script>
@endpush