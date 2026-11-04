{{--
    Mode gelap: sertakan di <head> setiap halaman. Skrip kecil di bawah menerapkan pilihan tersimpan SEBELUM halaman dicat,
    jadi tidak ada kilatan terang. Perilaku lengkap: public/js/dark-mode.js, gaya: public/css/dark-mode.css.

    Opsi ['splash' => true]: halaman juga memuat splash screen (komponen <x-splash-screen /> tepat setelah <body>).
    Layar pemuatan tampil saat halaman di-refresh, kunjungan pertama di tab, dan saat berpindah halaman lewat klik/form
    (splash.js memasang penanda sessionStorage 'simagang-splash-nav' lalu halaman baru melanjutkan layar yang sama).
    Penandanya dipasang di sini supaya tampil sejak cat pertama. Transisi ganti tema diurus dark-mode.js + splash.js.
--}}
<script>try { if (localStorage.getItem('simagang-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) {}</script>
@if (! empty($splash))
    <script>try { var smNav = performance.getEntriesByType('navigation')[0]; var smReload = smNav ? smNav.type === 'reload' : performance.navigation.type === 1; var smFirst = !sessionStorage.getItem('simagang-splash'); sessionStorage.setItem('simagang-splash', '1'); var smPrev = sessionStorage.getItem('simagang-splash-nav'); sessionStorage.removeItem('simagang-splash-nav'); if (smReload || smFirst || smPrev) { if (smPrev && !smReload && !smFirst) document.documentElement.classList.add('sm-splash-nav'); document.documentElement.classList.add('sm-splash-on'); setTimeout(function () { document.documentElement.classList.remove('sm-splash-on'); }, 9000); } } catch (e) {}</script>
    <link rel="stylesheet" href="{{ asset('css/splash.css') }}?v={{ @filemtime(public_path('css/splash.css')) }}">
@endif
<link rel="stylesheet" href="{{ asset('css/scrollbar.css') }}?v={{ @filemtime(public_path('css/scrollbar.css')) }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}?v={{ @filemtime(public_path('css/dark-mode.css')) }}">
<script defer src="{{ asset('js/dark-mode.js') }}?v={{ @filemtime(public_path('js/dark-mode.js')) }}"></script>
