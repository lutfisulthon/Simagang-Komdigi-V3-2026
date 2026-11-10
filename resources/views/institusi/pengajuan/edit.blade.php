@extends('layouts.app')

@section('title', 'Edit Pengajuan Magang - Sistem Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/institusi-pengajuan-edit.css') }}?v={{ @filemtime(public_path('css/pages/institusi-pengajuan-edit.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="hero-strip px-5 sm:px-8 py-6 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Institusi Dashboard</p>
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Edit Pengajuan Magang</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Ubah informasi pengajuan magang Anda dengan
                            tampilan yang konsisten dan rapi.</p>
                    </div>

                    <div class="soft-badge rounded-2xl px-4 py-4 text-sm font-semibold text-slate-700 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-file-pen"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Form</p>
                                <p class="text-base font-bold text-slate-900">Edit Pengajuan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-edit text-blue-200 text-base"></i>
                    <h2>Form Edit Pengajuan Magang</h2>
                </div>

                <div class="panel-body">
                    <form id="edit-pengajuan-form" method="POST" action="{{ route('institusi.pengajuan.update', $pengajuan->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

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
                                    <input type="text" name="no_surat" id="no_surat"
                                        value="{{ old('no_surat', $pengajuan->no_surat) }}"
                                        placeholder="contoh: 13035/UN4.1.17/HM.01.01/2025" required class="field-input">
                                    @error('no_surat')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="tujuan_surat" class="field-label">
                                        Penandatangan Surat <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="tujuan_surat" id="tujuan_surat"
                                        value="{{ old('tujuan_surat', $pengajuan->tujuan_surat) }}"
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
                                    <input type="date" name="start_date"
                                        value="{{ old('start_date', $pengajuan->start_date) }}" required
                                        class="field-input">
                                    @error('start_date')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="field-label">Tanggal Keluar <span class="text-red-500">*</span></label>
                                    <input type="date" name="end_date"
                                        value="{{ old('end_date', $pengajuan->end_date) }}" required class="field-input">
                                    @error('end_date')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="field-label">
                                        Keperluan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="keperluan" required class="field-select">
                                        <option value="" disabled
                                            {{ old('keperluan', $pengajuan->keperluan) ? '' : 'selected' }}>Pilih</option>
                                        @foreach (['Magang', 'KKN Profesi', 'PKL', 'Praktek Industri', 'Magang Industri', 'Guru Magang Industri', 'Job on Training'] as $p)
                                            <option value="{{ $p }}"
                                                {{ old('keperluan', $pengajuan->keperluan) == $p ? 'selected' : '' }}>
                                                {{ $p }}</option>
                                        @endforeach
                                    </select>
                                    @error('keperluan')
                                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="surat_magang" class="field-label">
                                        Surat Magang (.pdf) <span class="text-slate-500 text-xs font-medium">(Kosongkan jika
                                            tidak ingin mengubah)</span>
                                    </label>
                                    @if ($pengajuan->surat_path)
                                        <p class="mb-2 text-xs text-slate-500">
                                            File saat ini: <a href="{{ asset('storage/' . $pengajuan->surat_path) }}"
                                                class="text-blue-600 hover:underline" target="_blank">Lihat file</a>
                                        </p>
                                    @endif
                                    <input type="file" name="surat_magang" id="surat_magang" accept=".pdf"
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

                            <div id="intern-container">

                                @forelse($pengajuan->details as $detail)
                                    <div class="intern-card mb-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-md font-semibold text-blue-700 peserta-title">
                                                Calon Peserta Magang {{ $loop->iteration }}
                                            </h3>
                                            <button type="button" onclick="removeIntern(this)"
                                                class="delete-btn text-red-500 hover:text-red-700 font-semibold transition-colors {{ $pengajuan->details->count() == 1 ? 'hidden' : '' }}">
                                                <i class="fas fa-trash mr-1"></i>Hapus
                                            </button>
                                        </div>

                                        <div class="mb-6">
                                            <label class="field-label">
                                                Nama Lengkap <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="name[]"
                                                value="{{ old('name.' . $loop->index, $detail->nama) }}" required
                                                class="field-input">
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                            <div>
                                                <label class="field-label">
                                                    Email <span class="text-red-500">*</span>
                                                </label>
                                                <input type="email" name="email[]"
                                                    value="{{ old('email.' . $loop->index, $detail->email) }}" required
                                                    class="field-input">
                                            </div>

                                            <div>
                                                <label class="field-label">
                                                    Jurusan <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="jurusan[]"
                                                    value="{{ old('jurusan.' . $loop->index, $detail->jurusan) }}" required
                                                    class="field-input">
                                            </div>

                                            <div>
                                                <label class="field-label">
                                                    Jenis Kelamin <span class="text-red-500">*</span>
                                                </label>
                                                <select name="jenis_kelamin[]" required class="field-select">
                                                    <option value="">Pilih</option>
                                                    <option value="L"
                                                        {{ old('jenis_kelamin.' . $loop->index, $detail->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                                        Laki-laki</option>
                                                    <option value="P"
                                                        {{ old('jenis_kelamin.' . $loop->index, $detail->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                                        Perempuan</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="field-label">
                                                    Nomor Telepon <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="no_telp[]"
                                                    value="{{ old('no_telp.' . $loop->index, $detail->no_telp) }}" required
                                                    class="field-input">
                                            </div>

                                            <div>
                                                <label for="soft_skill" class="field-label">Soft Skill</label>
                                                <input type="text" name="soft_skill[]" id="soft_skill"
                                                    value="{{ old('soft_skill', $detail->soft_skill) }}"
                                                    placeholder="contoh: Komunikasi" class="field-input">
                                            </div>

                                            <div>
                                                <label for="hard_skill" class="field-label">Hard Skill</label>
                                                <input type="text" name="hard_skill[]" id="hard_skill"
                                                    value="{{ old('hard_skill', $detail->hard_skill) }}"
                                                    placeholder="contoh: Pemrograman" class="field-input">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="intern-card mb-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-md font-semibold text-blue-700 peserta-title">Calon Peserta Magang
                                                1</h3>
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
                                        </div>
                                    </div>
                                @endforelse

                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <span class="text-sm text-slate-500" id="count-info">{{ $pengajuan->details->count() }} calon
                                peserta magang ditambahkan</span>
                            <button type="button" onclick="addIntern()"
                                class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                <i class="fas fa-plus mr-2"></i> Tambah Peserta
                            </button>
                        </div>

                        <div
                            class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 pt-6">
                            <a href="{{ route('institusi.pengajuan.index') }}" class="action-link">
                                <i class="fas fa-arrow-left"></i>Kembali
                            </a>
                            <button type="button" id="btn-open-edit-modal"
                                class="primary-btn inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-8 py-3 text-sm sm:text-base font-semibold text-white shadow-lg transition hover:shadow-xl">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- ── CONFIRMATION MODAL ── --}}
    <div id="edit-confirm-modal"
        style="display:none;"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

            <div class="flex items-center justify-center w-14 h-14 mx-auto bg-blue-100 rounded-full mb-4">
                <i class="fas fa-save text-blue-600 text-2xl"></i>
            </div>

            <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Simpan Perubahan</h3>

            <p class="text-center text-gray-600 text-sm mb-6">
                Apakah Anda yakin ingin menyimpan perubahan pada pengajuan magang ini?
            </p>

            <div class="flex gap-3">
                <button type="button" id="btn-edit-cancel"
                    class="flex-1 px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="button" id="btn-edit-confirm"
                    class="flex-1 px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Ya, Simpan
                </button>
            </div>
        </div>
    </div>

    <script>
        // ── Intern cards ──
        let count = 1;

        function updateTitles() {
            const cards = document.querySelectorAll('.intern-card');
            cards.forEach((card, index) => {
                card.querySelector('.peserta-title').innerText = 'Calon Peserta Magang ' + (index + 1);

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
            const firstCard = container.querySelector('.intern-card');

            const newCard = firstCard.cloneNode(true);
            newCard.querySelectorAll('input').forEach(input => input.value = '');
            newCard.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

            container.appendChild(newCard);
            count++;
            updateTitles();
        }

        function removeIntern(button) {
            const card = button.closest('.intern-card');
            const container = document.getElementById('intern-container');

            if (container.querySelectorAll('.intern-card').length > 1) {
                card.remove();
                updateTitles();
            } else {
                alert('Minimal harus ada 1 peserta');
            }
        }

        // ── Confirmation modal ──
        const editModal = document.getElementById('edit-confirm-modal');
        const editForm  = document.getElementById('edit-pengajuan-form');

        document.getElementById('btn-open-edit-modal').addEventListener('click', function () {
            if (!editForm.reportValidity()) return;
            editModal.style.display = 'flex';
        });

        document.getElementById('btn-edit-cancel').addEventListener('click', function () {
            editModal.style.display = 'none';
        });

        document.getElementById('btn-edit-confirm').addEventListener('click', function () {
            editModal.style.display = 'none';
            editForm.submit();
        });

        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) editModal.style.display = 'none';
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && editModal.style.display === 'flex') {
                editModal.style.display = 'none';
            }
        });
    </script>
@endsection