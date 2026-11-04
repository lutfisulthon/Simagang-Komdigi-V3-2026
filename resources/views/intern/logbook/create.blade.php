@extends('layouts.app')

@section('title', 'Tambah Logbook - Sistem Magang')

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
                        <h1 class="text-3xl font-bold leading-tight text-white mb-1">Tambah Logbook</h1>
                        <p class="text-blue-200">Catat aktivitas harian Anda dengan detail</p>
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
                        Form Logbook Harian
                    </h2>
                </div>

                <div class="p-8">
                <form method="POST" action="{{ route('intern.logbook.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Tanggal Field -->
                    <div class="mb-6">
                        <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-blue-500 mr-2"></i>Tanggal
                        </label>
                        <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required 
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
                            placeholder="Tuliskan kegiatan yang Anda lakukan hari ini...">{{ old('activity') }}</textarea>
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
                                    <option value="{{ $task->id }}" @selected(old('task_id') == $task->id)>
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

                    <!-- Photo Field -->
                    <div class="mb-8">
                        <label for="photo" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-image text-blue-500 mr-2"></i>Foto Dokumentasi (Opsional)
                        </label>
                        <input type="file" name="photo" id="photo" accept="image/*" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-2 text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>Format: JPG, PNG (Maks: 2MB)
                        </p>
                        @error('photo')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center sm:justify-end gap-4 pt-6 border-t border-gray-200">
                        <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 w-full sm:w-auto justify-center">
                            <i class="fas fa-save mr-2"></i>Simpan Logbook
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
    @push('scripts')
    <script>document.body.classList.add('page-logbook');</script>
    @endpush

    @endsection