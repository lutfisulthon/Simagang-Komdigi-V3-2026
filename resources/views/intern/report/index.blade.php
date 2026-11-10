@extends('layouts.app')

@section('title', 'Laporan Akhir - Sistem Magang')

@section('content')

@push('styles')
<style>

    * { font-family: 'Plus Jakarta Sans', sans-serif; }
    .mono { font-family: 'DM Mono', monospace; }
    body.page-report { background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%); }

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
                        <h1 class="text-3xl font-bold leading-tight text-white mb-1">Laporan Akhir</h1>
                        <p class="text-blue-200">Selesaikan dan submit laporan akhir magangmu</p>
                    </div>
                </div>
            </div>
        </div>

        @if($report)
        <!-- Report Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden mb-6">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-file-alt mr-3"></i>
                    Detail Laporan Akhir
                </h2>
            </div>

            <div class="p-8">
                {{-- Header Info --}}
                <div class="flex flex-col md:flex-row justify-between items-start mb-6 pb-6 border-b border-gray-200">
                    <div>
                        <p class="text-sm text-gray-600 flex items-center">
                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                            Diupload pada: <span class="font-medium ml-1">{{ $report->submitted_at->format('d F Y H:i') }}</span>
                        </p>
                    </div>
                    <div class="flex flex-col items-end space-y-2 mt-4 md:mt-0">
                        <span class="px-4 py-2 rounded-xl text-sm font-semibold shadow-sm
                            @if($report->status == 'approved') bg-green-100 text-green-800 border border-green-200
                            @elseif($report->status == 'rejected') bg-red-100 text-red-800 border border-red-200
                            @else bg-yellow-100 text-yellow-800 border border-yellow-200
                            @endif">
                            <i class="fas fa-circle text-xs mr-1"></i>{{ ucfirst($report->status) }}
                        </span>

                        @if($report->needs_revision)
                        <span class="px-4 py-2 rounded-xl text-sm font-semibold bg-orange-100 text-orange-800 border border-orange-200 shadow-sm">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Perlu Revisi
                        </span>
                        @endif

                        @if($report->grade)
                        <span class="px-4 py-2 rounded-xl text-lg font-bold shadow-sm
                            @if($report->grade == 'A') bg-green-100 text-green-800 border border-green-200
                            @elseif($report->grade == 'B') bg-blue-100 text-blue-800 border border-blue-200
                            @else bg-yellow-100 text-yellow-800 border border-yellow-200
                            @endif">
                            <i class="fas fa-award mr-1"></i>Nilai: {{ $report->grade }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- BERKAS LAPORAN: progres Upload → Review mentor → Disetujui, status, dan upload/ganti berkas (PDF ≤ 5 MB) --}}
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                    Berkas Laporan Akhir
                </h3>
                <x-report.file-upload :report="$report" />

                {{-- DETAIL BERKAS LAINNYA --}}
                <div class="space-y-3 mt-8">

                    {{-- File Project (support multiple) --}}
                    @php
                        $projectFilesDisplay = $report->project_files ?? null;
                    @endphp
                    @if(!empty($projectFilesDisplay) && is_array($projectFilesDisplay))
                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold text-gray-700 flex items-center mt-4">
                                <i class="fas fa-folder text-blue-500 mr-2"></i>File Proyek
                            </h4>
                            @foreach($projectFilesDisplay as $pf)
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0 bg-gray-50 p-4 rounded-xl border-2 border-gray-200 hover:border-blue-300 transition-all">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file-archive text-gray-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">File Proyek {{ $loop->iteration }}</p>
                                            <p class="text-sm text-gray-600">{{ data_get($pf, 'name') ?? basename(data_get($pf, 'path', '')) }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('download', ['path' => data_get($pf, 'path')]) }}" target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-sm">
                                        <i class="fas fa-download mr-2"></i>Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @elseif($report->project_file)
                        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border-2 border-gray-200 hover:border-blue-300 transition-all">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-archive text-gray-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">File Proyek</p>
                                    <p class="text-sm text-gray-600">{{ $report->project_file_name ?? basename($report->project_file) }}</p>
                                </div>
                            </div>
                            <a href="{{ route('download', ['path' => $report->project_file]) }}" target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-sm">
                                <i class="fas fa-download mr-2"></i>Download
                            </a>
                        </div>
                    @endif

                    {{-- Link Project (support multiple) --}}
                    @php
                        $projectLinksDisplay = $report->project_links ?? null;
                    @endphp
                    @if(!empty($projectLinksDisplay) && is_array($projectLinksDisplay))
                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold text-gray-700 flex items-center mt-4">
                                <i class="fas fa-link text-indigo-500 mr-2"></i>Link Proyek
                            </h4>
                            @foreach($projectLinksDisplay as $pl)
                                @if(!empty($pl))
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0 bg-indigo-50 p-4 rounded-xl border-2 border-indigo-200 hover:border-indigo-300 transition-all">
                                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-link text-indigo-600 text-xl"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-gray-900">Link Proyek {{ $loop->iteration }}</p>
                                            <a href="{{ $pl }}" target="_blank" class="text-sm text-indigo-600 hover:underline break-all">{{ $pl }}</a>
                                        </div>
                                    </div>
                                    <a href="{{ $pl }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all shadow-sm ml-4 flex-shrink-0">
                                        <i class="fas fa-external-link-alt mr-2"></i>Buka
                                    </a>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @elseif($report->project_link)
                        <div class="flex items-center justify-between bg-indigo-50 p-4 rounded-xl border-2 border-indigo-200 hover:border-indigo-300 transition-all">
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-link text-indigo-600 text-xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900">Link Proyek</p>
                                    <a href="{{ $report->project_link }}" target="_blank" class="text-sm text-indigo-600 hover:underline break-all">{{ $report->project_link }}</a>
                                </div>
                            </div>
                            <a href="{{ $report->project_link }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all shadow-sm ml-4 flex-shrink-0">
                                <i class="fas fa-external-link-alt mr-2"></i>Buka
                            </a>
                        </div>
                    @endif
                    
                    {{-- Rekap tugas Workspace (hanya tampilan; hanya bila modul Workspace aktif) --}}
                    <x-workspace.report-task-recap mode="view" class="mt-4" />

                    {{-- Activities (Kegiatan Magang) --}}
                    <div class="mt-4 p-6 bg-green-50 rounded-xl border-2 border-green-200">
                        <h4 class="font-bold text-gray-900 flex items-center mb-3">
                            <i class="fas fa-tasks text-green-600 mr-2"></i>
                            Kegiatan Selama Magang
                        </h4>
                        @if($report->activities && is_array($report->activities) && count($report->activities) > 0)
                            <div class="space-y-2">
                                @foreach($report->activities as $activity)
                                    <div class="p-4 bg-white rounded-lg border border-green-200 shadow-sm">
                                        <p class="text-gray-900">{{ $activity['description'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600 italic">Belum ada kegiatan yang dicatat.</p>
                        @endif
                    </div>
                </div>

                {{-- Catatan Admin --}}
                @if($report->admin_note)
                <div class="mt-6 p-6 bg-blue-50 rounded-xl border-2 border-blue-200">
                    <p class="text-sm font-bold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-comment-dots mr-2"></i>Catatan Admin
                    </p>
                    <div class="bg-white p-4 rounded-lg border border-blue-200 shadow-sm">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap text-justify">
                            {{ trim($report->admin_note) }}
                        </p>
                    </div>
                </div>
                @endif

                {{-- Button --}}
                <div class="mt-6 flex flex-col sm:flex-row gap-4">
                    @if($report->status !== 'approved' || $report->needs_revision)
                    <button onclick="document.getElementById('uploadForm').classList.toggle('hidden')"
                        class="cta-btn">
                        <i class="fas fa-pen-to-square"></i>
                        Edit detail laporan
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- TESTIMONI SECTION --}}
        @if($report && $report->submitted_at)
        <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden mb-6">
            <div class="bg-purple-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-quote-left mr-3"></i>
                    Berikan Testimoni Anda
                </h2>
            </div>

            <div class="p-8">
                @if($testimonial)
                    {{-- Display Existing Testimonial --}}
                    <div class="mb-6">
                        <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-6">
                            <p class="text-sm text-purple-900 font-semibold flex items-center mb-3">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>Testimoni Anda
                            </p>
                            <p class="text-gray-800 text-base leading-relaxed mb-3">{{ $testimonial->testimony }}</p>
                            <p class="text-sm text-gray-500 flex items-center">
                                <i class="fas fa-clock mr-1"></i>Dikirim: {{ $testimonial->created_at->format('d F Y H:i') }}
                            </p>
                            <button onclick="document.getElementById('testimoniForm').classList.toggle('hidden')"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg transition-all">
                                <i class="fas fa-edit mr-2"></i>Edit Testimoni
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Testimonial Form --}}
                <div id="testimoniForm" class="@if($testimonial) hidden @endif">
                    <form method="POST" action="{{ route('intern.report.storeTestimonial', $report) }}">
                        @csrf
                        <div class="mb-4">
                            <label for="testimony" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-pen-fancy text-purple-500 mr-2"></i>Testimoni Anda
                            </label>
                            <textarea name="testimony" id="testimony" rows="5"
                                class="w-full px-4 py-3 border-2 border-purple-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" 
                                placeholder="Bagikan pengalamanmu atau berikan saran konstruktif selama magang."
                                required minlength="20" maxlength="1000">{{ old('testimony', $testimonial?->testimony ?? '') }}</textarea>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>Minimal 20 karakter, maksimal 1000 karakter
                                </p>
                                <span id="charCount" class="text-sm font-semibold text-purple-600">0/1000</span>
                            </div>
                            @error('testimony')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-times-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if($errors->has('success_testimony'))
                            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg flex items-start">
                                <i class="fas fa-check-circle text-green-600 mr-3 mt-0.5"></i>
                                <p class="text-sm text-green-800">{{ session('success_testimony') }}</p>
                            </div>
                        @endif

                        <div class="flex gap-3 pt-4">
                            @if($testimonial)
                                <button type="button" onclick="document.getElementById('testimoniForm').classList.add('hidden')"
                                    class="inline-flex items-center px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all">
                                    <i class="fas fa-times mr-2"></i>Batal
                                </button>
                            @endif
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                                <i class="fas fa-paper-plane mr-2"></i>{{ $testimonial ? 'Update Testimoni' : 'Kirim Testimoni' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- FORM UPDATE --}}
        <div id="uploadForm" class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-edit mr-3"></i>
                    Edit Detail Laporan
                </h2>
            </div>

            <div class="p-8">
                <form method="POST" action="{{ route('intern.report.update', $report) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <p class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-sm text-blue-900 flex items-start gap-2">
                        <i class="fas fa-circle-info mt-0.5"></i>
                        <span>Untuk mengganti berkas laporan (PDF), gunakan bagian <strong>Berkas Laporan Akhir</strong> di atas. Form ini untuk file proyek, link proyek, dan kegiatan.</span>
                    </p>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-archive text-gray-600 mr-2"></i>Proyek Saat Ini (maks 3 file)
                        </label>
                        <div class="relative">
                            <input type="text" id="projectDisplay" value="{{ $report->project_file ? ($report->project_file_name ?? basename($report->project_file)) : 'Belum ada file proyek' }}" readonly
                                class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 bg-gray-50 text-gray-700 cursor-pointer hover:border-blue-400 transition-all"
                                onclick="document.getElementById('projectInput').click()">
                            <input type="file" name="project_files[]" id="projectInput" style="display: none;" onchange="updateProjectDisplay(this)" multiple accept=".zip,.rar,.7z,.tar,.gz">
                            <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-red-600 text-xl font-bold" onclick="clearProject()">×</button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 flex items-start">
                            <i class="fas fa-info-circle mr-1 mt-0.5"></i>
                            <span>Klik untuk pilih hingga 3 file baru atau biarkan untuk tetap menggunakan file saat ini.
                            @if($report->project_file)
                                <a href="{{ route('download', ['path' => $report->project_file]) }}" target="_blank" class="text-blue-600 hover:underline">Download file saat ini</a>
                            @endif
                            Format: .zip, .rar, .7z, .tar.gz (Maks: 100MB per file)</span>
                        </p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-link text-indigo-600 mr-2"></i>Link Proyek (Opsional, maks 3)
                        </label>
                        @php
                            $existingLinks = [];
                            if (!empty($report->project_links) && is_array($report->project_links)) {
                                $existingLinks = $report->project_links;
                            } elseif (!empty($report->project_link)) {
                                $existingLinks = [$report->project_link];
                            }
                        @endphp
                        @for($i = 0; $i < 3; $i++)
                            <input type="url" name="project_links[]"
                                value="{{ old('project_links.'.$i, data_get($existingLinks, $i, '')) }}"
                                placeholder="https://example.com/project-{{ $i + 1 }}"
                                class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @endfor
                        <p class="mt-2 text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: Link dari GitHub, GitHub Pages, Google Drive, YouTube, Canva, Tableau (Opsional, Maks: 3 link)
                        </p>
                    </div>

                    {{-- Activities (Kegiatan Magang) --}}
                    {{-- Rekap tugas Workspace (hanya bila modul Workspace aktif) --}}
                    <x-workspace.report-task-recap :checked="data_get($report, 'include_task_recap', true)" />

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tasks text-green-600 mr-2"></i>Kegiatan Selama Magang (Opsional)
                        </label>
                        <textarea name="activities[0][description]" rows="5" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                            placeholder="Deskripsikan kegiatan harian/mingguan yang relevan untuk laporan akhir...">{{ old('activities.0.description', data_get($report, 'activities.0.description', '')) }}</textarea>
                        <p class="mt-2 text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Opsional: Masukkan ringkasan kegiatan selama magang.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-gray-200">
                        <button type="button" onclick="document.getElementById('uploadForm').classList.add('hidden')"
                            class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-300 w-full sm:w-auto justify-center">
                            <i class="fas fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 w-full sm:w-auto justify-center">
                            <i class="fas fa-save mr-2"></i>Update Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @else
        <!-- Upload Initial Report Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-upload mr-3"></i>
                    Upload Laporan Akhir
                </h2>
            </div>

            <div class="p-8">
                <div class="text-center py-8 mb-8">
                    <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-alt text-blue-600 text-5xl"></i>
                    </div>
                    <p class="text-gray-600 text-lg font-medium mb-2">Anda belum mengupload laporan akhir.</p>
                    <p class="text-gray-500 text-sm">Lengkapi form di bawah untuk submit laporan akhir Anda.</p>
                </div>

                <form method="POST" action="{{ route('intern.report.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <p class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-file-pdf text-red-500 mr-2"></i>Upload Laporan Akhir <span class="text-red-600">*</span>
                        </p>
                        <x-report.file-upload mode="embedded" />
                    </div>

                    <div class="mb-6">
                        <label for="project_file" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-archive text-gray-600 mr-2"></i>Upload Proyek Akhir (opsional, maks 3 file)
                        </label>
                        <input type="file" name="project_files[]" id="project_file"
                            accept=".zip,.rar,.7z,.tar,.gz" multiple
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-2 text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: .zip, .rar, .7z, .tar.gz (Opsional, Maks: 100MB per file, maksimal 3 file)
                        </p>
                    </div>

                    <div class="mb-6">
                        <label for="project_link" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-link text-indigo-600 mr-2"></i>Link Proyek (opsional, maks 3)
                        </label>
                        @for($i = 0; $i < 3; $i++)
                            <input type="url" name="project_links[]" id="project_link_{{ $i }}"
                                placeholder="https://example.com/project-{{ $i + 1 }}"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                value="{{ old('project_links.'.$i) }}">
                        @endfor
                        <p class="mt-2 text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: Link dari GitHub, GitHub Pages, Google Drive, YouTube, Canva, Tableau (Opsional, Maks: 3 link)
                        </p>
                    </div>

                    {{-- Rekap tugas Workspace (hanya bila modul Workspace aktif) --}}
                    <x-workspace.report-task-recap />

                    {{-- Activities (Kegiatan Magang) --}}
                    <div class="mb-8">
                        <label for="activities_description" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tasks text-green-600 mr-2"></i>Kegiatan Selama Magang (opsional)
                        </label>
                        <textarea name="activities[0][description]" id="activities_description" rows="5"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            placeholder="Deskripsikan kegiatan harian/mingguan yang relevan untuk laporan akhir...">{{ old('activities.0.description') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Opsional: Masukkan ringkasan kegiatan selama magang.
                        </p>
                    </div>

                        <div class="flex justify-center pt-6 border-t border-gray-200">
                        <button type="submit"
                            class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-upload mr-2"></i>Upload Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            document.body.classList.add('page-report');
            // Custom file input functions
            function updateFileDisplay(input) {
                const display = document.getElementById('fileDisplay');
                if (input.files && input.files[0]) {
                    display.value = input.files[0].name;
                    display.classList.remove('text-gray-700');
                    display.classList.add('text-blue-700');
                }
            }

            function clearFile() {
                const display = document.getElementById('fileDisplay');
                const input = document.getElementById('fileInput');
                display.value = '{{ $report?->file_name }}';
                input.value = '';
                display.classList.remove('text-blue-700');
                display.classList.add('text-gray-700');
            }

            function updateProjectDisplay(input) {
                const display = document.getElementById('projectDisplay');
                if (!display) return;
                if (input.files && input.files.length) {
                    if (input.files.length > 3) {
                        alert('Maksimal 3 file proyek. Silakan pilih ulang.');
                        input.value = '';
                        return;
                    }
                    const names = Array.from(input.files).map(f => f.name).join(', ');
                    display.value = names;
                    display.classList.remove('text-gray-700', 'text-gray-500');
                    display.classList.add('text-blue-700');
                }
            }

            function clearProject() {
                const display = document.getElementById('projectDisplay');
                const inputUpdate = document.getElementById('projectInput');
                const inputCreate = document.getElementById('project_file');
                const orig = '{{ $report?->project_file ? ($report?->project_file_name ?? basename($report?->project_file)) : 'Belum ada file proyek' }}';
                if (display) display.value = orig;
                if (inputUpdate) inputUpdate.value = '';
                if (inputCreate) inputCreate.value = '';
                if (display) {
                    display.classList.remove('text-blue-700');
                    display.classList.add('text-gray-700');
                }
            }

            // Attach to global if elements exist
            const fileInput = document.getElementById('fileInput');
            const projectInput = document.getElementById('projectInput');
            const projectCreateInput = document.getElementById('project_file');
            if (fileInput) {
                fileInput.addEventListener('change', function() { updateFileDisplay(this); });
            }
            if (projectInput) {
                projectInput.addEventListener('change', function() { updateProjectDisplay(this); });
            }
            if (projectCreateInput) {
                projectCreateInput.addEventListener('change', function() { updateProjectDisplay(this); });
            }

            // Clear buttons
            const clearFileBtn = document.querySelector('button[onclick="clearFile()"]');
            const clearProjectBtn = document.querySelector('button[onclick="clearProject()"]');
            if (clearFileBtn) clearFileBtn.addEventListener('click', clearFile);
            if (clearProjectBtn) clearProjectBtn.addEventListener('click', clearProject);

            // Character counter for testimony
            const testimonyInput = document.getElementById('testimony');
            const charCount = document.getElementById('charCount');
            if (testimonyInput) {
                function updateCharCount() {
                    const count = testimonyInput.value.length;
                    charCount.textContent = count + '/1000';
                    charCount.classList.remove('text-green-600', 'text-yellow-600', 'text-red-600');
                    if (count < 20) {
                        charCount.classList.add('text-red-600');
                    } else if (count < 500) {
                        charCount.classList.add('text-green-600');
                    } else {
                        charCount.classList.add('text-yellow-600');
                    }
                }
                testimonyInput.addEventListener('input', updateCharCount);
                testimonyInput.addEventListener('change', updateCharCount);
                updateCharCount(); // Initial call
            }
        });
        </script>
        @endpush

        @endif

    </div>
</div>

@endsection