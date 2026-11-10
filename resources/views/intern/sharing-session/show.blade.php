@extends('layouts.app')

@section('title', 'Detail Sharing Session')

@section('content')
@php
    $isSpeaker = (int) $sharingSession->speaker_user_id === (int) auth()->id();
    $isModerator = (int) $sharingSession->moderator_user_id === (int) auth()->id();

    $materiLengkap = $sharingSession->material_status === 'lengkap';
    $bolehIsiEvaluasi = $materiLengkap && $sharingSession->evaluation_is_open;

    $statusTanggal = 'AKAN DATANG';
    $statusClass = 'bg-blue-100 text-blue-700';

    if ($sharingSession->session_date->isToday()) {
        $statusTanggal = 'HARI INI';
        $statusClass = 'bg-green-100 text-green-700';
    } elseif ($sharingSession->session_date->lt(today())) {
        $statusTanggal = 'SELESAI';
        $statusClass = 'bg-red-100 text-red-600';
    }

    $materialStatusText = 'MATERI BELUM DIISI';
    $materialStatusClass = 'bg-red-100 text-red-600';

    if ($sharingSession->material_status === 'lengkap') {
        $materialStatusText = 'MATERI LENGKAP';
        $materialStatusClass = 'bg-green-100 text-green-700';
    } elseif ($sharingSession->material_status === 'belum_lengkap') {
        $materialStatusText = 'MATERI BELUM LENGKAP';
        $materialStatusClass = 'bg-yellow-100 text-yellow-700';
    }
@endphp

<div class="min-h-screen bg-[#F4F7FF] px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('intern.sharing-session.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 mb-4">
                <i class="fas fa-arrow-left"></i>
                Kembali ke daftar sharing session
            </a>

            @if(session('success'))
                <div class="mb-5 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                    <div class="flex gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-comments text-3xl text-blue-600"></i>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ $statusTanggal }}
                                </span>

                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $materialStatusClass }}">
                                    {{ $materialStatusText }}
                                </span>
                            </div>

                            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                                {{ $sharingSession->title ?? 'Materi Belum Diisi' }}
                            </h1>

                            <p class="text-gray-500 mt-2">
                                Detail informasi kegiatan sharing session intern.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        @if($isSpeaker || $isModerator)
                            <a href="{{ route('intern.sharing-session.edit-materi', $sharingSession) }}"
                               class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-semibold shadow-sm">
                                <i class="fas fa-pen"></i>
                                Edit Data
                            </a>
                        @endif

                        @if($bolehIsiEvaluasi)
                            <a href="{{ $sharingSession->evaluation_form_link }}"
                               target="_blank"
                               class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-2xl font-semibold shadow-sm">
                                <i class="fas fa-clipboard-check"></i>
                                Isi Evaluasi
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left Content --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Deskripsi --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-11 h-11 rounded-2xl bg-purple-50 flex items-center justify-center">
                            <i class="fas fa-align-left text-purple-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">
                                Deskripsi Sharing Session
                            </h2>
                            <p class="text-sm text-gray-400">
                                Materi atau pembahasan utama kegiatan.
                            </p>
                        </div>
                    </div>

                    @if($sharingSession->description)
                        <div class="text-gray-600 leading-relaxed whitespace-pre-line">
                            {{ $sharingSession->description }}
                        </div>
                    @else
                        <div class="border border-dashed border-gray-200 rounded-2xl p-6 text-center bg-gray-50">
                            <i class="fas fa-file-alt text-3xl text-gray-300 mb-3"></i>
                            <p class="font-semibold text-gray-500">
                                Belum ada deskripsi sharing session.
                            </p>

                            @if($isSpeaker)
                                <p class="text-sm text-gray-400 mt-1">
                                    Narasumber dapat melengkapi deskripsi melalui tombol Edit Data.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Dokumentasi --}}
                <div id="dokumentasi" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-orange-50 flex items-center justify-center">
                                <i class="fas fa-image text-orange-500"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">
                                    Dokumentasi Sharing Session
                                </h2>
                                <p class="text-sm text-gray-400">
                                    Foto dokumentasi kegiatan.
                                </p>
                            </div>
                        </div>

                        @if($isModerator)
                            <a href="{{ route('intern.sharing-session.edit-materi', $sharingSession) }}"
                               class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">
                                <i class="fas fa-upload"></i>
                                Upload Dokumentasi
                            </a>
                        @endif
                    </div>

                    @if($sharingSession->documentation_photo_url)
                        <div class="rounded-3xl overflow-hidden border border-gray-200 bg-gray-100">
                            <a href="{{ $sharingSession->documentation_photo_url }}" target="_blank">
                                <img src="{{ $sharingSession->documentation_photo_url }}"
                                     alt="Dokumentasi Sharing Session"
                                     class="w-full max-h-[520px] object-cover hover:scale-[1.02] transition duration-300" loading="lazy" decoding="async">
                            </a>
                        </div>

                        <p class="text-sm text-gray-400 mt-3">
                            Klik foto untuk melihat ukuran penuh.
                        </p>
                    @else
                        <div class="border border-dashed border-gray-200 rounded-2xl p-8 text-center bg-gray-50">
                            <i class="fas fa-camera text-4xl text-gray-300 mb-3"></i>
                            <p class="font-semibold text-gray-500">
                                Belum ada dokumentasi yang diupload.
                            </p>

                            @if($isModerator)
                                <p class="text-sm text-gray-400 mt-1">
                                    Moderator dapat mengupload dokumentasi melalui tombol Upload Dokumentasi.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="space-y-6">

                {{-- Informasi Kegiatan --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-5">
                        Informasi Kegiatan
                    </h2>

                    <div class="space-y-5">

                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-calendar-alt text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-700">Tanggal</p>
                                <p class="text-sm text-gray-500">
                                    {{ $sharingSession->session_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-clock text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-700">Jam</p>
                                <p class="text-sm text-gray-500">
                                    {{ $sharingSession->start_time ? \Carbon\Carbon::parse($sharingSession->start_time)->format('H:i') : '-' }}
                                    WITA - Selesai
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-map-marker-alt text-orange-500"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-700">Lokasi</p>
                                <p class="text-sm text-gray-500">
                                    {{ $sharingSession->location ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-user-tie text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-700">Narasumber</p>
                                <p class="text-sm text-gray-500">
                                    {{ $sharingSession->speaker_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-user-check text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-700">Moderator</p>
                                <p class="text-sm text-gray-500">
                                    {{ $sharingSession->moderator_name }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Form Evaluasi --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        Form Evaluasi
                    </h2>

                    @if($sharingSession->material_status !== 'lengkap')
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-2xl p-4 text-sm font-semibold">
                            Form evaluasi belum dibuka karena materi sharing session belum lengkap.
                        </div>
                    @elseif($sharingSession->evaluation_is_open)
                        <a href="{{ $sharingSession->evaluation_form_link }}"
                           target="_blank"
                           class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-2xl font-semibold shadow-sm">
                            <i class="fas fa-clipboard-check"></i>
                            Isi Evaluasi
                        </a>

                        <p class="text-xs text-gray-400 mt-3 text-center">
                            Form evaluasi otomatis terbuka sesuai jadwal sharing session.
                        </p>
                    @elseif($sharingSession->evaluation_already_closed)
                        <div class="bg-red-50 border border-red-200 text-red-600 rounded-2xl p-4 text-sm font-semibold">
                            Form evaluasi sudah ditutup.
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 text-gray-500 rounded-2xl p-4 text-sm font-semibold">
                            Form evaluasi akan dibuka pada
                            {{ $sharingSession->evaluation_opens_at ? $sharingSession->evaluation_opens_at->format('d M Y H:i') : 'hari pelaksanaan' }}.
                        </div>
                    @endif
                </div>

                {{-- Aksi Cepat --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        Aksi Cepat
                    </h2>

                    <div class="space-y-3">
                        <a href="{{ route('intern.sharing-session.index') }}"
                           class="w-full inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-3 rounded-2xl font-semibold">
                            <i class="fas fa-list"></i>
                            Daftar Sharing Session
                        </a>

                        @if($isSpeaker || $isModerator)
                            <a href="{{ route('intern.sharing-session.edit-materi', $sharingSession) }}"
                               class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-semibold">
                                <i class="fas fa-pen"></i>
                                Edit Data
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection