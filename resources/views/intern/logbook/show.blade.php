@extends('layouts.app')

@section('title', 'Detail Logbook - Sistem Magang')

@push('styles')
    <style>

        *, body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .intern-logbook-hero { background: linear-gradient(110deg,#0f2878 0%,#2d3ecb 55%,#4f46e5 100%); border-radius:20px; padding:20px; box-shadow:0 10px 30px rgba(20,40,120,0.12); }
        .panel-shell { background:#fff; border-radius:16px; box-shadow:0 1px 3px rgba(20,40,120,0.06),0 6px 18px rgba(20,40,120,0.06); overflow:hidden; }
        .section-card { background:#f8faff; border:1px solid #e8eeff; border-radius:12px; }
        .status-pill { display:inline-flex; gap:8px; padding:8px 12px; border-radius:999px; font-weight:700; }
        .status-pill.pending { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .status-pill.approved { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
        .approval-note { white-space: pre-line; }
    </style>
@endpush

@section('content')
    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <div class="intern-logbook-hero px-5 sm:px-8 py-6 sm:py-8">
                    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl text-white">
                            <p class="text-xs sm:text-sm uppercase tracking-[0.3em] text-blue-100/80">Logbook</p>
                            <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold leading-tight">Detail Logbook</h1>
                            <p class="mt-1 text-sm text-blue-100">{{ \Carbon\Carbon::parse($logbook->date)->format('d M Y') }}</p>
                        </div>

                        <div class="inline-flex items-center gap-4 rounded-2xl bg-white/10 px-4 py-3 backdrop-blur-sm border border-white/10">
                            @if (Auth::user()->intern && Auth::user()->intern->photo_path)
                                <img src="{{ url('storage/' . Auth::user()->intern->photo_path) }}" class="h-14 w-14 rounded-full object-cover border-4 border-white shadow" alt="{{ Auth::user()->name }}" loading="lazy" decoding="async">
                            @else
                                <div class="h-14 w-14 rounded-full bg-white flex items-center justify-center shadow"><i class="fas fa-user text-blue-600 text-2xl"></i></div>
                            @endif
                            <div class="min-w-0 text-white">
                                <h2 class="text-lg sm:text-xl font-bold truncate">{{ Auth::user()->name }}</h2>
                                <p class="mt-1 text-xs sm:text-sm text-blue-100 truncate">{{ Auth::user()->intern->institution ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-shell">
                <div class="border-b border-slate-100 bg-white px-5 sm:px-8 py-4 sm:py-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                <i class="fas fa-clipboard-list text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Logbook</p>
                                <p class="text-base sm:text-lg font-bold text-slate-900">Detail catatan harian</p>
                            </div>
                        </div>

                        <div class="soft-badge inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700">
                            <i class="fas fa-calendar-day text-blue-600"></i>
                            <span>{{ \Carbon\Carbon::parse($logbook->date)->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 p-5 sm:p-8 lg:grid-cols-2">
                    <div class="section-card p-5 sm:p-6 lg:col-span-2">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-500">Identitas</p>
                                <h3 class="text-lg font-bold text-slate-900">Anak magang</h3>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white px-4 py-4 shadow-sm border border-slate-100">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Nama</p>
                                <p class="mt-1 text-sm sm:text-base font-semibold text-slate-900">{{ $logbook->intern->name }}</p>
                            </div>
                            <div class="rounded-2xl bg-white px-4 py-4 shadow-sm border border-slate-100">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Instansi</p>
                                <p class="mt-1 text-sm sm:text-base font-semibold text-slate-900">{{ $logbook->intern->institution }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="section-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-blue-500">Aktivitas</p>
                                <h3 class="text-lg font-bold text-slate-900">Isi logbook</h3>
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white border border-slate-100 p-4 sm:p-5 shadow-sm">
                            <p class="whitespace-pre-line text-sm sm:text-[15px] leading-7 text-slate-700">{{ $logbook->activity }}</p>
                        </div>
                    </div>

                    <div class="section-card p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-indigo-500">Dokumentasi</p>
                                <h3 class="text-lg font-bold text-slate-900">Foto kegiatan</h3>
                            </div>
                        </div>
                        @if ($logbook->photo_path)
                            @can('view', $logbook)
                                <div class="relative group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                                    <img src="{{ $logbook->photo_url }}" class="h-72 w-full object-cover transition duration-300 group-hover:scale-[1.02]" alt="Foto Logbook" onclick="window.open('{{ $logbook->photo_url }}', '_blank')" loading="lazy" decoding="async" />
                                    <button type="button" class="absolute bottom-4 right-4 inline-flex items-center gap-2 rounded-full bg-white/95 px-4 py-2 text-sm font-semibold text-blue-700 shadow-lg transition hover:bg-white" onclick="window.open('{{ $logbook->photo_url }}', '_blank')">Lihat penuh</button>
                                </div>
                                <p class="mt-3 text-xs text-slate-500"><i class="fas fa-circle-info mr-1 text-blue-500"></i> Klik gambar untuk membuka ukuran penuh.</p>
                            @else
                                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                                    <p class="mt-3 text-sm font-medium text-slate-500">Tidak ada akses.</p>
                                </div>
                            @endcan
                        @else
                            <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                                <i class="fas fa-image text-4xl text-slate-300"></i>
                                <p class="mt-3 text-sm font-medium text-slate-500">Tidak ada foto dokumentasi.</p>
                            </div>
                        @endif
                    </div>

                    <div class="section-card p-5 sm:p-6 lg:col-span-2">
                        @php
                            $status = $logbook->approval_status ?? 'pending';
                            $statusLabel = $status === 'approved' ? 'Disetujui' : 'Menunggu review';
                        @endphp

                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-emerald-500">Tinjauan</p>
                                <h3 class="text-lg font-bold text-slate-900">Approval dan catatan</h3>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-4 lg:grid-cols-3">
                            <div class="rounded-2xl bg-white px-4 py-4 shadow-sm border border-slate-100">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Status</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900"><span class="status-pill {{ $status }}">{{ $statusLabel }}</span></p>
                            </div>
                            <div class="rounded-2xl bg-white px-4 py-4 shadow-sm border border-slate-100">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Disetujui oleh</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $logbook->approver?->name ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white px-4 py-4 shadow-sm border border-slate-100">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Waktu approval</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $logbook->approved_at ? \Carbon\Carbon::parse($logbook->approved_at)->locale('id')->isoFormat('D MMMM Y') : '-' }}</p>
                            </div>
                        </div>

                        @if ($status === 'approved')
                            <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100 mt-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Catatan approval</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900 whitespace-pre-line">{{ $logbook->approval_note ?: '-' }}</p>

                                <p class="mt-3 text-xs text-slate-500">Disetujui oleh <strong>{{ $logbook->approver?->name ?? '-' }}</strong> pada {{ $logbook->approved_at ? \Carbon\Carbon::parse($logbook->approved_at)->locale('id')->isoFormat('D MMMM Y') : '-' }}</p>
                            </div>
                        @else
                            <div class="mt-4">
                                <p class="text-sm text-slate-500">Logbook belum direview oleh mentor.</p>
                            </div>
                        @endif
                    </div>

                    <div class="lg:col-span-2 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ url()->previous() }}" onclick="event.preventDefault(); window.history.back();" class="inline-flex items-center justify-center rounded-2xl bg-slate-600 px-5 py-3 text-sm sm:text-base font-semibold text-white shadow-sm hover:bg-slate-700">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
