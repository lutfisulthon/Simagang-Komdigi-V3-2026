@extends('layouts.app')

@section('title', 'Detail Nilai Sertifikat - Institusi')

@push('styles')
    <style>

        *,
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dash-bg {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8eeff 0%, #f0f4ff 40%, #e4ecff 100%);
        }

        .hero-strip {
            background: linear-gradient(110deg, #1e3a8a 0%, #3b4fd8 55%, #4f46e5 100%);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(20, 40, 120, 0.16);
        }

        .hero-strip::before {
            content: '';
            position: absolute;
            top: -70px;
            right: -50px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-strip::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: 18%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        .panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(20, 40, 120, 0.06), 0 4px 18px rgba(20, 40, 120, 0.06);
            overflow: hidden;
        }

        .panel-header {
            background: linear-gradient(100deg, #1e3a8a 0%, #3b4fd8 100%);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-header h2 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.01em;
            margin: 0;
        }

        .panel-body {
            padding: 20px 22px;
        }

        .section-card {
            background: #f8faff;
            border: 1px solid #e8eeff;
            border-radius: 18px;
        }
    </style>
@endpush

@section('content')
    <div class="dash-bg py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="hero-strip px-6 py-7 sm:px-8 sm:py-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl text-white">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Institusi Dashboard</p>
                        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Detail Nilai Sertifikat</h1>
                        <p class="mt-3 text-sm sm:text-base text-blue-100/90">Detail nilai sertifikat untuk
                            <strong>{{ $certificate->intern->name }}</strong>.</p>
                    </div>

                    <div
                        class="bg-white/10 border border-white/10 rounded-2xl px-4 py-4 text-white shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-100/80">Sertifikat</p>
                                <p class="text-base font-bold">#{{ $certificate->certificate_number }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-id-card text-blue-200 text-base"></i>
                    <h2>Ringkasan Sertifikat</h2>
                </div>
                <div class="panel-body">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Sertifikat
                                #{{ $certificate->certificate_number }}</h2>
                            <p class="text-sm text-slate-500">Diterbitkan:
                                {{ optional($certificate->issue_date)->translatedFormat('d F Y') ?? '-' }}</p>
                        </div>
                        <a href="{{ route('institusi.certificate.index') }}"
                            class="inline-flex items-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-3 text-blue-700 transition hover:bg-blue-100">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Daftar Sertifikat
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="section-card p-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4">Informasi Intern</h3>
                            <dl class="space-y-3 text-sm text-slate-700">
                                <div class="flex justify-between gap-4">
                                    <dt class="font-semibold">Nama</dt>
                                    <dd>{{ $certificate->intern->name }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-semibold">Institusi</dt>
                                    <dd>{{ $certificate->intern->institution }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-semibold">Periode Magang</dt>
                                    <dd>{{ optional($certificate->intern->start_date)->translatedFormat('d M Y') ?? '-' }} -
                                        {{ optional($certificate->intern->end_date)->translatedFormat('d M Y') ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-semibold">Status</dt>
                                    <dd>{{ $certificate->intern->is_active ? 'Aktif' : 'Alumni' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="section-card p-6 bg-white">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">Ringkasan Nilai</h3>
                            <div class="space-y-3 text-sm text-slate-700">
                                @if ($certificate->score)
                                    <div class="flex justify-between gap-4">
                                        <span class="font-semibold">Rata-rata Sertifikat</span>
                                        <span>{{ $certificate->score->average }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="font-semibold">Grade Sertifikat</span>
                                        <span>{{ scoreToGrade($certificate->score->average) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="font-semibold">Micro Skill</span>
                                        <span>{{ $certificate->score->micro_skill }} selesai</span>
                                    </div>
                                @else
                                    <div class="flex justify-between gap-4">
                                        <span class="font-semibold">Rata-rata Sertifikat</span>
                                        <span>-</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="font-semibold">Grade Sertifikat</span>
                                        <span>-</span>
                                    </div>
                                @endif

                                @if ($certificate->intern->finalReport)
                                    <div class="pt-4 border-t border-slate-200">
                                        <div class="flex justify-between gap-4">
                                            <span class="font-semibold">Nilai Laporan Akhir</span>
                                            <span>{{ $certificate->intern->finalReport->score ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between gap-4">
                                            <span class="font-semibold">Grade Laporan Akhir</span>
                                            <span>{{ $certificate->intern->finalReport->grade ?? '-' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="pt-4 border-t border-slate-200">
                                        <p class="text-sm text-slate-500">Belum ada laporan akhir.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($certificate->score)
                <div class="panel">
                    <div class="panel-header">
                        <i class="fas fa-chart-column text-blue-200 text-base"></i>
                        <h2>Detail Komponen Nilai</h2>
                    </div>
                    <div class="panel-body">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @php
                                $items = [
                                    [
                                        'label' => 'Kedisiplinan & Kehadiran',
                                        'value' => $certificate->score->discipline_attendance,
                                    ],
                                    ['label' => 'Tanggung Jawab', 'value' => $certificate->score->responsibility],
                                    [
                                        'label' => 'Kerjasama & Komunikasi',
                                        'value' => $certificate->score->teamwork_communication,
                                    ],
                                    ['label' => 'Keterampilan Teknis', 'value' => $certificate->score->technical_skill],
                                    ['label' => 'Etos Kerja', 'value' => $certificate->score->work_ethic],
                                    [
                                        'label' => 'Inisiatif & Kreativitas',
                                        'value' => $certificate->score->initiative_creativity,
                                    ],
                                ];
                            @endphp

                            @foreach ($items as $item)
                                <div class="section-card p-5">
                                    <p class="text-sm text-slate-500">{{ $item['label'] }}</p>
                                    <p class="mt-3 text-3xl font-bold text-blue-700">{{ $item['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
