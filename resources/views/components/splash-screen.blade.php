{{--
    Splash screen Simagang: layar pemuatan (kunjungan pertama per sesi tab) dan penutup transisi saat mode gelap/terang diganti.
    Tempatkan tepat setelah <body>, dan sertakan theme-head dengan ['splash' => true] di <head>.
    Aset: public/css/splash.css, public/js/splash.js. Elemen ini data-dm-native: mesin mode gelap tidak menyentuhnya,
    varian terang/gelap diatur sendiri lewat data-variant.
--}}
<div id="sm-splash" class="sm-splash" data-dm-native data-variant="light" role="status" aria-live="polite" aria-label="Memuat Simagang" aria-hidden="true">
    <div class="sm-splash__glow" aria-hidden="true"></div>
    <div class="sm-splash__inner">
        <div class="sm-splash__logo" aria-hidden="true">
            <span class="sm-splash__ring"></span>
            <span class="sm-splash__ring"></span>
            <img src="{{ url('storage/vendor/logo_komdigi.png') }}" alt="" width="72" height="72">
        </div>
        <div class="sm-splash__word" aria-hidden="true">
            <span style="color: #9d272a">SI</span><span style="color: #086bb0">MA</span><span style="color: #2dabe2">GA</span><span style="color: #efc400">NG</span>
        </div>
        <p class="sm-splash__tag">Sistem Manajemen Magang</p>
        <div class="sm-splash__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Kemajuan pemuatan">
            <span class="sm-splash__fill"></span>
        </div>
        <div class="sm-splash__meta">
            <span class="sm-splash__status">Menyiapkan tampilan…</span>
            <span class="sm-splash__pct">0%</span>
        </div>
    </div>
    <p class="sm-splash__foot">BBLSDM Komdigi Makassar</p>
</div>
<script src="{{ asset('js/splash.js') }}?v={{ @filemtime(public_path('js/splash.js')) }}"></script>
