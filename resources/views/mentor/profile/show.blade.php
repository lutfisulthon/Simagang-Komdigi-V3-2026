@extends('layouts.app')

@section('title', 'Profile - Sistem Manajemen Magang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/mentor-profile-show.css') }}?v={{ @filemtime(public_path('css/pages/mentor-profile-show.css')) }}">
@endpush

@section('content')
<div class="dash-bg py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ── HERO HEADER ──────────────────────────────── --}}
        <div class="hero-strip shadow-xl a1">
            <div class="relative z-10 px-6 py-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Profile Mentor</h1>
                    <p class="text-blue-200 text-sm">Informasi profil Anda</p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('mentor.dashboard') }}" class="btn-nav btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('mentor.profile.edit') }}" class="btn-nav btn-edit">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        {{-- ── PROFILE PANEL ────────────────────────────– --}}
        <div class="panel a3">
            <div class="panel-header">
                <i class="fas fa-user-circle text-blue-200"></i>
                <h2>Informasi Profil</h2>
            </div>
            <div class="panel-body">

                {{-- Photo & Name Section --}}
                <div class="profile-photo-section">
                    <div class="profile-photo-wrapper">
                        @if($mentor->photo_path)
                            <img src="{{ asset('storage/' . $mentor->photo_path) }}" alt="Profile Photo" class="profile-photo" loading="lazy" decoding="async">
                        @else
                            <div class="profile-photo">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="profile-name">
                        <h3>{{ $mentor->name }}</h3>
                        <p>{{ $mentor->position ?? 'Mentor' }}</p>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="info-grid">
                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <div class="info-value">
                            {{ $user->email }}
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-phone"></i> Nomor Telepon
                        </label>
                        <div class="info-value">
                            {{ $mentor->phone ?? '—' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-briefcase"></i> Posisi
                        </label>
                        <div class="info-value">
                            {{ $mentor->position ?? '—' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-sitemap"></i> Tim
                        </label>
                        <div class="info-value">
                            {{ $mentor->team ? $mentor->team->name : '—' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">
                            <i class="fas fa-check-circle"></i> Status
                        </label>
                        <span class="status-badge {{ $mentor->is_active ? 'status-active' : 'status-inactive' }}">
                            <i class="fas {{ $mentor->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            {{ $mentor->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
