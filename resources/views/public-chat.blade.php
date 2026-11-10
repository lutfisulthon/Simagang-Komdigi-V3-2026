{{--
    Pemuat Chat Publik untuk SEMUA halaman setelah login (di-include layouts/app).
    - Di setiap halaman: menjaga lencana merah jumlah pesan belum dibaca pada menu "Chat Publik" di sidebar.
    - Di halaman /chat-publik (resources/views/chat-publik/index.blade.php): juga menggerakkan percakapan.
    Tersambung ke server lewat window.PublicChatConfig.endpoints (kontrak: docs/public-chat-backend.md).
    User nonaktif tidak memuat skrip ini (server juga menolak permintaannya).
    Perilaku: public/js/public-chat.js, gaya halaman: public/css/public-chat.css.
--}}
@php
    $pcUser = auth()->user();
@endphp
@if($pcUser && ! \App\Support\PublicChat::isBlocked($pcUser))
<script>
    // Identitas pengguna diambil dari sesi login; server tetap memakai sesi, bukan data ini, untuk penulis pesan.
    window.PublicChatConfig = Object.assign({ mock: false }, window.PublicChatConfig || {}, {
        endpoints: {
            list: @js(route('public-chat.messages.index')),
            send: @js(route('public-chat.messages.store')),
        },
        user: @js(['id' => (string) $pcUser->id, 'name' => (string) $pcUser->name, 'role' => \App\Support\PublicChat::roleOf($pcUser)]),
        // Realtime (Reverb) bila aktif; null = hanya polling.
        realtime: @js(\App\Support\Realtime::clientConfig()),
        authEndpoint: @js(url('/broadcasting/auth')),
    });
</script>
<script defer src="{{ asset('js/public-chat.js') }}?v={{ @filemtime(public_path('js/public-chat.js')) }}"></script>
@endif
