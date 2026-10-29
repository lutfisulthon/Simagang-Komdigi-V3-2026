{{-- @extends('layouts.app') --}}

{{-- @section('title', 'Daftar Lowongan Magang') --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.theme-head')
    <title>Daftar Lowongan Magang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ url('storage/vendor/icon-komdigi.png') }}">
{{-- @push('styles') --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }

        body { background: #f3f7ff; }

        .dash-bg {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(99,102,241,.12), transparent 30%),
                linear-gradient(135deg, #eef4ff 0%, #f5f7ff 40%, #edf3ff 100%);
        }

        /* ── HERO ── */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            background: linear-gradient(135deg, #1e3a8a 0%, #4338ca 55%, #6366f1 100%);
            box-shadow: 0 15px 40px rgba(30,58,138,.18);
        }
        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            top: -120px; right: -80px;
        }
        .hero-card::after {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            bottom: -180px; left: -80px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: .5rem 1rem;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(10px);
            color: #dbeafe;
            padding: .5rem 1rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: #1d6fca;
            color: white;
            transform: translateX(-4px);
        }

        .floating-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(10px);
            color: #dbeafe;
            padding: .5rem 1rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .hero-stat-box {
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.2rem 1.8rem;
            text-align: center;
            min-width: 140px;
        }

        /* ── FILTER ── */
        .filter-card {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(18px);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,.7);
            box-shadow: 0 8px 30px rgba(15,23,42,.06);
            padding: 1.4rem 1.6rem;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            height: 50px;
            border-radius: 16px;
            border: 1px solid #dbe3f0;
            background: #fff;
            padding: 0 1rem;
            font-size: .9rem;
            color: #334155;
            transition: .2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,.10);
        }

        .filter-btn {
            height: 50px;
            padding: 0 1.4rem;
            border-radius: 16px;
            font-size: .88rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            transition: all .2s ease;
            font-family: 'Plus Jakarta Sans', sans-serif;
            white-space: nowrap;
        }
        .filter-btn-primary {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #fff;
            box-shadow: 0 8px 20px rgba(79,70,229,.20);
        }
        .filter-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(79,70,229,.28);
        }
        .filter-btn-reset {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
        .filter-btn-reset:hover {
            background: #e2e8f0;
            color: #334155;
        }

        /* ── SORT CHIPS ── */
        .sort-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            transition: .15s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .sort-chip.active,
        .sort-chip:hover {
            background: #eef2ff;
            border-color: #a5b4fc;
            color: #4338ca;
        }

        /* ── LOWONGAN CARD ── */
        .lowongan-card {
            background: #fff;
            border-radius: 24px;
            padding: 1.4rem;
            border: 1px solid #e8edf5;
            box-shadow: 0 4px 16px rgba(15,23,42,.06);
            transition: .22s ease;
            display: flex;
            flex-direction: column;
            min-height: 220px;
            position: relative;
        }
        .lowongan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(15,23,42,.10);
            border-color: #c7d7f5;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .22rem .7rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .3px;
            white-space: nowrap;
        }
        .badge-aktif   { background: #dcfce7; color: #15803d; }
        .badge-nonaktif { background: #fee2e2; color: #b91c1c; }
        .badge-new     { background: #eef2ff; color: #4338ca; }

        .card-title {
            margin-top: .75rem;
            font-size: 1.12rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.35;
            flex: 1;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem .75rem;
            margin-top: .55rem;
        }
        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            font-size: .72rem;
            font-weight: 600;
            color: #64748b;
        }
        .meta-chip i { font-size: .62rem; color: #94a3b8; }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: .9rem;
            border-top: 1px solid #f1f5f9;
            gap: .75rem;
        }
        .card-company {
            display: flex;
            align-items: center;
            gap: .6rem;
            min-width: 0;
        }
        .company-logo {
            width: 36px; height: 36px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #f8faff;
            flex-shrink: 0;
        }
        .company-logo img { width: 100%; height: 100%; object-fit: cover; }
        .company-name {
            font-size: .75rem;
            font-weight: 700;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 145px;
        }
        .company-sub {
            font-size: .65rem;
            color: #94a3b8;
            font-weight: 500;
        }
        .card-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-shrink: 0;
        }
        .btn-card {
            height: 34px;
            padding: 0 1rem;
            border-radius: 10px;
            font-size: .75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: none;
            cursor: pointer;
            transition: .2s;
            text-decoration: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
            white-space: nowrap;
        }
        .btn-detail {
            background: #0f172a;
            color: #fff;
        }
        .btn-detail:hover {
            background: #4f46e5;
            color: #fff;
            text-decoration: none;
        }
        .btn-apply {
            background: #dcfce7;
            color: #15803d;
        }
        .btn-apply:hover {
            background: #16a34a;
            color: #fff;
            text-decoration: none;
        }

        /* ── EMPTY ── */
        .empty-card {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(18px);
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,.7);
            box-shadow: 0 8px 30px rgba(15,23,42,.06);
            padding: 5rem 2rem;
            text-align: center;
            grid-column: 1 / -1;
        }
        .empty-icon-wrap {
            width: 100px; height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0e7ff, #dbeafe);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: #a5b4fc;
        }

        /* ── PAGINATION ── */
        .pagination-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pagination-wrap .page-item .page-link {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-size: .85rem;
            font-weight: 700;
            transition: .18s;
            text-decoration: none;
        }
        .pagination-wrap .page-item.active .page-link {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 12px rgba(79,70,229,.22);
        }
        .pagination-wrap .page-item .page-link:hover {
            background: #eef2ff;
            border-color: #a5b4fc;
            color: #4338ca;
        }
        .pagination-wrap .page-item.disabled .page-link {
            opacity: .4;
            cursor: default;
        }

        /* ── GRID ── */
        .lowongan-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
        }
        @media (max-width: 1100px) {
            .lowongan-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 768px) {
            .lowongan-grid { grid-template-columns: 1fr; }
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-1 { animation: fadeSlideUp .5s ease both; }
        .anim-2 { animation: fadeSlideUp .5s ease .1s both; }
        .anim-3 { animation: fadeSlideUp .5s ease .2s both; }
        .anim-4 { animation: fadeSlideUp .5s ease .3s both; }
    </style>
{{-- @endpush --}}

{{-- @section('content') --}}
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
</body>
{{-- @endsection --}}