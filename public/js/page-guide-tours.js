/*
 * Isi tutorial tiap halaman menu sidebar, untuk semua peran (admin, super admin, mentor, peserta, institusi, industri).
 * Dimuat malas oleh public/js/page-guide.js saat Panduan dibuka; penjalannya ada di page-guide-ui.js.
 *
 * Satu tutorial: T(id, [regex path], judul, ringkasan, [langkah]).  Satu langkah: S(el, judul, teks, sisi?, peran?).
 *   el      penyorot: selector CSS | [selector, ...] | btn('teks tombol/tautan') | card('judul kartu'); null = di tengah layar.
 *   peran   (opsional) daftar peran yang melihat langkah ini: 'mentor' | 'intern' | 'admin' | 'institusi' | 'industri'.
 * Langkah yang elemennya tidak ada di halaman (mis. tabel kosong) dilewati otomatis, tetapi tetap tampil di "Ringkasan cara pakai".
 * Menambah halaman baru: tambahkan satu T(...) di bawah. Halaman tanpa T(...) memakai tutorial umum.
 */
(function () {
    'use strict';

    const HERO = ['main .hero-strip', 'main .hero-header', 'main [class*="hero"]', 'main h1'];
    const FILTER = 'main form[method="get" i]';
    const TABLE = 'main table';
    const PAGER = ['main nav[aria-label*="agination" i]', 'main .pagination', 'main nav[role="navigation"]'];
    const btn = (t) => ({ text: t, in: 'main a, main button' });
    const card = (t) => ({ card: t });
    const S = (el, title, text, side, roles) => { const s = { el, title, text }; if (side) s.side = side; if (roles) s.roles = roles; return s; };

    const tours = [];
    const T = (id, match, title, about, steps) => tours.push({ id, match, title, about, steps });

    const HELP_FILTER = 'Isi kolom pencarian atau pilih saringan untuk mempersempit data. Hasil diperbarui otomatis.';
    const HELP_PAGER = 'Bila datanya banyak, pindah antarhalaman dari sini.';

    // =====================================================================
    //  UMUM (semua peran)
    // =====================================================================

    T('chat', ['^/chat-publik$'], 'Chat Publik', 'Ruang obrolan bersama untuk semua pengguna yang sudah login: mentor, peserta, institusi, industri, dan admin.', [
        S('.pcl__top', 'Cari orang atau pesan', 'Ketik nama untuk menemukan seseorang, atau kata untuk mencari pesan. Hasilnya bisa diklik untuk melompat ke pesannya.', 'right'),
        S('#pc-chips', 'Saring menurut peran', 'Tampilkan hanya mentor, peserta, institusi, industri, atau admin. Klik "Semua" untuk kembali.', 'right'),
        S('#pc-people', 'Orang di ruang ini', 'Daftar yang pernah berpesan, lengkap dengan peran dan pesan terakhirnya. Klik satu nama untuk melompat ke pesannya.', 'right'),
        S('.pcp__head', 'Ruang Bersama', 'Menampilkan jumlah yang sedang online dan beberapa orang yang terakhir berpesan.', 'bottom'),
        S('#pc-pinwrap', 'Pengumuman tersemat', 'Pengumuman penting dari mentor tampil di sini. Klik bilahnya untuk melompat ke pesan aslinya, atau panah di kanan untuk daftar lengkap dengan sisa waktunya.', 'bottom'),
        S('#pc-list', 'Percakapan', 'Pesan terbaru ada di bawah. Pesan yang panjang dilipat: klik "Selengkapnya" untuk membacanya utuh. Tombol panah bawah membawa Anda kembali ke pesan terbaru.', 'left'),
        S('#pc-compose', 'Menulis pesan', 'Ketik pesan di kotak. Tombol Enter membuat baris baru; pesan terkirim saat Anda menekan tombol Kirim. Ikon senyum menyisipkan emoji.', 'top'),
        S('#pc-ann', 'Kirim sebagai pengumuman', 'Khusus mentor: aktifkan megafon, pilih 7 atau 30 hari, lalu kirim. Pesan akan disematkan di bilah pengumuman (maksimal 3 aktif) dan bisa Anda hapus kapan saja.', 'top', ['mentor']),
    ]);

    T('ws-list', ['^/workspace$'], 'Workspace: daftar kelompok', 'Semua kelompok kerja dan tugasnya. Dari sini Anda masuk ke kelompok untuk bekerja.', [
        S(btn('Buat kelompok'), 'Buat kelompok', 'Mulai dari sini: beri nama, pilih warna, dan pilih anak bimbingan sebagai anggota.', 'bottom', ['mentor']),
        S('input[type="search"][name="q"]', 'Cari kelompok', 'Cari berdasarkan nama kelompok atau anggota, lalu saring per unit atau mentor.', 'bottom', ['admin']),
        S('a.ws-card', 'Kartu kelompok', 'Tiap kartu menampilkan progres tugas, tugas yang menunggu review atau terlambat, dan anggotanya. Klik "Buka" untuk masuk.', 'top'),
    ]);

    T('ws-detail', ['^/workspace/\\d+$'], 'Workspace: halaman kelompok', 'Tempat merencanakan, mengerjakan, dan memantau tugas satu kelompok.', [
        S('.ws-stats', 'Ringkasan tugas', 'Jumlah tugas per status, termasuk yang terlambat.', 'bottom'),
        S('.ws-tabs', 'Bagian kelompok', 'Backlog untuk merencanakan, Board untuk mengerjakan, Timeline untuk jadwal, Laporan untuk grafik, dan Channel untuk chat kelompok.', 'bottom'),
        S('.ws-sprint-bar', 'Sprint berjalan', 'Periode kerja saat ini beserta targetnya. Klik ikon ? di sampingnya untuk penjelasan istilah.', 'bottom'),
        S('.ws-task-card', 'Kartu tugas', 'Klik kartu untuk melihat detail. Mentor menilai atau meminta revisi dari sini.', 'right', ['mentor', 'admin']),
        S('.ws-task-card', 'Kartu tugas', 'Geser kartu ke kolom berikutnya saat dikerjakan, lalu pindahkan ke Review bila sudah selesai. Klik kartu untuk detail.', 'right', ['intern']),
        S(btn('Buat tugas'), 'Buat tugas', 'Tambahkan tugas baru dari sini.', 'left', ['mentor', 'intern']),
    ]);

    // =====================================================================
    //  MENTOR
    // =====================================================================

    T('mentor-dashboard', ['^/mentor/dashboard$'], 'Dashboard Mentor', 'Ringkasan pekerjaan Anda hari ini: tugas yang perlu dinilai, yang terlambat, dan kehadiran anak bimbingan.', [
        S(card('Ringkasan bimbingan'), 'Ringkasan hari ini', 'Jumlah anak magang, yang hadir, izin atau sakit, dan yang belum hadir hari ini.'),
        S(card('Perlu Review'), 'Perlu review', 'Tugas yang menunggu penilaian Anda. Klik Review untuk membukanya, lalu setujui dan beri nilai, atau minta revisi.'),
        S(card('Tugas Terlambat'), 'Tugas terlambat', 'Tugas yang melewati deadline. Segera ditindaklanjuti atau perpanjang deadline-nya.'),
        S(card('Deadline 7 Hari'), 'Deadline 7 hari ke depan', 'Tugas yang jatuh tempo dalam seminggu, supaya tidak ada yang terlewat.'),
        S(card('Progres Kelompok'), 'Progres kelompok', 'Jumlah tugas selesai dan terlambat tiap kelompok. Klik kelompok untuk membukanya.'),
        S(card('Daftar Anak Magang'), 'Daftar anak magang', 'Anak bimbingan Anda. Pindah tab Aktif atau Alumni untuk menyaringnya.'),
        S(card('Absensi Hari Ini'), 'Absensi hari ini', 'Status kehadiran tiap anak bimbingan hari ini beserta jam check in dan check out.'),
        S(card('Top Mikro Skill'), 'Top mikro skill', 'Peringkat anak bimbingan berdasarkan mikro skill yang diselesaikan. "Lihat Selengkapnya" membuka klasemen lengkap.'),
    ]);

    T('mentor-interns', ['^/mentor/interns$'], 'Anak Bimbingan', 'Daftar anak magang yang Anda bimbing beserta kelompok dan perkembangannya.', [
        S(HERO, 'Anak magang bimbingan Anda', 'Halaman ini mengumpulkan semua anak magang di bawah bimbingan Anda.'),
        S(FILTER, 'Cari dan saring', 'Cari berdasarkan nama, atau saring menurut status (aktif atau alumni).'),
        S(TABLE, 'Tabel anak magang', 'Tiap baris menampilkan kelompok, institusi, serta jumlah logbook, absensi, dan mikro skill. Klik nama untuk membuka detail anak magang.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('mentor-attendance', ['^/mentor/attendance$'], 'Absensi Anak Bimbingan', 'Memantau kehadiran harian anak bimbingan Anda.', [
        S(HERO, 'Pantau kehadiran', 'Lihat siapa yang hadir, izin, sakit, atau belum absen.'),
        S(FILTER, 'Saring data absensi', 'Pilih anak magang, rentang tanggal, dan status untuk mempersempit data.'),
        S(TABLE, 'Data absensi', 'Menampilkan tanggal, status, jam check in dan check out, serta foto bukti kehadiran. Yang belum absen ditandai "Belum Absen".'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('mentor-logbook', ['^/mentor/logbook$'], 'Logbook Anak Bimbingan', 'Membaca dan menyetujui catatan harian anak bimbingan.', [
        S(HERO, 'Logbook anak bimbingan', 'Catatan aktivitas harian yang ditulis anak magang.'),
        S(FILTER, 'Saring logbook', 'Pilih anak magang dan rentang tanggal untuk mencari logbook tertentu.'),
        S(TABLE, 'Daftar logbook', 'Di kolom Aksi, klik Lihat untuk membaca isinya, dan tombol centang untuk menyetujui logbook yang sudah sesuai. Logbook yang disetujui diberi tanda Approved.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('mentor-report', ['^/mentor/report$'], 'Laporan Akhir Anak Bimbingan', 'Memeriksa dan menilai laporan akhir yang dikirim anak bimbingan.', [
        S(HERO, 'Laporan akhir', 'Laporan yang dikirim anak bimbingan di akhir masa magang.'),
        S(FILTER, 'Saring laporan', 'Saring menurut anak magang, status laporan, dan yang perlu revisi.'),
        S(TABLE, 'Daftar laporan', 'Kolom File membuka berkas laporan. Nilai dan Revisi menunjukkan hasil penilaian. Buka Detail untuk membaca laporan, memberi nilai 0 sampai 100, lalu menyetujui atau meminta revisi dengan catatan.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('mentor-microskill', ['^/mentor/microskill$'], 'Mikro Skill', 'Memantau mikro skill (kursus singkat Digitalent) yang dikerjakan anak bimbingan.', [
        S(HERO, 'Mikro skill', 'Kumpulan mikro skill yang dikerjakan anak magang untuk menambah keterampilan.'),
        S(FILTER, 'Cari mikro skill', 'Ketik judul mikro skill untuk menemukannya.'),
        S({ text: 'Detail', in: 'main a' }, 'Detail mikro skill', 'Klik Detail untuk melihat rinciannya. Tautan di kartu membuka materinya di Digitalent Komdigi.', 'left'),
    ]);

    // =====================================================================
    //  PESERTA MAGANG (intern)
    // =====================================================================

    T('intern-dashboard', ['^/intern/dashboard$'], 'Dashboard Peserta Magang', 'Titik awal harian Anda: tugas, absensi, dan pintasan ke menu yang sering dipakai.', [
        S(card('Tugas Saya'), 'Tugas saya', 'Tugas yang sedang Anda kerjakan beserta statusnya. "Buka board" membawa Anda ke papan tugas.'),
        S(card('Pesan belum dibaca'), 'Pesan belum dibaca', 'Pesan baru dari kelompok Anda. Klik untuk langsung membukanya.'),
        S(card('Status Absensi Hari Ini'), 'Absensi hari ini', 'Menunjukkan apakah Anda sudah absen hari ini. Klik "Absensi Sekarang" bila belum.'),
        S(card('Aksi Cepat'), 'Aksi cepat', 'Pintasan ke Absensi, Logbook, Workspace, Sharing Session, Mikro Skill, dan Laporan Akhir.'),
        S(card('Distribusi Kehadiran'), 'Distribusi kehadiran', 'Grafik kehadiran Anda: hadir, izin, sakit, dan tidak hadir.'),
        S(card('Leaderboard Mikro Skill'), 'Leaderboard mikro skill', 'Peringkat peserta berdasarkan mikro skill yang diselesaikan. "Lihat Klasemen Lengkap" membuka semuanya.'),
        S(card('Informasi Bimbingan'), 'Informasi bimbingan', 'Mentor dan kelompok yang membimbing Anda.'),
    ]);

    T('intern-attendance', ['^/intern/attendance$'], 'Absensi', 'Mencatat kehadiran harian Anda dan melihat riwayatnya.', [
        S(card('Kehadiran'), 'Rekap kehadiran', 'Jumlah hari hadir, izin, sakit, dan tidak hadir Anda.'),
        S([btn('Absensi Baru'), btn('Buat Absensi')], 'Mulai absen', 'Klik untuk absen. Pilih Hadir, Izin, atau Sakit, ambil foto sebagai bukti kehadiran (izin atau sakit bisa melampirkan dokumen pendukung), lalu klik Simpan Absensi.', 'left'),
        S(TABLE, 'Riwayat absensi', 'Menampilkan jam check in dan check out beserta fotonya, keterangan, dan status dokumen. Saat pulang, lakukan Check Out.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('intern-logbook', ['^/intern/logbook$'], 'Logbook', 'Catatan aktivitas harian magang Anda.', [
        S(btn('Tambah Logbook'), 'Tambah logbook', 'Catat aktivitas hari ini: tulis uraiannya dan, bila ada, lampirkan foto dokumentasi, lalu klik Simpan Logbook.', 'left'),
        S(TABLE, 'Riwayat logbook', 'Daftar logbook yang sudah Anda isi. Gunakan kolom Aksi untuk membuka atau mengelola tiap logbook.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('intern-sharing', ['^/intern/sharing-session$'], 'Sharing Session', 'Jadwal dan materi sesi berbagi pengetahuan.', [
        S(FILTER, 'Saring jadwal', 'Pilih jenis atau peran narasumber untuk menyaring sesi yang tampil.'),
        S('main', 'Daftar sesi', 'Tiap sesi menampilkan jadwal, narasumber, dan lokasi. Materi dan form evaluasi muncul setelah tersedia, jadi lihat kembali sesudah sesi berlangsung.', 'top'),
    ]);

    T('intern-report', ['^/intern/report$'], 'Laporan Akhir', 'Mengunggah laporan akhir magang dan mengikuti hasil penilaiannya.', [
        S(card('Detail Laporan Akhir'), 'Status laporan', 'Menampilkan tahap laporan Anda: Upload, Review mentor, lalu Disetujui, beserta nilainya.'),
        S(card('Berkas Laporan Akhir'), 'Unggah laporan', 'Unggah berkas laporan akhir di sini. Bila mentor meminta revisi, perbaiki lalu unggah ulang.'),
        S(card('Rekap Tugas Workspace'), 'Rekap tugas Workspace', 'Ringkasan tugas yang Anda kerjakan di Workspace. "Lihat & cetak" membuka rekap yang bisa dicetak.'),
        S(card('Berikan Testimoni'), 'Testimoni', 'Tuliskan kesan dan pengalaman magang Anda, lalu kirim.'),
    ]);

    T('intern-microskill', ['^/intern/microskill$'], 'Mikro Skill', 'Mengerjakan mikro skill (kursus singkat Digitalent) dan mengirim buktinya.', [
        S(FILTER, 'Cari mikro skill', 'Ketik judul untuk menemukan mikro skill.'),
        S(btn('Buka Digitalent'), 'Kerjakan di Digitalent', 'Mikro skill dikerjakan di situs Digitalent Komdigi. Klik tombol ini untuk membukanya.', 'left'),
        S(btn('Upload Bukti'), 'Unggah bukti', 'Setelah selesai mengerjakan, klik Upload Bukti dan lampirkan bukti penyelesaiannya.', 'left'),
        S(TABLE, 'Mikro skill selesai', 'Daftar mikro skill yang sudah Anda kirimi bukti, beserta waktu pengiriman.'),
    ]);

    // =====================================================================
    //  ADMIN dan SUPER ADMIN
    // =====================================================================

    T('admin-dashboard', ['^/admin/dashboard$'], 'Dashboard Admin', 'Gambaran cepat seluruh peserta magang dan pintasan ke pekerjaan admin.', [
        S(card('Peserta Aktif'), 'Ringkasan peserta', 'Jumlah peserta aktif, yang hadir, izin, sakit, absen, serta total mikro skill.'),
        S(card('Absensi Hari Ini'), 'Absensi hari ini', 'Status kehadiran tiap peserta hari ini beserta jam dan foto absennya.'),
        S(card('Aksi Cepat'), 'Aksi cepat', 'Pintasan ke Rekap Absensi, Data Peserta Magang, Pengajuan Peserta, dan Leaderboard Mikro Skill.'),
        S(card('Distribusi Kehadiran'), 'Distribusi kehadiran', 'Grafik perbandingan hadir, izin, sakit, dan tidak hadir.'),
        S(card('Leaderboard Mikro Skill'), 'Leaderboard mikro skill', 'Peringkat peserta berdasarkan mikro skill yang diselesaikan.'),
    ]);

    T('admin-accounts', ['^/admin/accounts$'], 'Kelola Akun Admin', 'Mengatur akun admin dan hak aksesnya. Hanya tersedia untuk Super Admin.', [
        S(btn('Tambah Admin'), 'Tambah admin', 'Buat akun admin baru dan tentukan hak aksesnya.', 'left'),
        S(FILTER, 'Cari akun', 'Cari akun admin berdasarkan nama atau email.'),
        S(TABLE, 'Daftar admin', 'Kolom Akses menunjukkan hak akses tiap admin. Gunakan kolom Aksi untuk mengubah atau menghapus akun.'),
    ]);

    T('admin-mentor', ['^/admin/mentor$'], 'Kelola Mentor', 'Mengelola akun mentor pembimbing peserta magang.', [
        S(btn('Tambah Mentor'), 'Tambah mentor', 'Daftarkan mentor baru beserta tim dan jabatannya.', 'left'),
        S(TABLE, 'Daftar mentor', 'Menampilkan nama, email, tim, jabatan, telepon, dan status. Gunakan kolom Aksi untuk mengubah atau menghapus mentor.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('admin-mitra', ['^/admin/mitra$'], 'Kelola Mitra', 'Mengelola mitra magang: institusi pendidikan dan industri.', [
        S(btn('Tambah Mitra'), 'Tambah mitra', 'Daftarkan institusi atau industri baru sebagai mitra.', 'left'),
        S(FILTER, 'Cari dan saring', 'Cari nama mitra dan saring menurut jenisnya (institusi atau industri).'),
        S(TABLE, 'Daftar mitra', 'Menampilkan jenis, kategori, kontak, dan status tiap mitra. Gunakan kolom Aksi untuk mengubah atau menghapus.'),
    ]);

    T('admin-intern', ['^/admin/intern$'], 'Kelola Peserta Magang', 'Mengelola data peserta magang aktif dan alumni.', [
        S(btn('Tambah Peserta'), 'Tambah peserta', 'Daftarkan peserta magang baru beserta institusi, tim, dan mentornya.', 'left'),
        S(FILTER, 'Cari dan saring', 'Cari nama, lalu saring menurut tim atau mentor.'),
        S(TABLE, 'Peserta magang', 'Tabel pertama berisi peserta aktif, tabel berikutnya data alumni. Gunakan kolom Aksi untuk melihat, mengubah, atau menghapus data peserta.'),
    ]);

    T('admin-team', ['^/admin/team$'], 'Kelola Tim Kerja/Bagian', 'Mengelola daftar tim atau bagian tempat mentor dan peserta bertugas.', [
        S(btn('Tambah Tim'), 'Tambah tim', 'Tambahkan tim atau bagian kerja baru.', 'left'),
        S(TABLE, 'Daftar tim', 'Gunakan kolom Aksi untuk mengubah nama atau menghapus tim.'),
    ]);

    T('admin-lowongan', ['^/admin/lowongan$'], 'Manajemen Lowongan Magang', 'Mengelola lowongan magang yang tersedia bagi institusi.', [
        S(btn('Tambah Lowongan'), 'Tambah lowongan', 'Buat lowongan magang baru: posisi, divisi, kuota, dan tanggalnya.', 'left'),
        S(FILTER, 'Cari dan saring', 'Cari lowongan dan saring menurut divisi.'),
        S(TABLE, 'Daftar lowongan', 'Menampilkan posisi, divisi, kuota, dan tanggal. Gunakan kolom Aksi untuk membuka, mengubah, atau menghapus. Lowongan dari industri mitra ditinjau di menu Verifikasi Lowongan.'),
    ]);

    T('admin-verifikasi', ['^/admin/verifikasi-lowongan$'], 'Verifikasi Lowongan Industri Mitra', 'Meninjau lowongan yang dibuat industri sebelum ditampilkan.', [
        S(card('Total Lowongan'), 'Ringkasan status', 'Jumlah lowongan menurut status: Pending (menunggu), Disetujui, dan Ditolak.'),
        S(FILTER, 'Cari dan saring', 'Cari lowongan, lalu saring menurut perusahaan, divisi, atau status. Mulai dari status Pending.'),
        S('main', 'Meninjau lowongan', 'Buka lowongan yang pending untuk membaca rinciannya, lalu klik Setujui, atau Tolak dengan menyebutkan alasannya.', 'top'),
    ]);

    T('admin-pengajuan', ['^/admin/pengajuan$'], 'Pengajuan Peserta Magang', 'Memantau dan memproses pengajuan magang dari institusi.', [
        S(card('Total Pengajuan'), 'Ringkasan pengajuan', 'Jumlah pengajuan menurut status.'),
        S(FILTER, 'Cari dan saring', 'Cari berdasarkan nomor surat atau institusi, lalu saring menurut status.'),
        S(TABLE, 'Daftar pengajuan', 'Buka pengajuan lewat kolom Aksi untuk membaca rinciannya, lalu ubah statusnya menjadi Disetujui, Revisi, atau Ditolak.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('admin-attendance', ['^/admin/attendance$'], 'Monitoring Absensi', 'Memantau absensi semua peserta dan memeriksa dokumen izin atau sakit.', [
        S(card('Sanitasi Foto'), 'Sanitasi foto absensi', 'Alat perawatan untuk membersihkan foto absensi. Gunakan hanya bila diperlukan.'),
        S(FILTER, 'Saring data absensi', 'Pilih peserta, status, dan rentang tanggal.'),
        S(TABLE, 'Data absensi', 'Menampilkan jam check in dan check out beserta fotonya. Kolom Status Dokumen dan Aksi dipakai untuk memeriksa dokumen izin atau sakit yang dilampirkan peserta.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('admin-logbook', ['^/admin/logbook$'], 'Pantau Logbook Peserta Magang', 'Membaca logbook semua peserta magang.', [
        S(FILTER, 'Cari dan saring', 'Cari berdasarkan nama atau isi aktivitas, lalu batasi dengan rentang tanggal.'),
        S(TABLE, 'Daftar logbook', 'Menampilkan nama, institusi, tanggal, uraian aktivitas, dan foto dokumentasi peserta.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('admin-microskill', ['^/admin/microskill$'], 'Mikro Skill Peserta Magang', 'Mengelola daftar mikro skill dan memantau pengerjaannya.', [
        S(btn('Tambah Mikroskill'), 'Tambah mikro skill', 'Tambahkan mikro skill baru beserta tautannya.', 'left'),
        S(FILTER, 'Cari mikro skill', 'Ketik judul untuk menemukannya.'),
        S(TABLE, 'Daftar mikro skill', 'Kolom Total Pengerjaan menunjukkan berapa peserta yang sudah mengerjakannya. Gunakan kolom Aksi untuk melihat detail atau menghapus.'),
    ]);

    T('admin-sharing', ['^/admin/sharing-session$'], 'Kelola Sharing Session', 'Menjadwalkan dan mengelola sesi berbagi pengetahuan.', [
        S(btn('Tambah Jadwal'), 'Tambah jadwal', 'Buat jadwal sharing session baru: judul, waktu, narasumber, moderator, dan lokasi.', 'left'),
        S(FILTER, 'Saring jadwal', 'Saring sesi menurut statusnya.'),
        S(TABLE, 'Daftar sesi', 'Memantau status jadwal, kelengkapan materi, form evaluasi, dan dokumentasi tiap sesi. Gunakan kolom Aksi untuk mengubah atau menghapus.'),
    ]);

    T('admin-aktivitas', ['^/admin/aktivitas$'], 'Aktivitas Terbaru', 'Mengelola artikel dan video yang tampil sebagai aktivitas terbaru.', [
        S(btn('Tambah Aktivitas'), 'Tambah aktivitas', 'Tambahkan artikel atau video YouTube baru.', 'left'),
        S(btn('Artikel'), 'Saring jenis konten', 'Pilih Semua, Artikel, atau YouTube untuk menyaring daftar.', 'bottom'),
        S(TABLE, 'Daftar konten', 'Menampilkan tipe, tanggal, dan status tiap konten. Gunakan kolom Aksi untuk mengubah atau menghapus.'),
    ]);

    T('admin-monitoring', ['^/admin/monitoring$'], 'Monitoring Bulanan', 'Memantau peserta magang yang masuk, keluar, dan masih aktif tiap bulan.', [
        S(FILTER, 'Pilih bulan dan saringan', 'Pilih bulan, lalu saring menurut status, mentor, atau institusi.'),
        S(card('Total Aktif'), 'Ringkasan bulan ini', 'Jumlah peserta yang masuk, keluar, masih aktif, dan akan dilepas pada bulan yang dipilih.'),
        S(card('Rekap Tugas per Tim'), 'Rekap tugas per tim', 'Jumlah tugas tiap tim menurut status. Kolom terakhir menandai tim yang belum memberi tugas minggu ini.'),
        S(btn('Export Excel'), 'Ekspor data', 'Unduh data peserta ke Excel. Tersedia juga ekspor khusus alumni.', 'left'),
        S(TABLE, 'Daftar peserta', 'Gunakan kolom Aksi untuk menandai peserta sebagai pelepasan, atau mengaktifkannya kembali.'),
    ]);

    T('admin-monitoring-industri', ['^/admin/monitoring/industri$'], 'Monitoring Industri', 'Memantau industri mitra beserta jumlah peserta magangnya.', [
        S(FILTER, 'Cari industri', 'Cari nama industri mitra.'),
        S(TABLE, 'Daftar industri mitra', 'Menampilkan total peserta, yang masih aktif, dan alumni tiap industri. Buka lewat kolom Aksi untuk melihat rinciannya.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('admin-report', ['^/admin/report$'], 'Monitoring Laporan', 'Memantau laporan akhir seluruh peserta magang.', [
        S(FILTER, 'Saring laporan', 'Pilih peserta atau status laporan.'),
        S(TABLE, 'Daftar laporan', 'Menampilkan berkas, tanggal unggah, status, dan nilai. Buka laporan lewat kolom Aksi untuk membaca dan memperbarui statusnya.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('admin-rag', ['^/admin/rag-knowledge$'], 'Manajemen AI Knowledge Base', 'Mengelola dokumen sumber jawaban chatbot SIMA. Hanya tersedia untuk Super Admin.', [
        S(card('Unggah Dokumen Baru'), 'Unggah dokumen', 'Pilih berkas dan folder tujuannya, lalu klik Unggah Dokumen. Chatbot akan menjawab berdasarkan dokumen ini.'),
        S(btn('Sync AI Database'), 'Sinkronkan database AI', 'Klik setelah menambah atau mengubah dokumen supaya chatbot memakai isi terbaru.', 'left'),
        S(btn('Clear Cache'), 'Bersihkan cache', 'Menghapus cache jawaban chatbot bila jawabannya terasa usang.', 'left'),
        S(TABLE, 'Daftar dokumen', 'Dokumen yang tersimpan beserta kategori dan ukurannya. Gunakan kolom Aksi untuk melihat, mengubah, atau menghapus.'),
    ]);

    // =====================================================================
    //  INSTITUSI
    // =====================================================================

    T('institusi-dashboard', ['^/institusi/dashboard$'], 'Dashboard Institusi', 'Ringkasan pengajuan magang dan peserta dari institusi Anda.', [
        S(card('Rincian Status'), 'Status pengajuan', 'Jumlah pengajuan menurut status: disetujui, menunggu persetujuan, dan lainnya, lengkap dengan persentasenya.'),
        S(card('Aksi Cepat'), 'Aksi cepat', 'Pintasan ke daftar lowongan, daftar pengajuan, ubah profil institusi, dan pengajuan yang masih pending.'),
        S('main h1', 'Profil institusi', 'Informasi institusi Anda. Lengkapi lewat "Edit Profil Institusi" agar data pengajuan akurat.'),
    ]);

    T('institusi-lowongan', ['^/institusi/lowongan$'], 'Lowongan Magang', 'Mencari lowongan magang yang tersedia, lalu mengajukan peserta.', [
        S(FILTER, 'Cari lowongan', 'Cari lowongan, lalu saring menurut perusahaan atau divisi.'),
        S(btn('Detail'), 'Lihat detail', 'Buka detail untuk membaca syarat, posisi, dan kuota lowongan.', 'left'),
        S(btn('Ajukan'), 'Ajukan peserta', 'Klik Ajukan pada lowongan yang cocok, lalu lengkapi formulir pengajuan peserta Anda.', 'left'),
    ]);

    T('institusi-pengajuan', ['^/institusi/pengajuan$'], 'Pengajuan Magang', 'Memantau status pengajuan magang yang Anda kirim.', [
        S(card('Total Pengajuan'), 'Ringkasan pengajuan', 'Jumlah pengajuan menurut status: disetujui, diproses, dan revisi.'),
        S(FILTER, 'Cari dan saring', 'Cari berdasarkan nomor surat, lalu saring menurut status atau tanggal.'),
        S(TABLE, 'Daftar pengajuan', 'Pantau status tiap pengajuan. Bila diminta revisi, buka lewat kolom Aksi untuk memperbaiki dan mengirim ulang.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('institusi-intern', ['^/institusi/intern$'], 'Peserta Magang', 'Memantau peserta magang dari institusi Anda.', [
        S(card('Monitoring Kehadiran'), 'Pintasan pemantauan', 'Kartu-kartu ini membuka Absensi, Logbook, dan Nilai peserta Anda dengan cepat.'),
        S(FILTER, 'Cari peserta', 'Cari nama peserta, lalu saring menurut status.'),
        S(TABLE, 'Daftar peserta', 'Menampilkan program studi serta jumlah logbook, absensi, dan mikro skill tiap peserta.'),
    ]);

    T('institusi-attendance', ['^/institusi/attendance$'], 'Absensi Peserta Magang', 'Memantau kehadiran peserta magang dari institusi Anda.', [
        S(FILTER, 'Saring data absensi', 'Pilih peserta, rentang tanggal, dan status.'),
        S(TABLE, 'Data absensi', 'Menampilkan tanggal, status, jam check in dan check out, serta foto bukti kehadiran.'),
        S(card('Riwayat Absensi'), 'Riwayat kehadiran', 'Persentase kehadiran tiap peserta. Buka lewat kolom Aksi untuk melihat rincian per peserta.'),
    ]);

    T('institusi-logbook', ['^/institusi/logbook$'], 'Logbook Peserta Magang', 'Membaca catatan harian peserta magang dari institusi Anda.', [
        S(FILTER, 'Saring logbook', 'Pilih peserta dan rentang tanggal.'),
        S(TABLE, 'Daftar logbook', 'Menampilkan aktivitas dan foto dokumentasi. Buka lewat kolom Aksi untuk membaca isi lengkapnya.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('institusi-microskill', ['^/institusi/microskill$'], 'Mikro Skill Peserta Magang', 'Memantau mikro skill yang diselesaikan peserta dari institusi Anda.', [
        S(card('Total Submissions'), 'Ringkasan', 'Jumlah pengiriman bukti mikro skill dan jumlah peserta yang ikut mengerjakan.'),
        S(FILTER, 'Pilih peserta', 'Pilih satu peserta untuk melihat mikro skill miliknya.'),
        S(TABLE, 'Data mikro skill', 'Menampilkan judul kursus, waktu pengiriman, dan dokumentasi buktinya.'),
    ]);

    T('institusi-sertifikat', ['^/institusi/sertifikat$'], 'Nilai Sertifikat Peserta Magang', 'Melihat sertifikat dan nilai akhir peserta yang telah menyelesaikan magang.', [
        S(FILTER, 'Cari sertifikat', 'Cari berdasarkan nama peserta.'),
        S(TABLE, 'Data sertifikat', 'Menampilkan tanggal terbit, nilai dan grade sertifikat, serta nilai dan grade laporan. Buka lewat kolom Aksi untuk melihat sertifikatnya.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    // =====================================================================
    //  INDUSTRI
    // =====================================================================

    T('industri-dashboard', ['^/industri/dashboard$'], 'Dashboard Industri', 'Ringkasan lowongan dan pengajuan magang di perusahaan Anda.', [
        S(btn('Lengkapi Profil'), 'Lengkapi profil', 'Bila profil industri belum lengkap, lengkapi dulu supaya lowongan dan pengajuan dapat diproses.', 'bottom'),
        S(card('Total Lowongan'), 'Ringkasan', 'Jumlah lowongan, lowongan yang sudah diverifikasi admin, dan pengajuan yang masuk.'),
        S(card('Buat Lowongan'), 'Pintasan', 'Buat lowongan baru, kelola lowongan, lihat data pelamar, atau ubah profil industri dari kartu-kartu ini.'),
    ]);

    T('industri-lowongan', ['^/industri/lowongan$'], 'Lowongan Magang', 'Mengelola lowongan magang yang Anda tawarkan.', [
        S(btn('Tambah Lowongan'), 'Tambah lowongan', 'Buat lowongan magang baru. Lowongan baru ditinjau admin sebelum tampil bagi institusi.', 'left'),
        S(card('Total Lowongan'), 'Ringkasan', 'Jumlah lowongan menurut statusnya: total, dibuka, dan ditutup.'),
        S(FILTER, 'Cari dan saring', 'Cari lowongan, lalu saring menurut status atau hasil verifikasi.'),
        S(TABLE, 'Daftar lowongan', 'Kolom Verifikasi menunjukkan apakah admin sudah menyetujui lowongan. Gunakan kolom Aksi untuk membuka, mengubah, atau menghapus.'),
    ]);

    T('industri-pengajuan', ['^/industri/pengajuan$'], 'Pengajuan Peserta Magang', 'Meninjau pengajuan magang dari institusi untuk lowongan Anda.', [
        S(card('Total Pengajuan'), 'Ringkasan pengajuan', 'Jumlah pengajuan menurut status.'),
        S(FILTER, 'Cari dan saring', 'Cari berdasarkan nomor surat atau institusi, lalu saring menurut status.'),
        S(TABLE, 'Daftar pengajuan', 'Buka pengajuan lewat kolom Aksi untuk membaca rinciannya, lalu ubah statusnya menjadi Disetujui, Revisi, atau Ditolak. Surat balasan dapat diunduh dari sana.'),
    ]);

    T('industri-intern', ['^/industri/intern$'], 'Kelola Peserta Magang', 'Mengelola peserta magang yang bertugas di perusahaan Anda.', [
        S(btn('Tambah Peserta'), 'Tambah peserta', 'Daftarkan peserta magang baru di perusahaan Anda.', 'left'),
        S(FILTER, 'Cari peserta', 'Cari berdasarkan nama peserta.'),
        S(TABLE, 'Peserta magang', 'Tabel pertama berisi peserta aktif, tabel berikutnya data alumni. Gunakan kolom Aksi untuk melihat, mengubah, atau menghapus.'),
    ]);

    T('industri-attendance', ['^/industri/attendance$'], 'Absensi Peserta Magang', 'Memantau kehadiran peserta magang di perusahaan Anda.', [
        S(FILTER, 'Saring data absensi', 'Pilih peserta, rentang tanggal, dan status.'),
        S(TABLE, 'Data absensi', 'Menampilkan jam check in dan check out beserta fotonya. Gunakan kolom Aksi untuk memeriksa dokumen izin atau sakit.'),
        S(card('Riwayat Absensi'), 'Riwayat kehadiran', 'Persentase kehadiran tiap peserta. Buka lewat kolom Aksi untuk melihat rincian per peserta.'),
    ]);

    T('industri-logbook', ['^/industri/logbook$'], 'Logbook Peserta Magang', 'Membaca catatan harian peserta magang di perusahaan Anda.', [
        S(FILTER, 'Saring logbook', 'Pilih peserta dan rentang tanggal.'),
        S(TABLE, 'Daftar logbook', 'Menampilkan aktivitas dan foto dokumentasi. Buka lewat kolom Aksi untuk membaca isi lengkapnya.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    T('industri-microskill', ['^/industri/microskill$'], 'Mikro Skill Peserta Magang', 'Memantau mikro skill yang diselesaikan peserta di perusahaan Anda.', [
        S(card('Total Submissions'), 'Ringkasan', 'Jumlah pengiriman bukti mikro skill dan jumlah peserta yang ikut mengerjakan.'),
        S(FILTER, 'Pilih peserta', 'Pilih satu peserta untuk melihat mikro skill miliknya.'),
        S(TABLE, 'Data mikro skill', 'Menampilkan judul kursus, waktu pengiriman, dan dokumentasi buktinya.'),
    ]);

    T('industri-report', ['^/industri/report$'], 'Monitoring Laporan', 'Memantau laporan akhir peserta magang di perusahaan Anda.', [
        S(FILTER, 'Saring laporan', 'Pilih peserta atau status laporan.'),
        S(TABLE, 'Daftar laporan', 'Menampilkan berkas, tanggal unggah, status, dan nilai. Buka laporan lewat kolom Aksi untuk membaca dan memperbarui statusnya.'),
        S(PAGER, 'Halaman data', HELP_PAGER),
    ]);

    window.PageGuideTours = tours;
})();
