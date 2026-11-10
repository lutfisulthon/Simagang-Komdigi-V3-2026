@extends('layouts.app')

@section('title', 'Tambah Pengajuan Magang - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-pengajuan-create.css') }}?v={{ @filemtime(public_path('css/pages/institusi-pengajuan-create.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="hero-strip px-5 sm:px-8 py-6 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight">
                            Tambah Pengajuan Magang
                        </h1>
                    </div>
                    <div class="soft-badge rounded-2xl px-4 py-4 text-sm font-semibold text-slate-700 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-file-pen"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Form</p>
                                <p class="text-base font-bold text-slate-900">Pengajuan Magang</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="border-b border-slate-100 bg-white px-5 sm:px-8 py-4 sm:py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <i class="fas fa-edit text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Lengkapi Data</p>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">Informasi Pengajuan</h2>
                        </div>
                    </div>
                </div>

                <div class="p-5 sm:p-8">
                    <form method="POST" action="{{ route('institusi.pengajuan.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="section-card p-5 sm:p-6 mb-6 sm:mb-8">
                            <div class="flex items-start gap-4 mb-6">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 flex-shrink-0">
                                    <i class="fas fa-briefcase text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Informasi Pengerjaan Magang
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-500">Isi periode magang, tujuan kegiatan, dan
                                        unggah surat pengajuan.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                <div>
                                    <label for="no_surat" class="field-label">
                                        Nomor Surat Pengajuan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="no_surat" id="no_surat" value="{{ old('no_surat') }}"
                                        placeholder="contoh: 13035/UN4.1.17/HM.01.01/2025" required class="field-input">
                                    @error('no_surat')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror

                                    <input type="hidden" name="lowongan_id" value="{{ $lowongan?->id ?? '' }}">
                                </div>

                                <div>
                                    <label for="tujuan_surat" class="field-label">
                                        Penandatangan Surat <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="tujuan_surat" id="tujuan_surat"
                                        value="{{ old('tujuan_surat') }}"
                                        placeholder="contoh: Wakil Dekan Bidang Akademik dan Kemahasiswaan" required
                                        class="field-input">
                                    @error('tujuan_surat')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="field-label">
                                        Tanggal Masuk <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                        class="field-input">
                                    @error('start_date')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="field-label">Tanggal Keluar <span class="text-red-500">*</span></label>
                                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                        class="field-input">
                                    @error('end_date')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="field-label">
                                        Keperluan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="keperluan" required class="field-select">
                                        <option value="" disabled {{ old('keperluan') ? '' : 'selected' }}>Pilih
                                        </option>
                                        @foreach (['Magang', 'KKN Profesi', 'PKL', 'Praktek Industri', 'Magang Industri', 'Guru Magang Industri', 'Job on Training'] as $p)
                                            <option value="{{ $p }}"
                                                {{ old('keperluan') == $p ? 'selected' : '' }}>{{ $p }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('keperluan')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="surat_magang" class="field-label">
                                        Surat Magang (.pdf) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="surat_magang" id="surat_magang" accept=".pdf" required
                                        class="upload-box">
                                    @error('surat_magang')
                                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                            <i class="fas fa-exclamation-circle"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="section-card p-5 sm:p-6 mb-6 sm:mb-8">
                            <div class="flex items-start gap-4 mb-6">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 flex-shrink-0">
                                    <i class="fas fa-user text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Informasi Calon Peserta Magang
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-500">Isi data dan kontak calon peserta magang.</p>
                                </div>
                            </div>

                            @if($lowongan)
                                <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-4">
                                    <div class="flex items-center gap-2 text-blue-700">
                                        <i class="fas fa-users"></i>
                                        <span>
                                            Kuota Lowongan tersedia:
                                            <strong>{{ $lowongan->kuota_peserta }}</strong> peserta
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <div id="intern-container">
                                <div class="intern-card mb-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-md font-semibold text-blue-700 peserta-title">
                                            Calon Peserta Magang 1
                                        </h3>
                                        <button type="button" onclick="removeIntern(this)"
                                            class="delete-btn text-red-500 hover:text-red-700 font-semibold transition-colors hidden">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    </div>

                                    <div class="mb-6">
                                        <label class="field-label">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="name[]" required class="field-input">
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                        <div>
                                            <label class="field-label">
                                                Email <span class="text-red-500">*</span>
                                            </label>
                                            <input type="email" name="email[]" required class="field-input">
                                        </div>

                                        <div>
                                            <label class="field-label">
                                                Jurusan <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="jurusan[]" required class="field-input">
                                        </div>

                                        <div>
                                            <label class="field-label">
                                                Jenis Kelamin <span class="text-red-500">*</span>
                                            </label>
                                            <select name="jenis_kelamin[]" required class="field-select">
                                                <option value="">Pilih</option>
                                                <option value="L">Laki-laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="field-label">
                                                Nomor Telepon <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="no_telp[]" required class="field-input">
                                        </div>

                                        <div>
                                            <label for="soft_skill" class="field-label">Soft Skill</label>
                                            <input type="text" name="soft_skill[]" id="soft_skill"
                                                placeholder="contoh: Komunikasi" class="field-input">
                                        </div>

                                        <div>
                                            <label for="hard_skill" class="field-label">Hard Skill</label>
                                            <input type="text" name="hard_skill[]" id="hard_skill"
                                                placeholder="contoh: Pemrograman" class="field-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <span class="text-sm text-slate-500" id="count-info">1 calon peserta magang ditambahkan</span>
                            <button
                                type="button"
                                onclick="addIntern()"
                                {{ isset($lowongan) && $lowongan->kuota_peserta <= 0 ? 'disabled' : '' }}
                                class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Peserta
                            </button>
                        </div>

                        <div
                            class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 pt-6">
                            <a href="{{ route('institusi.pengajuan.index') }}" class="action-link">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </a>
                            <button type="submit"
                                class="primary-btn inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-8 py-3 text-sm sm:text-base font-semibold text-white shadow-lg transition hover:shadow-xl">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        const maxPeserta = {{ $lowongan?->kuota_peserta ?? 999999 }};
        let count = 1;

        function updateTitles() {
            const cards = document.querySelectorAll('.intern-card');
            cards.forEach((card, index) => {
                card.querySelector('.peserta-title').innerText = 'Calon Peserta Magang ' + (index + 1);

                // Tampilkan tombol hapus hanya jika ada lebih dari 1 kartu
                const deleteBtn = card.querySelector('.delete-btn');
                if (cards.length > 1) {
                    deleteBtn.classList.remove('hidden');
                } else {
                    deleteBtn.classList.add('hidden');
                }
            });
            document.getElementById('count-info').innerText = cards.length + ' calon peserta magang ditambahkan';
        }

        function addIntern() {
            const container = document.getElementById('intern-container');
            const jumlahPeserta = container.querySelectorAll('.intern-card').length;

            if (jumlahPeserta >= maxPeserta) {
                alert(
                    `Kuota yang tersedia hanya ${maxPeserta} peserta.`
                );
                return;
            }

            const firstCard = container.querySelector('.intern-card');
            const newCard = firstCard.cloneNode(true);

            newCard.querySelectorAll('input').forEach(input => {
                input.value = '';
            });

            newCard.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });

            container.appendChild(newCard);

            updateTitles();
        }

        function removeIntern(button) {
            const card = button.closest('.intern-card');
            const container = document.getElementById('intern-container');

            // Pastikan minimal ada 1 kartu
            if (container.querySelectorAll('.intern-card').length > 1) {
                card.remove();
                updateTitles();
            } else {
                alert('Minimal harus ada 1 peserta');
            }
        }
    </script>
@endsection