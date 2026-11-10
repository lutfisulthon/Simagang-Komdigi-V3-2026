@extends('layouts.app')

@section('title', 'Detail Pengajuan Peserta Magang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/industri-pengajuan-show.css') }}?v={{ @filemtime(public_path('css/pages/industri-pengajuan-show.css')) }}">
@endpush

@section('content')
    <div class="dash-bg py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="hero-strip px-5 sm:px-8 py-6 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Detail Pengajuan Peserta Magang</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Lihat informasi pengajuan, lowongan terkait, dan daftar calon peserta magang dalam satu tampilan.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('industri.pengajuan.index') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm border border-white/10 transition hover:bg-white/15">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Kolom Kiri -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="section-card p-5 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                                    <div class="section-title mb-0">
                                        <div class="section-icon bg-blue-50 text-blue-600">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div class="section-text">
                                            <p>Informasi Surat</p>
                                            <h2>Informasi Surat Pengajuan Magang</h2>
                                        </div>
                                    </div>
                                    <a href="{{ route('pengajuan.surat', $pengajuan) }}" target="_blank"
                                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 hover:shadow-lg whitespace-nowrap">
                                        <i class="fas fa-download mr-2"></i>
                                        Download Surat
                                    </a>
                                </div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                    <div class="info-card p-4">
                                        <span class="field-label">Nomor Surat Pengajuan</span>
                                        <p class="field-value">{{ $pengajuan->no_surat }}</p>
                                    </div>
                                    <div class="info-card p-4">
                                        <span class="field-label">Penanggung Jawab</span>
                                        <p class="field-value">{{ $pengajuan->tujuan_surat }}</p>
                                    </div>
                                    <div class="info-card p-4">
                                        <span class="field-label">Tanggal Pengajuan</span>
                                        <p class="field-value">{{ $pengajuan->created_at->format('d F Y') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="section-card p-5 sm:p-6">
                                <div class="section-title">
                                    <div class="section-icon bg-indigo-50 text-indigo-600">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="section-text">
                                        <p>Pengajuan</p>
                                        <h2>Informasi Pengajuan Magang</h2>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100 hover:bg-blue-500 hover:shadow-[0_8px_20px_-6px_rgba(59,130,246,0.4)] transition-all duration-300 flex items-start gap-4 group flex-1">
                                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center flex-shrink-0 group-hover:scale-110 shadow-sm transition-transform duration-300">
                                            <i class="fas fa-briefcase text-blue-500 text-xl group-hover:text-blue-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-blue-600/80 uppercase tracking-wider mb-1 group-hover:text-blue-100 truncate">Keperluan</p>
                                            <p class="font-bold text-blue-900 group-hover:text-white truncate">{{ $pengajuan->keperluan }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-amber-50 p-5 rounded-2xl border border-amber-100 hover:bg-amber-400 hover:shadow-[0_8px_20px_-6px_rgba(245,158,11,0.4)] transition-all duration-300 flex items-start gap-4 group flex-1">
                                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center flex-shrink-0 group-hover:scale-110 shadow-sm transition-transform duration-300">
                                            <i class="fas fa-users text-amber-500 text-xl group-hover:text-amber-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-amber-600/80 uppercase tracking-wider mb-1 group-hover:text-amber-100 truncate">Jumlah Peserta</p>
                                            <p class="font-bold text-amber-900 group-hover:text-white flex items-baseline gap-1 truncate">
                                                <span class="text-2xl leading-none">{{ $pengajuan->details->count() }}</span>
                                                <span class="text-sm font-medium">Orang</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 p-5 rounded-2xl border border-green-100 hover:bg-green-500 hover:shadow-[0_8px_20px_-6px_rgba(34,197,94,0.4)] transition-all duration-300 flex items-start gap-4 group flex-1">
                                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center flex-shrink-0 group-hover:scale-110 shadow-sm transition-transform duration-300">
                                            <i class="fas fa-calendar-check text-green-500 text-xl group-hover:text-green-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-green-600/80 uppercase tracking-wider mb-1 group-hover:text-green-100 truncate">Tanggal Masuk</p>
                                            <p class="font-bold text-green-900 group-hover:text-white truncate">{{ $pengajuan->start_date }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-orange-50 p-5 rounded-2xl border border-orange-100 hover:bg-orange-500 hover:shadow-[0_8px_20px_-6px_rgba(249,115,22,0.4)] transition-all duration-300 flex items-start gap-4 group flex-1">
                                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center flex-shrink-0 group-hover:scale-110 shadow-sm transition-transform duration-300">
                                            <i class="fas fa-calendar-times text-orange-500 text-xl group-hover:text-orange-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-orange-600/80 uppercase tracking-wider mb-1 group-hover:text-orange-100 truncate">Tanggal Keluar</p>
                                            <p class="font-bold text-orange-900 group-hover:text-white truncate">{{ $pengajuan->end_date }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-card p-5 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                                    <div class="section-title mb-0">
                                        <div class="section-icon bg-blue-50 text-blue-600">
                                            <i class="fas fa-circle-info"></i>
                                        </div>
                                        <div class="section-text">
                                            <p>Status</p>
                                            <h2>Status Pengajuan Magang</h2>
                                        </div>
                                    </div>

                                    {{-- @if ($pengajuan->status == 'approved')
                                        <a href="{{ route('industri.pengajuan.surat-balasan', $pengajuan) }}" target="_blank"
                                            class="inline-flex items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-green-700 hover:shadow-lg whitespace-nowrap">
                                            <i class="fas fa-download mr-2"></i>
                                            Download Surat Balasan
                                        </a>
                                    @endif --}}
                                </div>

                                <div class="info-card p-4">
                                    <span class="field-label mb-3">Status Pengajuan</span>
                                    @php
                                        $statusClass = match ($pengajuan->status) {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'revised' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-yellow-100 text-yellow-800',
                                        };
                                    @endphp
                                    <span class="status-pill {{ $statusClass }}">{{ ucfirst($pengajuan->status) }}</span>

                                    @if ($pengajuan->status == 'revised' && $pengajuan->admin_note)
                                        <div class="mt-4 rounded-xl border border-orange-200 bg-orange-50 p-4">
                                            <h4 class="text-sm font-semibold text-orange-800 mb-2">📝 Catatan Revisi</h4>
                                            <p class="text-sm text-orange-700 whitespace-pre-wrap">{{ $pengajuan->admin_note }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="lg:col-span-1">
                            @if ($pengajuan->lowongan)
                            <div class="section-card p-5 sm:p-6 sticky top-6 border-l-4 border-l-purple-500">
                                <div class="section-title">
                                    <div class="section-icon bg-purple-50 text-purple-600">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="section-text">
                                        <p>Lowongan Terkait</p>
                                        <h2>Informasi Lowongan</h2>
                                    </div>
                                </div>

                                <div class="space-y-2.5">
                                    <div class="info-card p-3.5">
                                        <span class="field-label">Judul Lowongan</span>
                                        <p class="field-value line-clamp-2">{{ $pengajuan->lowongan->judul_lowongan }}</p>
                                    </div>
                                    <div class="info-card p-3.5">
                                        <span class="field-label">Posisi Magang</span>
                                        <p class="field-value">{{ $pengajuan->lowongan->posisi_magang }}</p>
                                    </div>
                                    <div class="info-card p-3.5">
                                        <span class="field-label">Divisi</span>
                                        <p class="field-value">{{ $pengajuan->lowongan->divisi }}</p>
                                    </div>
                                    <div class="info-card p-3.5">
                                        <span class="field-label">Kuota Peserta</span>
                                        <p class="field-value">{{ $pengajuan->lowongan->kuota_peserta }} Peserta</p>
                                    </div>
                                    <div class="info-card p-3.5">
                                        <span class="field-label">Perusahaan</span>
                                        <p class="field-value">{{ optional($pengajuan->lowongan->industri)->nama_industri ?? '-' }}</p>
                                    </div>
                                    <div class="info-card p-3.5">
                                        <span class="field-label">Status Lowongan</span>
                                        @php
                                            $lowonganStatusClass = match($pengajuan->lowongan->status) {
                                                'dibuka' => 'bg-green-100 text-green-800',
                                                'ditutup' => 'bg-red-100 text-red-800',
                                                default => 'bg-yellow-100 text-yellow-800'
                                            };
                                        @endphp
                                        <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold {{ $lowonganStatusClass }}">{{ ucfirst($pengajuan->lowongan->status) }}</span>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <a href="#"
                                        class="w-full inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:shadow-lg hover:from-purple-700 hover:to-purple-800">
                                        <i class="fas fa-eye mr-2"></i>Lihat Detail Lengkap
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="section-card p-5 sm:p-6 mt-6">
                        <div class="section-title">
                            <div class="section-icon bg-blue-50 text-blue-600">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="section-text">
                                <p>Peserta</p>
                                <h2>Daftar Calon Peserta Magang</h2>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach ($pengajuan->details as $detail)
                                <div class="info-card p-5 sm:p-6 border-l-4 border-l-blue-400">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-base font-bold text-blue-600">
                                            <i class="fas fa-user-circle mr-2"></i>Peserta #{{ $loop->iteration }}
                                        </h3>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <span class="field-label">Nama</span>
                                            <p class="field-value">{{ $detail->nama }}</p>
                                        </div>
                                        <div>
                                            <span class="field-label">Jurusan</span>
                                            <p class="field-value">{{ $detail->jurusan }}</p>
                                        </div>
                                        <div>
                                            <span class="field-label">Email</span>
                                            <p class="field-value break-all text-blue-600">{{ $detail->email }}</p>
                                        </div>
                                        <div>
                                            <span class="field-label">No Telepon</span>
                                            <p class="field-value">{{ $detail->no_telp }}</p>
                                        </div>
                                        <div>
                                            <span class="field-label">Jenis Kelamin</span>
                                            <p class="field-value">{{ $detail->jenis_kelamin === 'P' ? 'Perempuan' : 'Lelaki' }}</p>
                                        </div>
                                        <div>
                                            <span class="field-label">Soft Skill</span>
                                            <p class="field-value">{{ $detail->soft_skill ?? '—' }}</p>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <span class="field-label">Hard Skill</span>
                                            <p class="field-value">{{ $detail->hard_skill ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Form Update Status -->
                    <div class="section-card p-5 sm:p-6 mt-6">
                        <div class="section-title">
                            <div class="section-icon bg-amber-50 text-amber-600">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="section-text">
                                <p>Aksi</p>
                                <h2>Update Status Pengajuan</h2>
                            </div>
                        </div>

                        <form id="update-status-form" method="POST"
                            action="{{ route('industri.pengajuan.update-status', $pengajuan) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="notify_whatsapp" id="notify_whatsapp" value="0">

                            <div class="grid grid-cols-1 gap-6 mb-6">
                                <div>
                                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Status Pengajuan
                                    </label>
                                    <select name="status" id="status" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        {{ in_array($pengajuan->status, ['approved', 'rejected'], true) ? 'disabled' : '' }}>
                                        <option value="approved" {{ $pengajuan->status == 'approved' ? 'selected' : '' }}>
                                            Approved
                                        </option>
                                        <option value="revised" {{ $pengajuan->status == 'revised' ? 'selected' : '' }}>
                                            Revisi
                                        </option>
                                        <option value="rejected" {{ $pengajuan->status == 'rejected' ? 'selected' : '' }}>
                                            Rejected
                                        </option>
                                    </select>
                                </div>

                                {{-- <div id="no_surat_block">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nomor Surat Balasan
                                    </label>
                                    <input type="text" name="nomor_surat_balasan"
                                        placeholder="contoh: B-747/BBPSDMP.73/UM.01.01/12/2025"
                                        value="{{ old('nomor_surat_balasan', $pengajuan->nomor_surat_balasan ?? '') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                                </div> --}}
                            </div>

                            <div class="mb-6" id="admin-note-block"
                                style="display: {{ $pengajuan->status == 'revised' ? 'block' : 'none' }};">
                                <label for="admin_note" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Catatan Revisi
                                </label>
                                <textarea name="admin_note" id="admin_note" rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ old('admin_note', $pengajuan->admin_note) }}</textarea>
                                <p class="mt-2 text-xs text-gray-500">Tuliskan apa saja yang perlu direvisi oleh institusi/peserta magang.</p>
                            </div>

                            <script>
                                (function() {
                                    const statusEl = document.getElementById('status');
                                    const noSuratBlock = document.getElementById('no_surat_block');
                                    const noteBlock = document.getElementById('admin-note-block');

                                    function updateUI(value) {
                                        if (value === 'rejected' || value === 'revised') {
                                            noSuratBlock.style.display = 'none';
                                        } else {
                                            noSuratBlock.style.display = 'block';
                                        }

                                        if (value === 'revised') {
                                            noteBlock.style.display = 'block';
                                        } else {
                                            noteBlock.style.display = 'none';
                                        }
                                    }

                                    statusEl.addEventListener('change', function(e) {
                                        updateUI(e.target.value);
                                    });

                                    updateUI(statusEl.value);
                                })();
                            </script>

                            <!-- Modal Konfirmasi Simpan -->
                            <div id="save-confirm-modal"
                                class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
                                <div class="absolute inset-0 bg-black bg-opacity-40"></div>
                                <div class="relative max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden">
                                    <div class="p-6 text-center">
                                        <div class="mx-auto w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                            <span class="text-blue-600 text-xl">?</span>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-800">Simpan Perubahan?</h3>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Status akan diperbarui terlebih dahulu. Setelah itu Anda bisa memilih apakah ingin langsung mengirim notifikasi WhatsApp.
                                        </p>
                                    </div>
                                    <div class="px-4 pb-4 pt-2 bg-gray-50 flex gap-3 justify-center">
                                        <button id="save-confirm-cancel" type="button"
                                            class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 font-semibold hover:bg-gray-100">
                                            Tidak
                                        </button>
                                        <button id="save-confirm-accept" type="button"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                                            Ya, Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <script>
                                (function() {
                                    const form = document.getElementById('update-status-form');
                                    const statusEl = document.getElementById('status');
                                    const saveModal = document.getElementById('save-confirm-modal');
                                    const saveCancelButton = document.getElementById('save-confirm-cancel');
                                    const saveAcceptButton = document.getElementById('save-confirm-accept');
                                    let isSaving = false;

                                    function openModal(modal) {
                                        modal.classList.remove('hidden');
                                    }

                                    function closeModal(modal) {
                                        modal.classList.add('hidden');
                                    }

                                    async function saveStatus() {
                                        if (isSaving) return;

                                        isSaving = true;
                                        saveAcceptButton.disabled = true;
                                        saveCancelButton.disabled = true;
                                        saveAcceptButton.textContent = 'Menyimpan...';

                                        try {
                                            const formData = new FormData(form);
                                            const response = await fetch(form.action, {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'Accept': 'application/json',
                                                }
                                            });

                                            if (!response.ok) {
                                                const errorData = await response.json();
                                                throw new Error(errorData.message || 'Gagal menyimpan status');
                                            }

                                            await response.json();
                                            closeModal(saveModal);
                                            window.location.reload();
                                        } catch (error) {
                                            alert(error.message || 'Gagal menyimpan status.');
                                        } finally {
                                            isSaving = false;
                                            saveAcceptButton.disabled = false;
                                            saveCancelButton.disabled = false;
                                            saveAcceptButton.textContent = 'Ya, Simpan';
                                        }
                                    }

                                    form.addEventListener('submit', function(event) {
                                        event.preventDefault();
                                        openModal(saveModal);
                                        saveCancelButton.focus();
                                    });

                                    saveCancelButton.addEventListener('click', function() {
                                        closeModal(saveModal);
                                    });

                                    saveAcceptButton.addEventListener('click', function() {
                                        saveStatus();
                                    });

                                })();
                            </script>

                            <div class="form-actions flex flex-col gap-3 pt-6 border-t border-slate-100">
                                @if ($pengajuan->status == 'approved')
                                    {{-- <a href="{{ route('industri.pengajuan.surat-balasan', $pengajuan) }}" target="_blank"
                                        class="w-full inline-flex items-center px-6 py-3 bg-green-100 hover:bg-green-200 text-green-700 justify-center font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                        <i class="fas fa-download mr-2"></i>
                                        Download Surat Balasan
                                    </a> --}}
                                    <button type="button" disabled
                                        class="w-full inline-flex items-center px-6 py-3 bg-gray-300 text-gray-700 justify-center font-bold rounded-xl shadow-sm transition-all duration-300">
                                        Sudah Disetujui — Tidak dapat diubah
                                    </button>
                                @elseif($pengajuan->status == 'rejected')
                                    <button type="button" disabled
                                        class="w-full inline-flex items-center px-6 py-3 bg-gray-300 text-gray-700 justify-center font-bold rounded-xl shadow-sm transition-all duration-300">
                                        Sudah Ditolak — Tidak dapat diubah
                                    </button>
                                @else
                                    <button type="submit"
                                        class="w-full inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white justify-center font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                        <i class="fas fa-save mr-2"></i>Simpan Status
                                    </button>
                                @endif

                                {{-- <div id="whatsapp-resend-section"
                                    class="mt-2 {{ empty($whatsapp['url']) ? 'hidden' : '' }}">
                                    <a id="whatsapp-resend-link" href="{{ $whatsapp['url'] ?? '#' }}" target="_blank"
                                        rel="noopener"
                                        class="w-full inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white justify-center font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                        <i class="fab fa-whatsapp mr-2"></i>
                                        Kirim Notifikasi WhatsApp
                                    </a>
                                    <p class="text-xs text-gray-500 text-center mt-2">
                                        Gunakan tombol ini jika Anda memilih tidak mengirim WhatsApp setelah status disimpan.
                                    </p>
                                </div> --}}
                            </div>
                        </form>
                    </div>

                    <div class="flex justify-start gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('industri.pengajuan.index') }}"
                            class="inline-flex items-center px-4 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                        </a>
                    </div>
                </div>
        </div>
    </div>
@endsection
