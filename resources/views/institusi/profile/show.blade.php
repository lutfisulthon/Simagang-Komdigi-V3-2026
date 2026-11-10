@extends('layouts.app')

@section('title', 'Profile Institusi - Sistem Manajemen Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-profile-show.css') }}?v={{ @filemtime(public_path('css/pages/institusi-profile-show.css')) }}">
@endpush

@section('content')
    <div class="dash-bg min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Hero Strip -->
            <div class="hero-strip">
                <h1><i class="fas fa-user-circle mr-3" style="font-size: 2rem;"></i>Profile Institusi</h1>
                <p>Kelola informasi profil institusi Anda</p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons mb-6">
                <a href="{{ route('institusi.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>Kembali ke Dashboard
                </a>
                <a href="{{ route('institusi.profile.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>Edit Profile
                </a>
            </div>

            <!-- Profile Info Panel -->
            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-building"></i>Informasi Institusi
                </div>
                <div class="panel-body">
                    <div class="info-row">
                        <div class="info-item md:col-span-2">
                            <label>Nama Institusi</label>
                            <span>{{ $institusi->nama_institusi ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <label>Nama Lengkap</label>
                            <span>{{ $user->name ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <span>{{ $user->email ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <label>Jenis Institusi</label>
                            <span>{{ $institusi->jenis_institusi ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Nomor Identitas</label>
                            <span>{{ $institusi->nomor_identitas ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <label>Nomor Telepon</label>
                            <span>{{ $institusi->no_hp ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Fakultas</label>
                            <span>{{ $institusi->fakultas ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <label>Departemen</label>
                            <span>{{ $institusi->departemen ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Status Akun</label>
                            <span
                                class="status-badge {{ $user->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                <i
                                    class="fas {{ $user->is_active ?? true ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                {{ $user->is_active ?? true ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
