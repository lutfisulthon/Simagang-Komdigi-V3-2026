@extends('layouts.app')

@section('title', 'Mikro Skill - Sistem Magang')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/intern-microskill-index.css') }}?v={{ @filemtime(public_path('css/pages/intern-microskill-index.css')) }}">
@endpush

<div class="py-8" style="min-height:100vh">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Flash errors --}}
        @if ($errors->any())
            <div class="bg-red-100 border-t-4 border-red-500 text-red-700 px-6 py-4 mb-6 rounded-xl" role="alert">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ── Hero ── --}}
        <div class="hero-strip shadow-xl a1 mb-6">
            <div class="hero-inner">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold leading-tight">Mikro Skill Saya</h1>
                        <p class="text-blue-200">Kelola aktifitas mikro skillmu</p>
                    </div>

                    <div></div>
                </div>
            </div>
        </div>

        {{-- ── Rekomendasi ── --}}
        <p class="ms-section-label">
            <i class="fas fa-lightbulb" style="color:#16a34a;font-size:12px"></i>
            Rekomendasi Mikro Skill
        </p>

        <div class="ms-rec-carousel-wrap">
            @if(!empty($recommendations) && count($recommendations) > 0)
                <div class="ms-rec-carousel">
                    @foreach($recommendations as $r)
                        <div class="ms-rec-card">
                            <div>
                                <span class="ms-rec-badge">
                                    <i class="fas fa-star" style="font-size:10px"></i> Tersedia
                                </span>
                            </div>
                            <p class="ms-rec-title">
                                <a href="{{ $r->link_micro }}" target="_blank" rel="noopener" class="ms-rec-title-link">{{ $r->judul_micro }}</a>
                            </p>
                            <a href="{{ $r->link_micro }}" target="_blank" rel="noopener" class="ms-rec-link">
                                <i class="fas fa-globe" style="font-size:13px"></i>
                                {{ parse_url($r->link_micro, PHP_URL_HOST) ?? $r->link_micro }}
                            </a>
                            {{-- <div class="ms-rec-footer">
                                <a href="{{ route('intern.microskill.create', ['title' => $r->judul_micro]) }}" class="ms-upload-btn">
                                    <i class="fas fa-upload" style="font-size:11px"></i> Upload bukti
                                </a>
                                <a href="{{ $r->link_micro }}" target="_blank" rel="noopener" class="ms-ext-link">
                                    <i class="fas fa-external-link-alt" style="font-size:11px"></i> Buka
                                </a>
                            </div> --}}
                        </div>
                    @endforeach

                    {{-- Kartu CTA: Lihat lebih banyak --}}
                    <div class="ms-rec-card ms-rec-card-more" aria-hidden="false">
                        <div>
                            <span class="ms-rec-badge" style="background:#eef2ff;color:#3730a3">
                                <i class="fas fa-ellipsis-h" style="font-size:11px"></i> Lebih banyak
                            </span>
                        </div>
                        {{-- <p class="ms-rec-title">Lihat lebih banyak rekomendasi</p> --}}
                        <a href="https://digitalent.komdigi.go.id/micro-skill" target="_blank" rel="noopener" class="ms-more-cta">
                            Buka Digitalent Micro-skill
                        </a>
                    </div>
                </div>
            @else
                <div class="ms-rec-empty">
                    <i class="fas fa-check-circle" style="font-size:2rem;color:#86efac;margin-bottom:8px;display:block"></i>
                    Semua mikro skill sudah dikerjakan. Kerja bagus! 🎉
                </div>
            @endif
        </div>

        {{-- ── Data Mikro Skill ── --}}

        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-star mr-3"></i>
                        Mikro Skill Selesai
                    </h2>
                    <div class="flex items-center gap-3">
                        <form method="GET" action="{{ route('intern.microskill.index') }}" class="ms-search-wrap">
                            <i class="fas fa-search" style="color:rgba(13, 20, 152, 0.966);font-size:13px"></i>
                            <input id="q" name="q" value="{{ request('q') }}" type="text" placeholder="Cari judul mikro skill..." aria-label="Cari mikro skill" />
                            @if (request('q'))
                                <a href="{{ route('intern.microskill.index') }}" class="ms-search-clear" aria-label="Bersihkan pencarian">
                                    <i class="fas fa-times"></i>
                                </a>
                            @else
                                <button type="submit" class="ms-search-btn" aria-label="Cari">
                                    <i class="fas fa-arrow-right" style="font-size:13px"></i>
                                </button>
                            @endif
                        </form>

                        @if($cekaktif)
                            <a href="{{ route('intern.microskill.create') }}" class="cta-btn">
                                <i class="fas fa-upload"></i>
                                Upload Bukti
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    {{-- search & upload moved to hero header to avoid duplication --}}
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[500px] scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-blue-50">
                                <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tl-lg">Judul</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">Dikirim</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">Bukti</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-blue-900 uppercase tracking-wider rounded-tr-lg">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($submissions as $s)
                                <tr class="hover:bg-blue-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-left">
                                        <div class="flex items-center justify-start min-w-0">
                                            <span class="text-sm font-medium text-gray-900 truncate">{{ $s->title }}</span>
                                        </div>
                                    </td>
                                    {{-- status column removed --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                        @if ($s->submitted_at)
                                            <div class="flex items-center justify-center">
                                                {{ \Carbon\Carbon::parse($s->submitted_at)->setTimezone('Asia/Makassar')->format('d/m/Y') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($s->photo_path)
                                            <img src="{{ $s->photo_url }}" alt="Bukti" class="w-12 h-12 object-cover rounded-lg border-2 border-blue-200 cursor-pointer hover:border-blue-400 transition-all shadow-sm mx-auto" onclick="window.open('{{ $s->photo_url }}', '_blank')" title="Klik untuk melihat full size" loading="lazy" decoding="async">
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <a href="{{ route('intern.microskill.edit', $s->id) }}" class="inline-flex items-center justify-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-all duration-200" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { url: '{{ route('intern.microskill.destroy', $s->id) }}' } }))" class="inline-flex items-center justify-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-all duration-200" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-inbox text-5xl mb-3 text-gray-300"></i>
                                            <p class="text-lg font-medium">Belum ada pengumpulan.</p>
                                            <p class="text-sm mt-2">Mulai dengan membuat pengumpulan pertama Anda.</p>
                                            @if ($cekaktif)
                                                <a href="{{ route('intern.microskill.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300">
                                                    <i class="fas fa-upload mr-2"></i>Upload Bukti
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($submissions->count() > 0)
                    <div class="mt-6">
                        {{ $submissions->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-data="{ showDeleteModal: false, deleteUrl: '' }" @open-delete-modal.window="showDeleteModal = true; deleteUrl = $event.detail.url">
    <!-- Modal Backdrop -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" x-transition.opacity>
        <!-- Modal Content -->
        <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all" x-show="showDeleteModal" x-transition.scale.origin.bottom>
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Konfirmasi Hapus</h3>
            <p class="text-center text-gray-600 mb-6">Apakah Anda yakin ingin menghapus mikro skill ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-center gap-3">
                <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Batal
                </button>
                <form :action="deleteUrl" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-trash"></i> Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>document.body.classList.add('page-microskill');</script>
@endpush

@endsection