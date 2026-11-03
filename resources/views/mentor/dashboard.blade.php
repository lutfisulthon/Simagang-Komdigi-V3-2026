@extends('layouts.app')

@section('title', 'Dashboard Mentor - Sistem Magang')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

        .mdash, .mdash *:not(i) { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mdash .mono { font-family: 'DM Mono', monospace; }

        .mdash { min-height: 100vh; background: #f4f7fb; }
        .mdash-in { display: flex; flex-direction: column; gap: 20px; }

        /* ── Palet (data-dm-native: warna gelap diatur di sini, bukan oleh filter) ── */
        .mdash-sec {
            --card: #ffffff; --card-b: #e6ebf3; --shadow: 0 1px 2px rgba(15, 30, 80, .04), 0 6px 20px rgba(15, 30, 80, .04);
            --txt: #1e293b; --mut: #64748b; --faint: #94a3b8;
            --row: #f6f8fc; --row-h: #edf2ff; --row-b: #e8edf6; --line: #edf0f6;
            --prim: #2563eb; --prim-s: #e2ecff; --track: #e8edf7;
            --hero: #eaf1ff; --hero-b: #d6e4ff; --hero-sub: #3b5b94;
            --warn-bg: #fff4e0; --warn-fg: #9a4a07; --warn-b: #fbd9a0;
            --btn-soft: #e2ecff; --btn-soft-h: #d0e0ff; --btn-soft-fg: #1d4ed8;
            --tico: #fff; --seg: #eef2f8; --alum: #fff2f4; --alum-b: #fcd5dc;
        }
        html[data-theme="dark"] .mdash-sec {
            --card: #161b22; --card-b: rgba(255, 255, 255, .08); --shadow: 0 1px 2px rgba(0, 0, 0, .4);
            --txt: #e6edf3; --mut: #9aa7b8; --faint: #6b7788;
            --row: #1b222c; --row-h: #222c3b; --row-b: rgba(255, 255, 255, .06); --line: rgba(255, 255, 255, .07);
            --prim: #7db1ff; --prim-s: rgba(99, 140, 255, .16); --track: rgba(255, 255, 255, .09);
            --hero: #16233b; --hero-b: #243656; --hero-sub: #9db4dc;
            --warn-bg: rgba(245, 158, 11, .13); --warn-fg: #fcd34d; --warn-b: rgba(245, 158, 11, .3);
            --btn-soft: rgba(99, 140, 255, .16); --btn-soft-h: rgba(99, 140, 255, .26); --btn-soft-fg: #a5c4ff;
            --tico: rgba(255, 255, 255, .08); --seg: #0f141b; --alum: rgba(244, 63, 94, .09); --alum-b: rgba(244, 63, 94, .22);
        }

        .tone-blue   { --tb: #eff5ff; --tf: #1d4ed8; --ti: #3b82f6; --tl: #d3e2ff; }
        .tone-green  { --tb: #edfaf4; --tf: #15803d; --ti: #22c55e; --tl: #cdeedb; }
        .tone-amber  { --tb: #fff8e8; --tf: #b45309; --ti: #f59e0b; --tl: #f6e3b4; }
        .tone-slate  { --tb: #f4f6f9; --tf: #475569; --ti: #64748b; --tl: #e0e5ec; }
        .tone-violet { --tb: #f6f2ff; --tf: #6d28d9; --ti: #8b5cf6; --tl: #e3d8fc; }
        .tone-rose   { --tb: #fff1f3; --tf: #be123c; --ti: #f43f5e; --tl: #fbd0d8; }
        html[data-theme="dark"] .tone-blue   { --tb: rgba(59, 130, 246, .15);  --tf: #93c5fd; --tl: rgba(59, 130, 246, .28); }
        html[data-theme="dark"] .tone-green  { --tb: rgba(34, 197, 94, .14);   --tf: #86efac; --tl: rgba(34, 197, 94, .26); }
        html[data-theme="dark"] .tone-amber  { --tb: rgba(245, 158, 11, .14);  --tf: #fcd34d; --tl: rgba(245, 158, 11, .26); }
        html[data-theme="dark"] .tone-slate  { --tb: rgba(148, 163, 184, .14); --tf: #cbd5e1; --tl: rgba(148, 163, 184, .24); }
        html[data-theme="dark"] .tone-violet { --tb: rgba(139, 92, 246, .16);  --tf: #c4b5fd; --tl: rgba(139, 92, 246, .28); }
        html[data-theme="dark"] .tone-rose   { --tb: rgba(244, 63, 94, .14);   --tf: #fda4af; --tl: rgba(244, 63, 94, .26); }

        /* ── Banner ── */
        .m-hero {
            position: relative; overflow: hidden; border-radius: 16px; padding: 26px 28px;
            background: var(--hero); border: 1px solid var(--hero-b);
            display: flex; flex-wrap: wrap; align-items: center; gap: 22px;
        }
        .m-hero::after {
            content: ''; position: absolute; right: -60px; top: -80px; width: 260px; height: 260px; border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, .16), transparent 70%); pointer-events: none;
        }
        .m-hero__avatar {
            width: 56px; height: 56px; border-radius: 50%; flex-shrink: 0; display: grid; place-items: center;
            background: linear-gradient(135deg, #3b82f6, #6366f1); color: #fff; font-size: 22px;
            box-shadow: 0 6px 18px rgba(59, 130, 246, .35);
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
        .m-alert {
            margin-top: 12px; display: inline-flex; align-items: center; gap: 10px; padding: 8px 13px; border-radius: 12px;
            background: var(--warn-bg); color: var(--warn-fg); border: 1px solid var(--warn-b); font-size: 13px; font-weight: 700;
        }
        .m-alert a { color: inherit; text-decoration: underline; font-weight: 800; margin-left: 6px; }
        .m-meter { position: relative; z-index: 1; text-align: right; flex-shrink: 0; }
        .m-meter small { display: block; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--hero-sub); }
        .m-meter b { display: block; font-size: 44px; font-weight: 800; line-height: 1.1; color: var(--txt); }
        .m-meter b span { font-size: 20px; color: var(--hero-sub); }
        .m-meter em { display: block; font-style: normal; font-size: 12px; color: var(--hero-sub); margin-top: 2px; }
        .m-bar { height: 5px; width: 150px; margin: 8px 0 0 auto; border-radius: 99px; background: var(--track); overflow: hidden; }
        #hero-bar { height: 100%; width: 0; border-radius: 99px; background: linear-gradient(90deg, #34d399, #10b981); transition: width 1.2s ease; }

        /* ── Kartu statistik ── */
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
        .att-bar-track { grid-column: 1 / -1; grid-row: 3; width: 100%; height: 4px; margin-top: 10px; border-radius: 99px; background: rgba(100, 116, 139, .18); overflow: hidden; }
        .att-bar-fill { height: 100%; width: 0; border-radius: 99px; background: var(--ti); transition: width 1.2s cubic-bezier(.4, 0, .2, 1); }

        /* ── Kartu & judul ── */
        .m-card { background: var(--card); border: 1px solid var(--card-b); border-radius: 14px; box-shadow: var(--shadow); overflow: hidden; display: flex; flex-direction: column; }
        .m-card__head { display: flex; align-items: center; gap: 12px; padding: 18px 22px 0; }
        .m-ico { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; background: var(--tb); color: var(--tf); font-size: 15px; flex-shrink: 0; }
        .m-title { margin: 0; font-size: 16px; font-weight: 700; color: var(--txt); }
        .m-sub { margin: 2px 0 0; font-size: 12.5px; color: var(--mut); }
        .m-badge { margin-left: auto; font-size: 11.5px; font-weight: 700; padding: 3px 12px; border-radius: 999px; background: var(--prim-s); color: var(--prim); white-space: nowrap; }
        .m-card__body { padding: 16px 22px 22px; flex: 1; }
        .m-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--faint); margin: 0 0 12px; }
        .m-cols { display: grid; grid-template-columns: minmax(0, 1fr); gap: 20px; align-items: stretch; }
        @media (min-width: 1024px) { .m-cols { grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); } }
        .m-stack { display: flex; flex-direction: column; gap: 20px; }
        .m-empty { text-align: center; padding: 34px 16px; color: var(--faint); }
        .m-empty i { display: block; font-size: 36px; margin-bottom: 10px; opacity: .7; }
        .m-empty p { margin: 0; font-size: 13px; }
        .m-empty p + p { font-size: 12px; margin-top: 4px; }

        /* ── Tab ── */
        .m-seg { display: flex; gap: 4px; background: var(--seg); border-radius: 11px; padding: 4px; margin-bottom: 14px; }
        .tab-btn { flex: 1; padding: 8px 14px; border-radius: 8px; border: 0; background: transparent; color: var(--mut); font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background .2s ease, color .2s ease; }
        .tab-btn:hover { color: var(--txt); }
        .tab-btn.active { background: var(--prim); color: #fff; box-shadow: 0 2px 8px rgba(37, 99, 235, .28); }
        html[data-theme="dark"] .tab-btn.active { background: #2f6fe0; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* ── Daftar anak magang ── */
        .intern-list { display: flex; flex-direction: column; gap: 8px; }
        .intern-row { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 12px; background: var(--row); border: 1px solid var(--row-b); transition: background .2s ease, transform .2s ease; }
        .intern-row:hover { background: var(--row-h); transform: translateX(3px); }
        .intern-row.is-alumni { background: var(--alum); border-color: var(--alum-b); }
        .avatar-sm { width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; flex-shrink: 0; color: #fff; font-weight: 700; font-size: 14px; background: linear-gradient(135deg, #3b82f6, #6366f1); }
        .avatar-sm.is-rose { background: linear-gradient(135deg, #f43f5e, #be185d); }
        .m-name { margin: 0; font-size: 13.5px; font-weight: 700; color: var(--txt); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .m-meta { margin: 1px 0 0; font-size: 12px; color: var(--mut); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .mini-pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: var(--tb); color: var(--tf); font-family: 'DM Mono', monospace; }
        .mini-pill i { font-size: 9px; }
        .m-pager { margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
        .m-pager > span { font-size: 12px; color: var(--faint); }
        .m-pager div { display: flex; gap: 5px; }
        .pg { display: inline-flex; align-items: center; gap: 4px; padding: 5px 11px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid var(--row-b); background: var(--row); color: var(--prim); cursor: pointer; transition: background .2s ease; }
        .pg:hover { background: var(--row-h); }
        .pg.is-on { background: var(--prim); border-color: var(--prim); color: #fff; }
        html[data-theme="dark"] .pg.is-on { background: #2f6fe0; border-color: #2f6fe0; }

        /* ── Peringkat ── */
        .podium-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; border: 1px solid transparent; background: var(--tb); transition: transform .2s ease; }
        .podium-item:hover { transform: translateX(4px); }
        .rank-badge { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; font-size: 13px; flex-shrink: 0; color: #fff; }
        .rank-1 { background: linear-gradient(135deg, #fbbf24, #d97706); box-shadow: 0 3px 10px rgba(251, 191, 36, .4); }
        .rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); box-shadow: 0 3px 10px rgba(100, 116, 139, .3); }
        .rank-3 { background: linear-gradient(135deg, #fb923c, #ea580c); box-shadow: 0 3px 10px rgba(251, 146, 60, .35); }
        .m-score { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; font-family: 'DM Mono', monospace; background: var(--card); color: var(--tf); }
        .m-score i { font-size: 9px; }
        .m-photo { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid var(--card); box-shadow: 0 2px 8px rgba(0, 0, 0, .15); }
        .m-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 16px; padding: 10px 20px; border-radius: 10px; font-size: 12.5px; font-weight: 700; text-decoration: none; background: var(--prim); color: #fff; transition: opacity .2s ease; }
        .m-btn:hover { opacity: .88; }
        html[data-theme="dark"] .m-btn { background: #2f6fe0; }

        /* ── Donut ── */
        .m-donut { position: relative; width: 120px; height: 120px; margin: 0 auto 16px; }
        .m-donut svg { transform: rotate(-90deg); }
        .m-donut .track { fill: none; stroke: var(--track); stroke-width: 10; }
        .m-donut__c { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .m-donut__c b { font-size: 22px; font-weight: 800; color: var(--txt); }
        .m-donut__c small { font-size: 9px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--faint); }
        .m-legend { display: flex; flex-direction: column; gap: 9px; }
        .m-legend > div { display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: var(--mut); }
        .m-legend > div > span:first-child { display: flex; align-items: center; gap: 8px; }
        .m-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
        .m-legend b { color: var(--txt); font-size: 13px; }

        /* ── Tabel absensi ── */
        .m-table { width: 100%; border-collapse: collapse; }
        .m-table th { padding: 11px 22px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--mut); background: var(--row); white-space: nowrap; }
        .m-table td { padding: 11px 22px; border-top: 1px solid var(--line); font-size: 13px; color: var(--txt); vertical-align: middle; }
        .att-tr { transition: background .15s ease; }
        .att-tr:hover { background: var(--row-h); }
        .att-tr-absent { background: var(--alum); }
        .att-tr-absent td { border-top-color: var(--alum-b); }
        .m-who { display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .m-who .avatar-sm { width: 30px; height: 30px; font-size: 12px; }
        .m-time { font-family: 'DM Mono', monospace; font-weight: 600; }
        .m-dash { color: var(--faint); font-size: 12px; }
        .m-thumb { width: 38px; height: 38px; object-fit: cover; border-radius: 9px; border: 1.5px solid var(--card-b); cursor: pointer; transition: transform .2s ease, border-color .2s ease; }
        .m-thumb:hover { transform: scale(1.08); border-color: var(--prim); }
        .s-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 11px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: .02em; background: var(--tb); color: var(--tf); }
        .s-badge i { font-size: 9px; }

        /* ── Logbook ── */
        .m-logs { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .logbook-card { background: var(--row); border: 1px solid var(--row-b); border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; transition: transform .2s ease, box-shadow .2s ease; }
        .logbook-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .logbook-card__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 14px 16px; border-bottom: 1px solid var(--line); }
        .logbook-card__who { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
        .logbook-card__body { padding: 14px 16px; flex: 1; }
        .logbook-card__act { margin: 0; font-size: 13px; line-height: 1.5; color: var(--txt); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .logbook-card__row { display: flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 12px; color: var(--mut); }
        .logbook-card__row.is-link { margin-top: 6px; color: var(--prim); }
        .logbook-card__foot { display: flex; gap: 8px; padding: 10px 14px; border-top: 1px solid var(--line); background: var(--card); }
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
            .m-table th, .m-table td { padding: 10px 14px; }
            .m-card__head { padding: 16px 16px 0; }
            .m-card__body { padding: 14px 16px 18px; }
        }
    </style>
@endpush

@section('content')
    @php
        if (!isset($alumni)) {
            $alumniCollection = $interns->filter(function ($i) {
                $s = strtolower($i->status ?? '');
                return in_array($s, ['tidak aktif', 'inactive', 'pelepasan', 'released', 'alumni']);
            });
        } else {
            $alumniCollection = $alumni;
        }
        $alumniCount = is_countable($alumniCollection) ? $alumniCollection->count() : 0;
        $hadirCount = $todayAttendances->where('status', 'hadir')->count();
        $izinCount = $todayAttendances->whereIn('status', ['izin', 'sakit'])->count();
        $alfaCount = $todayAttendances->where('status', 'alfa')->count() + $todayAbsentInterns->count();
        $internsTotal = $interns->count();
        $hadirPct = $internsTotal ? round(($hadirCount / $internsTotal) * 100) : 0;
    @endphp

    <div class="mdash py-7">
        <div class="mdash-in max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ── BANNER + STATISTIK ── --}}
            <div class="mdash-sec" data-dm-native style="display:flex;flex-direction:column;gap:20px;">

                <div class="m-hero a1">
                    <div class="m-hero__avatar"><i class="fas fa-chalkboard-teacher"></i></div>

                    <div class="m-hero__main">
                        <span class="m-chip"><i class="fas fa-circle"></i> Aktif Membimbing</span>
                        <h1>{{ $mentor?->name ?? auth()->user()->name }}</h1>
                        <p>
                            Ringkasan bimbingan hari ini &mdash;
                            <strong>{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('l, d F Y') }}</strong>
                        </p>
                        @if (isset($todayLogbooksPending) && count($todayLogbooksPending) > 0)
                            <div class="m-alert">
                                <i class="fas fa-book"></i>
                                <span>
                                    Ada {{ count($todayLogbooksPending) }} logbook perlu direview hari ini.
                                    <a href="{{ route('mentor.logbook.index') }}">Lihat sekarang</a>
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="m-meter">
                        <small>Kehadiran Hari Ini</small>
                        <b class="mono">{{ $hadirPct }}<span>%</span></b>
                        <em>{{ $hadirCount }} / {{ $internsTotal }} hadir</em>
                        <div class="m-bar"><div id="hero-bar"></div></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 a2">
                    <div class="m-tile tone-blue">
                        <div class="m-tile__ico"><i class="fas fa-users"></i></div>
                        <p class="m-tile__lbl m-0" style="margin:0;">Anak Magang</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $internsTotal }}</p>
                    </div>

                    <div class="m-tile tone-green">
                        <div class="m-tile__ico"><i class="fas fa-calendar-check"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Hadir Hari Ini</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $hadirCount }}</p>
                        <div class="att-bar-track"><div class="att-bar-fill" data-w="{{ $hadirPct }}"></div></div>
                    </div>

                    <div class="m-tile tone-amber">
                        <div class="m-tile__ico"><i class="fas fa-calendar-times"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Izin / Sakit</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $izinCount }}</p>
                        <div class="att-bar-track"><div class="att-bar-fill" data-w="{{ $internsTotal ? round(($izinCount / $internsTotal) * 100) : 0 }}"></div></div>
                    </div>

                    <div class="m-tile tone-slate">
                        <div class="m-tile__ico"><i class="fas fa-user-times"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Tidak Hadir</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $alfaCount }}</p>
                        <div class="att-bar-track"><div class="att-bar-fill" data-w="{{ $internsTotal ? round(($alfaCount / $internsTotal) * 100) : 0 }}"></div></div>
                    </div>

                    <div class="m-tile tone-violet">
                        <div class="m-tile__ico"><i class="fas fa-graduation-cap"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Mikro Skill Hari Ini</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $microTodayTotal }}</p>
                        <p class="m-tile__note" style="margin:0;">Total hari ini</p>
                    </div>

                    <div class="m-tile tone-rose">
                        <div class="m-tile__ico"><i class="fas fa-user-graduate"></i></div>
                        <p class="m-tile__lbl" style="margin:0;">Alumni</p>
                        <p class="m-tile__num mono" style="margin:0;">{{ $alumniCount }}</p>
                    </div>
                </div>
            </div>

            {{-- ── WORKSPACE: Perlu Review & Tugas Terlambat (hanya tampil bila modul Workspace aktif) ── --}}
            <x-workspace.dashboard-widgets role="mentor" class="a2" />

            {{-- ── DAFTAR, PERINGKAT, ABSENSI, LOGBOOK ── --}}
            <div class="mdash-sec" data-dm-native style="display:flex;flex-direction:column;gap:20px;">

                <div class="m-cols">

                    {{-- KIRI: daftar anak magang --}}
                    <div class="m-card a3">
                        <div class="m-card__head">
                            <div class="m-ico tone-blue"><i class="fas fa-users-cog"></i></div>
                            <div>
                                <h2 class="m-title">Daftar Anak Magang</h2>
                                <p class="m-sub">Peserta yang Anda bimbing</p>
                            </div>
                            <span class="m-badge">{{ $internsTotal }} orang</span>
                        </div>
                        <div class="m-card__body">
                            <div class="m-seg">
                                <button class="tab-btn active" onclick="switchTab('aktif', this)">
                                    <i class="fas fa-user-check mr-1"></i> Aktif ({{ $internsTotal }})
                                </button>
                                <button class="tab-btn" onclick="switchTab('alumni', this)">
                                    <i class="fas fa-user-graduate mr-1"></i> Alumni ({{ $alumniCount }})
                                </button>
                            </div>

                            {{-- Tab aktif --}}
                            <div id="tab-aktif" class="tab-content active">
                                @php
                                    $internsList = collect($interns);
                                    $internsPerPage = 8;
                                    $internsCurrentPage = request('interns_page', 1);
                                    $internsSliced = $internsList->slice(
                                        ($internsCurrentPage - 1) * $internsPerPage,
                                        $internsPerPage,
                                    );
                                    $internsTotalPages = ceil($internsList->count() / $internsPerPage);
                                @endphp
                                <div class="intern-list">
                                    @forelse($internsSliced as $intern)
                                        <div class="intern-row">
                                            <div class="avatar-sm">{{ strtoupper(substr($intern->name, 0, 1)) }}</div>
                                            <div class="flex-1 min-w-0">
                                                <p class="m-name">{{ $intern->name }}</p>
                                                <p class="m-meta">{{ $intern->institution }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <span class="mini-pill tone-blue"><i class="fas fa-calendar-alt"></i> {{ $intern->attendances_count }}</span>
                                                <span class="mini-pill tone-violet"><i class="fas fa-book"></i> {{ $intern->micro_skills_count }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="m-empty">
                                            <i class="fas fa-user-slash"></i>
                                            <p>Belum ada anak magang.</p>
                                        </div>
                                    @endforelse
                                </div>
                                @if ($internsList->count() > $internsPerPage)
                                    <div class="m-pager">
                                        <span>
                                            Menampilkan
                                            {{ ($internsCurrentPage - 1) * $internsPerPage + 1 }}–{{ min($internsCurrentPage * $internsPerPage, $internsList->count()) }}
                                            dari {{ $internsList->count() }}
                                        </span>
                                        <div>
                                            @if ($internsCurrentPage > 1)
                                                <a href="?interns_page=1" class="pg"><i class="fas fa-chevron-left"></i> Awal</a>
                                            @endif
                                            @for ($i = max(1, $internsCurrentPage - 1); $i <= min($internsTotalPages, $internsCurrentPage + 1); $i++)
                                                @if ($i === $internsCurrentPage)
                                                    <button class="pg is-on">{{ $i }}</button>
                                                @else
                                                    <a href="?interns_page={{ $i }}" class="pg">{{ $i }}</a>
                                                @endif
                                            @endfor
                                            @if ($internsCurrentPage < $internsTotalPages)
                                                <a href="?interns_page={{ $internsTotalPages }}" class="pg">Akhir <i class="fas fa-chevron-right"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Tab alumni --}}
                            <div id="tab-alumni" class="tab-content">
                                @php
                                    $alumniList = collect($alumniCollection);
                                    $alumniPerPage = 8;
                                    $alumniCurrentPage = request('alumni_page', 1);
                                    $alumniSliced = $alumniList->slice(
                                        ($alumniCurrentPage - 1) * $alumniPerPage,
                                        $alumniPerPage,
                                    );
                                    $alumniTotalPages = ceil($alumniList->count() / $alumniPerPage);
                                @endphp
                                <div class="intern-list">
                                    @forelse($alumniSliced as $a)
                                        <div class="intern-row is-alumni">
                                            <div class="avatar-sm is-rose">{{ strtoupper(substr($a->name, 0, 1)) }}</div>
                                            <div class="flex-1 min-w-0">
                                                <p class="m-name">{{ $a->name }}</p>
                                                <p class="m-meta">{{ $a->institution }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <span class="mini-pill tone-blue"><i class="fas fa-calendar-alt"></i> {{ $a->attendances_count ?? 0 }}</span>
                                                <span class="mini-pill tone-violet"><i class="fas fa-book"></i> {{ $a->micro_skills_count ?? 0 }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="m-empty">
                                            <i class="fas fa-inbox"></i>
                                            <p>Belum ada data alumni.</p>
                                        </div>
                                    @endforelse
                                </div>
                                @if ($alumniList->count() > $alumniPerPage)
                                    <div class="m-pager">
                                        <span>
                                            Menampilkan
                                            {{ ($alumniCurrentPage - 1) * $alumniPerPage + 1 }}–{{ min($alumniCurrentPage * $alumniPerPage, $alumniList->count()) }}
                                            dari {{ $alumniList->count() }}
                                        </span>
                                        <div>
                                            @if ($alumniCurrentPage > 1)
                                                <a href="?alumni_page=1" class="pg"><i class="fas fa-chevron-left"></i> Awal</a>
                                            @endif
                                            @for ($i = max(1, $alumniCurrentPage - 1); $i <= min($alumniTotalPages, $alumniCurrentPage + 1); $i++)
                                                @if ($i === $alumniCurrentPage)
                                                    <button class="pg is-on">{{ $i }}</button>
                                                @else
                                                    <a href="?alumni_page={{ $i }}" class="pg">{{ $i }}</a>
                                                @endif
                                            @endfor
                                            @if ($alumniCurrentPage < $alumniTotalPages)
                                                <a href="?alumni_page={{ $alumniTotalPages }}" class="pg">Akhir <i class="fas fa-chevron-right"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: peringkat + donut --}}
                    <div class="m-stack">

                        <div class="m-card a3">
                            <div class="m-card__head">
                                <div class="m-ico tone-amber"><i class="fas fa-trophy"></i></div>
                                <div>
                                    <h2 class="m-title">Top Mikro Skill</h2>
                                    <p class="m-sub">Top 3 bimbingan</p>
                                </div>
                            </div>
                            <div class="m-card__body">
                                @if (isset($topMicroSkills) && count($topMicroSkills))
                                    <div style="display:flex;flex-direction:column;gap:10px;">
                                        @foreach ($topMicroSkills->take(3) as $index => $row)
                                            <div class="podium-item {{ ['tone-amber', 'tone-slate', 'tone-rose'][$index] ?? 'tone-slate' }}">
                                                <div class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                                                @if (!empty($row['photo_path']))
                                                    <img src="{{ url('storage/' . $row['photo_path']) }}" class="m-photo" alt="{{ $row['name'] }}">
                                                @else
                                                    <div class="avatar-sm">{{ strtoupper(substr($row['name'], 0, 1)) }}</div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <p class="m-name">{{ $row['name'] }}</p>
                                                    <p class="m-meta">{{ $row['institution'] }}</p>
                                                </div>
                                                <span class="m-score"><i class="fas fa-star"></i> {{ $row['total'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="m-empty">
                                        <i class="fas fa-chart-line"></i>
                                        <p>Belum ada data.</p>
                                    </div>
                                @endif
                                <a href="{{ route('mentor.microskill.leaderboard') }}" class="m-btn">
                                    Lihat Selengkapnya <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        @php
                            $circ = 188.5;
                            $hadirDash = $internsTotal ? ($hadirCount / $internsTotal) * $circ : 0;
                            $izinDash = $internsTotal ? ($izinCount / $internsTotal) * $circ : 0;
                            $alfaDash = $internsTotal ? ($alfaCount / $internsTotal) * $circ : 0;
                        @endphp
                        <div class="m-card a4">
                            <div class="m-card__head">
                                <div class="m-ico tone-green"><i class="fas fa-chart-pie"></i></div>
                                <div>
                                    <h2 class="m-title">Distribusi Kehadiran</h2>
                                    <p class="m-sub">Hari ini</p>
                                </div>
                            </div>
                            <div class="m-card__body">
                                <div class="m-donut">
                                    <svg width="120" height="120" viewBox="0 0 80 80">
                                        <circle class="track" cx="40" cy="40" r="30" />
                                        <circle id="d-hadir" cx="40" cy="40" r="30" fill="none" stroke="#22c55e"
                                            stroke-width="10" stroke-dasharray="{{ $circ }}"
                                            stroke-dashoffset="{{ $circ }}"
                                            style="transition:stroke-dashoffset 1.2s ease;" data-dash="{{ $hadirDash }}"
                                            data-start="0" />
                                        <circle id="d-izin" cx="40" cy="40" r="30" fill="none" stroke="#f59e0b"
                                            stroke-width="10" stroke-dasharray="{{ $circ }}"
                                            stroke-dashoffset="{{ $circ }}"
                                            style="transition:stroke-dashoffset 1.2s ease .15s;"
                                            data-dash="{{ $izinDash }}" data-start="{{ $hadirDash }}" />
                                        <circle id="d-alfa" cx="40" cy="40" r="30" fill="none" stroke="#ef4444"
                                            stroke-width="10" stroke-dasharray="{{ $circ }}"
                                            stroke-dashoffset="{{ $circ }}"
                                            style="transition:stroke-dashoffset 1.2s ease .3s;"
                                            data-dash="{{ $alfaDash }}" data-start="{{ $hadirDash + $izinDash }}" />
                                    </svg>
                                    <div class="m-donut__c">
                                        <b class="mono">{{ $hadirPct }}%</b>
                                        <small>Hadir</small>
                                    </div>
                                </div>
                                <div class="m-legend">
                                    <div><span><i style="background:#22c55e;"></i> Hadir</span><b class="mono">{{ $hadirCount }}</b></div>
                                    <div><span><i style="background:#f59e0b;"></i> Izin/Sakit</span><b class="mono">{{ $izinCount }}</b></div>
                                    <div><span><i style="background:#ef4444;"></i> Tidak Hadir</span><b class="mono">{{ $alfaCount }}</b></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── ABSENSI HARI INI ── --}}
                <div class="m-card a5">
                    <div class="m-card__head" style="padding-bottom:14px;">
                        <div class="m-ico tone-blue"><i class="fas fa-clipboard-check"></i></div>
                        <div>
                            <h2 class="m-title">Absensi Hari Ini</h2>
                            <p class="m-sub">Kehadiran seluruh anak bimbingan</p>
                        </div>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="m-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Foto In</th>
                                    <th>Check Out</th>
                                    <th>Foto Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hadirExists = false; @endphp

                                @forelse($todayAttendances->where('status', 'hadir') as $attendance)
                                    @php $hadirExists = true; @endphp
                                    <tr class="att-tr">
                                        <td>
                                            <div class="m-who">
                                                <div class="avatar-sm">{{ strtoupper(substr($attendance->intern->name, 0, 1)) }}</div>
                                                <span>{{ $attendance->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="s-badge tone-green"><i class="fas fa-check-circle"></i> Hadir</span></td>
                                        <td class="m-time">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_path)
                                                @php $photoUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_path)]); @endphp
                                                @can('view', $attendance)
                                                    <img src="{{ $photoUrl }}" alt="In" class="m-thumb" onclick="window.open('{{ $photoUrl }}', '_blank')">
                                                @else
                                                    <span class="m-dash">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                        <td class="m-time">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_checkout)
                                                @php $photoOutUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_checkout)]); @endphp
                                                @can('view', $attendance)
                                                    <img src="{{ $photoOutUrl }}" alt="Out" class="m-thumb" onclick="window.open('{{ $photoOutUrl }}', '_blank')">
                                                @else
                                                    <span class="m-dash">Tidak ada akses</span>
                                                @endcan
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse

                                @forelse($todayAttendances->whereIn('status', ['izin', 'sakit', 'alfa']) as $attendance)
                                    <tr class="att-tr">
                                        <td>
                                            <div class="m-who">
                                                <div class="avatar-sm">{{ strtoupper(substr($attendance->intern->name, 0, 1)) }}</div>
                                                <span>{{ $attendance->intern->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="s-badge @if ($attendance->status == 'izin') tone-amber @elseif($attendance->status == 'sakit') tone-violet @else tone-rose @endif">
                                                @if (in_array($attendance->status, ['izin', 'sakit']))
                                                    <i class="fas fa-notes-medical"></i>
                                                @else
                                                    <i class="fas fa-times-circle"></i>
                                                @endif
                                                {{ $attendance->status == 'alfa' ? 'Tidak Hadir' : ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td class="m-time">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_path)
                                                @php $photoUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_path)]); @endphp
                                                <img src="{{ $photoUrl }}" alt="In" class="m-thumb" onclick="window.open('{{ $photoUrl }}', '_blank')">
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                        <td class="m-time">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '—' }}</td>
                                        <td>
                                            @if ($attendance->photo_checkout)
                                                @php $photoOutUrl = route('mentor.attendance.photo', ['filename' => basename($attendance->photo_checkout)]); @endphp
                                                <img src="{{ $photoOutUrl }}" alt="Out" class="m-thumb" onclick="window.open('{{ $photoOutUrl }}', '_blank')">
                                            @else
                                                <span class="m-dash">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse

                                @forelse($todayAbsentInterns as $absentIntern)
                                    <tr class="att-tr att-tr-absent">
                                        <td>
                                            <div class="m-who">
                                                <div class="avatar-sm is-rose">{{ strtoupper(substr($absentIntern->name, 0, 1)) }}</div>
                                                <span>{{ $absentIntern->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="s-badge tone-rose"><i class="fas fa-times-circle"></i> Belum Absen</span></td>
                                        <td colspan="4" class="m-dash" style="font-style:italic;">Belum melakukan absensi hari ini</td>
                                    </tr>
                                @empty
                                    @if (!$hadirExists)
                                        <tr>
                                            <td colspan="6">
                                                <div class="m-empty">
                                                    <i class="fas fa-inbox"></i>
                                                    <p>Belum ada data absensi hari ini.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── LOGBOOK PERLU REVIEW ── --}}
                <div class="m-card a5">
                    <div class="m-card__head">
                        <div class="m-ico tone-violet"><i class="fas fa-book"></i></div>
                        <div>
                            <h2 class="m-title">Logbook Hari Ini Perlu Di-Review</h2>
                            <p class="m-sub">Tinjau dan setujui logbook anak bimbingan</p>
                        </div>
                        <span class="m-badge">{{ count($todayLogbooksPending ?? []) }} logbook</span>
                    </div>
                    <div class="m-card__body">
                        @if ($todayLogbooksPending && count($todayLogbooksPending) > 0)
                            <div class="m-logs">
                                @foreach ($todayLogbooksPending as $logbook)
                                    @php
                                        $isApproved = $logbook->approval_status === 'approved';
                                        $isPending = !$isApproved;
                                    @endphp
                                    <div class="logbook-card">
                                        <div class="logbook-card__head">
                                            <div class="logbook-card__who">
                                                <div class="avatar-sm">{{ strtoupper(substr($logbook->intern->name, 0, 1)) }}</div>
                                                <div style="min-width:0;">
                                                    <p class="m-name">{{ $logbook->intern->name }}</p>
                                                    <p class="m-meta">{{ $logbook->intern->institution }}</p>
                                                </div>
                                            </div>
                                            @if ($isApproved)
                                                <span class="s-badge tone-green"><i class="fas fa-check-circle"></i> Approved</span>
                                            @else
                                                <span class="s-badge tone-amber"><i class="fas fa-clock"></i> Pending</span>
                                            @endif
                                        </div>

                                        <div class="logbook-card__body">
                                            <p class="m-label" style="margin-bottom:5px;">Aktivitas</p>
                                            <p class="logbook-card__act">{{ $logbook->activity }}</p>
                                            <div class="logbook-card__row">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>{{ \Carbon\Carbon::parse($logbook->date)->locale('id')->translatedFormat('d M Y') }}</span>
                                            </div>
                                            @if ($logbook->photo_path)
                                                <div class="logbook-card__row is-link">
                                                    <i class="fas fa-image"></i> Ada dokumentasi foto
                                                </div>
                                            @endif
                                        </div>

                                        <div class="logbook-card__foot">
                                            <a href="{{ route('mentor.logbook.show', $logbook->id) }}" class="m-act is-soft">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                            @if ($isPending)
                                                <button type="button" class="m-act is-ok" onclick="openApprovalModal({{ $logbook->id }})">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="m-empty" style="padding:44px 24px;">
                                <i class="fas fa-inbox"></i>
                                <p style="font-size:14px;font-weight:600;">Belum ada logbook yang perlu direview hari ini.</p>
                                <p>Logbook akan muncul di sini ketika anak magang mengupload logbook baru atau jika ada yang belum disetujui.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── APPROVAL MODAL ── --}}
    {{-- Wrapper uses min-height trick so fixed-like overlay works in normal flow --}}
    <div id="approvalModalOverlay"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
        <div
            style="background:#fff;border-radius:18px;border:1px solid #e6ebf3;box-shadow:0 24px 60px rgba(15,23,42,.28);max-width:480px;width:100%;max-height:90vh;overflow-y:auto;">
            <div
                style="background:#eef4ff;border-bottom:1px solid #d6e4ff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;border-radius:18px 18px 0 0;">
                <div>
                    <p
                        style="color:#3b5b94;font-size:11px;text-transform:uppercase;letter-spacing:.06em;margin:0;font-weight:700;">
                        Persetujuan Logbook</p>
                    <h3 style="color:#0f172a;font-size:17px;font-weight:800;margin:6px 0 0;">Setujui Logbook</h3>
                </div>
                <button onclick="closeApprovalModal()"
                    style="background:#fff;border:1px solid #d6e4ff;color:#475569;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;"
                    onmouseover="this.style.background='#f1f6ff';"
                    onmouseout="this.style.background='#fff';">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="padding:22px 24px;">
                <form id="approvalForm" style="display:flex;flex-direction:column;gap:14px;">
                    <input type="hidden" id="logbookId" name="logbook_id">
                    @csrf
                    <div>
                        <label
                            style="display:block;font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">
                            Catatan Approval (Opsional)
                        </label>
                        <textarea name="note" id="approvalNote"
                            style="width:100%;padding:11px;border:1px solid #dfe5ef;border-radius:10px;font-size:13px;font-family:inherit;resize:vertical;min-height:90px;outline:none;transition:border-color .2s;"
                            placeholder="Tambahkan catatan atau feedback untuk anak magang..." onfocus="this.style.borderColor='#2563eb';"
                            onblur="this.style.borderColor='#dfe5ef';"></textarea>
                    </div>
                    <div style="display:flex;gap:10px;padding-top:10px;border-top:1px solid #e5e7eb;">
                        <button type="button" onclick="closeApprovalModal()"
                            style="flex:1;padding:11px;background:#f1f5f9;color:#475569;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:13px;"
                            onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
                            Batal
                        </button>
                        <button type="submit"
                            style="flex:1;padding:11px;background:#16a34a;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:13px;"
                            onmouseover="this.style.background='#15803d';" onmouseout="this.style.background='#16a34a';">
                            <i class="fas fa-check" style="margin-right:5px;"></i> Setujui Logbook
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="logbookAlertOverlay"
        style="display:none;position:fixed;inset:0;background:rgba(26,26,46,0.75);z-index:10000;align-items:center;justify-content:center;padding:24px;">
        <div style="background:#fff;border-radius:20px;max-width:420px;width:100%;overflow:hidden;border:1px solid #e6ebf3;box-shadow:0 24px 60px rgba(15,23,42,.28);">

            {{-- Header berwarna --}}
            <div style="background:#fff1f2;padding:24px 24px 36px;position:relative;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                    <div style="width:52px;height:52px;border-radius:14px;background:#ffe4e6;border:1px solid #fecdd3;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-book-open" style="color:#e11d48;font-size:22px;"></i>
                    </div>
                    <button onclick="closeLogbookAlert()"
                        style="width:28px;height:28px;border-radius:8px;background:#fff;border:1px solid #fecdd3;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#be123c;font-size:14px;">
                        &times;
                    </button>
                </div>
                <p style="margin:14px 0 2px;font-size:18px;font-weight:800;color:#0f172a;">Logbook Baru Hari Ini</p>
                <p style="margin:0;font-size:13px;color:#9f1239;">{{ now()->isoFormat('dddd, D MMMM YYYY') }} &mdash; review diperlukan</p>
                <div style="position:absolute;bottom:-1px;left:0;right:0;height:18px;background:#fff;border-radius:20px 20px 0 0;"></div>
            </div>

            {{-- Body --}}
            <div style="padding:20px 24px 24px;">
                <div style="display:flex;gap:10px;margin-bottom:18px;">
                    <div style="flex:1;border-radius:12px;padding:12px 14px;border:1px solid #e5e7eb;background:#f9fafb;">
                        <p style="font-size:11px;color:#9ca3af;margin:0 0 4px;text-transform:uppercase;letter-spacing:.04em;">Menunggu review</p>
                        <p style="font-size:22px;font-weight:800;color:#e11d48;margin:0;">{{ count($todayLogbooksPending ?? []) }}</p>
                    </div>
                    <div style="flex:1;border-radius:12px;padding:12px 14px;border:1px solid #e5e7eb;background:#f9fafb;">
                        <p style="font-size:11px;color:#9ca3af;margin:0 0 4px;text-transform:uppercase;letter-spacing:.04em;">Batas waktu</p>
                        <p style="font-size:22px;font-weight:800;color:#c2410c;margin:0;">Hari ini</p>
                    </div>
                </div>

                <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#fff7ed;border-radius:12px;border:1px solid #fed7aa;margin-bottom:18px;">
                    <i class="fas fa-info-circle" style="color:#c2410c;font-size:16px;flex-shrink:0;margin-top:2px;"></i>
                    <p style="font-size:13px;color:#9a3412;margin:0;line-height:1.6;">Mahasiswa menunggu feedback kamu. Tinjau logbook sekarang agar progres mereka tidak terhambat.</p>
                </div>

                <div style="display:flex;gap:8px;">
                    <a href="{{ route('mentor.logbook.index') }}"
                        style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 18px;background:#e11d48;color:#fff;border-radius:12px;box-shadow:0 8px 18px -8px rgba(225,29,72,.6);text-decoration:none;font-weight:700;font-size:14px;">
                        <i class="fas fa-arrow-right" style="font-size:14px;"></i>
                        Tinjau sekarang
                    </a>
                    <button type="button" onclick="closeLogbookAlert()"
                        style="padding:11px 16px;background:transparent;color:#6b7280;border:1px solid #e5e7eb;border-radius:12px;font-weight:600;font-size:14px;cursor:pointer;">
                        Nanti
                    </button>
                </div>
            </div>

        </div>
    </div>
    @push('scripts')
        <script>
            // ── Animate bars & donut on load ──
            window.addEventListener('load', function() {
                // Hero bar
                document.getElementById('hero-bar').style.width = '{{ $hadirPct }}%';

                // Stat tile bars
                document.querySelectorAll('.att-bar-fill').forEach(function(el) {
                    el.style.width = (el.dataset.w || 0) + '%';
                });

                // Donut segments
                const circ = {{ $circ }};
                ['hadir', 'izin', 'alfa'].forEach(function(id) {
                    const el = document.getElementById('d-' + id);
                    if (!el) return;
                    const dash = parseFloat(el.dataset.dash) || 0;
                    const start = parseFloat(el.dataset.start) || 0;
                    el.style.strokeDashoffset = circ - dash;
                    el.style.strokeDasharray = dash + ' ' + (circ - dash);
                    el.style.transform = 'rotate(' + ((start / circ) * 360) + 'deg)';
                    el.style.transformOrigin = '40px 40px';
                });
            });

            // ── Tab switcher ──
            function switchTab(name, btn) {
                document.querySelectorAll('.tab-content').forEach(function(el) {
                    el.classList.remove('active');
                });
                document.querySelectorAll('.tab-btn').forEach(function(el) {
                    el.classList.remove('active');
                });
                document.getElementById('tab-' + name).classList.add('active');
                btn.classList.add('active');
            }

            // ── Logbook alert popup ──
            function openLogbookAlert() {
                const el = document.getElementById('logbookAlertOverlay');
                if (!el) return;
                el.style.display = 'flex';
            }

            function closeLogbookAlert() {
                const el = document.getElementById('logbookAlertOverlay');
                if (!el) return;
                el.style.display = 'none';
            }

            window.addEventListener('load', function() {
                const todayCount = {{ count($todayLogbooksPending ?? []) }};
                if (todayCount && todayCount > 0) {
                    setTimeout(openLogbookAlert, 700);
                }
            });

            // ── Approval modal ──
            function openApprovalModal(logbookId) {
                document.getElementById('logbookId').value = logbookId;
                document.getElementById('approvalNote').value = '';
                const overlay = document.getElementById('approvalModalOverlay');
                overlay.style.display = 'flex';
            }

            function closeApprovalModal() {
                document.getElementById('approvalModalOverlay').style.display = 'none';
            }

            document.getElementById('approvalModalOverlay').addEventListener('click', function(e) {
                if (e.target === this) closeApprovalModal();
            });

            document.getElementById('approvalForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const logbookId = document.getElementById('logbookId').value;
                const note = document.getElementById('approvalNote').value;
                const form = new FormData();
                form.append('status', 'approved');
                form.append('note', note);
                form.append('_token', document.querySelector('input[name="_token"]').value);
                form.append('_method', 'PUT');

                fetch('/mentor/logbook/' + logbookId + '/approve', {
                        method: 'POST',
                        body: form
                    })
                    .then(function(res) {
                        if (res.ok) {
                            alert('Logbook berhasil disetujui!');
                            window.location.reload();
                        } else {
                            alert('Terjadi kesalahan saat menyimpan approval.');
                        }
                    })
                    .catch(function(err) {
                        console.error('Error:', err);
                        alert('Terjadi kesalahan saat mengirim data.');
                    });
            });
        </script>
    @endpush
@endsection
