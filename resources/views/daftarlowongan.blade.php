{{-- @extends('layouts.app') --}}

{{-- @section('title', 'Daftar Lowongan Magang') --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Daftar Lowongan Magang</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/fa-subset.css') }}?v={{ @filemtime(public_path('vendor/fontawesome/fa-subset.css')) }}">
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
{{-- @push('styles') --}}
    <link rel="stylesheet" href="{{ asset('css/pages/daftarlowongan.css') }}?v={{ @filemtime(public_path('css/pages/daftarlowongan.css')) }}">
{{-- @endpush --}}

{{-- @section('content') --}}
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
</head>
<body>

    <div class="dash-bg py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── HERO ── --}}
            <div class="hero-card p-7 anim-1">
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    <div class="flex-1">
                        <div class="btn-back mb-4 mr-1">
                            <a href="{{ url('/') }}">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </a>
                        </div>
                        <div class="floating-badge mb-4">
                            <i class="fas fa-briefcase"></i>
                            Lowongan Magang
                        </div>
                        <h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                            Temukan Lowongan <br class="hidden lg:block"> Magang Terbaik
                        </h1>
                        <p class="mt-3 text-blue-100 leading-relaxed max-w-xl">
                            Jelajahi lowongan magang dari mitra terpercaya dan mulai perjalanan
                            kariermu bersama BBLSDM Komdigi Makassar.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-4 text-sm text-indigo-100">
                            <span><i class="fas fa-map-marker-alt mr-1"></i> Makassar, Sulawesi Selatan</span>
                            <span><i class="fas fa-clock mr-1"></i> Dibuka setiap semester</span>
                        </div>
                    </div>

                    <div class="hero-stat-box">
                        <p class="text-indigo-200 text-xs uppercase tracking-widest font-bold mb-2">
                            Total Lowongan
                        </p>
                        <h2 class="text-5xl font-extrabold text-white mono">
                            {{ $lowongans->total() ?? count($lowongans) }}
                        </h2>
                        <p class="text-indigo-200 text-sm mt-1">Posisi tersedia</p>
                    </div>

                </div>
            </div>

            {{-- ── FILTER ── --}}
            <div class="filter-card anim-2">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="flex flex-wrap xl:flex-nowrap items-center gap-3">

                        <div class="relative flex-1 min-w-[200px]">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari lowongan, posisi, atau divisi..."
                                class="filter-input pl-10">
                        </div>

                        <div class="w-full sm:w-[200px]">
                            <select name="perusahaan" class="filter-select">
                                <option value="">Semua Perusahaan</option>
                                @foreach($perusahaans ?? [] as $perusahaan)
                                    <option value="{{ $perusahaan->id }}"
                                        {{ request('perusahaan') == $perusahaan->id ? 'selected' : '' }}>
                                        {{ $perusahaan->nama_industri }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full sm:w-[180px]">
                            <select name="divisi" class="filter-select">
                                <option value="">Semua Divisi</option>
                                @foreach($divisis ?? [] as $divisi)
                                    <option value="{{ $divisi }}"
                                        {{ request('divisi') == $divisi ? 'selected' : '' }}>
                                        {{ $divisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button type="submit" class="filter-btn filter-btn-primary flex-1 sm:flex-none">
                                <i class="fas fa-search"></i>
                                Cari
                            </button>
                            @if(request()->filled('search') || request()->filled('perusahaan') || request()->filled('divisi'))
                                <a href="{{ url()->current() }}" class="filter-btn filter-btn-reset flex-1 sm:flex-none">
                                    <i class="fas fa-rotate-left"></i>
                                    Reset
                                </a>
                            @endif
                        </div>

                    </div>
                </form>
            </div>

            {{-- ── RESULTS BAR ── --}}
            <div class="flex flex-wrap items-center justify-between gap-3 anim-3 px-1">
                <p class="text-sm text-slate-500">
                    Menampilkan
                    <span class="font-bold text-slate-700">
                        {{ $lowongans instanceof \Illuminate\Pagination\LengthAwarePaginator
                            ? $lowongans->firstItem() . '–' . $lowongans->lastItem()
                            : count($lowongans) }}
                    </span>
                    dari
                    <span class="font-bold text-slate-700">
                        {{ $lowongans instanceof \Illuminate\Pagination\LengthAwarePaginator
                            ? $lowongans->total()
                            : count($lowongans) }}
                    </span>
                    lowongan
                </p>
                <div class="flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'terbaru']) }}"
                    class="sort-chip {{ request('sort', 'terbaru') === 'terbaru' ? 'active' : '' }}">
                        <i class="fas fa-clock"></i> Terbaru
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'kuota']) }}"
                    class="sort-chip {{ request('sort') === 'kuota' ? 'active' : '' }}">
                        <i class="fas fa-users"></i> Kuota terbanyak
                    </a>
                </div>
            </div>

            {{-- ── GRID ── --}}
            <div class="lowongan-grid anim-4">

                @forelse($lowongans as $lowongan)
                    @php
                        $status = $lowongan->status ?? 'dibuka';

                        $logo = optional($lowongan->industri)->logo_industri
                            ? asset('storage/' . $lowongan->industri->logo_industri)
                            : null;
                    @endphp

                    <div class="lowongan-card">
                        <div class="flex justify-end">
                            <span class="status-badge {{ $status === 'dibuka' ? 'badge-aktif' : 'badge-nonaktif' }}">
                                <i class="fas {{ $status === 'dibuka' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                {{ ucfirst($status) }}
                            </span>
                        </div>

                        <h2 class="card-title">
                            {{ $lowongan->judul_lowongan ?? '-' }}
                        </h2>

                        <div class="card-meta">
                            @if($lowongan->posisi_magang)
                                <span class="meta-chip">
                                    <i class="fas fa-briefcase"></i>
                                    {{ Str::limit($lowongan->posisi_magang, 20) }}
                                </span>
                            @endif
                            @if($lowongan->divisi)
                                <span class="meta-chip">
                                    <i class="fas fa-layer-group"></i>
                                    {{ Str::limit($lowongan->divisi, 20) }}
                                </span>
                            @endif
                            <span class="meta-chip">
                                <i class="fas fa-users"></i>
                                {{ $lowongan->kuota_peserta ?? 0 }} Peserta
                            </span>
                        </div>

                        <div class="card-footer">
                            <div class="card-company">
                                <div class="company-logo">
                                    @if($logo)
                                        <img src="{{ $logo }}" alt="Logo">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(optional($lowongan->industri)->nama_industri ?? 'I') }}&background=4f46e5&color=fff&size=64" alt="Logo">
                                    @endif
                                </div>
                                <div>
                                    <p class="company-name">{{ optional($lowongan->industri)->nama_industri ?? 'BBLSDM Komdigi Makassar' }}</p>
                                    <p class="company-sub">{{ optional($lowongan->created_at)->translatedFormat('d M Y') ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a href="{{ route('institusi.create') }}" class="btn-card btn-detail">Detail</a>
                            </div>
                        </div>
                    </div>

                @empty

                    <div class="empty-card">
                        <div class="empty-icon-wrap">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="text-2xl font-extrabold text-slate-700 mb-3">
                            Tidak Ada Lowongan
                        </h3>
                        <p class="text-slate-500 max-w-md mx-auto leading-relaxed">
                            Saat ini belum ada lowongan yang sesuai dengan filter yang dipilih.
                            Coba ubah kriteria pencarian Anda.
                        </p>
                        @if(request()->filled('search') || request()->filled('perusahaan') || request()->filled('divisi'))
                            <a href="{{ url()->current() }}"
                            class="inline-flex items-center gap-2 mt-6 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition">
                                <i class="fas fa-rotate-left"></i>
                                Reset Filter
                            </a>
                        @endif
                    </div>

                @endforelse

            </div>

            {{-- ── PAGINATION ── --}}
            @if($lowongans instanceof \Illuminate\Pagination\LengthAwarePaginator && $lowongans->hasPages())
                <div class="pagination-wrap py-2">
                    {{-- Previous --}}
                    @if($lowongans->onFirstPage())
                        <span class="page-item disabled">
                            <span class="page-link"><i class="fas fa-chevron-left text-xs"></i></span>
                        </span>
                    @else
                        <a class="page-item" href="{{ $lowongans->previousPageUrl() }}">
                            <span class="page-link"><i class="fas fa-chevron-left text-xs"></i></span>
                        </a>
                    @endif

                    {{-- Pages --}}
                    @foreach($lowongans->getUrlRange(1, $lowongans->lastPage()) as $page => $url)
                        @if($page == $lowongans->currentPage())
                            <span class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </span>
                        @else
                            <a class="page-item" href="{{ $url }}">
                                <span class="page-link">{{ $page }}</span>
                            </a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($lowongans->hasMorePages())
                        <a class="page-item" href="{{ $lowongans->nextPageUrl() }}">
                            <span class="page-link"><i class="fas fa-chevron-right text-xs"></i></span>
                        </a>
                    @else
                        <span class="page-item disabled">
                            <span class="page-link"><i class="fas fa-chevron-right text-xs"></i></span>
                        </span>
                    @endif
                </div>
            @endif

        </div>
    </div>
    <script defer src="{{ asset('js/live-filter.js') }}?v={{ @filemtime(public_path('js/live-filter.js')) }}"></script>
</body>
{{-- @endsection --}}