@extends('layouts.app')

@section('title', 'Absensi Baru - Sistem Magang')

@section('content')
    <div class="min-h-screen bg-blue-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @push('styles')
            <style>
                * { font-family: 'Plus Jakarta Sans', sans-serif; }
                .hero-strip { background: linear-gradient(110deg, #0f2878 0%, #2d3ecb 55%, #4f46e5 100%); border-radius: 20px; position: relative; overflow: hidden; margin-bottom: 28px; }
                .hero-strip::before { content: ''; position: absolute; top: -80px; right: -60px; width: 260px; height: 260px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none; }
                .hero-strip::after { content: ''; position: absolute; bottom: -100px; left: 25%; width: 320px; height: 320px; background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none; }
                .panel { background:#fff;border-radius:20px;padding:24px;box-shadow:0 1px 3px rgba(30,58,138,0.06), 0 4px 20px rgba(30,58,138,0.06); }
                .cta-btn { display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:linear-gradient(110deg,#1e3a8a,#3b4fd8);color:#fff;border-radius:12px;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s ease }
                .cta-btn:hover { box-shadow:0 6px 16px rgba(59,79,216,0.3); transform:translateY(-1px); color:#fff }
            </style>
            @endpush

            <!-- Header (logbook-style hero) -->
            <div class="hero-strip shadow-xl mb-6">
                <div class="relative z-10 px-6 py-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold leading-tight text-white mb-1">Absensi Baru</h1>
                            <p class="text-blue-200">Catat kehadiran Anda hari ini</p>
                        </div>
                        <a href="{{ route('intern.attendance.index') }}" class="cta-btn" style="width:auto;">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="panel">
                <div class="bg-blue-600 px-6 py-4 rounded-t-xl" style="margin:-24px -24px 12px -24px; padding:18px 24px;">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-clipboard-check mr-3"></i>
                            Form Absensi
                        </h2>
                    </div>

                    <div class="p-6">
                    <form method="POST" action="{{ route('intern.attendance.store') }}" enctype="multipart/form-data"
                        id="attendanceForm">
                        @csrf

                        <!-- Status Absensi -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-info-circle mr-1 text-blue-500"></i> Status Absensi
                            </label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="">Pilih Status</option>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600 flex items-center"><i
                                        class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hadir Section -->
                        <div id="hadirSection" class="hidden">
                            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-4">
                                    <i class="fas fa-camera mr-1 text-blue-500"></i> Foto Bukti Kehadiran
                                </label>

                                <!-- Camera Display -->
                                <div class="mb-4">
                                    <video id="video" width="100%" height="auto"
                                        class="border-2 border-blue-300 rounded-lg hidden shadow-md transform -scale-x-100"
                                        autoplay playsinline></video>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <img id="capturedImage"
                                        class="w-full max-w-md mx-auto border-2 border-green-300 rounded-lg hidden mb-4 shadow-md"
                                        alt="Captured image" loading="lazy" decoding="async">
                                </div>

                                <!-- Camera Controls -->
                                <div class="flex flex-wrap gap-3 mb-4">
                                    <button type="button" id="startCamera"
                                        class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                        <i class="fas fa-camera mr-2"></i>Buka Kamera
                                    </button>
                                    <button type="button" id="capturePhoto"
                                        class="hidden inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                        <i class="fas fa-camera-retro mr-2"></i>Ambil Foto
                                    </button>
                                    <button type="button" id="stopCamera"
                                        class="hidden inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                        <i class="fas fa-stop mr-2"></i>Stop Kamera
                                    </button>
                                </div>

                                <input type="file" name="photo" id="photo" accept="image/*" class="hidden">
                                <input type="hidden" name="photo_data" id="photo_data" value="">
                                @error('photo')
                                    <p class="mt-2 text-sm text-red-600 flex items-center"><i
                                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror

                                <div class="mt-4 bg-blue-100 border border-blue-300 rounded-lg p-3">
                                    <p class="text-xs text-blue-800 flex items-start">
                                        <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                                        <span>Pastikan wajah Anda terlihat jelas pada foto. Foto akan digunakan sebagai
                                            bukti kehadiran.</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Izin/Sakit Section -->
                        <div id="izinSakitSection" class="hidden">
                            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6 mb-6">
                                <!-- Keterangan -->
                                <div class="mb-6">
                                    <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-edit mr-1 text-yellow-500"></i> Keterangan
                                    </label>
                                    <textarea name="note" id="note" rows="4" placeholder="Jelaskan alasan izin/sakit Anda..."
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all"></textarea>
                                    @error('note')
                                        <p class="mt-2 text-sm text-red-600 flex items-center"><i
                                                class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Upload Document -->
                                <div>
                                    <label for="document" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-file-upload mr-1 text-yellow-500"></i> Upload Dokumen Pendukung
                                    </label>
                                    <div class="relative">
                                        <input type="file" name="document" id="document"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-100 file:text-yellow-700 hover:file:bg-yellow-200">
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Format: PDF, DOC, DOCX, JPG, PNG (Maks: 5MB)
                                    </p>
                                    @error('document')
                                        <p class="mt-2 text-sm text-red-600 flex items-center"><i
                                                class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4 bg-yellow-100 border border-yellow-300 rounded-lg p-3">
                                    <p class="text-xs text-yellow-800 flex items-start">
                                        <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                                        <span>Untuk izin, wajib melampirkan dokumen pendukung. Untuk sakit, dokumen opsional
                                            tetapi disarankan.</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('intern.attendance.index') }}"
                                class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                            </a>
                                <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                                    <i class="fas fa-save mr-2"></i>Simpan Absensi
                                </button>
                        </div>
                    </form>
                </div>

            <!-- Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <!-- Hadir Info -->
                <div
                    class="bg-white rounded-xl shadow-md border border-green-200 p-4 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-green-600 text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-gray-900">Hadir</h3>
                            <p class="text-xs text-gray-600 mt-1">Wajib upload foto kehadiran</p>
                        </div>
                    </div>
                </div>

                <!-- Izin Info -->
                <div
                    class="bg-white rounded-xl shadow-md border border-yellow-200 p-4 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-times text-yellow-600 text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-gray-900">Izin</h3>
                            <p class="text-xs text-gray-600 mt-1">Wajib keterangan & dokumen</p>
                        </div>
                    </div>
                </div>

                <!-- Sakit Info -->
                <div
                    class="bg-white rounded-xl shadow-md border border-red-200 p-4 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-minus text-red-600 text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-gray-900">Sakit</h3>
                            <p class="text-xs text-gray-600 mt-1">Wajib keterangan, dokumen opsional</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            const statusSelect = document.getElementById('status');
            const hadirSection = document.getElementById('hadirSection');
            const izinSakitSection = document.getElementById('izinSakitSection');
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const capturedImage = document.getElementById('capturedImage');
            const startCameraBtn = document.getElementById('startCamera');
            const capturePhotoBtn = document.getElementById('capturePhoto');
            const stopCameraBtn = document.getElementById('stopCamera');
            const photoInput = document.getElementById('photo');
            const photoData = document.getElementById('photo_data');
            let stream = null;

            statusSelect.addEventListener('change', function() {
                if (this.value === 'hadir') {
                    hadirSection.classList.remove('hidden');
                    izinSakitSection.classList.add('hidden');
                    document.getElementById('note').removeAttribute('required');
                    document.getElementById('document').removeAttribute('required');
                } else if (this.value === 'izin' || this.value === 'sakit') {
                    hadirSection.classList.add('hidden');
                    izinSakitSection.classList.remove('hidden');
                    photoInput.removeAttribute('required');
                    if (this.value === 'izin') {
                        document.getElementById('note').setAttribute('required', 'required');
                        document.getElementById('document').setAttribute('required', 'required');
                    } else {
                        document.getElementById('note').setAttribute('required', 'required');
                        document.getElementById('document').removeAttribute('required');
                    }
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                } else {
                    hadirSection.classList.add('hidden');
                    izinSakitSection.classList.add('hidden');
                }
            });

            startCameraBtn.addEventListener('click', async function() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        },
                        audio: false
                    });
                    video.srcObject = stream;
                    video.classList.remove('hidden');
                    startCameraBtn.classList.add('hidden');
                    capturePhotoBtn.classList.remove('hidden');
                    stopCameraBtn.classList.remove('hidden');
                } catch (err) {
                    alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin akses kamera.');
                    console.error(err);
                }
            });

            capturePhotoBtn.addEventListener('click', function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const ctx = canvas.getContext('2d');

                ctx.save();
                ctx.scale(-1, 1);
                ctx.drawImage(video, -video.videoWidth, 0);
                ctx.restore();

                const imageData = canvas.toDataURL('image/png');

                capturedImage.src = imageData;
                capturedImage.classList.remove('hidden');

                photoData.value = imageData;
            });

            stopCameraBtn.addEventListener('click', function() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                video.classList.add('hidden');
                startCameraBtn.classList.remove('hidden');
                capturePhotoBtn.classList.add('hidden');
                stopCameraBtn.classList.add('hidden');
            });

            document.getElementById('attendanceForm').addEventListener('submit', function(e) {
                if (statusSelect.value === 'hadir' && !photoData.value) {
                    e.preventDefault();
                    alert('Silakan ambil foto terlebih dahulu.');
                    return false;
                }

                if (statusSelect.value === 'hadir' && stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
            });
        </script>
    @endpush
@endsection
