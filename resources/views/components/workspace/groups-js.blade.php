{{-- Skrip kelompok (modal buat/kelola kelompok, daftar anak bimbingan). Dimuat sekali, hanya di halaman yang memakainya;
     harus tampil sebelum Alpine mulai (stack 'scripts' ada di akhir <body>, Alpine memakai defer). --}}
@pushOnce('scripts', 'ws-groups-js')
    <script src="{{ asset('js/workspace/groups.js') }}?v={{ @filemtime(public_path('js/workspace/groups.js')) }}"></script>
@endPushOnce
