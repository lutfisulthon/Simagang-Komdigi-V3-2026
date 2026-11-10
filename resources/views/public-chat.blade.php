{{--
    Pemuat Chat Publik untuk SEMUA halaman setelah login (di-include layouts/app).
    - Di setiap halaman: menjaga lencana merah jumlah pesan belum dibaca pada menu "Chat Publik" di sidebar.
    - Di halaman /chat-publik (resources/views/chat-publik/index.blade.php): juga menggerakkan percakapan.
    Saat ini berjalan dengan DATA CONTOH di browser (mode "mock"); alamat server diisi lewat window.PublicChatConfig.endpoints
    (lihat docs/public-chat-backend.md). Perilaku: public/js/public-chat.js, gaya halaman: public/css/public-chat.css.
--}}
@php
    $pcUser = auth()->user();
    $pcRole = match (true) {
        ! $pcUser => 'intern',
        $pcUser->isAdmin() || $pcUser->isSuperAdmin() => 'admin',
        $pcUser->isMentor() => 'mentor',
        $pcUser->isInstitusi() => 'institusi',
        $pcUser->isIndustri() => 'industri',
        default => 'intern',
    };
@endphp
<script>
    // Identitas pengguna diambil dari sesi login. Alamat server diisi oleh backend bila sudah ada (lihat docs/public-chat-backend.md).
    // Tanpa endpoints, chat memakai data contoh.
    window.PublicChatConfig = Object.assign({ mock: true }, window.PublicChatConfig || {}, {
        user: @js(['id' => (string) $pcUser?->id, 'name' => (string) $pcUser?->name, 'role' => $pcRole]),
    });
</script>
<script defer src="{{ asset('js/public-chat.js') }}?v={{ @filemtime(public_path('js/public-chat.js')) }}"></script>
