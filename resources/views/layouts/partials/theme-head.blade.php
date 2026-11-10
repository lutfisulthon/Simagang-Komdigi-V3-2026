{{--
    Mode gelap: sertakan di <head> setiap halaman. Skrip kecil di bawah menerapkan pilihan tersimpan SEBELUM halaman dicat,
    jadi tidak ada kilatan terang. Perilaku lengkap: public/js/dark-mode.js, gaya: public/css/dark-mode.css.

    Opsi ['splash' => true]: halaman juga memuat splash screen (komponen <x-splash-screen /> tepat setelah <body>).
    Layar pemuatan tampil saat halaman di-refresh, kunjungan pertama di tab (termasuk setelah login), dan saat ganti tema.
    Berpindah halaman lewat klik/form TIDAK memakai splash: splash.js memasang penanda sessionStorage 'simagang-nav' dan
    halaman baru melanjutkan bar tipis di tepi atas (kelas html.sm-navbar-on). Penandanya dipasang di sini supaya
    tampil sejak cat pertama. Transisi ganti tema diurus dark-mode.js + splash.js.
--}}
<script>try { if (localStorage.getItem('simagang-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) {}</script>
@if (! empty($splash))
    <script>try { var smNav = performance.getEntriesByType('navigation')[0]; var smReload = smNav ? smNav.type === 'reload' : performance.navigation.type === 1; var smFirst = !sessionStorage.getItem('simagang-splash'); sessionStorage.setItem('simagang-splash', '1'); var smPrev = sessionStorage.getItem('simagang-nav'); sessionStorage.removeItem('simagang-nav'); if (smReload || smFirst) { document.documentElement.classList.add('sm-splash-on'); setTimeout(function () { document.documentElement.classList.remove('sm-splash-on'); }, 9000); } else if (smPrev) { if (/^(#|rgb)/.test(smPrev)) document.documentElement.style.setProperty('--sm-nav-color', smPrev); document.documentElement.classList.add('sm-navbar-on', 'sm-navbar-resume'); } } catch (e) {}</script>
    <link rel="stylesheet" href="{{ asset('css/splash.css') }}?v={{ @filemtime(public_path('css/splash.css')) }}">
@else
    {{-- Halaman tanpa splash (login, landing): mengirim form login membuat halaman tujuan dianggap kunjungan pertama, jadi splash pembuka tampil sekali setelah masuk. --}}
    <script>document.addEventListener('submit', function (e) { try { if (/\/login\/?$/.test(new URL(e.target.action, location.href).pathname)) sessionStorage.removeItem('simagang-splash'); } catch (err) {} });</script>
@endif
<link rel="stylesheet" href="{{ asset('css/scrollbar.css') }}?v={{ @filemtime(public_path('css/scrollbar.css')) }}">
<link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}?v={{ @filemtime(public_path('css/dark-mode.css')) }}">
<script defer src="{{ asset('js/dark-mode.js') }}?v={{ @filemtime(public_path('js/dark-mode.js')) }}"></script>
