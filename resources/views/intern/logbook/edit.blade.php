@extends('layouts.app')

@section('title', 'Edit Logbook - Sistem Magang')

@section('content')
@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

    * { font-family: 'Plus Jakarta Sans', sans-serif; }
    .mono { font-family: 'DM Mono', monospace; }
    body.page-logbook { background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%); }

    .dash-bg { background: transparent; min-height: 100vh; }
    .hero-strip { background: linear-gradient(110deg, #0f2878 0%, #2d3ecb 55%, #4f46e5 100%); border-radius: 20px; position: relative; overflow: hidden; margin-bottom: 28px; }
    .hero-strip::before { content: ''; position: absolute; top: -80px; right: -60px; width: 260px; height: 260px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none; }
    .hero-strip::after { content: ''; position: absolute; bottom: -100px; left: 25%; width: 320px; height: 320px; background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none; }
    .cta-btn { display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:linear-gradient(110deg,#1e3a8a,#3b4fd8);color:#fff;border-radius:12px;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s ease }
    .cta-btn:hover { box-shadow:0 6px 16px rgba(59,79,216,0.3); transform:translateY(-1px); color:#fff }
</style>
@endpush

    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="hero-strip shadow-xl a1 mb-6">
                <div class="relative z-10 px-6 py-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold leading-tight text-white mb-1">Edit Logbook</h1>
                            <p class="text-blue-200">Perbarui aktivitas harian Anda</p>
                        </div>
                        <a href="{{ route('intern.logbook.index') }}" class="cta-btn" style="width:auto;">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-edit mr-3"></i>
                        Form Edit Logbook
                    </h2>
                </div>

                <div class="p-8">
                    <form id="updateForm" method="POST" action="{{ route('intern.logbook.update', $logbook) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Tanggal Field -->
                        <div class="mb-6">
                            <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar text-blue-500 mr-2"></i>Tanggal
                            </label>
                            <input type="date" name="date" id="date"
                                value="{{ old('date', $logbook->date->format('Y-m-d')) }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                            @error('date')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Activity Field -->
                        <div class="mb-6">
                            <label for="activity" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tasks text-blue-500 mr-2"></i>Kegiatan Harian
                            </label>
                            <textarea name="activity" id="activity" rows="10" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="Tuliskan kegiatan yang Anda lakukan hari ini...">{{ old('activity', $logbook->activity) }}</textarea>
                            @error('activity')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Linked Task Field -->
                        @if (isset($tasks) && $tasks->isNotEmpty())
                            <div class="mb-6">
                                <label for="task_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-link text-blue-500 mr-2"></i>Tugas Terkait (Opsional)
                                </label>
                                <select name="task_id" id="task_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">Tidak terkait tugas</option>
                                    @foreach ($tasks as $task)
                                        <option value="{{ $task->id }}" @selected(old('task_id', $logbook->task_id) == $task->id)>
                                            {{ $task->code }} — {{ $task->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('task_id')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif

                        <!-- Current Photo Display -->
                        @if ($logbook->photo_path)
                            <div class="mb-6">
                                <p class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-image text-blue-500 mr-2"></i>Foto Saat Ini
                                </p>
                                <div class="bg-gray-50 rounded-xl p-4 border-2 border-dashed border-gray-300">
                                    <img src="{{ $logbook->photo_url }}" alt="Current photo"
                                        class="w-full max-w-md mx-auto rounded-lg shadow-md border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all"
                                        onclick="window.open('{{ $logbook->photo_url }}', '_blank')"
                                        title="Klik untuk melihat full size">
                                </div>
                            </div>
                        @endif

                        <!-- Photo Upload Field -->
                        <div class="mb-8">
                            <label for="photo" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-camera text-blue-500 mr-2"></i>Ganti Foto Dokumentasi (Opsional)
                            </label>
                            <input type="file" name="photo" id="photo" accept="image/*"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-2 text-sm text-gray-500 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>Format: JPG, PNG (Maks: 2MB). Kosongkan jika tidak
                                ingin mengganti foto.
                            </p>
                            @error('photo')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center sm:justify-end gap-4 pt-6 border-t border-gray-200">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-update-modal'))" class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 w-full sm:w-auto justify-center">
                                <i class="fas fa-save mr-2"></i>Update Logbook
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Confirmation Modal -->
    <div x-data="{ showUpdateModal: false }" @open-update-modal.window="showUpdateModal = true">
        <!-- Modal Backdrop -->
        <div x-show="showUpdateModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" x-transition.opacity>
            <!-- Modal Content -->
            <div @click.away="showUpdateModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all" x-show="showUpdateModal" x-transition.scale.origin.bottom>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-question text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Perubahan</h3>
                <p class="text-center text-gray-600 mb-6">Apakah Anda yakin ingin menyimpan perubahan logbook ini?</p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showUpdateModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="button" onclick="document.getElementById('updateForm').submit()" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i> Ya, Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>document.body.classList.add('page-logbook');</script>
@endpush
@endsection
