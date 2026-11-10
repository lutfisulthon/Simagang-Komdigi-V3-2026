<?php

/*
| Pengaturan Chat Publik (ruang obrolan untuk semua pengguna yang login).
| Kontrak API: docs/public-chat-backend.md.
*/
return [
    // Jumlah pesan terakhir yang dikirim saat halaman pertama kali dimuat.
    'history' => 50,

    // Batas pesan yang dikirim untuk satu permintaan polling ?after=<id>.
    'poll_limit' => 200,

    'max_length' => 10000,

    // Batas laju kirim per user: jeda minimum (milidetik) dan jumlah per menit.
    'min_interval_ms' => 600,
    'per_minute' => 40,

    // Pengumuman tersemat (khusus mentor).
    'max_pins' => 3,
    'pin_days' => [7, 30],

    // Hitungan "online": user yang polling dalam N detik terakhir.
    'online_window' => 45,

    // Tautan (http, www, domain) hanya boleh dikirim admin dan mentor. Peran lain ditolak (cegah spam/phishing).
    'links_allowed_roles' => ['admin', 'mentor'],

    // Pesan lebih tua dari N hari dihapus oleh perintah public-chat:prune (pengumuman aktif tidak ikut dihapus).
    'retention_days' => (int) env('PUBLIC_CHAT_RETENTION_DAYS', 30),
];
