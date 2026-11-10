@extends('layouts.app')

@section('title', 'Profile - Sistem Manajemen Magang')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/intern-profile-show.css') }}?v={{ @filemtime(public_path('css/pages/intern-profile-show.css')) }}">
@endpush

@section('content')
<div class="dash-bg py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="hero-strip shadow-xl a1">
            <div class="relative z-10 px-6 py-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Profile</h1>
                    {{-- <p class="text-blue-200 text-sm">Informasi profil Anda</p> --}}
                </div>
                <div class="hero-actions">
                    <a href="{{ route('intern.dashboard') }}" class="btn-nav btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('intern.profile.edit') }}" class="btn-nav btn-edit">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="panel a3">
            <div class="panel-header">
                <i class="fas fa-user-circle text-blue-200"></i>
                <h2>Informasi Profil</h2>
            </div>
            <div class="panel-body">

                <div class="profile-photo-section">
                    <div class="profile-photo-wrapper">
                        @if($intern->photo_path)
                            <img src="{{ asset('storage/' . $intern->photo_path) }}" alt="Profile Photo" class="profile-photo" loading="lazy" decoding="async">
                        @else
                            <div class="profile-photo">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="profile-name">
                        <h3>{{ $intern->name }}</h3>
                        <p>{{ $intern->position ?? '' }}</p>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <label class="info-label"><i class="fas fa-envelope"></i> Email</label>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label"><i class="fas fa-phone"></i> Nomor Telepon</label>
                        <div class="info-value">{{ $intern->phone ?? '-' }}</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label"><i class="fas fa-briefcase"></i> Tingkat Pendidikan</label>
                        <div class="info-value">{{ $intern->education_level ?? '-' }}</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label"><i class="fas fa-sitemap"></i> Institusi</label>
                        <div class="info-value">{{ $intern->institution ?? '-' }}</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label"><i class="fas fa-calendar"></i> Periode</label>
                        <div class="info-value">{{ $intern->start_date ? $intern->start_date->format('d M Y') : '-' }} — {{ $intern->end_date ? $intern->end_date->format('d M Y') : '-' }}</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label"><i class="fas fa-flag"></i> Status Magang</label>
                        <div class="info-value">
                            <span class="status-badge {{ $intern->is_active ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $intern->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                {{ $intern->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection