@extends('layouts.app')

@section('title', 'Profile - Sistem Manajemen Magang')

@section('content')
@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

    *, body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .mono { font-family: 'DM Mono', monospace; }

    .dash-bg { min-height: 100vh; background: #f1f5ff; }
    .hero-strip { background: linear-gradient(110deg, #0f2878 0%, #2d3ecb 55%, #4f46e5 100%); border-radius: 20px; position: relative; overflow: hidden; margin-bottom: 28px; }
    .hero-strip::before { content: ''; position: absolute; top: -80px; right: -60px; width: 260px; height: 260px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none; }
    .hero-strip::after { content: ''; position: absolute; bottom: -100px; left: 25%; width: 320px; height: 320px; background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none; }

    .btn-nav { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; transition: all .2s ease; }
    .btn-back { background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.3); }
    .btn-back:hover { background: rgba(255,255,255,0.3); border-color: rgba(255,255,255,0.5); transform: translateX(-3px); }
    .btn-edit { background: linear-gradient(135deg, #3b82f6, #1e40af); color: #fff; box-shadow: 0 3px 12px rgba(59,79,216,0.3); }
    .btn-edit:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(59,79,216,0.4); }

    .panel { background: #fff; border-radius: 20px; box-shadow: 0 1px 3px rgba(20,40,120,0.06), 0 4px 18px rgba(20,40,120,0.06); overflow: hidden; margin-bottom: 24px; }
    .panel-header { background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 100%); padding: 16px 22px; display: flex; align-items: center; gap: 10px; }
    .panel-header h2 { color: #fff; font-size: 15px; font-weight: 700; letter-spacing: 0.01em; margin: 0; }
    .panel-body { padding: 24px 22px; }

    .profile-photo-section { display: flex; flex-direction: column; align-items: center; gap: 24px; padding-bottom: 24px; border-bottom: 2px solid #f3f4f6; margin-bottom: 24px; }
    .profile-photo-wrapper { position: relative; width: 100px; height: 100px; }
    .profile-photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #dbeafe; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #3b4fd8; font-size: 40px; box-shadow: 0 4px 12px rgba(59,79,216,0.2); }
    .profile-name h3 { font-size: 22px; font-weight: 800; color: #1f2937; margin: 0 0 4px 0; }
    .profile-name p { font-size: 13px; color: #6b7280; margin: 0; }

    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
    .info-item { display: flex; flex-direction: column; gap: 6px; }
    .info-label { font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; display: flex; align-items: center; gap: 6px; }
    .info-value { font-size: 14px; font-weight: 600; color: #1f2937; padding: 10px 12px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px; transition: all .2s ease; overflow-wrap: anywhere; min-width: 0; }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; }
    .status-active { background: #dcfce7; color: #15803d; }
    .status-inactive { background: #fee2e2; color: #b91c1c; }

    .a1 { animation: fadeUp .5s ease both; }
    .a3 { animation: fadeUp .5s .16s ease both; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

    /* Actions container for hero buttons */
    .hero-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

    /* Responsive tweaks for small screens */
    @media (max-width: 640px) {
        .hero-strip .relative { flex-direction: column; align-items: flex-start; gap: 12px; }
        .hero-actions { width: 100%; justify-content: flex-end; }
        .hero-actions .btn-nav { padding: 8px 12px; font-size: 13px; border-radius: 8px; }
        .hero-actions .btn-edit { min-width: 110px; }
        .hero-actions .btn-back { min-width: 80px; }
    }

    @media (max-width: 420px) {
        .hero-actions { justify-content: flex-end; gap: 6px; }
        .hero-actions .btn-nav { padding: 6px 10px; font-size: 12px; }
        .hero-actions .btn-nav i { margin-right: 6px; }
    }
</style>
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
                            <img src="{{ asset('storage/' . $intern->photo_path) }}" alt="Profile Photo" class="profile-photo">
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