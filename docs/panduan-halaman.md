# Panduan penggunaan per halaman (tutorial semua menu sidebar)

Tombol **Panduan penggunaan** di sidebar (semua peran: admin, super admin, mentor, peserta, institusi, industri) membuka panel yang berisi:

1. **Halaman ini**: judul, ringkasan, dan tombol *Mulai tutorial halaman ini* (sorotan langkah demi langkah memakai Driver.js).
2. **Ringkasan cara pakai**: langkah-langkah yang sama dalam bentuk teks, bisa dibaca tanpa menjalankan tutorial.
3. **Tutorial halaman lain**: daftar menu sidebar peran itu. Klik salah satu untuk pindah ke halamannya dan langsung memulai tutorialnya (tautan `?tutorial=1`). Halaman yang sudah selesai ditandai *Sudah dilihat* (disimpan di browser).
4. **Workspace** (hanya peran yang memakai Workspace): pintasan ke panduan istilah, alur kerja, dan FAQ Workspace yang sudah ada.

Tutorial tidak muncul sendiri (kecuali tutorial pertama kali Workspace yang sudah ada); pengguna yang bingung membukanya lewat Panduan.

## Berkas

| Berkas | Isi | Dimuat |
|---|---|---|
| `resources/views/layouts/partials/page-guide.blade.php` | Menyiapkan `window.PageGuideConfig` (peran, label, id pengguna) dan memuat `page-guide.js`. Di-include `layouts/app` untuk semua halaman login. | semua halaman |
| `public/js/page-guide.js` | Pemuat kecil (~4 KB): `PageGuide.open()`, `PageGuide.start()`, dan pembaca `?tutorial=1`. | semua halaman |
| `public/js/page-guide-ui.js` | Panel Panduan dan penjalan tutorial. | saat Panduan dibuka |
| `public/js/page-guide-tours.js` | **Isi tutorial** tiap halaman (data). | saat Panduan dibuka |
| `public/css/page-guide.css` | Gaya panel (terang, gelap netral). | saat Panduan dibuka |
| `public/css/workspace-tour.css`, `public/vendor/driverjs/` | Gaya kartu tutorial dan pustaka Driver.js (dipakai bersama tutorial Workspace). | saat tutorial dimulai |

Tombol di sidebar ada di `resources/views/layouts/partials/sidebar-footer.blade.php`. Panel dan tutorial tidak bergantung pada kode Workspace, jadi institusi dan industri (yang tidak memuat aset Workspace) memakainya juga.

## Menambah atau mengubah tutorial

Semua isi ada di `public/js/page-guide-tours.js`, berupa satu panggilan per halaman:

```js
T('mentor-absensi', ['^/mentor/attendance$'], 'Absensi Anak Bimbingan', 'Ringkasan satu kalimat.', [
    S(HERO,   'Judul langkah', 'Penjelasan langkah.'),
    S(FILTER, 'Cari dan saring', 'Isi kolom pencarian atau pilih saringan.'),
    S(TABLE,  'Data absensi', 'Penjelasan tabel.', 'top'),
]);
```

- `T(id, [regex path], judul, ringkasan, langkah)`. Regex dicocokkan dengan path halaman (tanpa domain dan tanpa `/` di akhir). Halaman yang tidak cocok dengan tutorial mana pun memakai **tutorial umum** yang dibentuk dari struktur halaman (judul, saringan, tombol Tambah, tabel, pagination).
- `S(el, judul, teks, sisi?, peran?)`:
  - `el` penyorot: selector CSS, daftar selector (yang pertama terlihat dipakai), `btn('Teks tombol')` (tautan/tombol di `main` yang teksnya diawali itu), `card('Judul kartu')` (kartu yang memuat judul ber-teks itu), atau `null` (langkah di tengah layar).
  - `sisi`: `top`, `bottom` (bawaan), `left`, `right`.
  - `peran`: daftar peran yang melihat langkah itu (`'mentor'`, `'intern'`, `'admin'`, `'institusi'`, `'industri'`). Admin dan super admin sama-sama `'admin'`.
- Langkah yang elemennya tidak ada (mis. tabel kosong, tidak ada pagination) **dilewati otomatis** saat tutorial, tetapi tetap tampil di *Ringkasan cara pakai*.
- Selector bersama: `HERO`, `FILTER` (form GET), `TABLE`, `PAGER`.
- Tulis teks sesuai yang benar-benar ada di halaman. Bila UI halaman berubah, perbarui teks dan selector-nya.

## Memeriksa

Di konsol browser pada halaman mana pun (setelah login): `PageGuide.load().then(ui => console.log(ui.inspect()))` menampilkan tutorial yang dipakai halaman itu dan tiap langkahnya (`found: false` berarti elemennya tidak ketemu).
