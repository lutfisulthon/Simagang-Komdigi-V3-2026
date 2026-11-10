{{-- CSS halaman Workspace (board, backlog, kartu, hero, dst.). Dimuat sekali per halaman, hanya bila dibutuhkan:
     layout Workspace/Notifikasi memuatnya lewat workspace-head; halaman lain memuatnya otomatis lewat komponen workspace yang dipakainya. --}}
@pushOnce('styles', 'ws-page-css')
    <link rel="stylesheet" href="{{ asset('css/workspace.css') }}?v={{ @filemtime(public_path('css/workspace.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/workspace-ui.css') }}?v={{ @filemtime(public_path('css/workspace-ui.css')) }}">
@endPushOnce
