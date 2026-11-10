@extends('layouts.app')

@section('title', 'Edit Akun Admin - Sistem Manajemen Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-account-edit.css') }}?v={{ @filemtime(public_path('css/pages/admin-account-edit.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">

            {{-- ── HERO STRIP ── --}}
            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="hero-title text-4xl font-bold mb-1">Edit Akun Admin</h1>
                        <p class="text-blue-100 text-sm">{{ $user->email }}</p>
                        <div class="mt-2">
                            @if ($user->isSuperAdmin())
                                <span class="super-badge">
                                    <i class="fas fa-shield-alt text-xs"></i> Super Admin
                                </span>
                            @else
                                <span class="text-blue-200 text-sm">
                                    <i class="fas fa-user-cog mr-1"></i>
                                    {{ $user->getRoleNames()->first() ? str_replace('_', ' ', ucwords($user->getRoleNames()->first(), '_')) : 'Admin' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── FORM PANEL ── --}}
            <div class="panel">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-pen mr-3"></i>Informasi Akun
                    </h2>
                </div>

                <form action="{{ route('admin.accounts.update', $user) }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Data Utama --}}
                    <p class="section-label">Data Utama</p>

                    <div>
                        <label class="field-label">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
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
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
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
                        @if ($user->isSuperAdmin())
                            <input type="text" value="Super Admin" class="input-main" disabled>
                            <input type="hidden" name="role" value="super_admin">
                            <p class="field-hint"><i class="fas fa-lock mr-1"></i>Akses Super Admin tidak dapat diubah.</p>
                        @else
                            <select name="role" class="input-main" required>
                                <option value="">Pilih Akses...</option>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('role', $user->role ?? $user->getRoleNames()->first()) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('role')
                            <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="form-divider"></div>

                    {{-- Password --}}
                    <p class="section-label">Ubah Password</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">
                                <i class="fas fa-lock"></i> Password Baru
                            </label>
                            <input type="password" name="password" class="input-main"
                                placeholder="Kosongkan jika tidak diubah">
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
                            <input type="password" name="password_confirmation" class="input-main"
                                placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <p class="field-hint">
                        <i class="fas fa-info-circle mr-1"></i>
                        Biarkan kosong jika tidak ingin mengubah password.
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
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
@endsection