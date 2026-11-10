@extends('layouts.app')

@section('title', 'Edit Bukti Mikro Skill - Sistem Magang')

@section('content')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css">
<style>

    * { font-family: 'Plus Jakarta Sans', sans-serif; }
    body.page-microskill { background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%); }

    .dash-bg { background: transparent; min-height: 100vh; }
    .hero-strip { background: linear-gradient(110deg, #0f2a6e 0%, #2d3ecb 55%, #4f46e5 100%); border-radius: 20px; position: relative; overflow: hidden; margin-bottom: 28px; }
    .hero-strip::before { content: ''; position: absolute; top: -80px; right: -60px; width: 260px; height: 260px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none; }
    .hero-strip::after { content: ''; position: absolute; bottom: -100px; left: 25%; width: 320px; height: 320px; background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none; }
    .cta-btn { display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:linear-gradient(110deg,#1e3a8a,#3b4fd8);color:#fff;border-radius:12px;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s ease }
    .cta-btn:hover { box-shadow:0 6px 16px rgba(59,79,216,0.3); transform:translateY(-1px); color:#fff }

    /* ── TOM SELECT MODERN ── */
    .ts-wrapper{ width: 100%; }
    .ts-control{
        display:flex!important; align-items:center; flex-wrap:wrap; gap:8px; min-height:60px;
        padding:10px 16px!important; border:2px solid #dbeafe!important; border-radius:18px!important;
        background:#fff!important; box-shadow:none!important; transition:.25s;
    }
    .ts-control.focus{ border-color:#2563eb!important; box-shadow:0 0 0 5px rgba(37,99,235,.12)!important; }
    .ts-control input{ flex:1 1 auto!important; width:100%!important; min-width:220px!important; font-size:15px!important; color:#111827!important; padding:6px 0!important; margin:0!important; border:none!important; background:transparent!important; }
    .ts-control input::placeholder{ color:#94a3b8!important; }
    .ts-control .item{ background:#2563eb!important; color:#fff!important; border-radius:10px!important; padding:5px 12px!important; }
    
    .ts-dropdown{ margin-top:8px; border:none!important; border-radius:18px!important; overflow:hidden; box-shadow:0 20px 45px rgba(0,0,0,.15); }
    .ts-dropdown-content{ max-height:320px; overflow:auto; }
    .ts-dropdown .option{ padding:14px 18px; }
    .ts-dropdown .active{ background:#2563eb!important; color:white!important; }

    .preview-card { display: none; margin-top: 20px; border-radius: 18px; border: 1px solid #dbeafe; background: linear-gradient(135deg, #eff6ff, #ffffff); padding: 22px; }
    .preview-icon { width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; background: #2563eb; color: white; font-size: 22px; }
    .btn-open { display: inline-flex; align-items: center; gap: 10px; margin-top: 14px; background: #2563eb; color: white; padding: 10px 18px; border-radius: 12px; text-decoration: none; transition: .25s; }
    .btn-open:hover { background: #1d4ed8; color: white; }

    @media(max-width:768px){
        .hero-strip h1 { font-size:28px; }
        .preview-flex { flex-direction:column; }
    }
    .ts-control > input { width:100%!important; min-width:250px!important; flex:1!important; }
    .ts-wrapper.single .ts-control input { width:100%!important; }
    .ts-control.has-items input { min-width:220px!important; }
    .ts-control input:focus { outline:none!important; }
</style>
@endpush

    <div class="min-h-screen bg-blue-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="hero-strip shadow-xl mb-6">
                <div class="relative z-10 px-6 py-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold leading-tight text-white mb-1">Edit Bukti Mikro Skill</h1>
                            <p class="text-blue-200">Perbarui bukti pengumpulan mikro skill Anda</p>
                        </div>
                        <a href="{{ route('intern.microskill.index') }}" class="cta-btn" style="width:auto;">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-edit mr-3"></i>
                        Form Edit
                    </h2>
                </div>

                <form id="updateForm" method="POST" action="{{ route('intern.microskill.update', $submission->id) }}"
                    enctype="multipart/form-data" class="p-8 space-y-7">
                    @csrf
                    @method('PUT')

                    <!-- Title / Select Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-heading mr-2 text-blue-500"></i>
                            Pilih atau Masukkan Mikro Skill <span class="text-red-500">*</span>
                        </label>
                        
                        <select id="micro_skill_select" name="title" required>
                            <option value="">Cari atau ketik judul baru...</option>
                            @foreach($microSkills as $skill)
                                <option value="{{ $skill->judul_micro }}" 
                                        data-link="{{ $skill->link_micro }}"
                                        {{ old('title', $submission->title) == $skill->judul_micro ? 'selected' : '' }}>
                                    {{ $skill->judul_micro }}
                                </option>
                            @endforeach
                            
                            @if(!in_array($submission->title, $microSkills->pluck('judul_micro')->toArray()))
                                <option value="{{ $submission->title }}" selected>{{ $submission->title }}</option>
                            @endif
                        </select>

                        @error('title')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror

                        <!-- Preview Card Pelatihan -->
                        <div id="skillPreview" class="preview-card">
                            <div class="flex preview-flex gap-5">
                                <div class="preview-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 id="previewTitle" class="text-xl font-bold text-gray-800"></h3>
                                    <p class="text-gray-500 mt-2">Digital Talent • Komdigi</p>
                                    <a id="previewLink" target="_blank" class="btn-open">
                                        <i class="fas fa-external-link-alt"></i> Buka Pelatihan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-align-left mr-2 text-blue-500"></i>
                            Deskripsi <span class="text-gray-500 font-normal text-xs">(Opsional)</span>
                        </label>
                        <textarea name="description" rows="5" placeholder="Jelaskan detail dari bukti mikro skill ini..."
                            class="w-full px-5 py-4 border-2 border-blue-100 focus:border-blue-500 focus:ring-blue-500 rounded-2xl transition-all resize-none">{{ old('description', $submission->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Photo Field Container -->
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-image mr-2 text-blue-500"></i>
                            Foto Bukti <span class="text-gray-500 font-normal text-xs">(Opsional)</span>
                        </label>

                        <!-- Current Photo Display -->
                        @if ($submission->photo_path)
                            <div id="currentPhotoContainer" class="mb-6">
                                <div class="bg-white rounded-xl p-4 border border-blue-200">
                                    <p class="text-xs font-semibold text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-image mr-2 text-blue-500"></i> Foto Saat Ini
                                    </p>
                                    <div class="flex justify-center">
                                        <img src="{{ $submission->photo_url }}" alt="Current Photo"
                                            class="w-full max-w-xs sm:max-w-sm md:max-w-md h-auto rounded-lg border-2 border-blue-300 shadow-md mx-auto object-contain" loading="lazy" decoding="async">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- New Upload Preview Box -->
                        <div id="imagePreview" class="mb-6" style="display:none">
                            <div class="bg-white rounded-xl p-4 border border-green-200">
                                <p class="text-xs font-semibold text-green-700 mb-3 flex items-center">
                                    <i class="fas fa-circle-check mr-2"></i> Preview Bukti Baru (Siap Diunggah)
                                </p>
                                <div class="flex justify-center">
                                    <img id="previewImage" class="w-full max-w-xs sm:max-w-sm md:max-w-md h-auto rounded-lg border-2 border-green-300 shadow-md mx-auto object-contain" loading="lazy" decoding="async">
                                </div>
                            </div>
                        </div>

                        <!-- File Input Drag Drop Area -->
                        <div id="uploadArea" class="relative border-2 border-dashed border-blue-300 rounded-2xl bg-white hover:bg-blue-50/50 transition cursor-pointer p-8 text-center">
                            <input type="file" name="photo" accept="image/*" id="photo-input"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                            <div>
                                <div class="mx-auto w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h3 class="mt-4 text-base font-bold text-gray-800">Klik atau Drag & Drop untuk mengubah foto</h3>
                                <p class="text-gray-400 text-xs mt-1">Format: JPG, PNG (Max. 4MB)</p>
                            </div>
                        </div>

                        <div class="mt-4 bg-blue-100/70 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-800 flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-0.5 flex-shrink-0"></i>
                                <span>Biarkan kosong jika tidak ingin mengubah berkas foto bukti yang sudah ada sebelumnya.</span>
                            </p>
                        </div>

                        @error('photo')
                            <p class="mt-3 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Informasi Box -->
                    <div class="rounded-2xl bg-blue-50 border border-blue-200 p-5">
                        <div class="flex gap-3">
                            <div class="text-blue-600 text-xl">
                                <i class="fas fa-circle-info"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-blue-800">Informasi Pengeditan</h4>
                                <ul class="text-sm text-blue-700 mt-2 space-y-1">
                                    <li>• Anda diperkenankan mengubah judul rumpun Mikro Skill secara manual jika tidak terdaftar di sistem.</li>
                                    <li>• Mengunggah foto bukti baru akan otomatis menggantikan berkas lama di server penyimpanan.</li>
                                    <li>• Harap periksa kembali detail deskripsi sebelum melakukan pengiriman pembaruan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('intern.microskill.index') }}"
                            class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                        </a>
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-update-modal'))"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
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
                <p class="text-center text-gray-600 mb-6 text-sm">Apakah Anda yakin ingin memperbarui data bukti pengumpulan mikro skill ini?</p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="showUpdateModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="button" id="submitBtnReal" onclick="submitFormWithLoading()" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i> Ya, Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.body.classList.add('page-microskill');

        /* ==========================================
           INITIALIZE TOM SELECT WITH TAG CREATION
        ========================================== */
        const ts = new TomSelect("#micro_skill_select", {
            create: true,
            createOptionHeader: 'Tambahkan judul baru: ',
            searchField: ["text"],
            valueField: "value",
            labelField: "text",
            maxOptions: 1000,
            allowEmptyOption: true,
            hideSelected: false,
            closeAfterSelect: true,
            placeholder: "Ketik nama atau buat judul Mikro Skill...",
            render: {
                option: function(data, escape) {
                    let isDbOption = data.link && data.link !== "";
                    let subText = isDbOption ? "Digital Talent Komdigi" : "Judul Kustom Mandiri";
                    let iconClass = isDbOption ? "fa-book text-blue-600" : "fa-plus text-green-600";
                    let iconBg = isDbOption ? "bg-blue-100" : "bg-green-100";
                    
                    return `
                    <div class="flex gap-3 items-start py-2">
                        <div class="w-10 h-10 rounded-xl ${iconBg} flex items-center justify-center flex-shrink-0">
                            <i class="fas ${iconClass}"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-semibold text-gray-800 break-words">
                                ${escape(data.text)}
                            </div>
                            <div class="text-xs text-gray-500">
                                ${subText}
                            </div>
                        </div>
                    </div>`;
                },
                item: function(data, escape) {
                    return `<div class="truncate">${escape(data.text)}</div>`;
                }
            }
        });

        const inputSearch = ts.control_input;
        inputSearch.style.width = "100%";
        inputSearch.style.minWidth = "250px";
        inputSearch.style.flex = "1";
        inputSearch.style.padding = "6px 0";

        /* ==========================================
           PREVIEW LOGIC
        ========================================== */
        const select = document.getElementById('micro_skill_select');
        const preview = document.getElementById('skillPreview');
        const previewLink = document.getElementById("previewLink");

        function updatePreview() {
            const currentValue = ts.getValue();

            if (!currentValue || currentValue === "") {
                preview.style.display = "none";
                return;
            }

            preview.style.display = "block";
            document.getElementById("previewTitle").innerHTML = currentValue;

            const selectedOption = select.options[select.selectedIndex];
            const dataLink = selectedOption ? selectedOption.dataset.link : null;

            if (dataLink && dataLink !== "") {
                previewLink.href = dataLink;
                previewLink.style.display = "inline-flex";
            } else {
                previewLink.href = "#";
                previewLink.style.display = "none";
            }
        }

        ts.on('change', updatePreview);
        window.addEventListener("load", updatePreview);

        /* ==========================================
           FILE PREVIEW & DRAG & DROP LOGIC
        ========================================== */
        const fileInput = document.getElementById("photo-input");
        const imagePreview = document.getElementById("imagePreview");
        const previewImage = document.getElementById("previewImage");
        const currentPhotoContainer = document.getElementById("currentPhotoContainer");

        fileInput.addEventListener("change", function() {
            if (!this.files.length) {
                imagePreview.style.display = "none";
                if (currentPhotoContainer) currentPhotoContainer.style.display = "block";
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                imagePreview.style.display = "block";
                if (currentPhotoContainer) currentPhotoContainer.style.display = "none";
            }
            reader.readAsDataURL(this.files[0]);
        });

        const uploadArea = document.getElementById("uploadArea");
        ["dragenter", "dragover"].forEach(eventName => {
            uploadArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                uploadArea.classList.add("border-blue-600", "bg-blue-50");
            });
        });

        ["dragleave", "drop"].forEach(eventName => {
            uploadArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                uploadArea.classList.remove("border-blue-600", "bg-blue-50");
            });
        });

        uploadArea.addEventListener("drop", function(e) {
            const files = e.dataTransfer.files;
            fileInput.files = files;
            fileInput.dispatchEvent(new Event("change"));
        });

        // Form Submit Loading Trigger via Modal
        function submitFormWithLoading() {
            const btn = document.getElementById("submitBtnReal");
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            }
            document.getElementById('updateForm').submit();
        }
    </script>
    @endpush
@endsection