@extends('layouts.app')

@section('title', 'Sharing Session')

@section('content')
<div class="min-h-screen bg-[#F4F7FF] px-6 py-8">

    <div class="mb-8 bg-gradient-to-r from-blue-800 to-indigo-600 rounded-3xl p-8 text-white shadow-lg">
        <p class="text-blue-100 text-sm font-semibold uppercase tracking-wider mb-2">
            Peserta Magang
        </p>

        <h1 class="text-3xl font-bold">
            Sharing Session
        </h1>

        <p class="text-blue-100 mt-2">
            Lihat jadwal sharing session dan isi evaluasi pada hari pelaksanaan.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex justify-end">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">

            <select name="filter"
                   
                    class="bg-white border border-gray-200 rounded-2xl px-5 py-3 shadow-sm text-gray-700 font-medium">

                <option value="semua"
                    {{ ($filter ?? 'semua') == 'semua' ? 'selected' : '' }}>
                    Semua Jadwal
                </option>

                <option value="hari-ini"
                    {{ ($filter ?? 'semua') == 'hari-ini' ? 'selected' : '' }}>
                    Hari Ini
                </option>

                <option value="akan-datang"
                    {{ ($filter ?? 'semua') == 'akan-datang' ? 'selected' : '' }}>
                    Akan Datang
                </option>

                <option value="selesai"
                    {{ ($filter ?? 'semua') == 'selesai' ? 'selected' : '' }}>
                    Selesai
                </option>
            </select>

            <select name="role_filter"
                   
                    class="bg-white border border-gray-200 rounded-2xl px-5 py-3 shadow-sm text-gray-700 font-medium">

                <option value="semua"
                    {{ ($roleFilter ?? 'semua') == 'semua' ? 'selected' : '' }}>
                    Semua Peran
                </option>

                <option value="narasumber-saya"
                    {{ ($roleFilter ?? 'semua') == 'narasumber-saya' ? 'selected' : '' }}>
                    Saya Narasumber
                </option>

                <option value="moderator-saya"
                    {{ ($roleFilter ?? 'semua') == 'moderator-saya' ? 'selected' : '' }}>
                    Saya Moderator
                </option>
            </select>

        </form>
    </div>

    <div class="grid gap-6">

        @forelse($sessions as $session)

            @php
                $isSpeaker = (int) $session->speaker_user_id === (int) auth()->id();
                $isModerator = (int) $session->moderator_user_id === (int) auth()->id();

                $formEvaluasiUrl = $session->evaluation_form_link;
                $materiLengkap = $session->material_status === 'lengkap';
                $bolehIsiEvaluasi = $materiLengkap && $session->evaluation_is_open;
            @endphp

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">

                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">

                    <div class="flex gap-4 flex-1">

                        <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-comments text-blue-600 text-xl"></i>
                        </div>

                        <div class="flex-1">

                            <div class="flex flex-wrap items-center gap-2 mb-2">

                                <h2 class="text-xl font-bold text-gray-800">
                                    {{ $session->title ?? 'Materi Belum Diisi' }}
                                </h2>

                                @if($session->session_date->isToday())
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                        HARI INI
                                    </span>
                                @elseif($session->session_date->isTomorrow())
                                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">
                                        BESOK
                                    </span>
                                @elseif($session->session_date->isPast())
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold">
                                        SELESAI
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                        AKAN DATANG
                                    </span>
                                @endif

                                @if($session->material_status === 'lengkap')
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                        MATERI LENGKAP
                                    </span>
                                @elseif($session->material_status === 'belum_lengkap')
                                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">
                                        MATERI BELUM LENGKAP
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold">
                                        MATERI BELUM DIISI
                                    </span>
                                @endif

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm text-gray-500">

                                <p>
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                    {{ $session->session_date->format('d M Y') }}
                                </p>

                                <p>
                                    <i class="fas fa-clock text-green-500 mr-2"></i>
                                    {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-' }}
                                    WITA - Selesai
                                </p>

                                <p>
                                    <i class="fas fa-map-marker-alt text-orange-500 mr-2"></i>
                                    {{ $session->location ?? '-' }}
                                </p>

                                <p>
                                    <i class="fas fa-user-tie text-purple-500 mr-2"></i>
                                    Narasumber: {{ $session->speaker_name }}
                                </p>

                                <p>
                                    <i class="fas fa-user-check text-indigo-500 mr-2"></i>
                                    Moderator: {{ $session->moderator_name }}
                                </p>

                            </div>

                            {{-- Deskripsi singkat --}}
                            @if($session->description)
                                <div class="mt-4 bg-gray-50 rounded-2xl p-4 text-gray-600 text-sm leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit($session->description, 180) }}

                                    @if(strlen($session->description) > 180)
                                        <a href="{{ route('intern.sharing-session.show', $session) }}"
                                           class="text-blue-600 font-semibold hover:text-blue-800 ml-1">
                                            Lihat selengkapnya
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="mt-4 bg-gray-50 rounded-2xl p-4 text-gray-400 text-sm">
                                    Belum ada deskripsi sharing session.
                                </div>
                            @endif

                            {{-- Dokumentasi thumbnail --}}
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">
                                    Dokumentasi Sharing Session
                                </p>

                                @if($session->documentation_photo_url)
                                    <a href="{{ route('intern.sharing-session.show', $session) }}#dokumentasi"
                                       class="inline-block group">
                                        <div class="relative w-40 h-28 rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-gray-100">
                                            <img
                                                src="{{ $session->documentation_photo_url }}"
                                                alt="Dokumentasi Sharing Session"
                                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" decoding="async">

                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                                                <span class="opacity-0 group-hover:opacity-100 text-white text-xs font-bold">
                                                    Lihat Foto
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    <div class="border border-dashed border-gray-200 rounded-2xl p-4 text-sm text-gray-400 bg-white">
                                        Belum ada dokumentasi yang diupload.
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    {{-- Tombol kanan --}}
                    <div class="lg:text-right flex flex-col gap-2 min-w-[220px]">

                        <a href="{{ route('intern.sharing-session.show', $session) }}"
                           class="inline-flex items-center justify-center gap-2 bg-white hover:bg-blue-50 text-blue-700 border border-blue-100 px-6 py-3 rounded-2xl font-semibold shadow-sm">
                            <i class="fas fa-eye"></i>
                            Lihat Detail
                        </a>

                        @if($isSpeaker)
                            <a href="{{ route('intern.sharing-session.edit-materi', $session) }}"
                               class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-semibold shadow">
                                <i class="fas fa-pen"></i>
                                Lengkapi Materi
                            </a>
                        @elseif($isModerator)
                            <a href="{{ route('intern.sharing-session.edit-materi', $session) }}"
                               class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-2xl font-semibold shadow">
                                <i class="fas fa-upload"></i>
                                Upload Dokumentasi
                            </a>
                        @endif

                        {{-- Tombol Evaluasi --}}
                        @if($bolehIsiEvaluasi)
                            <a href="{{ $formEvaluasiUrl }}"
                               target="_blank"
                               class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-semibold shadow">
                                <i class="fas fa-clipboard-check"></i>
                                Isi Evaluasi
                            </a>
                        @elseif(!$materiLengkap)
                            <span class="inline-flex items-center justify-center gap-2 bg-yellow-50 text-yellow-700 border border-yellow-200 px-6 py-3 rounded-2xl font-semibold">
                                <i class="fas fa-file-circle-xmark"></i>
                                Materi Belum Lengkap
                            </span>
                        @elseif($session->evaluation_already_closed)
                            <span class="inline-flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-200 px-6 py-3 rounded-2xl font-semibold">
                                <i class="fas fa-lock"></i>
                                Form Sudah Ditutup
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-500 border border-gray-200 px-6 py-3 rounded-2xl font-semibold">
                                <i class="fas fa-clock"></i>
                                Form Belum Dibuka
                            </span>
                        @endif

                    </div>

                </div>

            </div>

        @empty

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>

                <p class="font-semibold text-lg">
                    Belum ada jadwal sharing session.
                </p>
            </div>

        @endforelse

    </div>

    @if($sessions->hasPages())
        <div class="mt-8">
            {{ $sessions->links() }}
        </div>
    @endif

</div>
@endsection