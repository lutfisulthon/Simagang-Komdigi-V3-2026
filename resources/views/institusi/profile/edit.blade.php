@extends('layouts.app')

@section('title', 'Edit Profile Institusi - Sistem Manajemen Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-profile-edit.css') }}?v={{ @filemtime(public_path('css/pages/institusi-profile-edit.css')) }}">
@endpush

@section('content')
    <div class="dash-bg min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Hero Strip -->
            <div class="hero-strip">
                <h1><i class="fas fa-user-edit mr-3" style="font-size: 2rem;"></i>Edit Profile Institusi</h1>
                <p>Perbarui informasi profil institusi Anda</p>
            </div>

            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('institusi.profile.show') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>Kembali ke Profile
                </a>
            </div>

            <form id="institusi-form" method="POST" action="{{ route('institusi.profile.update') }}">
                @csrf
                @method('PUT')

                <!-- Informasi Institusi Panel -->
                <div class="panel">
                    <div class="panel-header">
                        <i class="fas fa-building"></i>Informasi Institusi
                    </div>
                    <div class="panel-body">
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="name">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                    placeholder="Masukkan nama lengkap" @error('name') class="error" @enderror>
                                @error('name')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-group">
                                <label for="nama_institusi">Nama Institusi</label>
                                <input type="text" name="nama_institusi" id="nama_institusi"
                                    value="{{ old('nama_institusi', $institusi->nama_institusi) }}"
                                    placeholder="Masukkan nama institusi" @error('nama_institusi') class="error" @enderror>
                                @error('nama_institusi')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="jenis_institusi">Jenis Institusi</label>
                                <input type="text" name="jenis_institusi" id="jenis_institusi"
                                    value="{{ old('jenis_institusi', $institusi->jenis_institusi) }}"
                                    placeholder="Contoh: Universitas / Perusahaan"
                                    @error('jenis_institusi') class="error" @enderror>
                                @error('jenis_institusi')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="nomor_identitas">Nomor Identitas</label>
                                <input type="text" name="nomor_identitas" id="nomor_identitas"
                                    value="{{ old('nomor_identitas', $institusi->nomor_identitas) }}"
                                    placeholder="NIM / NIK / No. Registrasi"
                                    @error('nomor_identitas') class="error" @enderror>
                                @error('nomor_identitas')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="no_hp">Nomor Telepon</label>
                                <input type="text" name="no_hp" id="no_hp"
                                    value="{{ old('no_hp', $institusi->no_hp) }}" placeholder="Masukkan nomor telepon"
                                    @error('no_hp') class="error" @enderror>
                                @error('no_hp')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email', $user->email) }}" placeholder="Masukkan email"
                                    @error('email') class="error" @enderror>
                                @error('email')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fakultas">Fakultas</label>
                                <input type="text" name="fakultas" id="fakultas"
                                    value="{{ old('fakultas', $institusi->fakultas) }}"
                                    placeholder="Masukkan nama fakultas">
                            </div>

                            <div class="form-group">
                                <label for="departemen">Departemen</label>
                                <input type="text" name="departemen" id="departemen"
                                    value="{{ old('departemen', $institusi->departemen) }}"
                                    placeholder="Masukkan nama departemen">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Change Panel -->
                <div class="panel">
                    <div class="panel-header">
                        <i class="fas fa-lock"></i>Ubah Password
                    </div>
                    <div class="panel-body">
                        <p class="help-text mb-6">Kosongkan field password jika tidak ingin mengubah password.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password">Password Lama</label>
                                <input type="password" name="current_password" id="current_password"
                                    placeholder="Masukkan password saat ini"
                                    @error('current_password') class="error" @enderror>
                                @error('current_password')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Password Baru</label>
                                <input type="password" name="password" id="password"
                                    placeholder="Masukkan password baru" @error('password') class="error" @enderror>
                                @error('password')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-group">
                                <label for="password_confirmation">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    placeholder="Konfirmasi password baru">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" id="btn-submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3 class="modal-title">Simpan Perubahan?</h3>
            <p class="modal-text">Apakah Anda yakin ingin menyimpan perubahan pada profil institusi?</p>
            <div class="modal-buttons">
                <button id="btn-cancel" type="button" class="modal-btn modal-btn-cancel">
                    <i class="fas fa-times"></i>Tidak
                </button>
                <button id="btn-confirm" type="button" class="modal-btn modal-btn-confirm">
                    <i class="fas fa-check"></i>Ya, Simpan
                </button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('confirm-modal');
        const form = document.getElementById('institusi-form');

        document.getElementById('btn-submit').addEventListener('click', function() {
            modal.classList.add('active');
        });

        document.getElementById('btn-cancel').addEventListener('click', function() {
            modal.classList.remove('active');
        });

        document.getElementById('btn-confirm').addEventListener('click', function() {
            modal.classList.remove('active');
            form.submit();
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    </script>
@endsection
