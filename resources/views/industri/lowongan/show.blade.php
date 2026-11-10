@extends('layouts.app')

@section('title', 'Detail Lowongan Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/industri-lowongan-show.css') }}?v={{ @filemtime(public_path('css/pages/industri-lowongan-show.css')) }}">
@endpush

@section('content')
    @php
        $logo = optional($lowongan->industri)->logo_industri
            ? asset('storage/' . $lowongan->industri->logo_industri)
            : 'https://ui-avatars.com/api/?name=' .
                urlencode(optional($lowongan->industri)->nama_industri ?? 'Industri') .
                '&background=4f46e5&color=fff';

        $status = $lowongan->status ?? 'dibuka';

        $badgeClass = match ($status) {
            'dibuka'  => 'badge-green',
            'ditutup' => 'badge-red',
            default   => 'badge-amber',
        };

        $badgeIcon = match ($status) {
            'dibuka'  => 'fa-check-circle',
            'ditutup' => 'fa-times-circle',
            default   => 'fa-clock',
        };
    @endphp

    <div class="dash-bg py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- ── PROFILE STRIP ── --}}
            <div class="profile-strip anim-1">
                <div class="px-6 py-7 flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">

                    {{-- Logo Industri ── --}}
                    <div style="background:linear-gradient(135deg,#60a5fa,#818cf8);padding:3px;border-radius:9999px;display:inline-flex;flex-shrink:0;">
                        <img src="{{ $logo }}"
                             alt="{{ optional($lowongan->industri)->nama_industri ?? 'Industri' }}"
                             style="width:80px;height:80px;border-radius:9999px;object-fit:cover;" loading="lazy" decoding="async">
                    </div>

                    {{-- Identity ── --}}
                    <div class="flex-1 text-center sm:text-left">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white">{{ $lowongan->judul_lowongan ?? '-' }}</h1>
                            <span class="badge {{ $badgeClass }}">
                                <i class="fas {{ $badgeIcon }} text-xs"></i>
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                        <p class="text-blue-200 font-semibold text-sm">
                            {{ optional($lowongan->industri)->nama_industri ?? '-' }}
                        </p>
                        <p class="text-blue-300 text-xs mt-1">
                            <i class="fas fa-briefcase mr-1"></i>{{ $lowongan->posisi_magang ?? '-' }}
                            @if($lowongan->divisi)
                                &nbsp;·&nbsp;
                                <i class="fas fa-layer-group mr-1"></i>{{ $lowongan->divisi }}
                            @endif
                            &nbsp;·&nbsp;
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Dibuat {{ optional($lowongan->created_at)->locale('id')->translatedFormat('d F Y') ?? '-' }}
                        </p>
                    </div>

                    {{-- Actions ── --}}
                   <div class="flex flex-wrap sm:flex-col gap-2 items-center sm:items-stretch justify-center sm:justify-center flex-shrink-0 sm:self-center" style="min-width:140px;">
                        <a href="{{ route('industri.lowongan.edit', $lowongan->id) }}"
                           class="action-strip-btn" style="justify-content:center; width:100%;">
                            <i class="fas fa-edit text-xs"></i> Edit
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── INFORMASI LOWONGAN ── --}}
            <div class="panel anim-2">
                <div class="section-header">
                    <div class="section-icon indigo"><i class="fas fa-briefcase"></i></div>
                    <div class="section-header-text">
                        <h3>Informasi Lowongan</h3>
                        <p>Detail informasi lowongan magang.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="detail-box">
                        <p class="detail-label">Judul Lowongan</p>
                        <p class="detail-value">{{ $lowongan->judul_lowongan ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Posisi Magang</p>
                        <p class="detail-value">{{ $lowongan->posisi_magang ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Divisi</p>
                        <p class="detail-value">{{ $lowongan->divisi ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Kuota Peserta</p>
                        <p class="detail-value">{{ $lowongan->kuota_peserta ?? 0 }} Peserta</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Durasi Magang</p>
                        <p class="detail-value">{{ $lowongan->durasi_magang ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Status Lowongan</p>
                        <p class="detail-value">{{ ucfirst($lowongan->status ?? '-') }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Status Lowongan</p>
                        <p class="detail-value">{{ ucfirst($lowongan->status ?? '-') }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Tanggal Dibuat</p>
                        <p class="detail-value">
                            {{ optional($lowongan->created_at)->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── FASILITAS ── --}}
            <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon green"><i class="fas fa-gift"></i></div>
                    <div class="section-header-text">
                        <h3>Fasilitas</h3>
                        <p>Benefit yang akan didapatkan peserta magang.</p>
                    </div>
                </div>
                <div class="description-box">
                    <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ $lowongan->fasilitas ?? '-' }}
                    </p>
                </div>
            </div>

            {{-- ── DESKRIPSI PEKERJAAN ── --}}
            <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon blue"><i class="fas fa-file-alt"></i></div>
                    <div class="section-header-text">
                        <h3>Deskripsi Pekerjaan</h3>
                        <p>Penjelasan pekerjaan dan aktivitas magang.</p>
                    </div>
                </div>
                <div class="description-box">
                    <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ $lowongan->deskripsi_pekerjaan ?? '-' }}
                    </p>
                </div>
            </div>

            {{-- ── REQUIREMENTS ── --}}
            <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon amber"><i class="fas fa-list-check"></i></div>
                    <div class="section-header-text">
                        <h3>Requirements / Persyaratan</h3>
                        <p>Persyaratan peserta magang.</p>
                    </div>
                </div>
                <div class="description-box">
                    <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ $lowongan->requirements ?? '-' }}
                    </p>
                </div>
            </div>

            {{-- ── INFORMASI INDUSTRI ── --}}
            <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon green"><i class="fas fa-building"></i></div>
                    <div class="section-header-text">
                        <h3>Informasi Industri</h3>
                        <p>Informasi perusahaan pengaju lowongan.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="detail-box">
                        <p class="detail-label">Nama Industri</p>
                        <p class="detail-value">{{ optional($lowongan->industri)->nama_industri ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Bidang Industri</p>
                        <p class="detail-value">{{ optional($lowongan->industri)->bidang_industri ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Kota / Kabupaten</p>
                        <p class="detail-value">{{ optional($lowongan->industri)->kota_kabupaten ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Email Industri</p>
                        <p class="detail-value break-all">{{ optional($lowongan->industri)->email_industri ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">Nomor Telepon</p>
                        <p class="detail-value">{{ optional($lowongan->industri)->nomor_telepon_industri ?? '-' }}</p>
                    </div>
                    <div class="detail-box">
                        <p class="detail-label">NIB</p>
                        <p class="detail-value">{{ optional($lowongan->industri)->nib ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- ── ALAMAT INDUSTRI ── --}}
            <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon rose"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="section-header-text">
                        <h3>Alamat Industri</h3>
                        <p>Lokasi lengkap perusahaan.</p>
                    </div>
                </div>
                <div class="description-box">
                    <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ optional($lowongan->industri)->alamat_industri ?? '-' }}
                    </p>
                </div>
            </div>

            {{-- ── DESKRIPSI INDUSTRI ── --}}
            <div class="panel anim-3">
                <div class="section-header">
                    <div class="section-icon violet"><i class="fas fa-circle-info"></i></div>
                    <div class="section-header-text">
                        <h3>Deskripsi Industri</h3>
                        <p>Gambaran umum perusahaan.</p>
                    </div>
                </div>
                <div class="description-box">
                    <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ optional($lowongan->industri)->deskripsi_industri ?? '-' }}
                    </p>
                </div>
            </div>

            {{-- ── FOOTER BACK ── --}}
            <div class="anim-4" style="padding-bottom:8px;">
                <a href="{{ route('industri.lowongan.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali ke Daftar Lowongan
                </a>
            </div>

        </div>
    </div>


    {{-- ── MODAL TOLAK ── --}}
    <div x-data="{ showRejectModal: false, rejectUrl: '', jobTitle: '' }"
         @open-reject-modal.window="showRejectModal = true; rejectUrl = $event.detail.url; jobTitle = $event.detail.title">
        <div x-show="showRejectModal" style="display:none;"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm"
             x-transition.opacity>
            <div @click.away="showRejectModal = false"
                 class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all"
                 x-show="showRejectModal" x-transition.scale.origin.bottom>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full mb-4">
                    <i class="fas fa-times-circle text-orange-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Tolak</h3>
                <p class="text-center text-gray-600 mb-6">
                    Apakah Anda yakin ingin menolak lowongan
                    <strong x-text="jobTitle"></strong>?
                    Lowongan ini tidak akan tampil untuk pelamar.
                </p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showRejectModal = false"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <form :action="rejectUrl" method="POST" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-xl transition-colors flex items-center gap-2">
                            <i class="fas fa-times"></i> Ya, Tolak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection