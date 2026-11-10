@php
    $selectedSpeakerType = old(
        'speaker_input_type',
        isset($sharingSession) && $sharingSession->speaker_user_id ? 'intern' : 'manual'
    );

    $selectedModeratorType = old(
        'moderator_input_type',
        isset($sharingSession) && $sharingSession->moderator_user_id ? 'intern' : 'manual'
    );

    $selectedSpeakerUserId = old('speaker_user_id', $sharingSession->speaker_user_id ?? '');
    $selectedModeratorUserId = old('moderator_user_id', $sharingSession->moderator_user_id ?? '');

    $manualSpeakerName = old('speaker', $sharingSession->speaker ?? '');
    $manualModeratorName = old('moderator', $sharingSession->moderator ?? '');
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
        <strong>Terdapat kesalahan input:</strong>

        <ul class="mt-2 list-inside list-disc text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">

    {{-- Nama Pemateri / Narasumber --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Nama Pemateri / Narasumber <span class="text-red-500">*</span>
        </label>

        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label class="flex cursor-pointer items-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-600 hover:border-blue-300">
                <input type="radio"
                       name="speaker_input_type"
                       value="manual"
                       class="text-blue-600 focus:ring-blue-500"
                       data-participant-toggle="speaker"
                       {{ $selectedSpeakerType === 'manual' ? 'checked' : '' }}>
                Tambah nama baru
            </label>

            <label class="flex cursor-pointer items-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-600 hover:border-blue-300">
                <input type="radio"
                       name="speaker_input_type"
                       value="intern"
                       class="text-blue-600 focus:ring-blue-500"
                       data-participant-toggle="speaker"
                       {{ $selectedSpeakerType === 'intern' ? 'checked' : '' }}>
                Pilih intern aktif
            </label>
        </div>

        <div data-participant-field="speaker-manual">
            <input type="text"
                   name="speaker"
                   value="{{ $manualSpeakerName }}"
                   placeholder="Masukkan nama pemateri baru"
                   class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>

        <div data-participant-field="speaker-intern" class="hidden">
            <select name="speaker_user_id"
                    class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih pemateri dari intern aktif</option>

                @foreach(($activeInternUsers ?? collect()) as $user)
                    <option value="{{ $user->id }}" {{ (string) $selectedSpeakerUserId === (string) $user->id ? 'selected' : '' }}>
                        {{ $user->intern?->name ?? $user->name }}{{ $user->intern?->institution ? ' - ' . $user->intern->institution : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <p class="mt-2 text-xs text-gray-400">
            Admin dapat mengetik nama baru atau memilih user intern yang statusnya aktif.
        </p>
    </div>

    {{-- Nama Moderator --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Nama Moderator <span class="text-red-500">*</span>
        </label>

        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label class="flex cursor-pointer items-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-600 hover:border-blue-300">
                <input type="radio"
                       name="moderator_input_type"
                       value="manual"
                       class="text-blue-600 focus:ring-blue-500"
                       data-participant-toggle="moderator"
                       {{ $selectedModeratorType === 'manual' ? 'checked' : '' }}>
                Tambah nama baru
            </label>

            <label class="flex cursor-pointer items-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-600 hover:border-blue-300">
                <input type="radio"
                       name="moderator_input_type"
                       value="intern"
                       class="text-blue-600 focus:ring-blue-500"
                       data-participant-toggle="moderator"
                       {{ $selectedModeratorType === 'intern' ? 'checked' : '' }}>
                Pilih intern aktif
            </label>
        </div>

        <div data-participant-field="moderator-manual">
            <input type="text"
                   name="moderator"
                   value="{{ $manualModeratorName }}"
                   placeholder="Masukkan nama moderator baru"
                   class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>

        <div data-participant-field="moderator-intern" class="hidden">
            <select name="moderator_user_id"
                    class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih moderator dari intern aktif</option>

                @foreach(($activeInternUsers ?? collect()) as $user)
                    <option value="{{ $user->id }}" {{ (string) $selectedModeratorUserId === (string) $user->id ? 'selected' : '' }}>
                        {{ $user->intern?->name ?? $user->name }}{{ $user->intern?->institution ? ' - ' . $user->intern->institution : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <p class="mt-2 text-xs text-gray-400">
            Jika memilih intern aktif, intern tersebut dapat mengakses tugasnya di menu sharing session.
        </p>
    </div>

    {{-- Tanggal --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Tanggal <span class="text-red-500">*</span>
        </label>

        <input type="date"
               name="session_date"
               value="{{ old('session_date', isset($sharingSession) && $sharingSession->session_date ? \Carbon\Carbon::parse($sharingSession->session_date)->format('Y-m-d') : '') }}"
               class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
               required>
    </div>

    {{-- Jam Mulai --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Jam Mulai
        </label>

        <input type="time"
               name="start_time"
               value="{{ old('start_time', isset($sharingSession) && $sharingSession->start_time ? \Carbon\Carbon::parse($sharingSession->start_time)->format('H:i') : '') }}"
               class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
    </div>

    {{-- Lokasi --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Lokasi / Ruangan
        </label>

        <input type="text"
               name="location"
               value="{{ old('location', $sharingSession->location ?? '') }}"
               placeholder="Masukkan lokasi atau ruangan"
               class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
    </div>

    {{-- Materi Sharing Session --}}
    <div class="mt-6 md:col-span-2">
        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-xl font-bold text-gray-800">
                Materi Sharing Session
            </h3>

            <p class="mt-1 text-sm text-gray-400">
                Admin dapat mengisi informasi materi sharing session.
            </p>
        </div>
    </div>

    {{-- Judul Materi --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Judul Materi
        </label>

        <input type="text"
               name="title"
               value="{{ old('title', $sharingSession->title ?? '') }}"
               placeholder="Masukkan judul materi"
               class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
    </div>

    {{-- Deskripsi Materi --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Deskripsi Materi
        </label>

        <textarea name="description"
                  rows="6"
                  placeholder="Tuliskan deskripsi atau ringkasan materi"
                  class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('description', $sharingSession->description ?? '') }}</textarea>
    </div>

    {{-- Form Evaluasi --}}
<div class="md:col-span-2">
    <label class="mb-2 block text-sm font-bold text-gray-600">
        Link Form Evaluasi
    </label>

    <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-700">
        <div class="flex items-start gap-3">
            <i class="fas fa-link mt-1"></i>

            <div>
                <p class="font-bold">
                    Link evaluasi otomatis:
                </p>

                <p class="mt-1 break-all">
                    https://s.komdigi.go.id/Sharingsession-magang
                </p>

                <p class="mt-2 text-xs text-blue-600">
                    Link ini akan terbuka otomatis pada hari pelaksanaan sesuai jam mulai sharing session.
                </p>
            </div>
        </div>
    </div>
</div>

    {{-- Dokumentasi --}}
    <div class="mt-6 md:col-span-2">
        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-xl font-bold text-gray-800">
                Dokumentasi Sharing Session
            </h3>

            <p class="mt-1 text-sm text-gray-400">
                Admin dapat mengunggah dokumentasi kegiatan sharing session.
            </p>
        </div>
    </div>

    {{-- Upload Dokumentasi --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-bold text-gray-600">
            Upload Dokumentasi
        </label>

        <input type="file"
               name="documentation_photo"
               accept="image/jpeg,image/png,image/webp"
               class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">

        <p class="mt-2 text-xs text-gray-400">
            Format file: JPG, JPEG, PNG, atau WEBP. Maksimal 5 MB.
        </p>

        @if(isset($sharingSession) && $sharingSession->documentation_photo_url)
            <div class="mt-5 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                <p class="mb-3 text-sm font-bold text-gray-600">
                    Dokumentasi Saat Ini
                </p>

                <a href="{{ $sharingSession->documentation_photo_url }}" target="_blank">
                    <img src="{{ $sharingSession->documentation_photo_url }}"
                         alt="Dokumentasi Sharing Session"
                         class="w-full max-w-md rounded-2xl border border-gray-200 shadow-sm" loading="lazy" decoding="async">
                </a>

                <p class="mt-2 text-xs text-gray-400">
                    Upload foto baru jika ingin mengganti dokumentasi lama.
                </p>
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const participants = ['speaker', 'moderator'];

        function syncParticipantField(participant) {
            const checked = document.querySelector(`input[name="${participant}_input_type"]:checked`);
            const selectedType = checked ? checked.value : 'manual';
            const manualField = document.querySelector(`[data-participant-field="${participant}-manual"]`);
            const internField = document.querySelector(`[data-participant-field="${participant}-intern"]`);

            if (!manualField || !internField) {
                return;
            }

            manualField.classList.toggle('hidden', selectedType !== 'manual');
            internField.classList.toggle('hidden', selectedType !== 'intern');
        }

        participants.forEach(function (participant) {
            document
                .querySelectorAll(`input[name="${participant}_input_type"]`)
                .forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        syncParticipantField(participant);
                    });
                });

            syncParticipantField(participant);
        });
    });
</script>