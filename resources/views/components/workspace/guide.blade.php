{{--
    Panduan penggunaan Workspace (panel geser). Murni frontend: tanpa route, isinya statis per peran.
    Dibuka dari tombol "Panduan" di sidebar, atau dari ikon "?" (<x-workspace.help term="sprint" />).
    Buka dari mana saja: window.dispatchEvent(new CustomEvent('ws-guide-open', { detail: { tab?, topic?, feature? } })).
    tab: 'mulai' | 'alur' | 'fitur' | 'kamus' | 'faq'
    topic: id istilah di $terms (otomatis membuka tab Kamus dan menyorot istilahnya).
    feature: id bagian di $features (otomatis membuka tab Fitur dan membuka bagiannya).
    Props: $role = 'mentor' | 'intern' | 'admin'.
--}}
@props(['role' => 'intern'])

@php
    $isMentor = $role === 'mentor';
    $isIntern = $role === 'intern';

    // Ambil hanya entri yang berlaku untuk peran ini. Entri tanpa 'roles' berlaku untuk semua peran.
    $forRole = fn (array $rows) => array_values(array_filter($rows, fn ($r) => ! isset($r['roles']) || in_array($role, $r['roles'], true)));

    $steps = match ($role) {
        'mentor' => [
            ['Buat kelompok', 'Buka menu Workspace, klik "Buat kelompok", lalu isi nama, warna, dan pilih anak bimbingan Anda. Satu anak magang hanya bisa berada di satu kelompok aktif.'],
            ['Susun tugas di Backlog', 'Buka kelompok, tab Backlog, lalu klik "Buat tugas". Isi judul, prioritas, estimasi story point, label, dan epic bila perlu.'],
            ['Buat dan mulai sprint', 'Klik "Buat sprint", pilih tugas yang akan dikerjakan, tentukan tanggal dan satu Ketua dari anggota, lalu mulai sprint.'],
            ['Pantau di Board', 'Tab Board menampilkan kartu per status. Kartu bergeser seiring pekerjaan anak magang; tanda merah berarti lewat deadline. Tab Deadline dan Laporan membantu melihat yang tertinggal.'],
            ['Review dan beri nilai', 'Buka tugas berstatus Review. Pilih "Setujui & beri nilai", atau "Minta revisi" dengan catatan perbaikan.'],
            ['Selesaikan sprint', 'Di akhir periode klik "Selesaikan sprint", lalu pilih ke mana tugas yang belum selesai dipindahkan (Backlog atau sprint baru).'],
            ['Akhiri kelompok', 'Setelah magang selesai, buka menu Kelola lalu "Tandai selesai magang". Kelompok menjadi hanya baca, sedangkan tugas, diskusi, dan nilai tetap tersimpan.'],
        ],
        'intern' => [
            ['Temukan tugas Anda', 'Buka menu Workspace. Di sana ada Space pribadi (tugas khusus untuk Anda) dan kelompok Anda. Tugas untuk Anda ditandai avatar Anda di kartu, dan muncul juga di lonceng notifikasi.'],
            ['Mulai kerjakan', 'Buka tugas lalu klik "Mulai kerjakan", atau geser kartu dari To Do ke In Progress. Dengan begitu mentor tahu Anda sudah mulai.'],
            ['Kirim untuk di-review', 'Setelah selesai, klik "Kirim untuk review" atau pindahkan kartu ke Review. Lampirkan tautan atau berkas hasil, dan tulis catatan singkat bila perlu.'],
            ['Jika diminta revisi', 'Kartu kembali ke kolom Revisi disertai catatan mentor. Klik "Perbaiki sekarang", perbaiki, lalu kirim ke Review lagi.'],
            ['Tugas selesai', 'Setelah disetujui, kartu masuk ke Selesai dan nilai dari mentor terlihat di detail tugas.'],
            ['Jika Anda menjadi Ketua Sprint', 'Anda yang membagi tugas sprint ke teman satu kelompok, lewat tombol Bagi atau avatar di kartu. Anda juga dapat menambah tugas baru ke sprint yang Anda pimpin.'],
        ],
        default => [
            ['Buka Workspace', 'Menu Workspace menampilkan "Semua Kelompok" dari semua mentor beserta progres tiap kelompok.'],
            ['Saring kelompok', 'Gunakan kolom cari serta pilihan unit dan mentor untuk menemukan kelompok tertentu.'],
            ['Perhatikan tanda', 'Tombol "Perlu perhatian" menyorot kelompok yang punya tugas lewat deadline atau menunggu review.'],
            ['Buka detail kelompok', 'Klik "Buka" untuk melihat Board, Timeline, Deadline, Laporan, Channel, dan Aktivitas kelompok tersebut.'],
            ['Cetak rekap', 'Rekap tugas tiap anak magang dapat dicetak atau disimpan sebagai PDF dari halaman detail anak magang dan laporan akhir.'],
        ],
    };

    // Penjelasan per fitur (tab "Fitur"). Tiap butir: ['t' => teks, 'roles' => opsional].
    $features = [
        [
            'id' => 'bagian', 'icon' => 'fa-table-columns', 'title' => 'Bagian di halaman kelompok',
            'items' => [
                ['t' => 'Backlog: daftar tugas yang belum dijadwalkan, sekaligus tempat menyusun sprint. Space pribadi tidak memiliki Backlog.'],
                ['t' => 'Board: kolom status untuk tugas di sprint yang sedang berjalan. Geser kartu untuk mengubah statusnya.'],
                ['t' => 'List: semua tugas dalam bentuk daftar. Bisa dicari berdasarkan judul atau kode, lalu disaring per assignee, prioritas, label, dan status.'],
                ['t' => 'Timeline: batang jadwal tiap tugas dari mulai sampai deadline, dengan garis "hari ini". Tugas tanpa deadline tidak tampil.'],
                ['t' => 'Deadline: agenda (Lewat deadline, Hari ini, Besok, dan seterusnya) atau kalender bulanan. Centang "Tampilkan yang selesai" untuk melihat tugas yang sudah beres.'],
                ['t' => 'Di tab Deadline, centang "Hanya tugas saya" untuk menyembunyikan tugas teman.', 'roles' => ['intern']],
                ['t' => 'Laporan: grafik burndown, tugas per status, tugas selesai per sprint, velocity, tugas selesai per minggu, ketepatan waktu, dan beban per anggota.'],
                ['t' => 'Channel: ruang chat kelompok. Penjelasannya ada di bagian Channel di bawah.'],
                ['t' => 'Aktivitas: riwayat perubahan semua tugas di kelompok, terbaru di atas. Klik kode tugas (mis. T-12) untuk membukanya, atau "Muat aktivitas lebih lama".'],
                ['t' => 'Tombol jumlah anggota di bagian atas halaman menampilkan daftar mentor dan anak magang kelompok ini.'],
                ['t' => 'Tanda jam atau segitiga merah di bagian atas halaman menunjukkan deadline terdekat atau tugas yang terlambat. Klik untuk langsung ke tab Deadline.'],
            ],
        ],
        [
            'id' => 'detail', 'icon' => 'fa-file-lines', 'title' => 'Detail tugas',
            'items' => [
                ['t' => 'Klik kartu tugas untuk membuka detailnya di panel samping. Menu titik tiga berisi "Buka halaman penuh" dan "Salin link" untuk membagikan tugas.'],
                ['t' => 'Tombol di bagian atas menyesuaikan status: "Mulai kerjakan", "Kirim untuk review", atau "Perbaiki sekarang".', 'roles' => ['intern']],
                ['t' => 'Untuk tugas berstatus Review tersedia "Setujui & beri nilai" dan "Minta revisi". Pilihan "Ubah status" dapat memindahkan tugas ke status mana pun.', 'roles' => ['mentor', 'admin']],
                ['t' => 'Menu titik tiga juga berisi "Edit tugas" dan "Hapus tugas" bila Anda berhak mengubahnya.', 'roles' => ['mentor', 'admin']],
                ['t' => 'Bila deadline sudah lewat, hasil tidak dapat dikirim ke Review. Minta mentor memperpanjang deadline.', 'roles' => ['intern']],
                ['t' => 'Tombol "Perpanjang deadline" membuka kembali tugas yang terlambat agar anak magang dapat mengirim hasilnya.', 'roles' => ['mentor', 'admin']],
                ['t' => 'Feedback dan nilai (0 sampai 100) tampil di kotak hijau. Catatan revisi tampil di kotak oranye.'],
                ['t' => 'Checklist: langkah kecil dalam satu tugas. Centang yang sudah selesai; persentasenya ikut tampil di kartu.'],
                ['t' => 'Link hasil dan Lampiran: hasil kerja yang dikirim bersama tugas.'],
                ['t' => 'Komentar: diskusi khusus tugas ini. Tekan Ctrl+Enter untuk mengirim.'],
                ['t' => 'Riwayat: siapa mengubah apa dan kapan pada tugas ini.'],
            ],
        ],
        [
            'id' => 'channel', 'icon' => 'fa-hashtag', 'title' => 'Channel (chat)',
            'items' => [
                ['t' => 'Tiap kelompok punya channel sendiri. Ada juga #pengumuman yang hanya dapat ditulisi mentor dan admin.'],
                ['t' => 'Enter untuk kirim, Shift+Enter untuk baris baru. Tulis **teks** untuk huruf tebal dan `teks` untuk kode.'],
                ['t' => 'Ketik @ untuk menyebut anggota (mereka mendapat notifikasi), atau # untuk merujuk tugas.'],
                ['t' => 'Sebutan @channel memberi tahu semua anggota sekaligus.', 'roles' => ['mentor', 'admin']],
                ['t' => 'Ikon klip melampirkan file (maks. 10 MB), ikon senyum menyisipkan emoji.'],
                ['t' => 'Arahkan kursor ke sebuah pesan untuk memberi reaksi, "Balas di thread", menyalin teks, atau mengedit dan menghapus pesan Anda sendiri.'],
                ['t' => 'Ikon kaca pembesar mencari pesan (minimal 2 huruf). Ikon pin menampilkan pesan yang disematkan.'],
                ['t' => 'Menu titik tiga di judul channel: "Bisukan channel" agar tidak mendapat notifikasi dari channel itu.'],
                ['t' => 'Anda dapat menyematkan pesan penting, menghapus pesan siapa pun, mengubah info channel, serta mengarsipkan atau memulihkan channel.', 'roles' => ['mentor', 'admin']],
            ],
        ],
        [
            'id' => 'sprint', 'icon' => 'fa-flag', 'title' => 'Mengelola sprint dan epic', 'roles' => ['mentor', 'admin'],
            'items' => [
                ['t' => 'Buat sprint: isi nama, deadline sprint, tujuan, ketua, dan daftar tugas. Tugas di sprint tanpa deadline sendiri akan berakhir bersama sprint.'],
                ['t' => 'Mulai sprint: tentukan durasi atau tanggal, lalu pilih Ketua (wajib). Hanya satu sprint yang dapat berjalan sekaligus.'],
                ['t' => 'Menu titik tiga pada sprint di Backlog berisi "Ubah" dan "Hapus". Hapus hanya untuk sprint yang belum dimulai; tugasnya kembali ke Backlog, tidak ikut terhapus.'],
                ['t' => 'Pindahkan tugas antara Backlog dan sprint lewat menu pada baris tugas di Backlog.'],
                ['t' => '"Selesaikan sprint" menanyakan ke mana tugas yang belum selesai dipindahkan.'],
                ['t' => 'Sprint yang sudah selesai dapat dibuka lagi lewat "Buka kembali", asalkan tidak ada sprint lain yang sedang berjalan.'],
                ['t' => 'Tombol Epic di Backlog untuk membuat, mengubah, dan menghapus epic. Epic dipilih saat membuat atau mengubah tugas.'],
            ],
        ],
        [
            'id' => 'ketua', 'icon' => 'fa-crown', 'title' => 'Tugas Ketua Sprint', 'roles' => ['intern'],
            'items' => [
                ['t' => 'Mentor menunjuk satu Ketua untuk tiap sprint. Bila Anda Ketua, tombol "Buat tugas" muncul di halaman kelompok.'],
                ['t' => 'Bagi tugas ke anggota lewat tombol Bagi atau avatar di kartu.'],
                ['t' => 'Anda dapat mengubah tugas di sprint yang Anda pimpin, dan menghapus tugas yang Anda buat sendiri.'],
                ['t' => 'Menilai hasil tetap tugas mentor. Ketua mengirim hasil ke Review seperti anggota lain.'],
            ],
        ],
        [
            'id' => 'pribadi', 'icon' => 'fa-user', 'title' => 'Space pribadi', 'roles' => ['intern', 'mentor'],
            'items' => [
                ['t' => 'Space pribadi berisi tugas khusus untuk satu anak magang, terpisah dari kelompok.'],
                ['t' => 'Tidak ada Backlog dan sprint. Tugas langsung tampil di Board dengan alur status yang sama: To Do, In Progress, Review, lalu Selesai atau Revisi.'],
                ['t' => 'Space pribadi dan workspace tim dibuat otomatis setelah admin menetapkan mentor.', 'roles' => ['intern']],
            ],
        ],
        [
            'id' => 'label', 'icon' => 'fa-tags', 'title' => 'Label',
            'items' => [
                ['t' => 'Label mengelompokkan tugas berdasarkan jenisnya, misalnya frontend atau dokumen. Label tampil di kartu dan dapat dipakai menyaring di tab List.'],
                ['t' => 'Pilih label saat membuat atau mengubah tugas.', 'roles' => ['mentor', 'admin']],
                ['t' => 'Klik "Kelola label" di form tugas untuk menambah, mengganti nama atau warna, dan menghapus label tim.', 'roles' => ['mentor', 'admin']],
            ],
        ],
        [
            'id' => 'kelompok', 'icon' => 'fa-sliders', 'title' => 'Mengelola kelompok', 'roles' => ['mentor', 'admin'],
            'items' => [
                ['t' => 'Tombol Kelola di bagian atas halaman kelompok berisi "Ganti nama" (sekaligus warna) dan "Tandai selesai magang".'],
                ['t' => '"Tambah anggota" hanya menawarkan anak bimbingan yang belum punya kelompok. Anggota tidak dapat dikeluarkan.', 'roles' => ['mentor']],
                ['t' => 'Anak bimbingan yang belum berkelompok juga dapat dimasukkan dari Dashboard lewat tombol "Masukkan".', 'roles' => ['mentor']],
                ['t' => '"Tandai selesai magang" memindahkan kelompok ke bagian Selesai magang dan membuatnya hanya baca. Tugas, diskusi, dan nilai tetap tersimpan.'],
            ],
        ],
        [
            'id' => 'rekap', 'icon' => 'fa-chart-pie', 'title' => 'Nilai, rekap, dan laporan',
            'items' => [
                ['t' => 'Di halaman Laporan Akhir, rekap tugas Workspace dapat disertakan, dan tombol "Isi ke kolom Kegiatan" menuliskan ringkasannya untuk Anda.', 'roles' => ['intern']],
                ['t' => 'Halaman detail anak magang dan laporan akhir memiliki tautan ke Rekap Tugas. Di sana klik "Cetak / Simpan PDF" untuk mencetak atau menyimpannya.', 'roles' => ['mentor', 'admin']],
                ['t' => 'Rekap Tugas berisi jumlah tugas selesai, persentase tepat waktu, serta nilai rata-rata, tertinggi, dan terendah.'],
                ['t' => 'Saat mengisi nilai sertifikat, nilai tugas Workspace ditampilkan sebagai bahan pertimbangan saja. Nilai sertifikat tetap Anda isi sendiri.', 'roles' => ['mentor']],
                ['t' => 'Nilai tugas terlihat oleh mentor saat menilai sertifikat sebagai bahan pertimbangan. Nilai sertifikat tetap ditentukan mentor.', 'roles' => ['intern']],
                ['t' => 'Halaman Monitoring menampilkan rekap tugas tiap kelompok.', 'roles' => ['admin']],
            ],
        ],
        [
            'id' => 'notifikasi', 'icon' => 'fa-bell', 'title' => 'Notifikasi dan tampilan',
            'items' => [
                ['t' => 'Ikon lonceng menampilkan pemberitahuan tugas baru, review, revisi, komentar, sebutan @, dan deadline. Klik untuk langsung membuka tugasnya.'],
                ['t' => 'Ikon gerigi di dalam lonceng membuka pengaturan notifikasi. Tersedia juga "tandai semua dibaca".'],
                ['t' => 'Ikon gerigi di pojok kanan atas untuk mode gelap dan saklar "Tutorial otomatis".'],
                ['t' => 'Tutorial dapat diulang kapan saja dari tab "Mulai cepat" di Panduan ini.'],
            ],
        ],
    ];
    $features = array_map(fn ($f) => [
        'id' => $f['id'], 'icon' => $f['icon'], 'title' => $f['title'],
        'items' => array_column($forRole($f['items']), 't'),
    ], $forRole($features));

    $terms = [
        ['id' => 'kelompok', 'term' => 'Kelompok', 'def' => 'Ruang kerja satu tim, dibuat oleh mentor, berisi anggota, tugas, dan chat.', 'analogy' => 'Seperti ruang kelas dengan papan tugasnya sendiri.'],
        ['id' => 'space-pribadi', 'term' => 'Space Pribadi', 'def' => 'Ruang kerja milik satu anak magang untuk tugas khusus dari mentor. Tidak memakai Backlog dan sprint.', 'analogy' => 'Seperti meja kerja pribadi di samping meja kelompok.'],
        ['id' => 'kanban', 'term' => 'Kanban / Board', 'def' => 'Papan tugas berkolom (To Do, In Progress, Review, Revisi, Selesai). Tugas berpindah kolom sesuai statusnya.', 'analogy' => 'Seperti papan tempel kertas: kertas dipindah dari "akan dikerjakan" ke "selesai".'],
        ['id' => 'list', 'term' => 'List', 'def' => 'Semua tugas dalam bentuk daftar yang bisa dicari dan disaring, termasuk yang tidak tampil di Board.', 'analogy' => 'Seperti daftar isi yang bisa dicari.'],
        ['id' => 'backlog', 'term' => 'Backlog', 'def' => 'Daftar tugas yang sudah dicatat tetapi belum dijadwalkan ke sprint.', 'analogy' => 'Seperti daftar belanja: dicatat dulu, dipilih nanti.'],
        ['id' => 'sprint', 'term' => 'Sprint', 'def' => 'Periode kerja pendek dengan target jelas (misalnya 2 minggu). Hanya tugas di sprint berjalan yang tampil di Board.', 'analogy' => 'Seperti satu babak pertandingan dengan target skor.'],
        ['id' => 'ketua-sprint', 'term' => 'Ketua Sprint', 'def' => 'Anak magang yang ditunjuk mentor untuk satu sprint. Ketua membagi tugas ke anggota dan dapat menambah tugas ke sprint yang ia pimpin.', 'analogy' => 'Seperti ketua kelompok dalam tugas kuliah.'],
        ['id' => 'epic', 'term' => 'Epic', 'def' => 'Kelompok tugas yang bertema sama, untuk melihat pekerjaan besar yang dipecah menjadi tugas kecil.', 'analogy' => 'Seperti satu bab dalam buku; tugas adalah halamannya.'],
        ['id' => 'label', 'term' => 'Label', 'def' => 'Penanda jenis tugas, misalnya frontend atau dokumen. Satu tugas boleh punya beberapa label, dan label dipakai untuk menyaring di List.', 'analogy' => 'Seperti stiker warna di map berkas.'],
        ['id' => 'story-point', 'term' => 'Story Point', 'def' => 'Angka estimasi seberapa berat sebuah tugas. Bukan jumlah jam.', 'analogy' => 'Seperti ukuran S, M, L pada baju.'],
        ['id' => 'checklist', 'term' => 'Checklist', 'def' => 'Daftar langkah kecil di dalam satu tugas. Persentase yang sudah dicentang tampil di kartu.', 'analogy' => 'Seperti daftar centang sebelum berangkat.'],
        ['id' => 'timeline', 'term' => 'Timeline', 'def' => 'Tampilan batang waktu tiap tugas dari mulai sampai deadline, dengan garis "hari ini". Tugas tanpa deadline tidak muncul di sini.', 'analogy' => 'Seperti jadwal proyek berbentuk batang.'],
        ['id' => 'deadline', 'term' => 'Deadline', 'def' => 'Batas waktu tugas. Tugas yang melewatinya ditandai terlambat dan hasilnya tidak bisa dikirim sampai mentor memperpanjang deadline.', 'analogy' => 'Seperti tanggal kumpul tugas kuliah.'],
        ['id' => 'review', 'term' => 'Review', 'def' => 'Tahap saat mentor memeriksa hasil kerja yang dikirim anak magang.', 'analogy' => 'Seperti dosen memeriksa draf sebelum disetujui.'],
        ['id' => 'revisi', 'term' => 'Revisi', 'def' => 'Hasil dikembalikan oleh mentor dengan catatan perbaikan, lalu dikerjakan ulang dan dikirim ke Review lagi.', 'analogy' => 'Seperti draf yang dicoret merah lalu diperbaiki.'],
        ['id' => 'burndown', 'term' => 'Burndown', 'def' => 'Grafik sisa pekerjaan dibanding rencana ideal di tab Laporan. Garis di bawah rencana berarti lebih cepat dari jadwal.', 'analogy' => 'Seperti indikator bensin: turun sesuai jadwal atau tertinggal.'],
        ['id' => 'velocity', 'term' => 'Velocity', 'def' => 'Jumlah story point yang diselesaikan dalam satu sprint. Dipakai untuk memperkirakan beban sprint berikutnya.', 'analogy' => 'Seperti kecepatan rata-rata saat berlari.'],
        ['id' => 'channel', 'term' => 'Channel', 'def' => 'Ruang chat kelompok. #pengumuman berlaku untuk semua dan hanya ditulisi mentor dan admin.', 'analogy' => 'Seperti grup chat kelas.'],
        ['id' => 'thread', 'term' => 'Thread', 'def' => 'Balasan yang dikumpulkan di bawah satu pesan agar percakapan utama tetap rapi.', 'analogy' => 'Seperti balasan berantai di bawah satu komentar.'],
        ['id' => 'mention', 'term' => 'Mention (@)', 'def' => 'Menyebut anggota dengan mengetik @nama di chat. Orang yang disebut mendapat notifikasi.', 'analogy' => 'Seperti memanggil nama seseorang di tengah rapat.'],
        ['id' => 'notifikasi', 'term' => 'Notifikasi', 'def' => 'Pemberitahuan tugas baru, review, revisi, komentar, dan deadline. Muncul sebagai pop-up, di ikon lonceng, dan di judul tab browser.', 'analogy' => 'Seperti pesan masuk di ponsel.'],
        ['id' => 'selesai-magang', 'term' => 'Selesai Magang', 'def' => 'Status kelompok yang sudah ditutup mentor. Isinya hanya bisa dibaca; tugas, diskusi, dan nilai tetap tersimpan.', 'analogy' => 'Seperti buku rapor yang sudah dijilid.'],
    ];

    $faqs = array_map(fn ($f) => ['q' => $f['q'], 'a' => $f['a']], $forRole([
        ['roles' => ['intern'], 'q' => 'Kenapa saya tidak bisa memindahkan kartu ke Selesai?', 'a' => 'Status Selesai dan Revisi hanya bisa diberikan mentor. Anak magang memindahkan kartu sampai Review, lalu mentor memutuskan.'],
        ['roles' => ['intern'], 'q' => 'Tombol "Kirim untuk review" tidak muncul.', 'a' => 'Deadline tugas sudah lewat, sehingga hasilnya terkunci. Hubungi mentor untuk memperpanjang deadline, lalu kirim lagi.'],
        ['roles' => ['intern'], 'q' => 'Kenapa saya tidak bisa membuat tugas?', 'a' => 'Tugas dibuat oleh mentor. Anak magang hanya dapat menambah tugas bila ditunjuk sebagai Ketua Sprint, dan hanya ke sprint yang ia pimpin.'],
        ['roles' => ['intern'], 'q' => 'Apakah nilai tugas masuk ke sertifikat?', 'a' => 'Nilai tugas ditampilkan ke mentor sebagai bahan pertimbangan saat menilai sertifikat. Nilai sertifikat tetap ditentukan mentor.'],
        ['roles' => ['intern', 'mentor'], 'q' => 'Kartu saya tidak muncul di Board.', 'a' => 'Board hanya menampilkan tugas di sprint yang sedang berjalan. Tugas lain ada di tab Backlog sampai sprint-nya dimulai, dan semua tugas dapat dicari di tab List.'],
        ['roles' => ['admin'], 'q' => 'Kenapa tidak ada tombol "Buat kelompok"?', 'a' => 'Kelompok dibuat oleh mentor untuk anak bimbingannya. Admin memantau semua kelompok.'],
        ['roles' => ['mentor'], 'q' => 'Bisakah anggota dikeluarkan dari kelompok?', 'a' => 'Saat ini anggota tidak dapat dikeluarkan. Anggota baru dapat ditambahkan lewat menu Kelola di halaman kelompok.'],
        ['roles' => ['mentor'], 'q' => 'Apa yang terjadi pada tugas yang belum selesai saat sprint ditutup?', 'a' => 'Anda memilih tujuannya: kembali ke Backlog atau dipindah ke sprint berikutnya.'],
        ['roles' => ['mentor', 'admin'], 'q' => 'Kenapa sprint tidak bisa dihapus?', 'a' => 'Hanya sprint yang belum dimulai yang dapat dihapus. Sprint yang sedang berjalan diselesaikan lewat "Selesaikan sprint".'],
        ['roles' => ['mentor', 'admin'], 'q' => 'Sprint sudah ditutup, tetapi masih ada yang perlu dikerjakan.', 'a' => 'Klik "Buka kembali" pada sprint tersebut di Backlog. Ini hanya bisa dilakukan bila tidak ada sprint lain yang sedang berjalan.'],
        ['roles' => ['mentor', 'admin'], 'q' => 'Anak magang tidak bisa mengirim tugas yang terlambat.', 'a' => 'Buka tugasnya lalu klik "Perpanjang deadline". Setelah itu anak magang dapat mengirim hasilnya ke Review.'],
        ['roles' => ['mentor', 'admin'], 'q' => 'Apa yang terjadi saat kelompok ditandai selesai magang?', 'a' => 'Kelompok pindah ke bagian Selesai magang dan menjadi hanya baca. Tugas, diskusi, dan nilai tetap tersimpan dan masih dapat dibuka.'],
        ['roles' => ['mentor', 'admin'], 'q' => 'Di mana mencetak rekap tugas seorang anak magang?', 'a' => 'Buka halaman detail anak magang atau laporan akhirnya, lalu klik tautan rekap tugas. Di halaman rekap, klik "Cetak / Simpan PDF".'],
        ['q' => 'Apa beda Backlog dan Board?', 'a' => 'Backlog adalah tempat merencanakan tugas ke sprint. Board adalah tempat mengerjakan tugas sprint yang sedang berjalan.'],
        ['q' => 'Tugas tanpa deadline tampil di mana?', 'a' => 'Di Backlog. Tugas yang sudah masuk sprint mengikuti tanggal akhir sprint, dan baru tampil di Timeline bila punya deadline.'],
        ['q' => 'Bagaimana membagikan tugas ke orang lain?', 'a' => 'Buka detail tugas, klik menu titik tiga, lalu "Salin link". Di chat, Anda juga bisa mengetik # untuk merujuk tugas.'],
        ['q' => 'Channel terlalu ramai. Bisakah notifikasinya dimatikan?', 'a' => 'Bisa. Buka channel, klik menu titik tiga di judulnya, lalu "Bisukan channel". Pesan tetap bisa dibaca kapan saja.'],
        ['q' => 'Di mana saya melihat pemberitahuan?', 'a' => 'Di ikon lonceng pojok kanan atas, juga sebagai pop-up saat ada yang baru dan angka di judul tab browser. Pengaturannya ada di ikon gerigi dalam lonceng.'],
        ['q' => 'Bagaimana mengganti ke mode gelap?', 'a' => 'Klik ikon gerigi di pojok kanan atas, lalu pilih mode gelap.'],
    ]));
@endphp

<div x-data="{
        tab: 'mulai',
        q: '',
        topic: null,
        faq: null,
        feat: null,
        steps: @js($steps),
        features: @js($features),
        terms: @js($terms),
        faqs: @js($faqs),
        get shownTerms() {
            const q = this.q.trim().toLowerCase();
            return q ? this.terms.filter((t) => (t.term + ' ' + t.def + ' ' + t.analogy).toLowerCase().includes(q)) : this.terms;
        },
        show(d) {
            if (d.tab) this.tab = d.tab;
            if (d.topic) { this.tab = 'kamus'; this.q = ''; this.topic = d.topic; }
            if (d.feature) { this.tab = 'fitur'; this.feat = d.feature; }
            this.$dispatch('ws-open-modal', 'ws-guide');
            const target = d.topic ? 'ws-guide-term-' + d.topic : (d.feature ? 'ws-guide-feat-' + d.feature : null);
            if (target) setTimeout(() => {
                const el = document.getElementById(target);
                if (el) el.scrollIntoView({ block: d.topic ? 'center' : 'start', behavior: 'smooth' });
            }, 360);
        },
     }"
     x-on:ws-guide-open.window="show($event.detail || {})">
    <x-workspace.slide-over name="ws-guide">
        <x-slot:header>
            <h2 id="ws-slideover-title-ws-guide" class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <span class="ws-guide__ico"><i class="fas fa-circle-question"></i></span> Panduan penggunaan
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $isMentor ? 'Untuk mentor' : ($isIntern ? 'Untuk anak magang' : 'Untuk admin') }} · Workspace
            </p>
        </x-slot:header>

        <div class="ws-guide">
            <div class="ws-guide__tabs" role="tablist" aria-label="Bagian panduan">
                <button type="button" role="tab" :aria-selected="(tab === 'mulai').toString()" :class="tab === 'mulai' && 'is-active'" x-on:click="tab = 'mulai'">Mulai cepat</button>
                <button type="button" role="tab" :aria-selected="(tab === 'alur').toString()" :class="tab === 'alur' && 'is-active'" x-on:click="tab = 'alur'">Alur kerja</button>
                <button type="button" role="tab" :aria-selected="(tab === 'fitur').toString()" :class="tab === 'fitur' && 'is-active'" x-on:click="tab = 'fitur'">Fitur</button>
                <button type="button" role="tab" :aria-selected="(tab === 'kamus').toString()" :class="tab === 'kamus' && 'is-active'" x-on:click="tab = 'kamus'">Kamus istilah</button>
                <button type="button" role="tab" :aria-selected="(tab === 'faq').toString()" :class="tab === 'faq' && 'is-active'" x-on:click="tab = 'faq'">FAQ</button>
            </div>

            {{-- Mulai cepat --}}
            <div x-show="tab === 'mulai'" role="tabpanel">
                <p class="ws-guide__lead">Langkah dari awal sampai selesai. Ikuti urutannya.</p>
                <ol class="ws-guide__steps">
                    <template x-for="(s, i) in steps" :key="i">
                        <li>
                            <span class="ws-guide__num" x-text="i + 1"></span>
                            <div>
                                <p class="ws-guide__step-title" x-text="s[0]"></p>
                                <p class="ws-guide__step-text" x-text="s[1]"></p>
                            </div>
                        </li>
                    </template>
                </ol>
                <div class="ws-guide__replay">
                    <p class="ws-guide__lead">Ulangi tutorial</p>
                    <div class="ws-guide__replay-btns">
                        <button type="button" x-on:click="$dispatch('ws-tour-start', { id: 'dasar' })"><i class="fas fa-play"></i> Tutorial dasar</button>
                        <button type="button" x-on:click="$dispatch('ws-tour-start', { id: 'halaman' })"><i class="fas fa-location-dot"></i> Tutorial halaman ini</button>
                    </div>
                </div>
                <p class="ws-guide__note">
                    <i class="fas fa-lightbulb"></i>
                    <span>Ingin tahu fungsi sebuah tombol atau tab? Buka tab <button type="button" x-on:click="tab = 'fitur'">Fitur</button>. Bingung dengan istilah? Buka <button type="button" x-on:click="tab = 'kamus'">Kamus istilah</button> atau klik ikon <span class="ws-help ws-help--inline">?</span> di samping judul.</span>
                </p>
            </div>

            {{-- Alur kerja --}}
            <div x-show="tab === 'alur'" x-cloak role="tabpanel">
                <p class="ws-guide__lead">Perjalanan sebuah tugas dari dibuat sampai dinilai.</p>
                <div class="ws-guide__flow">
                    <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#64748b;--b:#f1f5f9">To Do</span><p><b>Tugas baru</b> menunggu dikerjakan.</p></div>
                    <i class="fas fa-arrow-down ws-guide__arrow" aria-hidden="true"></i>
                    <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#2563eb;--b:#eff6ff">In Progress</span><p><b>Anak magang</b> sedang mengerjakan.</p></div>
                    <i class="fas fa-arrow-down ws-guide__arrow" aria-hidden="true"></i>
                    <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#7c3aed;--b:#f5f3ff">Review</span><p>Hasil dikirim. <b>Mentor</b> memeriksa.</p></div>
                    <div class="ws-guide__fork">
                        <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#059669;--b:#ecfdf5">Selesai</span><p><b>Disetujui</b> dan diberi nilai 0 sampai 100.</p></div>
                        <div class="ws-guide__flow-row"><span class="ws-guide__pill" style="--c:#ea580c;--b:#fff7ed">Revisi</span><p><b>Dikembalikan</b> dengan catatan, lalu kembali ke In Progress.</p></div>
                    </div>
                </div>
                <p class="ws-guide__note">
                    <i class="fas fa-user-lock"></i>
                    <span>Anak magang hanya dapat memindahkan kartu ke To Do, In Progress, dan Review. Selesai dan Revisi hanya dapat diberikan mentor.</span>
                </p>
                <p class="ws-guide__note">
                    <i class="fas fa-clock"></i>
                    <span>Bila deadline lewat sebelum hasil dikirim, tugas terkunci dan tidak dapat dikirim ke Review sampai mentor memperpanjang deadline.</span>
                </p>
                <p class="ws-guide__lead" style="margin-top:18px">Persiapan sebelum mengerjakan</p>
                <ol class="ws-guide__mini">
                    <li><b>Mentor</b> membuat kelompok dan memilih anggota.</li>
                    <li><b>Mentor</b> menyusun tugas di Backlog dan membuat sprint.</li>
                    <li><b>Mentor</b> memulai sprint dan menunjuk Ketua.</li>
                    <li><b>Ketua</b> membagi tugas ke anggota kelompok.</li>
                </ol>
                <p class="ws-guide__lead" style="margin-top:18px">Setelah sprint dan magang selesai</p>
                <ol class="ws-guide__mini">
                    <li><b>Mentor</b> menyelesaikan sprint dan memindahkan tugas yang tersisa.</li>
                    <li><b>Mentor</b> menandai kelompok selesai magang; kelompok menjadi hanya baca.</li>
                    <li><b>Rekap tugas</b> dan nilai dipakai di laporan akhir dan penilaian sertifikat.</li>
                </ol>
            </div>

            {{-- Fitur --}}
            <div x-show="tab === 'fitur'" x-cloak role="tabpanel">
                <p class="ws-guide__lead">Fungsi tiap bagian Workspace. Klik judul untuk membuka.</p>
                <div class="ws-guide__faqs">
                    <template x-for="f in features" :key="f.id">
                        <div class="ws-guide__faq ws-guide__feat" :id="'ws-guide-feat-' + f.id" :class="feat === f.id && 'is-open'">
                            <button type="button" x-on:click="feat = feat === f.id ? null : f.id" :aria-expanded="(feat === f.id).toString()">
                                <span class="ws-guide__feat-title"><i class="fas" :class="f.icon" aria-hidden="true"></i><span x-text="f.title"></span></span>
                                <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                            <ul x-show="feat === f.id" x-transition.opacity.duration.150ms>
                                <template x-for="(item, i) in f.items" :key="i">
                                    <li x-text="item"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Kamus --}}
            <div x-show="tab === 'kamus'" x-cloak role="tabpanel">
                <label class="ws-guide__search">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" x-model="q" placeholder="Cari istilah, mis. sprint" aria-label="Cari istilah">
                </label>
                <div class="ws-guide__terms">
                    <template x-for="t in shownTerms" :key="t.id">
                        <article class="ws-guide__term" :id="'ws-guide-term-' + t.id" :class="topic === t.id && 'is-focus'">
                            <h3 x-text="t.term"></h3>
                            <p x-text="t.def"></p>
                            <p class="ws-guide__analogy"><i class="fas fa-quote-left" aria-hidden="true"></i> <span x-text="t.analogy"></span></p>
                        </article>
                    </template>
                    <p class="ws-guide__empty" x-show="!shownTerms.length">Tidak ada istilah yang cocok.</p>
                </div>
            </div>

            {{-- FAQ --}}
            <div x-show="tab === 'faq'" x-cloak role="tabpanel">
                <div class="ws-guide__faqs">
                    <template x-for="(f, i) in faqs" :key="i">
                        <div class="ws-guide__faq" :class="faq === i && 'is-open'">
                            <button type="button" x-on:click="faq = faq === i ? null : i" :aria-expanded="(faq === i).toString()">
                                <span x-text="f.q"></span>
                                <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                            <p x-show="faq === i" x-transition.opacity.duration.150ms x-text="f.a"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </x-workspace.slide-over>
</div>
