@extends('layouts.app')

@section('title', 'Tambah Akun Admin - Sistem Manajemen Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-account-create.css') }}?v={{ @filemtime(public_path('css/pages/admin-account-create.css')) }}">
@endpush

@php
    $permissionLabels = [
        'view_users' => 'Melihat daftar user (Intern dan Mentor)',
        'create_users' => 'Membuat user baru (Intern dan Mentor)',
        'edit_users' => 'Mengedit user (Intern dan Mentor)',
        'delete_users' => 'Menghapus user (Intern dan Mentor)',
        'view_admins' => 'Melihat daftar Admin',
        'create_admin' => 'Membuat Admin baru (dengan assign aksesibilitas)',
        'edit_admin' => 'Mengedit Admin (update akses)',
        'delete_admin' => 'Menghapus Admin (hanya Super Admin)',
        'view_mentors' => 'Melihat daftar Mentor',
        'manage_mentors' => 'Kelola data Mentor (CRUD)',
        'view_attendance' => 'Melihat data Absensi',
        'manage_attendance' => 'Kelola Absensi (Approve/Reject)',
        'view_logbook' => 'Melihat data Logbook',
        'manage_logbook' => 'Kelola Logbook (Approve/Reject)',
        'view_reports' => 'Melihat Laporan Akhir',
        'manage_reports' => 'Kelola Laporan Akhir (Approve/Reject/Score)',
        'manage_pengajuan' => 'Kelola Pengajuan Calon Anak Magang (Approve/Reject/Revision)',
        'view_interns' => 'Melihat daftar Intern',
        'manage_interns' => 'Kelola data Intern (CRUD)',
        'view_teams' => 'Melihat daftar Tim',
        'manage_teams' => 'Kelola data Tim (CRUD)',
        'access_admin_dashboard' => 'Akses Admin Dashboard',
    ];

    $roleAccessInfo = [
        'admin_full' => [
            'description' => 'Akses paling lengkap untuk mengelola user, mentor, peserta magang, absensi, logbook, laporan akhir, pengajuan, tim, dan dashboard admin.',
            'permissions' => [
                'view_users',
                'create_users',
                'edit_users',
                'delete_users',
                'view_mentors',
                'manage_mentors',
                'view_interns',
                'manage_interns',
                'view_attendance',
                'manage_attendance',
                'view_logbook',
                'manage_logbook',
                'view_reports',
                'manage_reports',
                'manage_pengajuan',
                'view_teams',
                'manage_teams',
                'access_admin_dashboard',
            ],
        ],
        'admin_user_manager' => [
            'description' => 'Fokus pada pengelolaan user dan data pendukung, dengan akses lihat ke beberapa data operasional.',
            'permissions' => [
                'view_users',
                'view_interns',
                'manage_interns',
                'view_mentors',
                'manage_mentors',
                'view_attendance',
                'view_logbook',
                'view_reports',
                'access_admin_dashboard',
            ],
        ],
        'admin_data_manager' => [
            'description' => 'Berfokus pada pengelolaan data peserta magang dan operasional pendukung seperti absensi, logbook, dan laporan.',
            'permissions' => [
                'view_interns',
                'manage_interns',
                'view_attendance',
                'manage_attendance',
                'view_logbook',
                'manage_logbook',
                'view_reports',
                'manage_reports',
                'access_admin_dashboard',
            ],
        ],
    ];
@endphp

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7">
                    <h1 class="hero-title text-4xl font-bold mb-1">Tambah Akun Admin</h1>
                    <p class="text-blue-100">Buat akun admin baru dengan role yang sesuai kebutuhan.</p>
                </div>
            </div>

            {{-- ── FORM PANEL ── --}}
            <div class="panel">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-pen mr-3"></i>Informasi Akun
                    </h2>
                </div>

                <form action="{{ route('admin.accounts.store') }}" method="POST" class="p-6 space-y-5">
                    @csrf

                    {{-- Data Utama --}}
                    <p class="section-label">Data Utama</p>

                    <div>
                        <label class="field-label">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="input-main" required placeholder="Masukkan nama lengkap">
                        @error('name')
                            <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="field-label">
                            <i class="fas fa-envelope"></i> Alamat Email
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="input-main" required placeholder="contoh@email.com">
                        @error('email')
                            <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="field-label">
                            <i class="fas fa-id-badge"></i> Akses Admin
                        </label>
                        <select name="role" class="input-main" required>
                            <option value="">Pilih Akses...</option>
                            @foreach ($roleOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-3 rounded-2xl border border-blue-100 bg-blue-50/70 p-4" id="role-access-info" aria-live="polite">
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-800">Informasi akses role</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600" id="role-access-description">
                                        Pilih Akses Admin untuk melihat permission yang tersedia.
                                    </p>
                                    <div class="mt-3 hidden" id="role-access-permissions-wrapper">
                                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-500">Permission yang diberikan</p>
                                        <ul class="mt-2 grid gap-2 sm:grid-cols-2" id="role-access-permissions"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('role')
                            <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="form-divider"></div>

                    {{-- Password --}}
                    <p class="section-label">Password</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" name="password"
                                class="input-main" required placeholder="Minimal 8 karakter">
                            @error('password')
                                <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="field-label">
                                <i class="fas fa-lock"></i> Konfirmasi Password
                            </label>
                            <input type="password" name="password_confirmation"
                                class="input-main" required placeholder="Ulangi password">
                        </div>
                    </div>

                    <p class="field-hint">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik.
                    </p>

                    <div class="form-divider"></div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('admin.accounts.index') }}" class="btn-cancel">
                            <i class="fas fa-arrow-left text-xs"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save text-xs"></i>
                            Simpan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.querySelector('select[name="role"]');
            const description = document.getElementById('role-access-description');
            const permissionsWrapper = document.getElementById('role-access-permissions-wrapper');
            const permissionsList = document.getElementById('role-access-permissions');
            const permissionLabels = @json($permissionLabels);
            const roleAccessInfo = @json($roleAccessInfo);

            if (!roleSelect || !description || !permissionsWrapper || !permissionsList) {
                return;
            }

            const renderRoleInfo = (role) => {
                const info = roleAccessInfo[role];

                if (!info) {
                    description.textContent = 'Pilih Akses Admin untuk melihat permission yang tersedia.';
                    permissionsWrapper.classList.add('hidden');
                    permissionsList.innerHTML = '';
                    return;
                }

                description.textContent = info.description;
                permissionsList.innerHTML = info.permissions
                    .map((permission) => `
                        <li class="flex items-start gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-700 shadow-sm ring-1 ring-blue-100">
                            <i class="fas fa-circle-check mt-1 text-[11px] text-blue-600"></i>
                            <span>${permissionLabels[permission] ?? permission}</span>
                        </li>
                    `)
                    .join('');
                permissionsWrapper.classList.remove('hidden');
            };

            roleSelect.addEventListener('change', function () {
                renderRoleInfo(this.value);
            });

            renderRoleInfo(roleSelect.value);
        });
    </script>
@endsection