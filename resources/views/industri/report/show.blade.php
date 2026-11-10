@extends('layouts.app')

@section('title', 'Detail Laporan - Sistem Magang')

@push('styles')
    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dash-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        }

        .hero-strip {
            background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 50%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
            color: #fff;
        }

        .hero-strip::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: 30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.06), 0 4px 20px rgba(30, 58, 138, 0.06);
            overflow: hidden;
            border: 1px solid #dbeafe;
        }

        .panel-header {
            background: linear-gradient(110deg, #1e3a8a 0%, #3b4fd8 100%);
            color: #fff;
        }

        @media (max-width: 640px) {
            .hero-title {
                font-size: 1.55rem;
                line-height: 1.3;
            }

            .profile-strip {
                padding: 1rem;
            }

            .profile-strip .avatar-lg {
                width: 3rem;
                height: 3rem;
                margin-right: 0.75rem;
                border-width: 3px;
            }

            .profile-strip .profile-name {
                font-size: 1.2rem;
                line-height: 1.3;
            }

            .panel-content,
            .status-form {
                padding: 1rem;
            }

            .detail-grid {
                gap: 1rem;
            }

            .action-stack {
                flex-direction: column-reverse;
                align-items: stretch;
                gap: 0.75rem;
            }

            .action-stack a,
            .action-stack button {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="hero-strip mb-6">
                <div class="relative z-10 px-6 py-7">
                    <h1 class="hero-title text-3xl sm:text-4xl font-bold leading-tight mb-2">Detail Laporan Akhir</h1>
                    <p class="text-blue-100">Review penilaian untuk laporan akhir</p>
                </div>
            </div>

            {{-- Main Content Card --}}

            <div class="panel mb-6">

                {{-- Profile Header --}}
                <div class="panel-header profile-strip px-6 py-6">
                    <div class="flex items-center">
                        @if ($report->intern->photo_path)
                            <img src="{{ url('storage/' . $report->intern->photo_path) }}"
                                class="avatar-lg w-16 h-16 rounded-full object-cover border-4 border-white shadow-lg mr-4"
                                alt="{{ $report->intern->name }}" loading="lazy" decoding="async" />
                        @else
                            <div
                                class="avatar-lg w-16 h-16 rounded-full bg-white flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-user text-blue-600 text-2xl"></i>
                            </div>
                        @endif
                        <div class="text-white">
                            <h2 class="profile-name text-2xl font-bold">{{ $report->intern->name }}</h2>
                            <p class="text-blue-100 flex items-center mt-1">
                                <i class="fas fa-university mr-2"></i>
                                {{ $report->intern->institution }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Content --}}

                <div class="panel-content p-8 space-y-6">
                    <!-- File Info -->
                    <div class="detail-grid grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Nama File</p>
                                <p class="text-sm font-semibold text-gray-900 break-all">{{ $report?->file_name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Tanggal Upload</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $report->submitted_at->locale('id')->translatedFormat('d F Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Status & Grade --}}
                    <div class="detail-grid grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-info-circle text-green-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Status</p>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium
                                    @if ($report->status == 'approved') bg-green-100 text-green-800
                                    @elseif($report->status == 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                        @if ($report->status == 'approved')
                                            Approved
                                        @elseif($report->status == 'rejected')
                                            Rejected
                                        @else
                                            Pending
                                        @endif
                                    </span>
                                    @if ($report->needs_revision)
                                        <span
                                            class="px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                            ⚠️ Perlu Revisi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($report->grade)
                            <div class="flex items-start">
                                <div
                                    class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-star text-blue-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Nilai</p>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="text-4xl font-bold 
                                    @if ($report->grade == 'A') text-green-600
                                    @elseif($report->grade == 'B') text-blue-600
                                    @else text-yellow-600 @endif">
                                            {{ $report->grade }}
                                        </span>
                                        @if ($report->score)
                                            <span class="text-lg text-gray-600">({{ $report->score }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Admin Note -->
                    @if ($report->admin_note)
                        <div>
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-sticky-note text-gray-600"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Catatan</h3>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $report->admin_note }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Download Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <a href="{{ route('download', ['path' => $report->file_path]) }}" target="_blank"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-md hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-download mr-2"></i>
                            Download Laporan
                        </a>
                    </div>

                    <!-- Project Section -->
                    @php
                        $projectFilesDisplay = $report->project_files ?? null;
                        $projectLinksDisplay = $report->project_links ?? null;
                    @endphp

                    @if (
                        (!empty($projectFilesDisplay) && is_array($projectFilesDisplay)) ||
                            (!empty($projectLinksDisplay) && is_array($projectLinksDisplay)) ||
                            $report->project_file ||
                            $report->project_link)
                        <div class="pt-6 border-t border-gray-200">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-project-diagram text-blue-600"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Proyek</h3>
                            </div>
                            <div class="space-y-4">
                                @if (!empty($projectFilesDisplay) && is_array($projectFilesDisplay))
                                    @foreach ($projectFilesDisplay as $pf)
                                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                            <p class="text-sm text-gray-700 font-medium mb-2">
                                                <i class="fas fa-file-archive text-gray-500 mr-2"></i>
                                                File Proyek {{ $loop->iteration }}:
                                                {{ data_get($pf, 'name') ?? basename(data_get($pf, 'path', '')) }}
                                            </p>
                                            <a href="{{ route('download', ['path' => data_get($pf, 'path')]) }}"
                                                target="_blank"
                                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 shadow-sm hover:shadow-md transition-all duration-300">
                                                <i class="fas fa-download mr-2"></i>Download File Proyek
                                            </a>
                                        </div>
                                    @endforeach
                                @elseif($report->project_file)
                                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                        <p class="text-sm text-gray-700 font-medium mb-2">
                                            <i class="fas fa-file-archive text-gray-500 mr-2"></i>
                                            File Proyek:
                                            {{ $report?->project_file_name ?? basename($report?->project_file ?? '') }}
                                        </p>
                                        <a href="{{ route('download', ['path' => $report->project_file]) }}"
                                            target="_blank"
                                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 shadow-sm hover:shadow-md transition-all duration-300">
                                            <i class="fas fa-download mr-2"></i>Download File Proyek
                                        </a>
                                    </div>
                                @endif

                                @if (!empty($projectLinksDisplay) && is_array($projectLinksDisplay))
                                    @foreach ($projectLinksDisplay as $pl)
                                        @if (!empty($pl))
                                            <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                                                <p class="text-sm text-gray-700 font-medium mb-2">
                                                    <i class="fas fa-link text-blue-500 mr-2"></i>
                                                    Link Proyek {{ $loop->iteration }}
                                                </p>
                                                <a href="{{ $pl }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-300">
                                                    <i class="fas fa-external-link-alt mr-2"></i>Buka Link Proyek
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                @elseif($report->project_link)
                                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                                        <p class="text-sm text-gray-700 font-medium mb-2">
                                            <i class="fas fa-link text-blue-500 mr-2"></i>
                                            Link Proyek
                                        </p>
                                        <a href="{{ $report->project_link }}" target="_blank" rel="noopener"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-300">
                                            <i class="fas fa-external-link-alt mr-2"></i>Buka Link Proyek
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Activities Section -->
                    @if ($report->activities && is_array($report->activities) && count($report->activities) > 0)
                        <div class="pt-6 border-t border-gray-200">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-tasks text-green-600"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Kegiatan Selama Magang</h3>
                            </div>
                            <div class="space-y-3">
                                @foreach ($report->activities as $activity)
                                    <div class="p-4 bg-green-50 rounded-xl border border-green-200">
                                        <div class="flex items-start">
                                            <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                                            <p class="text-gray-900 flex-1">{{ $activity['description'] ?? '' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Update Status + Nilai (disatukan) --}}
            <div class="panel mb-6">
                <div class="panel-header px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-edit text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white">Update Status Laporan</h3>
                    </div>
                </div>

                <div class="status-form p-8">
                    <form method="POST" action="{{ route('industri.report.update-status', $report) }}" id="unified-form">
                        @csrf
                        @method('PUT')

                        {{-- Status --}}
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                onchange="handleStatusChange(this.value)">
                                <option value="pending"  {{ $report->status == 'pending'  ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $report->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="revised"  {{ $report->needs_revision       ? 'selected' : '' }}>Perlu Revisi</option>
                                <option value="rejected" {{ $report->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        {{-- Bagian Nilai — hanya muncul saat Approved --}}
                        <div id="grade-section"
                            class="{{ $report->status == 'approved' && !$report->grade ? '' : 'hidden' }}
                                    mb-6 p-6 bg-blue-50 border border-blue-200 rounded-xl space-y-5">

                            <div class="flex items-center mb-1">
                                <i class="fas fa-star text-blue-600 mr-2"></i>
                                <h4 class="text-base font-bold text-gray-900">Penilaian</h4>
                            </div>

                            @if ($report->grade)
                                <p class="text-sm text-gray-600">
                                    Laporan ini sudah diberi nilai
                                    <span class="text-3xl font-bold ml-2
                                        @if($report->grade == 'A') text-green-600
                                        @elseif($report->grade == 'B') text-blue-600
                                        @else text-yellow-600 @endif">
                                        {{ $report->grade }}
                                    </span>
                                    @if($report->score)
                                        <span class="text-lg text-gray-500">({{ $report->score }})</span>
                                    @endif
                                </p>
                            @else
                                <div>
                                    <label for="score" class="block text-sm font-bold text-gray-700 mb-2">
                                        Nilai (0–100) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="score" id="score"
                                        min="0" max="100"
                                        value="{{ old('score') }}"
                                        placeholder="Masukkan nilai 0–100"
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm
                                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                                text-lg font-semibold"
                                        oninput="updateGradePreview(this.value)">

                                    <div class="mt-3 p-3 bg-white rounded-lg border border-blue-200">
                                        <p class="text-sm text-blue-800 font-medium">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <strong>Kriteria:</strong> A = 85–100 | B = 70–84 | C = 0–69
                                        </p>
                                    </div>
                                    <p id="gradePreview" class="mt-2 text-sm font-bold"></p>
                                </div>
                            @endif
                        </div>

                        {{-- Catatan Admin (satu-satunya catatan) --}}
                        <div class="mb-6">
                            <label for="admin_note" class="block text-sm font-semibold text-gray-700 mb-2">
                                Catatan Admin
                            </label>
                            <textarea name="admin_note" id="admin_note" rows="5"
                                    placeholder="Berikan catatan, feedback, atau poin revisi jika diperlukan..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm
                                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ old('admin_note', $report->admin_note) }}</textarea>
                            <p class="mt-2 text-sm text-gray-500 flex items-start">
                                <i class="fas fa-lightbulb mr-2 mt-0.5 text-yellow-500"></i>
                                Jika perlu revisi, tuliskan poin-poin yang perlu diperbaiki
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="action-stack flex items-center justify-between pt-4 border-t border-gray-200">
                            <a href="{{ route('industri.report.index') }}"
                            class="text-blue-600 hover:text-blue-800 font-semibold transition duration-200 flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Batal &amp; Kembali
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700
                                        text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                                <i class="fas fa-save mr-2"></i>
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleStatusChange(val) {
            const section = document.getElementById('grade-section');
            const scoreInput = document.getElementById('score');

            if (val === 'approved') {
                section.classList.remove('hidden');
                if (scoreInput) scoreInput.required = true;
            } else {
                section.classList.add('hidden');
                if (scoreInput) {
                    scoreInput.required = false;
                    scoreInput.value = '';
                }
                const preview = document.getElementById('gradePreview');
                if (preview) preview.textContent = '';
            }
        }

        function updateGradePreview(val) {
            const el = document.getElementById('gradePreview');
            if (!el) return;
            const n = parseInt(val);
            if (isNaN(n) || val === '') { el.textContent = ''; return; }
            if (n >= 85) { el.textContent = 'Nilai: A'; el.className = 'mt-2 text-sm font-bold text-green-600'; }
            else if (n >= 70) { el.textContent = 'Nilai: B'; el.className = 'mt-2 text-sm font-bold text-blue-600'; }
            else              { el.textContent = 'Nilai: C'; el.className = 'mt-2 text-sm font-bold text-yellow-600'; }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const sel = document.getElementById('status');
            if (sel) handleStatusChange(sel.value);
        });
    </script>
@endsection
