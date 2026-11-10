@extends('layouts.app')

@section('title', 'Lengkapi Materi Sharing Session')

@section('content')
@php
    $isSpeaker = (int) $sharingSession->speaker_user_id === (int) auth()->id();
    $isModerator = (int) $sharingSession->moderator_user_id === (int) auth()->id();
@endphp

<div class="min-h-screen bg-[#F4F7FF] px-6 py-8">

    <div class="mb-8 bg-gradient-to-r from-blue-800 to-indigo-600 rounded-3xl p-8 text-white shadow-lg">
        <h1 class="text-3xl font-bold">
            Lengkapi Materi Sharing Session
        </h1>

        <p class="text-blue-100 mt-2">
            Lengkapi materi atau dokumentasi sesuai peran Anda.
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">

        <div class="mb-6 bg-blue-50 border border-blue-100 rounded-2xl p-5 text-sm text-gray-600 space-y-1">
            <p>
                <strong>Tanggal:</strong>
                {{ $sharingSession->session_date->format('d M Y') }}
            </p>

            <p>
                <strong>Jam:</strong>
                {{ $sharingSession->start_time
                    ? \Carbon\Carbon::parse($sharingSession->start_time)->format('H:i')
                    : '-' }}
                WITA
            </p>

            <p>
                <strong>Lokasi:</strong>
                {{ $sharingSession->location ?? '-' }}
            </p>

            <p>
                <strong>Narasumber:</strong>
                {{ $sharingSession->speakerUser?->name ?? $sharingSession->speaker ?? '-' }}
            </p>

            <p>
                <strong>Moderator:</strong>
                {{ $sharingSession->moderatorUser?->name ?? $sharingSession->moderator ?? '-' }}
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
                <p class="font-bold mb-2">Terjadi kesalahan:</p>

                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('intern.sharing-session.update-materi', $sharingSession) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6">

                {{-- KHUSUS NARASUMBER --}}
                @if($isSpeaker)
                    <div class="border border-blue-100 rounded-3xl p-5 bg-blue-50/40">
                        <div class="mb-5">
                            <p class="text-lg font-bold text-gray-800">
                                Materi Narasumber
                            </p>

                            <p class="text-sm text-gray-500 mt-1">
                                Isi judul dan deskripsi materi sharing session.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-2">
                                    Judul Sharing Session
                                </label>

                                <input
                                    type="text"
                                    name="title"
                                    value="{{ old('title', $sharingSession->title) }}"
                                    placeholder="Contoh: Tips Adaptasi di Dunia Kerja"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-3 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-600 mb-2">
                                    Deskripsi Materi
                                </label>

                                <textarea
                                    name="description"
                                    rows="6"
                                    placeholder="Tuliskan deskripsi singkat materi sharing session..."
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-3 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $sharingSession->description) }}</textarea>
                            </div>

                            <div class="bg-white border border-blue-100 rounded-2xl px-4 py-3 text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                Link evaluasi sudah diatur otomatis oleh sistem dan akan terbuka pada hari pelaksanaan.
                            </div>
                        </div>
                    </div>
                @endif

                {{-- KHUSUS MODERATOR --}}
                @if($isModerator)
                    <div class="border border-purple-100 rounded-3xl p-5 bg-purple-50/40">
                        <div class="mb-5">
                            <p class="text-lg font-bold text-gray-800">
                                Dokumentasi Moderator
                            </p>

                            <p class="text-sm text-gray-500 mt-1">
                                Upload foto dokumentasi sharing session.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-2">
                                Upload Dokumentasi Sharing Session
                            </label>

                            <input
                                type="file"
                                name="documentation_photo"
                                accept="image/*"
                                class="w-full border border-gray-200 rounded-2xl px-4 py-3 bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500">

                            <p class="text-xs text-gray-400 mt-2">
                                Format: JPG, JPEG, PNG, WEBP. Maksimal 5 MB. Foto akan otomatis dikompres oleh sistem.
                            </p>
                        </div>

                        @if($sharingSession->documentation_photo_url)
                            <div class="mt-5">
                                <p class="text-sm font-bold text-gray-600 mb-3">
                                    Dokumentasi Saat Ini
                                </p>

                                <a href="{{ $sharingSession->documentation_photo_url }}" target="_blank">
                                    <img
                                        src="{{ $sharingSession->documentation_photo_url }}"
                                        alt="Dokumentasi Sharing Session"
                                        class="w-full max-w-md rounded-2xl border border-gray-200 shadow-sm" loading="lazy" decoding="async">
                                </a>

                                <p class="text-xs text-gray-400 mt-2">
                                    Klik foto untuk melihat ukuran penuh. Jika upload foto baru, foto lama akan diganti.
                                </p>
                            </div>
                        @else
                            <div class="mt-5 border border-dashed border-gray-200 rounded-2xl p-5 text-sm text-gray-400 bg-white">
                                Belum ada dokumentasi yang diupload.
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8">

                <a href="{{ route('intern.sharing-session.show', $sharingSession) }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold">
                    <i class="fas fa-arrow-left"></i>
                    Batal
                </a>

                @if($isSpeaker)
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow">
                        <i class="fas fa-save"></i>
                        Simpan Materi
                    </button>
                @endif

                @if($isModerator)
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-semibold shadow">
                        <i class="fas fa-upload"></i>
                        Upload Dokumentasi
                    </button>
                @endif

            </div>

        </form>

    </div>

</div>
@endsection