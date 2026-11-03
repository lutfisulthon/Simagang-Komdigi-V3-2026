@extends('layouts.app')

@section('title', 'Dashboard Magang - Sistem Magang')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

        .mdash, .mdash *:not(i) { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mdash .mono { font-family: 'DM Mono', monospace; }

        .mdash { min-height: 100vh; background: #f4f7fb; }
        .mdash-in { display: flex; flex-direction: column; gap: 20px; }

        /* Palet Sistem Mentor (data-dm-native: mode gelap diatur di sini) */
        .mdash-sec {
            --card: #ffffff; --card-b: #e6ebf3; --shadow: 0 1px 2px rgba(15, 30, 80, .04), 0 6px 20px rgba(15, 30, 80, .04);
            --txt: #1e293b; --mut: #64748b; --faint: #94a3b8;
            --row: #f6f8fc; --row-h: #edf2ff; --row-b: #e8edf6; --line: #edf0f6;
            --prim: #2563eb; --prim-s: #e2ecff; --track: #e8edf7;
            --hero: #eaf1ff; --hero-b: #d6e4ff; --hero-sub: #3b5b94;
            --warn-bg: #fff4e0; --warn-fg: #9a4a07; --warn-b: #fbd9a0;
            --btn-soft: #e2ecff; --btn-soft-h: #d0e0ff; --btn-soft-fg: #1d4ed8;
            --tico: #fff; --seg: #eef2f8;
        }
        html[data-theme="dark"] .mdash-sec {
            --card: #161b22; --card-b: rgba(255, 255, 255, .08); --shadow: 0 1px 2px rgba(0, 0, 0, .4);
            --txt: #e6edf3; --mut: #9aa7b8; --faint: #6b7788;
            --row: #1b222c; --row-h: #222c3b; --row-b: rgba(255, 255, 255, .06); --line: rgba(255, 255, 255, .07);
            --prim: #7db1ff; --prim-s: rgba(99, 140, 255, .16); --track: rgba(255, 255, 255, .09);
            --hero: #16233b; --hero-b: #243656; --hero-sub: #9db4dc;
            --warn-bg: rgba(245, 158, 11, .13); --warn-fg: #fcd34d; --warn-b: rgba(245, 158, 11, .3);
            --btn-soft: rgba(99, 140, 255, .16); --btn-soft-h: rgba(99, 140, 255, .26); --btn-soft-fg: #a5c4ff;
            --tico: rgba(255, 255, 255, .08); --seg: #0f141b;
        }

        .tone-blue   { --tb: #eff5ff; --tf: #1d4ed8; --ti: #3b82f6; --tl: #d3e2ff; }
        .tone-green  { --tb: #edfaf4; --tf: #15803d; --ti: #22c55e; --tl: #cdeedb; }
        .tone-amber  { --tb: #fff8e8; --tf: #b45309; --ti: #f59e0b; --tl: #f6e3b4; }
        .tone-slate  { --tb: #f4f6f9; --tf: #475569; --ti: #64748b; --tl: #e0e5ec; }
        .tone-violet { --tb: #f6f2ff; --tf: #6d28d9; --ti: #8b5cf6; --tl: #e3d8fc; }
        .tone-rose   { --tb: #fff1f3; --tf: #be123c; --ti: #f43f5e; --tl: #fbd0d8; }
        .tone-teal   { --tb: #edfaf8; --tf: #0f766e; --ti: #14b8a6; --tl: #c7ede6; }
        .tone-orange { --tb: #fff7ed; --tf: #c2410c; --ti: #f97316; --tl: #fed7aa; }

        html[data-theme="dark"] .tone-blue   { --tb: rgba(59, 130, 246, .15);  --tf: #93c5fd; --tl: rgba(59, 130, 246, .28); }
        html[data-theme="dark"] .tone-green  { --tb: rgba(34, 197, 94, .14);   --tf: #86efac; --tl: rgba(34, 197, 94, .26); }
        html[data-theme="dark"] .tone-amber  { --tb: rgba(245, 158, 11, .14);  --tf: #fcd34d; --tl: rgba(245, 158, 11, .26); }
        html[data-theme="dark"] .tone-slate  { --tb: rgba(148, 163, 184, .14); --tf: #cbd5e1; --tl: rgba(148, 163, 184, .24); }
        html[data-theme="dark"] .tone-violet { --tb: rgba(139, 92, 246, .16);  --tf: #c4b5fd; --tl: rgba(139, 92, 246, .28); }
        html[data-theme="dark"] .tone-rose   { --tb: rgba(244, 63, 94, .14);   --tf: #fda4af; --tl: rgba(244, 63, 94, .26); }
        html[data-theme="dark"] .tone-teal   { --tb: rgba(20, 184, 166, .14);  --tf: #5eead4; --tl: rgba(20, 184, 166, .26); }
        html[data-theme="dark"] .tone-orange { --tb: rgba(249, 115, 22, .14);  --tf: #fdba74; --tl: rgba(249, 115, 22, .26); }

        /* Banner Hero */
        .m-hero {
            position: relative; overflow: hidden; border-radius: 20px; padding: 26px 28px;
            background: var(--hero); border: 1px solid var(--hero-b);
            display: flex; flex-wrap: wrap; align-items: center; gap: 22px;
        }
        .m-hero::after {
            content: ''; position: absolute; right: -60px; top: -80px; width: 280px; height: 280px; border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, .18), transparent 70%); pointer-events: none;
        }
        .m-hero__avatar {
            width: 64px; height: 64px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center;
            background: linear-gradient(135deg, #3b82f6, #6366f1); color: #fff; font-size: 24px;
            box-shadow: 0 6px 18px rgba(59, 130, 246, .35); overflow: hidden; border: 2px solid #fff;
        }
        .m-hero__main { flex: 1; min-width: 240px; position: relative; z-index: 1; }
        .m-chip {
            display: inline-flex; align-items: center; gap: 6px; padding: 3px 11px; border-radius: 999px; margin-bottom: 8px;
            font-size: 11px; font-weight: 700; background: var(--card); color: var(--hero-sub); border: 1px solid var(--hero-b);
        }
        .m-chip i { font-size: 6px; color: #22c55e; }
        .m-hero h1 { margin: 0 0 3px; font-size: 22px; font-weight: 800; color: var(--txt); }
        .m-hero p { margin: 0; font-size: 13.5px; color: var(--hero-sub); }
        .m-hero p strong { color: var(--txt); font-weight: 700; }
        .m-meter { position: relative; z-index: 1; text-align: right; flex-shrink: 0; }
        .m-meter small { display: block; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--hero-sub); }
        .m-meter b { display: block; font-size: 40px; font-weight: 800; line-height: 1.1; color: var(--txt); }
        .m-meter b span { font-size: 20px; color: var(--hero-sub); }
        .m-meter em { display: block; font-style: normal; font-size: 12px; color: var(--hero-sub); margin-top: 2px; }
        .m-bar { height: 5px; width: 150px; margin: 8px 0 0 auto; border-radius: 99px; background: var(--track); overflow: hidden; }
        #hero-bar { height: 100%; width: 0; border-radius: 99px; background: linear-gradient(90deg, #34d399, #10b981); transition: width 1.2s ease; }

        /* Kartu Statistik (m-tile) */
        .m-tile {
            background: var(--tb); border: 1px solid var(--tl); border-radius: 16px; padding: 14px 16px; min-width: 0;
            display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; column-gap: 12px; row-gap: 0;
            text-align: left; transition: transform .2s ease, box-shadow .2s ease;
        }
        .m-tile:hover { transform: translateY(-2px); box-shadow: 0 10px 22px -14px var(--ti); }
        .m-tile__ico {
            grid-row: 1 / span 2; width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center;
            background: var(--tico, #fff); color: var(--ti); font-size: 15px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 6px 12px -8px var(--ti);
        }
        .m-tile .m-tile__num { grid-column: 2; grid-row: 1; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; color: var(--txt); line-height: 1.1; }
        .m-tile__lbl { grid-column: 2; grid-row: 2; font-size: 11.5px; font-weight: 700; color: var(--tf); line-height: 1.25; }
        .m-tile__note { grid-column: 2; grid-row: 3; font-size: 11px; font-weight: 600; color: var(--tf); opacity: .8; }
        .m-tile .m-tile__note { display: none; }
        .att-bar-track { grid-column: 1 / -1; grid-row: 3; width: 100%; height: 4px; margin-top: 10px; border-radius: 99px; background: rgba(100, 116, 139, .18); overflow: hidden; }
        .att-bar-fill { height: 100%; width: 0; border-radius: 99px; background: var(--ti); transition: width 1.2s cubic-bezier(.4, 0, .2, 1); }

        /* Kartu Kontainer (m-card) */
        .m-card { background: var(--card); border: 1px solid var(--card-b); border-radius: 16px; box-shadow: var(--shadow); overflow: hidden; display: flex; flex-direction: column; }
        .m-card__head { display: flex; align-items: center; gap: 12px; padding: 18px 22px 0; }
        .m-ico { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; background: var(--tb); color: var(--ti); font-size: 14px; flex-shrink: 0; }
        .m-card__title { margin: 0; font-size: 15px; font-weight: 800; color: var(--txt); }
        .m-sub { margin: 2px 0 0; font-size: 12.5px; color: var(--mut); }
        .m-badge { margin-left: auto; font-size: 11.5px; font-weight: 700; padding: 3px 12px; border-radius: 999px; background: var(--prim-s); color: var(--prim); white-space: nowrap; }
        .m-card__body { padding: 18px 22px 22px; flex: 1; }
        .m-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--faint); margin: 0 0 12px; }
        .m-cols { display: grid; grid-template-columns: minmax(0, 1fr); gap: 20px; align-items: stretch; }
        @media (min-width: 1024px) { .m-cols { grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); } }
        .m-stack { display: flex; flex-direction: column; gap: 20px; }
        .m-empty { text-align: center; padding: 34px 16px; color: var(--faint); }
        .m-empty i { display: block; font-size: 36px; margin-bottom: 10px; opacity: .7; }
        .m-empty p { margin: 0; font-size: 13px; }
        .m-empty p + p { font-size: 12px; margin-top: 4px; }

        /* Aksi Cepat Grid */
        .m-action-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        @media (min-width: 640px) { .m-action-grid { grid-template-columns: repeat(3, 1fr); } }
        .m-action-tile {
            display: flex; flex-direction: column; align-items: flex-start; padding: 14px 16px; border-radius: 12px;
            background: var(--row); border: 1px solid var(--row-b); text-decoration: none; transition: all .2s ease; position: relative;
        }
        .m-action-tile:hover { background: var(--row-h); transform: translateY(-2px); border-color: var(--prim-s); box-shadow: 0 8px 18px -8px var(--ti, #2563eb); }
        .m-action-tile__ico {
            width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 15px; margin-bottom: 10px;
            background: var(--tb, #eff5ff); color: var(--ti, #2563eb); border: 1px solid var(--tl, #d3e2ff);
        }
        .m-action-tile__title { font-size: 13px; font-weight: 700; color: var(--txt); margin: 0; display: flex; align-items: center; justify-content: space-between; width: 100%; }
        .m-action-tile__desc { font-size: 11.5px; color: var(--mut); margin: 3px 0 0; line-height: 1.3; }

        /* Donut Chart */
        .m-donut { position: relative; width: 140px; height: 140px; margin: 0 auto 16px; }
        .m-donut svg { transform: rotate(-90deg); width: 140px; height: 140px; }
        .m-donut .track { fill: none; stroke: var(--track); stroke-width: 11; }
        .m-donut__c { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .m-donut__c b { font-size: 24px; font-weight: 800; color: var(--txt); line-height: 1; }
        .m-donut__c small { font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--faint); margin-top: 4px; }
        .m-legend { display: flex; flex-direction: column; gap: 9px; }
        .m-legend > div { display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: var(--mut); }
        .m-legend > div > span:first-child { display: flex; align-items: center; gap: 8px; }
        .m-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
        .m-legend b { color: var(--txt); font-size: 13px; font-family: 'DM Mono', monospace; }

        /* Leaderboard & Podium */
        .podium-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 12px; border: 1px solid var(--row-b); background: var(--row); transition: transform .2s ease; }
        .podium-item:hover { transform: translateX(3px); background: var(--row-h); }
        .podium-item.is-me { border-color: var(--prim); background: var(--prim-s); }
        .rank-badge { width: 32px; height: 32px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; font-size: 13px; flex-shrink: 0; color: #fff; }
        .rank-1 { background: linear-gradient(135deg, #fbbf24, #d97706); box-shadow: 0 3px 10px rgba(251, 191, 36, .4); }
        .rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); box-shadow: 0 3px 10px rgba(100, 116, 139, .3); }
        .rank-3 { background: linear-gradient(135deg, #fb923c, #ea580c); box-shadow: 0 3px 10px rgba(251, 146, 60, .35); }
        .m-score { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; font-family: 'DM Mono', monospace; background: var(--card); color: var(--tf, var(--txt)); border: 1px solid var(--row-b); }
        .m-photo { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid var(--card); }

        /* Tombol & Utilitas */
        .m-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 20px; border-radius: 10px; font-size: 12.5px; font-weight: 700; text-decoration: none; background: var(--prim); color: #fff; transition: opacity .2s ease; border: 0; cursor: pointer; }
        .m-btn:hover { opacity: .88; }
        html[data-theme="dark"] .m-btn { background: #2f6fe0; }
        .m-btn-soft { background: var(--btn-soft); color: var(--btn-soft-fg); border: 1px solid var(--tl, var(--hero-b)); }
        .m-btn-soft:hover { background: var(--btn-soft-h); }

        .m-act { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px; border: 0; border-radius: 9px; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; transition: background .2s ease; }
        .m-act.is-soft { background: var(--btn-soft); color: var(--btn-soft-fg); }
        .m-act.is-soft:hover { background: var(--btn-soft-h); }
        .m-act.is-ok { background: #059669; color: #fff; }
        .m-act.is-ok:hover { background: #047857; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .a1 { animation: fadeUp .5s ease both; }
        .a2 { animation: fadeUp .5s .08s ease both; }
        .a3 { animation: fadeUp .5s .16s ease both; }
        .a4 { animation: fadeUp .5s .24s ease both; }
        .a5 { animation: fadeUp .5s .32s ease both; }
        @media (prefers-reduced-motion: reduce) { .a1, .a2, .a3, .a4, .a5 { animation: none; } }

        @media (max-width: 640px) {
            .m-hero { padding: 20px; }
            .m-meter { text-align: left; }
            .m-bar { margin-left: 0; }
            .m-card__head { padding: 16px 16px 0; }
            .m-card__body { padding: 14px 16px 18px; }
        }
    </style>
@endpush

@section('content')
@php
    $totalAbsen = $totalHadir + $totalIzin + $totalSakit + $totalTidakHadir ?: 1;
    $pctHadir  = round(($totalHadir / $totalAbsen) * 100);
    $pctIzin   = round(($totalIzin / $totalAbsen) * 100);
    $pctSakit  = round(($totalSakit / $totalAbsen) * 100);
    $pctAlfa   = round(($totalTidakHadir / $totalAbsen) * 100);

    $circ = 263.9;
    $hadirDash = $pctHadir / 100 * $circ;
    $izinDash  = $pctIzin / 100 * $circ;
    $sakitDash = $pctSakit / 100 * $circ;
    $alfaDash  = $pctAlfa / 100 * $circ;

    $izinStart  = $hadirDash;
    $sakitStart = $hadirDash + $izinDash;
    $alfaStart  = $hadirDash + $izinDash + $sakitDash;

    $microPct = $microSkillTotal > 0 ? round(($microSkillApproved / $microSkillTotal) * 100) : 0;
@endphp

{{-- ALERT MODALS (JIKA DIHARUSKAN MELENGKAPI MATERI / DOKUMENTASI SHARING SESSION) --}}
@if(!empty($mustFillSharingMaterial) && $mustFillSharingMaterial && !empty($sharingSessionAlert))
<div id="sharingAlertOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-7 max-w-md w-full mx-4 text-center shadow-2xl border border-slate-100">
        <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-amber-600 text-2xl">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Lengkapi Materi Sharing Session</h3>
        <p class="text-sm text-gray-600 mb-6">
            Anda dijadwalkan sebagai narasumber pada sesi <strong>"{{ $sharingSessionAlert->title ?? 'Mendatang' }}"</strong>. Silakan lengkapi judul dan deskripsi materi sebelum melanjutkan aktivitas.
        </p>
        <button id="btnLengkapiMateri" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg transition">
            Lengkapi Materi Sekarang
        </button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.style.overflow = 'hidden';
    const btn = document.getElementById('btnLengkapiMateri');
    if (btn) {
        btn.addEventListener('click', function() {
            window.location.href = "{{ route('intern.sharing-session.edit-materi', $sharingSessionAlert->id) }}";
        });
    }
});
</script>
@endif

@if(!empty($mustUploadSharingDocumentation) && $mustUploadSharingDocumentation && !empty($sharingDocumentationAlert))
<div id="sharingDocumentationAlertOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-7 max-w-md w-full mx-4 text-center shadow-2xl border border-slate-100">
        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-blue-600 text-2xl">
            <i class="fas fa-camera"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Upload Dokumentasi Sharing Session</h3>
        <p class="text-sm text-gray-600 mb-6">
            Anda bertugas sebagai moderator pada sharing session <strong>"{{ $sharingDocumentationAlert->title }}"</strong>. Mohon unggah foto dokumentasi kegiatan.
        </p>
        <button id="btnUploadDokumentasi" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg transition">
            Upload Dokumentasi Sekarang
        </button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.style.overflow = 'hidden';
    const btn = document.getElementById('btnUploadDokumentasi');
    if (btn) {
        btn.addEventListener('click', function() {
            window.location.href = "{{ route('intern.sharing-session.edit-materi', $sharingDocumentationAlert->id) }}";
        });
    }
});
</script>
@endif

<div class="mdash py-7">
    <div class="mdash-in max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mdash-sec" data-dm-native style="display:flex;flex-direction:column;gap:20px;">

            {{-- ── HERO PROFILE BANNER ── --}}
            <div class="m-hero a1">
                <div class="m-hero__avatar">
                    @if($intern->photo_path)
                        <img src="{{ url('storage/'.$intern->photo_path) }}" alt="{{ $intern->name }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-user-graduate"></i>
                    @endif
                </div>

                <div class="m-hero__main">
                    <span class="m-chip">
                        <i class="fas fa-circle {{ $cekaktif ? 'text-green-500' : 'text-slate-400' }}"></i>
                        {{ $cekaktif ? 'Anak Magang Aktif' : 'Status: Non-Aktif' }}
                    </span>
                    <h1>{{ $intern->name }}</h1>
                    <p>
                        <strong>{{ $intern->institution }}</strong> &mdash; {{ $intern->education_level }} &bull; {{ $intern->major }}
                    </p>
                    <p style="margin-top:4px;font-size:12.5px;">
                        Periode: <strong>{{ $intern->start_date ? $intern->start_date->format('d M Y') : '-' }}</strong> s/d <strong>{{ $intern->end_date ? $intern->end_date->format('d M Y') : '-' }}</strong>
                    </p>

                    @if($certificate && ($certificate->issue_date->isToday() || $certificate->issue_date->isPast()))
                        <div style="margin-top:10px;">
                            <a href="{{ route('intern.certificates.print', $certificate->id) }}" target="_blank" class="m-chip" style="background:#fef3c7;color:#b45309;border-color:#fde68a;">
                                <i class="fas fa-award" style="color:#d97706;font-size:10px;"></i>
                                Unduh Sertifikat Magang
                            </a>
                        </div>
                    @endif
                </div>

                <div class="m-meter">
                    <small>Tingkat Kehadiran</small>
                    <b class="mono">{{ $pctHadir }}<span>%</span></b>
                    <em>{{ $totalHadir }} dari {{ $totalAbsen }} hari tercatat</em>
                    <div class="m-bar"><div id="hero-bar"></div></div>
                </div>
            </div>

            {{-- ── STAT TILES (6 METRICS) ── --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 a2">
                <div class="m-tile tone-green">
                    <div class="m-tile__ico"><i class="fas fa-calendar-check"></i></div>
                    <div class="m-tile__num">{{ $totalHadir }}</div>
                    <div class="m-tile__lbl">Hadir</div>
                    <div class="m-tile__note">{{ $pctHadir }}% kehadiran</div>
                    <div class="att-bar-track"><div class="att-bar-fill" data-width="{{ $pctHadir }}"></div></div>
                </div>

                <div class="m-tile tone-amber">
                    <div class="m-tile__ico"><i class="fas fa-clipboard-question"></i></div>
                    <div class="m-tile__num">{{ $totalIzin }}</div>
                    <div class="m-tile__lbl">Izin</div>
                    <div class="m-tile__note">{{ $pctIzin }}% dari total</div>
                    <div class="att-bar-track"><div class="att-bar-fill" data-width="{{ $pctIzin }}"></div></div>
                </div>

                <div class="m-tile tone-rose">
                    <div class="m-tile__ico"><i class="fas fa-notes-medical"></i></div>
                    <div class="m-tile__num">{{ $totalSakit }}</div>
                    <div class="m-tile__lbl">Sakit</div>
                    <div class="m-tile__note">{{ $pctSakit }}% dari total</div>
                    <div class="att-bar-track"><div class="att-bar-fill" data-width="{{ $pctSakit }}"></div></div>
                </div>

                <div class="m-tile tone-slate">
                    <div class="m-tile__ico"><i class="fas fa-user-xmark"></i></div>
                    <div class="m-tile__num">{{ $totalTidakHadir }}</div>
                    <div class="m-tile__lbl">Tidak Hadir</div>
                    <div class="m-tile__note">{{ $pctAlfa }}% dari total</div>
                    <div class="att-bar-track"><div class="att-bar-fill" data-width="{{ $pctAlfa }}"></div></div>
                </div>

                <div class="m-tile tone-teal">
                    <div class="m-tile__ico"><i class="fas fa-file-signature"></i></div>
                    <div class="m-tile__num" style="font-size:1.25rem;">{{ $hasFinalReport ? 'Sudah' : 'Belum' }}</div>
                    <div class="m-tile__lbl">Laporan Akhir</div>
                    <div class="m-tile__note">{{ $hasFinalReport ? 'Terkirim' : 'Belum diunggah' }}</div>
                    <div class="att-bar-track"><div class="att-bar-fill" data-width="{{ $hasFinalReport ? 100 : 0 }}"></div></div>
                </div>

                <div class="m-tile tone-violet">
                    <div class="m-tile__ico"><i class="fas fa-award"></i></div>
                    <div class="m-tile__num">{{ $microSkillApproved }}<span style="font-size:1rem;font-weight:600;opacity:.65;">/{{ $microSkillTotal }}</span></div>
                    <div class="m-tile__lbl">Mikro Skill</div>
                    <div class="m-tile__note">{{ $microPct }}% terselesaikan</div>
                    <div class="att-bar-track"><div class="att-bar-fill" data-width="{{ $microPct }}"></div></div>
                </div>
            </div>
        </div>

            {{-- ── WORKSPACE INTEGRATION (di luar area native agar ikut mode gelap otomatis) ── --}}
            <x-workspace.dashboard-widgets role="intern" class="a2" />

        <div class="mdash-sec" data-dm-native style="display:flex;flex-direction:column;gap:20px;">

            {{-- ── SHARING SESSION HARI INI (JIKA ADA JADWAL) ── --}}
            @if(isset($todaySharingSessions) && $todaySharingSessions->count() > 0)
                <div class="m-card a2">
                    <div class="m-card__head">
                        <div class="m-ico tone-teal"><i class="fas fa-comments"></i></div>
                        <div>
                            <h2 class="m-card__title">Sharing Session Hari Ini</h2>
                            <p class="m-sub">Agenda diskusi dan transfer pengetahuan terjadwal</p>
                        </div>
                        <span class="m-badge">{{ $todaySharingSessions->count() }} Sesi</span>
                    </div>
                    <div class="m-card__body">
                        <div class="space-y-4">
                            @foreach($todaySharingSessions as $session)
                                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700/60 flex flex-col md:flex-row md:items-center justify-between gap-4" style="background:var(--row);">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="m-score" style="background:#e0f2fe;color:#0369a1;font-size:11px;">
                                                <i class="fas fa-clock text-[10px]"></i>
                                                {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-' }} WITA
                                            </span>
                                            <span class="text-xs text-slate-500">
                                                <i class="fas fa-map-marker-alt text-orange-500 mr-1"></i>
                                                {{ $session->location ?? 'Online / Ruang Rapat' }}
                                            </span>
                                        </div>
                                        <h3 class="font-bold text-base text-slate-900 dark:text-slate-100">
                                            {{ $session->title ?? 'Materi Belum Diisi' }}
                                        </h3>
                                        <p class="text-xs text-slate-500">
                                            Narasumber: <strong>{{ $session->speakerUser?->name ?? '-' }}</strong> &bull; Moderator: <strong>{{ $session->moderatorUser?->name ?? '-' }}</strong>
                                        </p>

                                        @if($session->speaker_user_id === auth()->id() && $session->material_status !== 'lengkap')
                                            <p class="text-xs font-semibold text-amber-600 flex items-center gap-1.5 mt-2">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                Anda sebagai narasumber belum melengkapi judul & materi sesi ini.
                                            </p>
                                        @endif
                                        @if($session->moderator_user_id === auth()->id() && empty($session->documentation_photo))
                                            <p class="text-xs font-semibold text-amber-600 flex items-center gap-1.5 mt-2">
                                                <i class="fas fa-camera"></i>
                                                Anda sebagai moderator belum mengunggah foto dokumentasi kegiatan.
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($session->speaker_user_id === auth()->id())
                                            <a href="{{ route('intern.sharing-session.edit-materi', $session) }}" class="m-act is-soft" style="padding:8px 14px;border-radius:10px;text-decoration:none;">
                                                <i class="fas fa-pen-to-square"></i> Lengkapi Materi
                                            </a>
                                        @endif

                                        @if($session->moderator_user_id === auth()->id() && empty($session->documentation_photo))
                                            <a href="{{ route('intern.sharing-session.edit-materi', $session) }}" class="m-act is-soft" style="padding:8px 14px;border-radius:10px;text-decoration:none;background:#fef3c7;color:#b45309;">
                                                <i class="fas fa-camera"></i> Upload Dokumentasi
                                            </a>
                                        @endif

                                        @php
                                            $formEvaluasiUrl = $session->evaluation_form_link;
                                            $materiLengkap = $session->material_status === 'lengkap';
                                            $bolehIsiEvaluasi = $materiLengkap && $session->evaluation_is_open;
                                        @endphp

                                        @if($bolehIsiEvaluasi)
                                            <a href="{{ $formEvaluasiUrl }}" target="_blank" class="m-act is-ok" style="padding:8px 14px;border-radius:10px;text-decoration:none;">
                                                <i class="fas fa-clipboard-check"></i> Isi Evaluasi
                                            </a>
                                        @elseif(!$materiLengkap)
                                            <span class="m-score" style="background:#fef3c7;color:#b45309;">
                                                <i class="fas fa-file-circle-xmark"></i> Materi Belum Lengkap
                                            </span>
                                        @elseif($session->evaluation_already_closed)
                                            <span class="m-score" style="background:#fee2e2;color:#b91c1c;">
                                                <i class="fas fa-lock"></i> Form Ditutup
                                            </span>
                                        @else
                                            <span class="m-score">
                                                <i class="fas fa-clock"></i> Form Belum Dibuka
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── MAIN TWO COLUMNS (3fr : 2fr) ── --}}
            <div class="m-cols">

                {{-- KOLOM KIRI (3fr) --}}
                <div class="m-stack">

                    {{-- KARTU STATUS ABSENSI HARI INI & KAMERA CHECKOUT --}}
                    <div class="m-card a3">
                        <div class="m-card__head">
                            <div class="m-ico tone-green"><i class="fas fa-calendar-check"></i></div>
                            <div>
                                <h2 class="m-card__title">Status Absensi Hari Ini</h2>
                                <p class="m-sub">{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('l, d F Y') }}</p>
                            </div>
                            @if($todayAttendance)
                                <span class="m-badge" style="
                                    @if($todayAttendance->status=='hadir') background:#dcfce7;color:#15803d;
                                    @elseif($todayAttendance->status=='izin') background:#fef9c3;color:#a16207;
                                    @elseif($todayAttendance->status=='sakit') background:#ffedd5;color:#c2410c;
                                    @else background:#fee2e2;color:#b91c1c; @endif">
                                    {{ ucfirst($todayAttendance->status) }}
                                </span>
                            @else
                                <span class="m-badge" style="background:#f1f5f9;color:#64748b;">Belum Absen</span>
                            @endif
                        </div>

                        <div class="m-card__body">
                            @if($todayAttendance)
                                <div class="space-y-4">
                                    {{-- Baris Info Waktu --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/60 flex items-center justify-between" style="background:var(--row);">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg grid place-items-center bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                                                    <i class="fas fa-arrow-right-to-bracket text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-500 font-semibold">Check In</div>
                                                    <div class="text-sm font-bold mono text-slate-900 dark:text-slate-100">
                                                        {{ $todayAttendance->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') . ' WITA' : '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            @if($todayAttendance->check_in)
                                                <i class="fas fa-check-circle text-emerald-500"></i>
                                            @endif
                                        </div>

                                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/60 flex items-center justify-between" style="background:var(--row);">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg grid place-items-center bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    <i class="fas fa-arrow-right-from-bracket text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-500 font-semibold">Check Out</div>
                                                    <div class="text-sm font-bold mono text-slate-900 dark:text-slate-100">
                                                        {{ $todayAttendance->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') . ' WITA' : 'Belum Check Out' }}
                                                    </div>
                                                </div>
                                            </div>
                                            @if($todayAttendance->check_out)
                                                <i class="fas fa-check-circle text-emerald-500"></i>
                                            @else
                                                <i class="fas fa-hourglass-half text-amber-500"></i>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Form Checkout Kamera jika belum checkout --}}
                                    @if(!$todayAttendance->check_out && $todayAttendance->status === 'hadir')
                                        <form action="{{ route('intern.attendance.checkout') }}"
                                              method="POST"
                                              enctype="multipart/form-data"
                                              id="checkoutForm"
                                              class="p-4 sm:p-5 rounded-xl border border-blue-200 dark:border-blue-900/50 bg-blue-50/60 dark:bg-blue-950/20 space-y-4">
                                            @csrf

                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-blue-950 dark:text-blue-200 mb-1">
                                                    <i class="fas fa-camera mr-1 text-blue-600"></i>
                                                    Foto Bukti Absensi Keluar (Check Out)
                                                </label>
                                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                                    Ambil foto selfie di tempat magang sebagai bukti absensi kepulangan.
                                                </p>
                                            </div>

                                            <div class="flex flex-col items-center justify-center">
                                                <video id="checkoutVideo"
                                                       width="100%"
                                                       height="auto"
                                                       class="border-2 border-blue-300 dark:border-blue-700 rounded-xl hidden shadow-md max-w-sm transform -scale-x-100 mb-3"
                                                       autoplay
                                                       playsinline></video>

                                                <canvas id="checkoutCanvas" class="hidden"></canvas>

                                                <img id="checkoutCapturedImage"
                                                     class="w-full max-w-sm mx-auto border-2 border-emerald-400 rounded-xl hidden mb-3 shadow-md"
                                                     alt="Foto Terambil">

                                                <div class="flex flex-wrap gap-2.5 justify-center w-full">
                                                    <button type="button"
                                                            id="checkoutStartCamera"
                                                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow transition">
                                                        <i class="fas fa-camera"></i>
                                                        Buka Kamera
                                                    </button>

                                                    <button type="button"
                                                            id="checkoutCapturePhoto"
                                                            class="hidden inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition">
                                                        <i class="fas fa-camera-retro"></i>
                                                        Ambil Foto
                                                    </button>

                                                    <button type="button"
                                                            id="checkoutStopCamera"
                                                            class="hidden inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow transition">
                                                        <i class="fas fa-stop"></i>
                                                        Tutup Kamera
                                                    </button>
                                                </div>
                                            </div>

                                            <input type="file"
                                                   name="photo"
                                                   id="checkoutPhoto"
                                                   accept="image/*"
                                                   class="hidden">

                                            <input type="hidden"
                                                   name="photo_data"
                                                   id="checkoutPhotoData"
                                                   value="">

                                            <div class="pt-2 flex justify-end">
                                                <button type="submit"
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg transition">
                                                    <i class="fas fa-arrow-right-from-bracket"></i>
                                                    Kirim Absensi Keluar
                                                </button>
                                            </div>
                                        </form>
                                    @endif

                                    {{-- Pratinjau Foto Check In & Check Out --}}
                                    @if($todayAttendance->photo_path || $todayAttendance->photo_checkout)
                                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Foto Presensi Hari Ini</div>
                                            <div class="flex gap-4 flex-wrap">
                                                @if($todayAttendance->photo_path)
                                                    <div>
                                                        <div class="text-[11px] font-semibold text-slate-500 mb-1.5 flex items-center gap-1">
                                                            <i class="fas fa-arrow-right-to-bracket text-emerald-500"></i> Masuk
                                                        </div>
                                                        <img src="{{ $todayAttendance->check_in_photo_url }}"
                                                             alt="Check In"
                                                             class="w-20 h-20 object-cover rounded-xl border border-emerald-200 cursor-pointer hover:opacity-90 transition shadow-sm"
                                                             onclick="window.open('{{ $todayAttendance->check_in_photo_url }}','_blank')">
                                                    </div>
                                                @endif

                                                @if($todayAttendance->photo_checkout)
                                                    <div>
                                                        <div class="text-[11px] font-semibold text-slate-500 mb-1.5 flex items-center gap-1">
                                                            <i class="fas fa-arrow-right-from-bracket text-indigo-500"></i> Keluar
                                                        </div>
                                                        <img src="{{ $todayAttendance->check_out_photo_url }}"
                                                             alt="Check Out"
                                                             class="w-20 h-20 object-cover rounded-xl border border-indigo-200 cursor-pointer hover:opacity-90 transition shadow-sm"
                                                             onclick="window.open('{{ $todayAttendance->check_out_photo_url }}','_blank')">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="m-empty py-8">
                                    <i class="fas fa-calendar-plus text-blue-500"></i>
                                    <p class="font-bold text-slate-800 dark:text-slate-200">Anda belum melakukan absensi hari ini</p>
                                    <p>Pastikan Anda melakukan check-in tepat waktu sebelum memulai aktivitas magang.</p>
                                    @if($cekaktif)
                                        <div class="mt-4 flex justify-center">
                                            <a href="{{ route('intern.attendance.create') }}" class="m-btn m-btn-primary" style="width:auto;padding:10px 24px;">
                                                <i class="fas fa-calendar-check mr-2"></i>
                                                Absensi Sekarang
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- KARTU AKSI CEPAT / SHORTCUT MENU --}}
                    <div class="m-card a4">
                        <div class="m-card__head">
                            <div class="m-ico tone-blue"><i class="fas fa-bolt"></i></div>
                            <div>
                                <h2 class="m-card__title">Aksi Cepat</h2>
                                <p class="m-sub">Pintasan menuju modul dan fitur utama anak magang</p>
                            </div>
                        </div>
                        <div class="m-card__body">
                            <div class="m-action-grid">
                                <a href="{{ route('intern.attendance.index') }}" class="m-action-tile tone-green">
                                    <div class="m-action-tile__ico"><i class="fas fa-calendar-check"></i></div>
                                    <div class="m-action-tile__title">
                                        Absensi
                                        <i class="fas fa-arrow-right text-[10px] opacity-40"></i>
                                    </div>
                                    <div class="m-action-tile__desc">Riwayat kehadiran & jam kerja</div>
                                </a>

                                <a href="{{ route('intern.logbook.index') }}" class="m-action-tile tone-amber">
                                    <div class="m-action-tile__ico"><i class="fas fa-book-open"></i></div>
                                    <div class="m-action-tile__title">
                                        Logbook
                                        <i class="fas fa-arrow-right text-[10px] opacity-40"></i>
                                    </div>
                                    <div class="m-action-tile__desc">Catat aktivitas harian magang</div>
                                </a>

                                <a href="{{ route('workspace.index') }}" class="m-action-tile tone-violet">
                                    <div class="m-action-tile__ico"><i class="fas fa-layer-group"></i></div>
                                    <div class="m-action-tile__title">
                                        Workspace
                                        <i class="fas fa-arrow-right text-[10px] opacity-40"></i>
                                    </div>
                                    <div class="m-action-tile__desc">Board tugas & koordinasi tim</div>
                                </a>

                                <a href="{{ route('intern.sharing-session.index') }}" class="m-action-tile tone-teal">
                                    <div class="m-action-tile__ico"><i class="fas fa-comments"></i></div>
                                    <div class="m-action-tile__title">
                                        Sharing
                                        <i class="fas fa-arrow-right text-[10px] opacity-40"></i>
                                    </div>
                                    <div class="m-action-tile__desc">Jadwal & materi presentasi</div>
                                </a>

                                <a href="{{ route('intern.microskill.index') }}" class="m-action-tile tone-orange">
                                    <div class="m-action-tile__ico"><i class="fas fa-award"></i></div>
                                    <div class="m-action-tile__title">
                                        Mikro Skill
                                        @if($microSkillTotal > 0)
                                            <span class="m-score" style="font-size:10px;padding:1px 6px;">{{ $microSkillApproved }}/{{ $microSkillTotal }}</span>
                                        @endif
                                    </div>
                                    <div class="m-action-tile__desc">Kumpulan modul sertifikasi</div>
                                </a>

                                <a href="{{ route('intern.report.index') }}" class="m-action-tile tone-rose">
                                    <div class="m-action-tile__ico"><i class="fas fa-file-lines"></i></div>
                                    <div class="m-action-tile__title">
                                        Laporan
                                        @if(!$hasFinalReport)
                                            <span class="m-score" style="font-size:10px;padding:1px 6px;background:#fef3c7;color:#b45309;">Wajib</span>
                                        @endif
                                    </div>
                                    <div class="m-action-tile__desc">Unggah laporan akhir magang</div>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- NOTIFIKASI LOGBOOK DISETUJUI --}}
                    @if(!empty($latestApprovedLogbook))
                        <div class="m-card a4">
                            <div class="m-card__head">
                                <div class="m-ico tone-green"><i class="fas fa-circle-check"></i></div>
                                <div>
                                    <h2 class="m-card__title">Logbook Terakhir Disetujui</h2>
                                    <p class="m-sub">
                                        Disetujui pada {{ $latestApprovedLogbook->approved_at ? \Carbon\Carbon::parse($latestApprovedLogbook->approved_at)->locale('id')->isoFormat('D MMMM Y, HH:mm') : '-' }} WITA
                                    </p>
                                </div>
                                <a href="{{ route('intern.logbook.show', $latestApprovedLogbook) }}" class="m-badge" style="text-decoration:none;">
                                    Detail <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                </a>
                            </div>
                            <div class="m-card__body">
                                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/60" style="background:var(--row);">
                                    <p class="text-xs text-slate-700 dark:text-slate-300 font-medium line-clamp-3 leading-relaxed">
                                        {{ \Illuminate\Support\Str::limit($latestApprovedLogbook->activity, 160) }}
                                    </p>

                                    @if($latestApprovedLogbook->approval_note)
                                        <div class="mt-2.5 pt-2.5 border-t border-slate-200 dark:border-slate-700/50 text-xs text-slate-600 dark:text-slate-400">
                                            <strong class="text-slate-800 dark:text-slate-200">Catatan Pembimbing:</strong>
                                            <span class="italic font-normal">"{{ $latestApprovedLogbook->approval_note }}"</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- KOLOM KANAN (2fr) --}}
                <div class="m-stack">

                    {{-- KARTU DONUT DISTRIBUSI KEHADIRAN --}}
                    <div class="m-card a3">
                        <div class="m-card__head">
                            <div class="m-ico tone-green"><i class="fas fa-chart-pie"></i></div>
                            <div>
                                <h2 class="m-card__title">Distribusi Kehadiran</h2>
                                <p class="m-sub">Rasio presensi selama masa magang</p>
                            </div>
                        </div>
                        <div class="m-card__body">
                            {{-- Donut SVG --}}
                            <div class="m-donut">
                                <svg viewBox="0 0 100 100">
                                    <circle class="track" cx="50" cy="50" r="42" />

                                    <circle id="donut-hadir"
                                            cx="50" cy="50" r="42"
                                            fill="none" stroke="#22c55e" stroke-width="11"
                                            stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ }}"
                                            stroke-linecap="butt"
                                            style="transition:stroke-dashoffset 1.2s ease;"
                                            data-dash="{{ $hadirDash }}" data-start="0" />

                                    <circle id="donut-izin"
                                            cx="50" cy="50" r="42"
                                            fill="none" stroke="#f59e0b" stroke-width="11"
                                            stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ }}"
                                            stroke-linecap="butt"
                                            style="transition:stroke-dashoffset 1.2s ease 0.2s;"
                                            data-dash="{{ $izinDash }}" data-start="{{ $izinStart }}" />

                                    <circle id="donut-sakit"
                                            cx="50" cy="50" r="42"
                                            fill="none" stroke="#f43f5e" stroke-width="11"
                                            stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ }}"
                                            stroke-linecap="butt"
                                            style="transition:stroke-dashoffset 1.2s ease 0.4s;"
                                            data-dash="{{ $sakitDash }}" data-start="{{ $sakitStart }}" />

                                    <circle id="donut-alfa"
                                            cx="50" cy="50" r="42"
                                            fill="none" stroke="#94a3b8" stroke-width="11"
                                            stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ }}"
                                            stroke-linecap="butt"
                                            style="transition:stroke-dashoffset 1.2s ease 0.6s;"
                                            data-dash="{{ $alfaDash }}" data-start="{{ $alfaStart }}" />
                                </svg>

                                <div class="m-donut__c">
                                    <b class="mono">{{ $pctHadir }}%</b>
                                    <small>Kehadiran</small>
                                </div>
                            </div>

                            {{-- Legenda --}}
                            <div class="m-legend">
                                <div>
                                    <span><i style="background:#22c55e;"></i> Hadir</span>
                                    <b>{{ $totalHadir }} hari ({{ $pctHadir }}%)</b>
                                </div>
                                <div>
                                    <span><i style="background:#f59e0b;"></i> Izin</span>
                                    <b>{{ $totalIzin }} hari ({{ $pctIzin }}%)</b>
                                </div>
                                <div>
                                    <span><i style="background:#f43f5e;"></i> Sakit</span>
                                    <b>{{ $totalSakit }} hari ({{ $pctSakit }}%)</b>
                                </div>
                                <div>
                                    <span><i style="background:#94a3b8;"></i> Tidak Hadir</span>
                                    <b>{{ $totalTidakHadir }} hari ({{ $pctAlfa }}%)</b>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU LEADERBOARD MIKRO SKILL --}}
                    <div class="m-card a4">
                        <div class="m-card__head">
                            <div class="m-ico tone-amber"><i class="fas fa-trophy"></i></div>
                            <div>
                                <h2 class="m-card__title">Leaderboard Mikro Skill</h2>
                                <p class="m-sub">Top anak magang tersertifikasi</p>
                            </div>
                            <span class="m-badge">Top 3</span>
                        </div>
                        <div class="m-card__body">
                            @if(isset($topMicroSkills) && $topMicroSkills->count() > 0)
                                <div class="space-y-2.5">
                                    @foreach($topMicroSkills->take(3) as $index => $row)
                                        @php
                                            $isMe = ($row['intern_id'] === $intern->id);
                                            $rankClass = match($index) {
                                                0 => 'rank-1',
                                                1 => 'rank-2',
                                                2 => 'rank-3',
                                                default => 'rank-3',
                                            };
                                        @endphp

                                        <div class="podium-item {{ $isMe ? 'is-me' : '' }}">
                                            <div class="relative flex-shrink-0">
                                                <div class="rank-badge {{ $rankClass }}">
                                                    {{ $index + 1 }}
                                                </div>
                                            </div>

                                            @if(!empty($row['photo_path']))
                                                <img src="{{ url('storage/'.$row['photo_path']) }}" class="m-photo" alt="{{ $row['name'] }}">
                                            @else
                                                <div class="w-8 h-8 rounded-full grid place-items-center text-xs font-bold text-white bg-slate-400 flex-shrink-0">
                                                    {{ strtoupper(substr($row['name'], 0, 1)) }}
                                                </div>
                                            @endif

                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate flex items-center gap-1.5">
                                                    {{ $row['name'] }}
                                                    @if($isMe)
                                                        <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-violet-100 text-violet-700 font-extrabold">Kamu</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-400 truncate">
                                                    {{ $row['institution'] }}
                                                </div>
                                            </div>

                                            <span class="m-score">
                                                <i class="fas fa-medal text-amber-500 text-[10px]"></i>
                                                {{ $row['total'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('intern.microskill.leaderboard') }}" class="m-btn m-btn-soft">
                                        Lihat Klasemen Lengkap
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            @else
                                <div class="m-empty py-6">
                                    <i class="fas fa-chart-line text-slate-300"></i>
                                    <p>Belum ada data mikro skill yang tercatat</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- KARTU INFORMASI BIMBINGAN & TIM --}}
                    <div class="m-card a5">
                        <div class="m-card__head">
                            <div class="m-ico tone-violet"><i class="fas fa-id-badge"></i></div>
                            <div>
                                <h2 class="m-card__title">Informasi Bimbingan</h2>
                                <p class="m-sub">Mentor lapangan & penempatan divisi</p>
                            </div>
                        </div>
                        <div class="m-card__body space-y-3">
                            <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800" style="background:var(--row);">
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Mentor Pembimbing</div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-chalkboard-teacher text-blue-500"></i>
                                    {{ $intern->mentor?->name ?? 'Belum Ditentukan' }}
                                </div>
                            </div>

                            <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800" style="background:var(--row);">
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tim / Divisi Kerja</div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-users text-violet-500"></i>
                                    {{ $intern->team?->name ?? 'Tim Magang Komdigi' }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    // Inisialisasi Kamera Check Out
    const checkoutVideo = document.getElementById('checkoutVideo');
    const checkoutCanvas = document.getElementById('checkoutCanvas');
    const checkoutCapturedImage = document.getElementById('checkoutCapturedImage');
    const checkoutStartCameraBtn = document.getElementById('checkoutStartCamera');
    const checkoutCapturePhotoBtn = document.getElementById('checkoutCapturePhoto');
    const checkoutStopCameraBtn = document.getElementById('checkoutStopCamera');
    const checkoutPhotoInput = document.getElementById('checkoutPhoto');
    const checkoutPhotoData = document.getElementById('checkoutPhotoData');
    let checkoutStream = null;

    if (checkoutStartCameraBtn) {
        checkoutStartCameraBtn.addEventListener('click', async () => {
            try {
                checkoutStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });

                checkoutVideo.srcObject = checkoutStream;
                checkoutVideo.classList.remove('hidden');
                checkoutCapturedImage.classList.add('hidden');
                checkoutStartCameraBtn.classList.add('hidden');
                checkoutCapturePhotoBtn.classList.remove('hidden');
                checkoutStopCameraBtn.classList.remove('hidden');
            } catch (err) {
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
            }
        });

        checkoutCapturePhotoBtn.addEventListener('click', () => {
            checkoutCanvas.width = checkoutVideo.videoWidth;
            checkoutCanvas.height = checkoutVideo.videoHeight;

            const ctx = checkoutCanvas.getContext('2d');
            ctx.translate(checkoutCanvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(checkoutVideo, 0, 0);
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            const imageData = checkoutCanvas.toDataURL('image/png');
            checkoutCapturedImage.src = imageData;
            checkoutCapturedImage.classList.remove('hidden');
            checkoutPhotoData.value = imageData;

            fetch(imageData).then(r => r.blob()).then(blob => {
                const file = new File([blob], 'checkout-photo.png', { type: 'image/png' });
                const dt = new DataTransfer();
                dt.items.add(file);
                checkoutPhotoInput.files = dt.files;
            });

            if (checkoutStream) {
                checkoutStream.getTracks().forEach(t => t.stop());
                checkoutStream = null;
            }

            checkoutVideo.classList.add('hidden');
            checkoutStartCameraBtn.innerHTML = '<i class="fas fa-camera mr-2"></i>Ambil Foto Ulang';
            checkoutStartCameraBtn.classList.remove('hidden');
            checkoutCapturePhotoBtn.classList.add('hidden');
            checkoutStopCameraBtn.classList.add('hidden');
        });

        checkoutStopCameraBtn.addEventListener('click', () => {
            if (checkoutStream) {
                checkoutStream.getTracks().forEach(t => t.stop());
                checkoutStream = null;
            }

            checkoutVideo.classList.add('hidden');
            checkoutStartCameraBtn.classList.remove('hidden');
            checkoutCapturePhotoBtn.classList.add('hidden');
            checkoutStopCameraBtn.classList.add('hidden');
        });

        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                if (!checkoutPhotoData.value && !checkoutPhotoInput.files.length) {
                    e.preventDefault();
                    alert('Silakan ambil foto terlebih dahulu sebelum mengirim absensi keluar.');
                    return false;
                }

                if (checkoutStream) {
                    checkoutStream.getTracks().forEach(t => t.stop());
                }
            });
        }
    }

    // Inisialisasi Donut Chart & Animasi Progress Bar
    document.addEventListener('DOMContentLoaded', () => {
        const circ = {{ $circ }};

        setTimeout(() => {
            ['donut-hadir', 'donut-izin', 'donut-sakit', 'donut-alfa'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;

                const dash = parseFloat(el.dataset.dash || 0);
                const start = parseFloat(el.dataset.start || 0);

                el.style.strokeDasharray = `${dash} ${circ - dash}`;
                el.style.strokeDashoffset = -start;
            });
        }, 300);

        setTimeout(() => {
            document.querySelectorAll('.att-bar-fill').forEach(el => {
                el.style.width = el.dataset.width + '%';
            });
            const heroBar = document.getElementById('hero-bar');
            if (heroBar) {
                heroBar.style.width = '{{ $pctHadir }}%';
            }
        }, 300);
    });
</script>
@endpush
@endsection
