"""Menyusun juknis.html dari isi di bawah. Jalankan: python build.py  (lalu render.py untuk PDF)."""
import os, re, html

HERE = os.path.dirname(os.path.abspath(__file__))
LOGO = "img/logo_komdigi.png"
ROMAN = {1: "I", 2: "II", 3: "III", 4: "IV", 5: "V", 6: "VI"}

pages = []  # (kind, key, inner_html)  kind: cover|inner|front|body|back


def P(kind, inner, key=None):
    pages.append({"kind": kind, "inner": inner})


def chap(no, title, toc=True):
    return f'<div class="chap" data-toc="1" data-no="{ROMAN[no]}" data-title="{html.escape(title)}"><div class="no">{ROMAN[no]}</div><h1>{title}</h1></div>'


def h2(letter, title):
    return f'<h2 data-toc="2" data-title="{letter}. {html.escape(title)}">{letter}. {title}</h2>'


def steps(*items):
    return '<ol class="steps">' + "".join(f"<li>{i}</li>" for i in items) + "</ol>"


def hasil(text):
    return f'<div class="hasil"><b>Hasil:</b> {text}</div>'


def img(name, caption, h=50):
    return f'<figure><img src="img/{name}.png" alt="" style="height:{h}mm"><figcaption class="cap">{caption}</figcaption></figure>'


def table(head, rows, cls=""):
    th = "".join(f"<th>{c}</th>" for c in head)
    body = "".join("<tr>" + "".join(f"<td>{c}</td>" for c in r) + "</tr>" for r in rows)
    return f'<table class="t {cls}"><thead><tr>{th}</tr></thead><tbody>{body}</tbody></table>'


def ph(t):
    return f'<span class="ph">[{t}]</span>'


# ======================================================================
# SAMPUL, HALAMAN JUDUL, KATA PENGANTAR, DAFTAR ISI
# ======================================================================
P("cover", f'''
<div class="blk"></div><div class="blk2"></div>
<img class="logo" src="{LOGO}" alt="Komdigi">
<div class="t1">PETUNJUK TEKNIS</div>
<div class="band"><div class="a">Penggunaan Sistem Manajemen Magang (SIMAGANG)</div>
<div class="b">Panduan ringkas untuk seluruh pengguna aplikasi</div></div>
<div class="who"><b>Untuk:</b> Peserta Magang · Mentor · Admin dan Super Admin · Institusi Pendidikan · Industri Mitra</div>
<div class="foot">BBLSDM Komdigi Makassar<br><span>Kementerian Komunikasi dan Digital · Edisi 1 · 2026</span></div>
''')

P("inner", f'''
<div class="geo"></div><div class="geo2"></div>
<div class="h"><div class="k">Petunjuk Teknis</div>
<div class="j">Penggunaan Sistem Manajemen Magang (SIMAGANG)</div>
<div class="s">Dokumen ini menjelaskan cara memakai aplikasi SIMAGANG bagi setiap peran, dari pendaftaran magang sampai penilaian akhir.</div></div>
<div class="lg"><img src="{LOGO}" alt="Komdigi"><div>BBLSDM Komdigi Makassar<small>Kementerian Komunikasi dan Digital<br>Makassar, November 2026 · Edisi 1</small></div></div>
''')

P("front", f'''
<div class="chap" style="margin-bottom:5mm"><div class="no" style="font-size:11pt">iii</div><h1 style="font-size:13pt">Kata Pengantar</h1></div>
<p class="dc">Magang merupakan sarana penting untuk menyiapkan talenta digital yang siap bekerja. Pelaksanaannya melibatkan banyak pihak, yaitu peserta, mentor, pengelola program, institusi pendidikan, dan industri mitra, sehingga memerlukan pengelolaan yang tertib, mudah dipantau, dan dapat dipertanggungjawabkan.</p>
<p>Sistem Manajemen Magang (SIMAGANG) disediakan untuk menyatukan seluruh proses tersebut dalam satu aplikasi web: pendaftaran, pengajuan, absensi, logbook, penugasan, sampai laporan akhir dan penilaian. Agar aplikasi ini dipakai dengan cara yang sama oleh semua pihak, disusunlah Petunjuk Teknis ini.</p>
<p>Petunjuk Teknis ini sengaja dibuat ringkas dan berorientasi pada tindakan. Setiap peran dapat langsung menuju bagiannya, mengikuti langkah-langkah yang disajikan dalam bentuk “Cara …”, dan memperoleh hasil yang jelas pada tiap langkah. Dokumen ini akan diperbarui bila aplikasi atau ketentuan berubah.</p>
<p>Kami berharap Petunjuk Teknis ini membantu seluruh pengguna memanfaatkan SIMAGANG secara optimal, sehingga penyelenggaraan magang menjadi lebih tertib, transparan, dan berkualitas.</p>
<div style="margin:7mm 0 0 auto;width:68mm;text-align:center"><p style="text-align:center;margin:0">Makassar, November 2026</p><p style="text-align:center;margin:0">Kepala BBLSDM Komdigi Makassar,</p><div style="height:26mm"></div></div>
''')

P("front", "@@TOC@@")

# ======================================================================
# BAB I PENDAHULUAN (2 hal)
# ======================================================================
P("body", f'''
{chap(1, "Pendahuluan")}
{h2("A", "Latar Belakang")}
<p class="dc">Program magang di lingkungan BBLSDM Komdigi Makassar diikuti peserta dari berbagai institusi pendidikan dan industri mitra. Tanpa sistem terpadu, data pendaftaran, kehadiran, catatan harian, tugas, dan penilaian tersebar di banyak berkas sehingga sulit dipantau.</p>
<p>SIMAGANG dikembangkan untuk menyatukan seluruh proses itu dalam satu aplikasi web bagi enam peran. Petunjuk Teknis ini disusun agar setiap peran memahami tugasnya dan memakai aplikasi dengan cara yang seragam.</p>
{h2("B", "Maksud dan Tujuan")}
<p><b>Maksud:</b> pedoman praktis yang dapat diikuti langkah demi langkah tanpa pelatihan khusus. <b>Tujuan:</b></p>
<ul class="tight"><li>menyeragamkan cara kerja seluruh peran di dalam aplikasi;</li><li>mempercepat pengguna baru memahami tugas dan menu yang relevan; dan</li><li>memastikan data magang tercatat lengkap, tepat waktu, dan dapat ditelusuri.</li></ul>
{h2("C", "Cara Menggunakan Petunjuk Teknis")}
<p>Bacalah Bab II dan III sekali, lalu langsung menuju bagian peran Anda pada Bab IV.</p>
{table(["Anda sebagai", "Bacalah", "Halaman"], [
    ["Peserta Magang", "Bab II, III, lalu Bab IV bagian A", "@@IV.A@@"],
    ["Mentor", "Bab II, III, lalu Bab IV bagian B", "@@IV.B@@"],
    ["Admin / Super Admin", "Bab II, III, lalu Bab IV bagian C", "@@IV.C@@"],
    ["Institusi Pendidikan", "Bab II, III, lalu Bab IV bagian D", "@@IV.D@@"],
    ["Industri Mitra", "Bab II, III, lalu Bab IV bagian E", "@@IV.E@@"],
], "sm")}
<p style="font-size:8.2pt">Bab V (ketentuan, data, dan bantuan, halaman @@V@@) berlaku bagi semua peran.</p>
''')

TERMS = [
    ("SIMAGANG", "Sistem Manajemen Magang, aplikasi web yang diatur dalam petunjuk ini."),
    ("Peserta Magang", "Mahasiswa atau siswa yang sedang atau pernah menjalani magang."),
    ("Mentor", "Pegawai pembimbing yang memberi tugas dan menilai peserta."),
    ("Admin / Super Admin", "Pengelola aplikasi. Super Admin juga mengelola akun admin dan basis pengetahuan AI."),
    ("Institusi", "Sekolah, kampus, atau lembaga asal peserta yang mengajukan magang."),
    ("Industri (Mitra)", "Perusahaan atau instansi mitra yang membuka lowongan dan menerima peserta."),
    ("Lowongan", "Penawaran posisi magang beserta divisi, kuota, dan tanggalnya."),
    ("Pengajuan", "Permohonan magang dari institusi untuk satu lowongan; berstatus Menunggu, Disetujui, Revisi, atau Ditolak."),
    ("Workspace", "Ruang kerja digital berisi kelompok dan tugas peserta."),
    ("Kelompok", "Satu tim kerja buatan mentor di Workspace beserta anggota dan tugasnya."),
    ("Backlog, Sprint, Board", "Daftar rencana tugas; periode kerja berjangka dengan target; papan tugas per status."),
    ("Review / Revisi", "Tugas yang dikirim untuk dinilai mentor; tugas yang diminta diperbaiki."),
    ("Timeline", "Tampilan jadwal tugas menurut tenggat."),
    ("Absensi", "Catatan kehadiran harian (Hadir, Izin, Sakit) dengan check in dan check out serta foto bukti."),
    ("Logbook", "Catatan aktivitas harian peserta, disetujui mentor."),
    ("Mikro Skill", "Kursus singkat Digitalent Komdigi yang dikerjakan peserta dan dibuktikan dengan unggahan."),
    ("Sharing Session", "Sesi berbagi pengetahuan terjadwal dengan materi dan form evaluasi."),
    ("Laporan Akhir", "Laporan penutup magang yang diunggah peserta dan dinilai mentor (nilai 0 sampai 100)."),
    ("Alumni", "Peserta yang telah dinyatakan selesai (dilepas) dari magang."),
    ("Chat Publik", "Ruang obrolan bersama untuk semua pengguna yang sudah login."),
    ("SIMA", "Asisten tanya jawab (chatbot) yang menjawab berdasarkan dokumen pengetahuan."),
]
P("body", f'''
{h2("D", "Pengertian dan Istilah")}
{table(["Istilah", "Pengertian"], [[a, b] for a, b in TERMS], "sm")}
<p style="font-size:8.2pt;color:#5b6070"><i>Tangkapan layar dalam dokumen ini memakai akun contoh; nama, angka, dan tampilan di aplikasi sebenarnya dapat berbeda.</i></p>
''')

# ======================================================================
# BAB II GAMBARAN UMUM (2 hal)
# ======================================================================
P("body", f'''
{chap(2, "Gambaran Umum SIMAGANG")}
{h2("A", "Fungsi Aplikasi")}
<p class="dc">SIMAGANG membantu pengelolaan magang dari hulu ke hilir: membuka lowongan, memproses pengajuan, mencatat kehadiran dan aktivitas, membagi serta menilai tugas, hingga menutup magang dengan laporan akhir dan sertifikat. Aplikasi diakses lewat peramban (browser) dan tampilannya menyesuaikan layar komputer maupun ponsel.</p>
{h2("B", "Modul dan Menu Utama")}
<p>Menu berada di sisi kiri layar dan berbeda menurut peran. Modul yang tersedia:</p>
{table(["Modul / menu", "Kegunaan"], [
    ["Dashboard", "Ringkasan kondisi dan pintasan pekerjaan hari ini."],
    ["Chat Publik", "Obrolan bersama; mentor dapat menyematkan pengumuman. Angka merah di menu menandakan pesan belum dibaca."],
    ["Workspace", "Kelompok kerja dengan Backlog, Board, List, Timeline, Deadline, Laporan, Channel, dan Aktivitas."],
    ["Absensi", "Mencatat dan memantau kehadiran harian."],
    ["Logbook", "Mencatat dan menyetujui aktivitas harian."],
    ["Sharing Session", "Jadwal, materi, dan evaluasi sesi berbagi pengetahuan."],
    ["Mikro Skill", "Kursus singkat Digitalent dan bukti penyelesaiannya."],
    ["Laporan Akhir", "Unggah, penilaian, dan persetujuan laporan penutup magang."],
    ["Lowongan dan Pengajuan", "Pembukaan lowongan, verifikasi, dan pengajuan peserta oleh institusi."],
    ["Data Master", "Pengelolaan mentor, mitra, peserta magang, serta tim kerja/bagian."],
    ["Monitoring", "Pemantauan peserta, industri, dan laporan secara bulanan."],
], "sm")}
{h2("C", "Penggunaan Perangkat")}
<p>Gunakan peramban versi terbaru dengan koneksi internet. Pengguna ponsel membuka menu lewat ikon tiga garis di kiri atas. Tampilan terang atau gelap dapat dipilih lewat ikon roda gigi di bagian atas.</p>
''')

P("body", f'''
{h2("D", "Peran dan Hak Akses")}
<p>Setiap akun hanya melihat menu dan data yang menjadi haknya. Pembagian peran:</p>
{table(["Peran", "Tugas utama", "Menu yang tersedia"], [
    ["Peserta Magang", "Absen, mengisi logbook, mengerjakan tugas, mengirim laporan akhir.", "Dashboard, Chat Publik, Workspace, Absensi, Logbook, Sharing Session, Laporan Akhir, Mikro Skill"],
    ["Mentor", "Membimbing, membuat kelompok dan tugas, menilai.", "Dashboard, Chat Publik, Workspace, Anak Bimbingan, Absensi, Logbook, Laporan Akhir, Mikro Skill"],
    ["Admin", "Mengelola data master, lowongan, pengajuan, dan memantau seluruh peserta.", "Seluruh menu pengelolaan dan monitoring"],
    ["Super Admin", "Seperti Admin, ditambah mengelola akun admin dan basis pengetahuan AI.", "Menu Admin + Admin dan Manajemen AI RAG"],
    ["Institusi", "Mengajukan peserta dan memantau peserta serta nilainya.", "Dashboard, Chat Publik, Lowongan, Pengajuan, Peserta, Absensi, Logbook, Mikro Skill, Nilai"],
    ["Industri", "Membuka lowongan, memproses pengajuan, dan memantau peserta di perusahaannya.", "Dashboard, Chat Publik, Lowongan, Pengajuan, Peserta, Absensi, Logbook, Mikro Skill, Laporan"],
], "sm")}
{h2("E", "Cara Masuk dan Mendapat Bantuan")}
{steps("Buka alamat SIMAGANG di peramban, lalu masuk dengan <b>email</b> dan <b>kata sandi</b> yang diberikan admin. Lupa kata sandi? Klik <b>Lupa Password?</b> pada halaman masuk.", "Aplikasi membuka <b>Dashboard</b> sesuai peran Anda. Menu lain ada di sisi kiri.", "Di setiap halaman, klik <b>Panduan penggunaan</b> di dasar menu kiri, lalu <b>Mulai tutorial halaman ini</b> untuk disorot langkah demi langkah. Klik <b>Petunjuk Teknis</b> untuk membaca atau mengunduh dokumen ini.", "Setelah selesai, klik <b>Logout</b>. Sesi masuk berakhir sendiri bila lama tidak aktif (saat ini 120 menit).")}
<div class="note"><b>Chat Publik.</b> Seluruh pengguna yang sudah masuk dapat mengobrol di sini. Fitur ini masih dalam tahap pengembangan: sementara ini pesan belum disimpan di server pusat. Hormati etika pada Bab V bagian D.</div>
''')

# ======================================================================
# BAB III ALUR (3 hal)
# ======================================================================
P("body", f'''
{chap(3, "Alur dan Mekanisme")}
{h2("A", "Alur Besar Magang")}
<p class="dc">Magang di SIMAGANG berjalan dalam lima tahap berurutan. Tahap berikutnya hanya dapat berjalan setelah tahap sebelumnya selesai.</p>
<div class="chevs"><div class="c">1. Lowongan</div><div class="c">2. Pengajuan</div><div class="c">3. Pelaksanaan</div><div class="c">4. Penutupan</div><div class="c">5. Alumni</div></div>
{table(["Tahap", "Pelaku", "Hasil"], [
    ["1. Lowongan", "Industri atau Admin membuat; Admin memverifikasi lowongan industri.", "Lowongan berstatus Disetujui dan tampil bagi institusi."],
    ["2. Pengajuan", "Institusi mengajukan; Admin atau Industri memproses.", "Pengajuan berstatus Disetujui; peserta didaftarkan dan ditempatkan pada tim serta mentor."],
    ["3. Pelaksanaan", "Peserta menjalankan; Mentor membimbing dan menilai.", "Absensi, logbook, tugas Workspace, mikro skill, dan sharing session tercatat."],
    ["4. Penutupan", "Peserta mengunggah; Mentor menilai; Admin memantau.", "Laporan akhir disetujui, nilai dan sertifikat tersedia."],
    ["5. Alumni", "Admin melepas peserta.", "Peserta berpindah ke daftar alumni; data tetap dapat dilihat."],
], "sm")}
{h2("B", "Prinsip Pengisian Data")}
<ul class="tight"><li>Isi data pada hari kejadian: absensi, logbook, dan status tugas.</li><li>Lampirkan bukti (foto atau dokumen) bila diminta.</li><li>Gunakan status sesuai artinya; jangan menandai selesai sebelum benar-benar selesai.</li><li>Setiap perubahan status tercatat dan dapat ditelusuri oleh mentor dan admin.</li></ul>
''')

P("body", f'''
{h2("C", "Alur Pendaftaran (Tahap 1 dan 2)")}
<div class="flow">
<div class="fl"><div class="who">Industri / Admin</div><div class="box"><b>Membuat lowongan</b>: judul, posisi, divisi, deskripsi, persyaratan, fasilitas, dan kuota. Industri melengkapi profil lebih dahulu.</div></div>
<div class="fl dec"><div class="who">Admin</div><div class="box"><b>Verifikasi</b> lowongan industri: <i>Pending</i> menjadi <i>Disetujui</i> atau <i>Ditolak</i> (disertai alasan).</div></div>
<div class="fl"><div class="who">Institusi</div><div class="box"><b>Klik Ajukan</b> pada lowongan, lalu unggah surat magang (PDF), isi tanggal mulai dan selesai, keperluan, tujuan surat, serta data peserta.</div></div>
<div class="fl dec"><div class="who">Admin / Industri</div><div class="box"><b>Memproses pengajuan</b>: <i>Disetujui</i> (nomor surat balasan wajib diisi), <i>Revisi</i> (dengan catatan), atau <i>Ditolak</i>. Disetujui dan Ditolak bersifat final.</div></div>
<div class="fl"><div class="who">Admin / Industri</div><div class="box"><b>Mendaftarkan peserta</b> beserta institusi, tim, dan mentor, lalu akun peserta aktif.</div></div>
</div>
<div class="note"><b>Kuota.</b> Saat pengajuan disetujui, kuota lowongan berkurang sebanyak peserta yang diajukan; lowongan otomatis Ditutup bila kuota habis. Pengajuan Revisi dibuka kembali oleh institusi untuk diperbaiki sesuai catatan, lalu dikirim ulang.</div>
{h2("D", "Alur Pelaksanaan (Tahap 3)")}
<div class="hflow"><div class="hb"><b>Absen</b>check in dan check out harian</div><div class="ar">›</div><div class="hb"><b>Logbook</b>aktivitas harian</div><div class="ar">›</div><div class="hb"><b>Tugas</b>dikerjakan di Workspace</div><div class="ar">›</div><div class="hb"><b>Review</b>dinilai mentor</div></div>
<p>Selain empat kegiatan harian di atas, peserta mengerjakan <b>mikro skill</b> dan mengikuti <b>sharing session</b>. Mentor memantau semuanya dari dashboard dan menyetujui logbook.</p>
<div class="hflow"><div class="hb"><b>Peserta</b>geser tugas ke Review</div><div class="ar">›</div><div class="hb"><b>Mentor</b>menilai tugas</div><div class="ar">›</div><div class="hb"><b>Setuju</b>tugas berstatus Selesai</div><div class="ar">/</div><div class="hb"><b>Revisi</b>kembali ke peserta</div></div>
''')

P("body", f'''
{h2("E", "Alur Penutupan (Tahap 4 dan 5)")}
<div class="flow">
<div class="fl"><div class="who">Peserta</div><div class="box"><b>Mengunggah laporan akhir</b> (PDF/DOC/DOCX), berkas atau tautan proyek bila ada, lalu menulis testimoni.</div></div>
<div class="fl dec"><div class="who">Mentor</div><div class="box"><b>Memberi nilai</b> 0 sampai 100 beserta catatan (opsional). Laporan otomatis <i>Disetujui</i> dengan predikat A, B, atau C.</div></div>
<div class="fl dec"><div class="who">Admin</div><div class="box"><b>Memantau laporan</b> dan dapat menetapkan <i>Disetujui</i> (setelah mentor menilai), <i>Revisi</i> (dengan catatan), atau <i>Ditolak</i>.</div></div>
<div class="fl"><div class="who">Peserta</div><div class="box"><b>Memperbaiki dan mengunggah ulang</b> bila laporan berstatus Revisi.</div></div>
<div class="fl"><div class="who">Mentor / Admin</div><div class="box"><b>Menerbitkan sertifikat</b> beserta nilai enam aspek; Institusi melihatnya di menu <b>Nilai</b>.</div></div>
<div class="fl"><div class="who">Admin</div><div class="box"><b>Melepas peserta</b> pada Monitoring Bulanan; peserta masuk daftar <b>alumni</b>.</div></div>
</div>
{h2("F", "Arti Status yang Sering Muncul")}
{table(["Objek", "Status", "Artinya"], [
    ["Pengajuan", "Menunggu · Disetujui · Revisi · Ditolak", "Disetujui dan Ditolak final, tidak dapat diubah lagi."],
    ["Lowongan", "Pending · Disetujui · Ditolak", "Hasil verifikasi admin; lowongan juga Dibuka atau Ditutup."],
    ["Absensi", "Hadir · Izin · Sakit · Tidak Hadir", "Belum absen sampai lewat 12.00 WITA otomatis Tidak Hadir."],
    ["Dokumen izin/sakit", "Menunggu · Disetujui · Ditolak", "Diperiksa admin atau industri."],
    ["Tugas", "To Do · In Progress · Review · Revisi · Selesai", "Posisi tugas pada papan kerja."],
    ["Laporan akhir", "Menunggu · Disetujui · Perlu Revisi · Ditolak", "Nilai 0–100 dari mentor."],
], "sm")}
''')

# ======================================================================
# BAB IV PETUNJUK PER PERAN (9 hal)
# ======================================================================
P("body", f'''
{chap(4, "Petunjuk Pelaksanaan per Peran")}
<p class="dc">Tiap bagian disusun dengan pola yang sama: <b>Cara …</b> berisi langkah singkat dan diakhiri <b>Hasil</b> yang harus terlihat. Nama menu ditulis tebal sesuai tulisan di aplikasi.</p>
<h2 data-toc="2" data-key="IV.A" data-title="A. Peserta Magang"><span class="role">Peserta Magang</span>A. Peserta Magang</h2>
<p>Pekerjaan harian Anda: absen, mengisi logbook, mengerjakan tugas, dan memantau pesan. Dashboard menjadi titik awalnya.</p>
{img("intern-dashboard", "Gambar 1. Dashboard Peserta Magang")}
<h3>Cara Melakukan Absensi</h3>
{steps("Antara pukul <b>08.00 dan 12.00 WITA</b> pada hari kerja, buka menu <b>Absensi</b>, klik <b>Absensi Baru</b> (atau <b>Absensi Sekarang</b> di dashboard).", "Pilih <b>Hadir</b>, <b>Izin</b>, atau <b>Sakit</b>. Hadir: ambil <b>foto</b> (JPG/PNG, maks 2 MB). Izin: isi catatan dan lampirkan dokumen (PDF, DOC, DOCX, JPG, PNG, maks 5 MB). Sakit: isi catatan; dokumen boleh dilampirkan.", "Klik <b>Simpan Absensi</b>.", "Mulai pukul <b>16.00 WITA</b>, klik <b>Check Out</b> dan ambil foto.")}
{hasil("baris hari ini tampil di Riwayat Absensi dengan jam check in dan check out. Sabtu, Minggu, dan libur nasional tidak ada absensi.")}
''')

P("body", f'''
<h3>Cara Mengisi Logbook</h3>
{steps("Buka menu <b>Logbook</b>, klik <b>Tambah Logbook</b>.", "Pilih tanggal, tulis uraian aktivitas (maks 1.000 karakter), dan tautkan ke tugas Workspace bila ada. Foto dokumentasi bersifat opsional (JPG/PNG, maks 2 MB).", "Klik <b>Simpan Logbook</b>. Anda dapat mengubah atau menghapusnya kemudian.")}
{hasil("logbook muncul pada riwayat; setelah disetujui mentor diberi tanda <i>Approved</i>.")}
<h3>Cara Mengerjakan Tugas di Workspace</h3>
{steps("Buka menu <b>Workspace</b>, lalu klik kelompok Anda.", "Pada tab <b>Board</b>, klik kartu tugas untuk membaca rincian dan tenggatnya.", "Geser kartu tugas Anda ke <b>In Progress</b> saat mulai, dan ke <b>Review</b> bila sudah selesai. Selesai dan Revisi hanya ditetapkan mentor.", "Bila berstatus <b>Revisi</b>, baca catatan mentor, kembalikan ke <b>In Progress</b>, perbaiki, lalu kirim lagi ke Review.")}
{hasil("tugas disetujui mentor dan berstatus Selesai (dapat disertai nilai).")}
{img("workspace-detail", "Gambar 2. Workspace: papan tugas satu kelompok (tampilan mentor)", 40)}
<p>Tab lain pada kelompok: <b>Backlog</b> (rencana tugas), <b>Timeline</b> dan <b>Deadline</b> (jadwal dan tenggat), <b>Laporan</b> (grafik), <b>Channel</b> (obrolan kelompok), dan <b>Aktivitas</b> (riwayat).</p>
''')

P("body", f'''
<h3>Cara Mengerjakan Mikro Skill</h3>
{steps("Buka menu <b>Mikro Skill</b>, cari judul yang diinginkan.", "Klik <b>Buka Digitalent</b> dan selesaikan kursusnya di situs Digitalent Komdigi.", "Kembali ke SIMAGANG, klik <b>Upload Bukti</b>, lampirkan bukti penyelesaian, lalu kirim.")}
{hasil("kursus tampil pada daftar mikro skill selesai dan dihitung pada klasemen mikro skill.")}
<h3>Cara Mengikuti Sharing Session</h3>
{steps("Buka menu <b>Sharing Session</b>; saring menurut jenis atau narasumber bila perlu.", "Catat jadwal, narasumber, dan lokasi pada tiap sesi.", "Setelah sesi, buka kembali untuk mengunduh <b>materi</b> dan mengisi <b>form evaluasi</b>.")}
<h3>Cara Mengirim Laporan Akhir dan Testimoni</h3>
{steps("Buka menu <b>Laporan Akhir</b>. Kartu <b>Detail Laporan Akhir</b> menunjukkan tahap dan nilai Anda.", "Pada <b>Berkas Laporan Akhir</b>, unggah laporan (PDF, DOC, DOCX, maks 10 MB). Opsional: hingga 3 berkas proyek (ZIP, RAR, 7Z, TAR, GZ, maks 100 MB) dan 3 tautan (GitHub, Google Drive, YouTube, Canva, Tableau). Laporan hanya dapat diunggah satu kali.", "Bila berstatus Revisi, perbaiki lalu unggah ulang; laporan yang sudah Disetujui tidak dapat diubah.", "Isi <b>Testimoni</b> (20 sampai 1.000 karakter) dan kirim. <b>Lihat dan cetak</b> pada Rekap Tugas Workspace bila diperlukan.")}
{hasil("laporan tampil berstatus Menunggu, lalu Disetujui setelah mentor memberi nilai.")}
''')

P("body", f'''
<h2 data-toc="2" data-key="IV.B" data-title="B. Mentor"><span class="role">Mentor</span>B. Mentor</h2>
<p>Dashboard Mentor merangkum tugas yang perlu dinilai, tugas terlambat, tenggat tujuh hari, progres kelompok, dan absensi anak bimbingan hari ini.</p>
{img("mentor-dashboard", "Gambar 3. Dashboard Mentor")}
<h3>Cara Membuat Kelompok dan Tugas</h3>
{steps("Buka <b>Workspace</b>, klik <b>Buat kelompok</b>.", "Isi nama, pilih warna, lalu pilih anak bimbingan sebagai anggota; simpan.", "Masuk ke kelompok, klik <b>Buat tugas</b>, lalu isi rincian tugas dan simpan.", "Pantau periode kerja pada bilah <b>Sprint</b>; klik ikon <b>?</b> bila perlu penjelasan istilah.")}
{hasil("kelompok dan tugas tampil di Board anggota.")}
<h3>Cara Menilai Tugas (Review)</h3>
{steps("Pada dashboard, kartu <b>Perlu Review</b>, klik <b>Review</b> di tugas yang masuk.", "Baca hasil kerja peserta pada detail tugas.", "Pilih <b>setujui</b> (boleh disertai nilai), atau <b>minta revisi</b> (catatan wajib diisi).")}
{hasil("tugas Selesai, atau kembali ke peserta berstatus Revisi; peserta menerima pemberitahuan.")}
''')

P("body", f'''
<h3>Cara Memantau Absensi dan Logbook</h3>
{steps("Buka <b>Absensi</b>; saring menurut anak magang, tanggal, atau status. Baris <i>Belum Absen</i> menandai yang belum hadir.", "Buka <b>Logbook</b>, klik <b>Lihat</b> untuk membaca, lalu klik <b>tombol centang</b> untuk menyetujui logbook yang sesuai.")}
{hasil("logbook yang disetujui bertanda <i>Approved</i>.")}
<h3>Cara Menilai Laporan Akhir</h3>
{steps("Buka <b>Laporan Akhir</b>; saring menurut anak magang atau status.", "Klik <b>Detail</b>, baca laporan melalui kolom <b>File</b>.", "Isi <b>Nilai</b> 0 sampai 100 dan catatan (opsional), klik <b>Simpan Nilai</b>, lalu konfirmasi. Predikat: A = 85–100, B = 70–84, C = 0–69.")}
{hasil("laporan berstatus Disetujui dengan nilai dan predikat. Nilai diberikan satu kali; perubahan status laporan selanjutnya dilakukan admin.")}
<h3>Cara Menerbitkan Sertifikat dan Nilai</h3>
{steps("Pada daftar <b>Laporan Akhir</b>, klik ikon <b>Penilaian sertifikat</b> pada peserta.", "Isi tanggal terbit dan nilai 0 sampai 100 untuk enam aspek: disiplin dan kehadiran, tanggung jawab, kerja sama dan komunikasi, kemampuan teknis, etika kerja, serta inisiatif dan kreativitas.", "Simpan. Jumlah mikro skill disetujui terhitung otomatis; nomor sertifikat dibuat otomatis (CERT-tahun-urutan).")}
{hasil("sertifikat dapat dicetak. Sejak tanggal terbit tiba, nilainya tidak dapat diubah lagi.")}
<h3>Cara Menyematkan Pengumuman di Chat Publik</h3>
{steps("Buka <b>Chat Publik</b>, aktifkan ikon <b>megafon</b> pada kotak pesan.", "Pilih masa sematan <b>7 hari</b> atau <b>30 hari</b>, tulis pesan, lalu <b>Kirim</b>.")}
{hasil("pesan tampil pada bilah pengumuman (paling banyak 3 aktif); penulis dapat menghapusnya kapan saja.")}
''')

P("body", f'''
<h2 data-toc="2" data-key="IV.C" data-title="C. Admin dan Super Admin"><span class="role">Admin</span>C. Admin dan Super Admin</h2>
<p>Admin mengelola data master dan proses pendaftaran serta memantau seluruh peserta. Dashboard Admin menampilkan peserta aktif, absensi hari ini, dan distribusi kehadiran.</p>
{img("admin-dashboard", "Gambar 4. Dashboard Admin")}
<h3>Cara Mengelola Data Master</h3>
{table(["Data", "Menu", "Langkah"], [
    ["Mentor", "Mentor", "<b>Tambah Mentor</b>: isi tim, jabatan, kontak; ubah atau hapus lewat kolom Aksi."],
    ["Mitra", "Mitra", "<b>Tambah Mitra</b> (institusi atau industri); saring menurut jenis."],
    ["Peserta", "Peserta Magang", "<b>Tambah Peserta</b> beserta institusi, tim, dan mentor; tabel pertama aktif, berikutnya alumni."],
    ["Tim/Bagian", "Tim Kerja/Bagian", "<b>Tambah Tim</b>; nama tim dipakai saat menempatkan mentor dan peserta."],
], "sm")}
<h3>Cara Memverifikasi Lowongan Industri</h3>
{steps("Buka <b>Verifikasi Lowongan</b>, saring status <b>Pending</b>.", "Buka lowongan, baca rinciannya, lalu klik <b>Setujui</b>, atau <b>Tolak</b> dengan alasan.")}
{hasil("lowongan Disetujui tampil bagi institusi.")}
''')

P("body", f'''
<h3>Cara Memproses Pengajuan</h3>
{steps("Buka <b>Pengajuan</b>; cari menurut nomor surat atau institusi.", "Buka lewat kolom <b>Aksi</b>, periksa surat magang dan data peserta.", "Ubah status: <b>Disetujui</b> (isi nomor surat balasan; kuota lowongan berkurang), <b>Revisi</b> (isi catatan, maks 2.000 karakter), atau <b>Ditolak</b>. Disetujui dan Ditolak tidak dapat diubah lagi.")}
{hasil("institusi melihat status terbaru; pemberitahuan <b>WhatsApp</b> dengan pesan siap pakai dapat dikirim. Peserta yang disetujui didaftarkan lewat menu Peserta Magang.")}
<h3>Cara Memantau Peserta</h3>
{table(["Kebutuhan", "Menu", "Cara singkat"], [
    ["Kehadiran", "Absensi", "Saring peserta, status, tanggal; periksa dokumen izin/sakit lewat Aksi lalu pilih <b>Disetujui</b> atau <b>Ditolak</b>."],
    ["Aktivitas harian", "Logbook", "Cari nama atau isi aktivitas, batasi dengan tanggal."],
    ["Masuk, keluar, aktif", "Monitoring Peserta Magang", "Pilih bulan; tandai peserta sebagai <b>pelepasan</b> lewat Aksi; <b>Export Excel</b> untuk data."],
    ["Mitra industri", "Monitoring Industri", "Lihat total, aktif, dan alumni tiap industri; buka rincian lewat Aksi."],
    ["Laporan akhir", "Laporan", "Buka laporan, tetapkan <b>Disetujui</b> (hanya setelah mentor menilai), <b>Revisi</b>, atau <b>Ditolak</b> dengan catatan."],
], "sm")}
<h3>Cara Mengelola Konten Pendukung</h3>
<ul class="tight"><li><i>Mikro Skill:</i> klik <b>Tambah Mikroskill</b> dengan tautannya; kolom Total Pengerjaan menunjukkan jumlah penyelesai.</li><li><i>Sharing Session:</i> klik <b>Tambah Jadwal</b> (judul, waktu, narasumber, moderator, lokasi); pantau materi dan evaluasi.</li><li><i>Aktivitas Terbaru:</i> klik <b>Tambah Aktivitas</b> untuk artikel atau video yang tampil di beranda.</li><li><i>Lowongan:</i> klik <b>Tambah Lowongan</b> (judul, posisi, tim/bagian, deskripsi, persyaratan, fasilitas, kuota, status).</li></ul>
<div class="note"><b>Khusus Super Admin.</b> Menu <b>Admin</b> untuk menambah akun admin dan mengatur hak aksesnya. Menu <b>Manajemen AI RAG</b> untuk mengunggah dokumen sumber jawaban SIMA; klik <b>Sync AI Database</b> setelah dokumen berubah, dan <b>Clear Cache</b> bila jawaban terasa usang.</div>
''')

P("body", f'''
<h2 data-toc="2" data-key="IV.D" data-title="D. Institusi"><span class="role">Institusi</span>D. Institusi Pendidikan</h2>
<p>Dashboard Institusi menampilkan rincian status pengajuan, pintasan, dan profil institusi. Lengkapi profil lewat <b>Edit Profil Institusi</b> agar data pengajuan akurat.</p>
<h3>Cara Mengajukan Peserta Magang</h3>
{steps("Buka <b>Lowongan Magang</b>; cari lalu saring menurut perusahaan atau divisi.", "Klik <b>Detail</b> untuk membaca persyaratan, fasilitas, dan kuota.", "Klik <b>Ajukan</b>. Unggah <b>surat magang</b> (PDF), isi tanggal mulai dan selesai, keperluan, tujuan surat, lalu data tiap peserta (nama, email, jurusan, jenis kelamin, nomor telepon; minimal satu peserta). Kirim.")}
{hasil("pengajuan tampil di menu Pengajuan Magang berstatus Menunggu.")}
<h3>Cara Memantau Status dan Memperbaiki Pengajuan</h3>
{steps("Buka <b>Pengajuan Magang</b>; cari menurut nomor surat atau saring status.", "Bila berstatus <b>Revisi</b>, buka lewat kolom <b>Aksi</b>, perbaiki sesuai catatan, kirim ulang.", "Bila <b>Disetujui</b>, unduh <b>surat balasan</b> dari halaman pengajuan.")}
<h3>Cara Memantau Peserta dan Nilai</h3>
{table(["Kebutuhan", "Menu", "Cara singkat"], [
    ["Daftar peserta", "Peserta Magang", "Cari nama; lihat program studi serta jumlah logbook, absensi, mikro skill."],
    ["Kehadiran", "Absensi", "Saring peserta dan tanggal; kartu Riwayat Absensi menampilkan persentase kehadiran."],
    ["Aktivitas", "Logbook", "Buka lewat Aksi untuk membaca isi dan foto dokumentasi."],
    ["Kursus", "Mikro Skill", "Pilih satu peserta untuk melihat kursus dan bukti yang ia kirim."],
    ["Sertifikat dan nilai", "Nilai", "Lihat tanggal terbit, nilai dan grade sertifikat, serta nilai laporan; buka sertifikat lewat Aksi."],
], "sm")}
<div class="note"><b>Catatan.</b> Institusi hanya melihat peserta dari institusinya sendiri dan tidak dapat mengubah data absensi, logbook, atau nilai.</div>
''')

P("body", f'''
<h2 data-toc="2" data-key="IV.E" data-title="E. Industri"><span class="role">Industri</span>E. Industri Mitra</h2>
<p>Dashboard Industri menampilkan total lowongan, lowongan yang sudah diverifikasi, dan pengajuan masuk. Bila diminta, klik <b>Lengkapi Profil</b> terlebih dahulu agar lowongan dapat diproses.</p>
<h3>Cara Membuat Lowongan</h3>
{steps("Buka <b>Lowongan Magang</b>, klik <b>Tambah Lowongan</b> (profil industri harus sudah lengkap).", "Isi judul, posisi, divisi, deskripsi pekerjaan, persyaratan, fasilitas, kuota (minimal 1), dan status Aktif atau Nonaktif; simpan.", "Pantau kolom <b>Verifikasi</b>: lowongan baru ditinjau admin sebelum tampil bagi institusi.")}
{hasil("lowongan berstatus Disetujui dan dapat diajukan institusi.")}
<h3>Cara Memproses Pengajuan</h3>
{steps("Buka <b>Pengajuan Magang</b>; saring menurut status atau institusi.", "Buka lewat <b>Aksi</b>, baca rincian, lalu pilih <b>Disetujui</b>, <b>Revisi</b>, atau <b>Ditolak</b>.", "Unduh <b>surat balasan</b> dari halaman pengajuan bila diperlukan.")}
<h3>Cara Mengelola dan Memantau Peserta</h3>
{steps("Buka <b>Peserta Magang</b>, klik <b>Tambah Peserta</b> untuk mendaftarkan peserta di perusahaan Anda; data aktif dan alumni tampil pada tabel terpisah.", "Pantau <b>Absensi</b> (periksa dokumen izin/sakit lewat Aksi: <b>Disetujui</b> atau <b>Ditolak</b>), <b>Logbook</b>, dan <b>Mikro Skill</b> per peserta.", "Buka <b>Laporan Akhir</b> untuk membaca laporan dan memantau statusnya.")}
{hasil("data peserta, kehadiran, dan laporan di perusahaan Anda terpantau dalam satu tempat.")}
''')

# ======================================================================
# BAB V KETENTUAN, DATA, BANTUAN (2 hal)
# ======================================================================
P("body", f'''
{chap(5, "Ketentuan, Data, dan Bantuan")}
{h2("A", "Ketentuan Umum")}
<p class="dc">Ketentuan berikut dijalankan langsung oleh aplikasi, sehingga berlaku sama bagi semua pengguna.</p>
{table(["Hal", "Ketentuan"], [
    ["Hari absensi", "Senin sampai Jumat. Sabtu, Minggu, dan libur nasional tidak ada absensi."],
    ["Absensi masuk dan keluar", "Masuk pukul 08.00 sampai 12.00 WITA, satu kali sehari, Hadir wajib berfoto (JPG/PNG, maks 2 MB). Keluar mulai pukul 16.00 WITA, dengan foto, setelah absen masuk berstatus Hadir."],
    ["Izin dan sakit", "Catatan wajib. Izin wajib melampirkan dokumen (PDF, DOC, DOCX, JPG, PNG, maks 5 MB); sakit boleh tanpa dokumen. Dokumen diperiksa admin atau industri."],
    ["Tidak absen", "Setelah pukul 12.00 WITA, yang belum absen otomatis tercatat <b>Tidak Hadir</b> (diproses pukul 12.05 WITA)."],
    ["Logbook", "Uraian maks 1.000 karakter; foto opsional (JPG/PNG, maks 2 MB); dapat diubah atau dihapus penulisnya."],
    ["Laporan akhir", "Satu laporan per peserta: PDF/DOC/DOCX maks 10 MB; maks 3 berkas proyek (maks 100 MB) dan 3 tautan; testimoni 20 sampai 1.000 karakter."],
    ["Nilai laporan", "Nilai 0–100 dari mentor: A = 85–100, B = 70–84, C = 0–69. Aplikasi tidak menetapkan nilai minimal kelulusan."],
    ["Nilai sertifikat", "Enam aspek bernilai 0–100: A ≥ 85, B+ 80–84, B 70–79, C+ 65–69, C 60–64, D 50–59, E &lt; 50."],
], "sm")}
{h2("B", "Keamanan Akun dan Data Pribadi")}
<ul class="tight"><li>Kata sandi minimal 8 karakter dan bersifat rahasia; jangan dibagikan atau memakai akun orang lain.</li><li>Sesi masuk berakhir otomatis bila lama tidak aktif (saat ini 120 menit); <b>Logout</b> bila memakai komputer bersama.</li><li>Foto dan dokumen absensi tersimpan tertutup dan hanya dibuka bagi pihak berwenang. Foto absensi lebih dari 3 bulan dapat dibersihkan admin lewat <b>Sanitasi Foto</b>; data absensinya tetap ada.</li><li>Unggah hanya berkas yang berkaitan dengan magang; data peserta dipakai untuk penyelenggaraan magang saja.</li></ul>
''')

P("body", f'''
{h2("C", "Etika Penggunaan Chat Publik")}
<ul class="tight"><li>Gunakan bahasa yang sopan dan relevan dengan magang.</li><li>Jangan menyebarkan data pribadi, kata sandi, atau informasi internal yang tidak untuk umum.</li><li>Pengumuman resmi hanya disematkan oleh mentor; periksa masa sematannya.</li><li>Pesan yang melanggar dapat dilaporkan kepada admin.</li></ul>
{h2("D", "Pertanyaan yang Sering Diajukan")}
{table(["Pertanyaan", "Jawaban"], [
    ["Form absensi tidak bisa dibuka.", "Absensi masuk hanya pukul 08.00–12.00 WITA pada hari kerja, dan hanya sekali sehari. Peserta tidak dapat mengubah absensi sendiri; hubungi mentor atau admin."],
    ["Tombol Check Out ditolak.", "Check Out baru bisa mulai pukul 16.00 WITA dan setelah absen masuk berstatus Hadir."],
    ["Lowongan belum tampil bagi institusi.", "Lowongan industri harus berstatus Disetujui oleh admin dan masih Dibuka (kuota belum habis)."],
    ["Pengajuan berstatus Revisi.", "Buka pengajuan, perbaiki sesuai catatan admin, lalu kirim ulang."],
    ["Laporan akhir tidak bisa diunggah lagi.", "Satu peserta satu laporan. Unggah ulang hanya bila berstatus Revisi; yang sudah Disetujui tidak dapat diubah."],
    ["Tutorial tidak muncul sendiri.", "Klik Panduan penggunaan di menu kiri, lalu Mulai tutorial halaman ini."],
], "sm")}
{h2("E", "Bantuan")}
<ul class="tight"><li><b>Di dalam aplikasi:</b> tombol <b>Panduan penggunaan</b> (tutorial tiap halaman) dan asisten <b>SIMA</b>.</li><li><b>Mentor:</b> untuk pertanyaan tugas, absensi, dan logbook.</li><li><b>Admin BBLSDM Komdigi Makassar:</b> untuk akun, data, dan kendala aplikasi; dapat dihubungi lewat <b>Chat Publik</b>.</li></ul>
''')

# ======================================================================
# BAB VI PENUTUP (1 hal)
# ======================================================================
P("body", f'''
{chap(6, "Penutup")}
<p class="dc">Petunjuk Teknis ini diharapkan membantu setiap pengguna memakai SIMAGANG dengan benar, tertib, dan efisien. Karena aplikasi terus dikembangkan, tampilan dan menu dapat berubah; tutorial di dalam aplikasi selalu menjadi rujukan terbaru untuk setiap halaman.</p>
<p>Kritik, saran, dan temuan kekeliruan dapat disampaikan kepada admin untuk perbaikan edisi berikutnya.</p>
{h2("A", "Rangkuman Butir Penting")}
{table(["Peran", "Tiga hal yang perlu diingat"], [
    ["Peserta Magang", "Absen masuk 08.00–12.00 WITA dan Check Out mulai 16.00 WITA · isi logbook · kirim tugas ke Review saat selesai dan unggah laporan akhir."],
    ["Mentor", "Tinjau kartu Perlu Review secara rutin · setujui logbook · beri nilai laporan akhir (hanya sekali) dan terbitkan sertifikat."],
    ["Admin / Super Admin", "Verifikasi lowongan dan proses pengajuan (final bila Disetujui/Ditolak) · jaga kebenaran data master · lepas peserta yang selesai."],
    ["Institusi", "Lengkapi profil sebelum mengajukan · perbaiki pengajuan berstatus Revisi · pantau kehadiran dan nilai peserta."],
    ["Industri", "Lengkapi profil · tunggu verifikasi lowongan · proses pengajuan masuk dan pantau peserta."],
], "sm")}
{h2("B", "Riwayat Dokumen")}
{table(["Edisi", "Tanggal", "Keterangan"], [["1", "November 2026", "Penerbitan pertama."]], "sm")}
''')

P("back", f'''
<div class="card"><img src="{LOGO}" alt="Komdigi"></div>
<div class="tx">Petunjuk Teknis Penggunaan SIMAGANG<small>BBLSDM Komdigi Makassar<br>Kementerian Komunikasi dan Digital · Edisi 1 · November 2026</small></div>
<div class="blk"></div>
''')

# ======================================================================
# PENOMORAN, DAFTAR ISI, RENDER
# ======================================================================
FRONT_ROMAN = {2: "iii", 3: "iv"}
label = []
body_n = 0
for idx, pg in enumerate(pages):
    if pg["kind"] == "body":
        body_n += 1
        label.append(str(body_n))
    else:
        label.append(FRONT_ROMAN.get(idx, ""))
    pg["label"] = label[-1]

# kumpulkan entri daftar isi
entries = []
for pg in pages:
    if pg["kind"] != "body":
        continue
    for m in re.finditer(r'<(div class="chap"|h2)([^>]*)data-toc="(\d)"([^>]*)>', pg["inner"]):
        tag, a, lvl, b = m.groups()
        attrs = a + b
        title = re.search(r'data-title="([^"]*)"', attrs).group(1)
        key = re.search(r'data-key="([^"]*)"', attrs)
        no = re.search(r'data-no="([^"]*)"', attrs)
        entries.append({"lvl": int(lvl), "title": title, "no": no.group(1) if no else "", "page": pg["label"], "key": key.group(1) if key else None})

# peta kunci halaman
keymap = {}
for e in entries:
    if e["key"]:
        keymap[e["key"]] = e["page"]
    if e["lvl"] == 1:
        keymap[e["no"]] = e["page"]

toc_items = []
for e in entries:
    if e["lvl"] == 1:
        toc_items.append(f'<li class="c1"><span class="l"><span class="r">{e["no"]}</span>{e["title"].upper()}</span><span class="d"></span><span class="n">{e["page"]}</span></li>')
    else:
        toc_items.append(f'<li class="c2"><span class="l">{e["title"]}</span><span class="d"></span><span class="n">{e["page"]}</span></li>')
toc_html = '<div class="chap" style="margin-bottom:3mm"><div class="no" style="font-size:11pt">iv</div><h1 style="font-size:13pt">Daftar Isi</h1></div><ul class="toc">' + "".join(toc_items) + "</ul>"

out = []
for idx, pg in enumerate(pages):
    inner = pg["inner"].replace("@@TOC@@", toc_html)
    inner = re.sub(r"@@([A-Za-z.]+)@@", lambda m: keymap.get(m.group(1), "?"), inner)
    parity = "odd" if (idx + 1) % 2 == 1 else "even"
    k = pg["kind"]
    if k in ("cover", "inner", "back"):
        cls = {"cover": "cover", "inner": "inner", "back": "back"}[k]
        out.append(f'<section class="pg {cls} {parity}">{inner}</section>')
    else:
        run = "Petunjuk Teknis SIMAGANG"
        out.append(f'<section class="pg {parity}"><div class="run">{run}</div><div class="body">{inner}</div><div class="tab">{pg["label"]}</div></section>')

doc = f'''<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Petunjuk Teknis SIMAGANG</title>
<link rel="stylesheet" href="juknis.css"></head><body>
{"".join(out)}
</body></html>'''
open(os.path.join(HERE, "juknis.html"), "w", encoding="utf-8").write(doc)
print("halaman:", len(pages), "| bab/subbab di daftar isi:", len(entries))
