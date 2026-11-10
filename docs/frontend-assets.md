# Aset Frontend: cara kerja dan cara membangun ulang

Catatan untuk tim. Semua aset kini **lokal** (tanpa CDN), supaya halaman tidak menunggu domain luar dan tetap jalan di jaringan yang membatasi akses internet.

## 1. Tailwind CSS (build statis)

Sebelumnya Tailwind dikompilasi di browser lewat `cdn.tailwindcss.com` (398 KB JS yang menahan render). Sekarang memakai CSS hasil build: `public/css/tailwind.css`. **Berkas hasil build ikut di-commit**, jadi server produksi tidak perlu Node.

```bash
npm install          # sekali saja
npm run build:css    # bangun ulang setelah menambah/mengubah kelas Tailwind di view atau JS
npm run watch:css    # saat mengembangkan
```

- Konfigurasi: `tailwind.config.js` (pemindaian `resources/views`, `public/js`, `app`, dan view paginasi bawaan Laravel).
- **Kelas yang dibentuk dinamis** (mis. `'bg-' + warna`) tidak terbaca pemindai. Tulis kelas lengkap di kode, atau daftarkan di `safelist` pada `tailwind.config.js`.
- Tailwind sengaja dimuat di **akhir `<head>`** (sama seperti perilaku CDN dulu), supaya urutan kaskade terhadap CSS halaman tidak berubah.

## 2. Font dan ikon

| Aset | Lokasi | Catatan |
|---|---|---|
| Plus Jakarta Sans, DM Mono | `public/css/fonts.css`, `public/fonts/` | Plus Jakarta berupa **satu berkas variabel** per subset (bobot 200 sampai 800). |
| Poppins | `public/css/fonts-poppins.css` | Hanya halaman publik (landing, artikel). |
| Font Awesome 6.4.0 Free | `public/vendor/fontawesome/` | **Subset**: hanya ikon yang namanya muncul di kode. Lihat catatan di bawah. |
| Alpine.js, SortableJS | `public/vendor/alpine/`, `public/vendor/sortable/` | Disalin dari CDN, versi dipatok. |
| Driver.js (tutorial) | `public/vendor/driverjs/` | Dimuat malas, hanya saat tutorial dimulai. |

**Menambah ikon Font Awesome baru:** jika ikon belum ada di subset, tampilannya kosong atau berganti glyph. Buat ulang subset dari paket resmi (fontTools `pyftsubset`, ambil juga kode karakter 1 sampai 2 digit seperti `\2b` untuk `fa-plus`) atau ganti sementara ke `all.min.css` resmi.

## 3. CSS Workspace dipisah

| Berkas | Dipakai oleh |
|---|---|
| `workspace-shell.css` (±12 KB) | Semua halaman yang punya Workspace (lonceng, toast, panel Panduan, ikon "?"). |
| `workspace.css` + `workspace-ui.css` | Halaman Workspace/Notifikasi dan halaman yang memakai komponen `x-workspace.*` (dimuat otomatis lewat `<x-workspace.page-css />`). |
| `workspace-tour.css` | Dimuat malas bersama Driver.js. |

**Aturan penting:** `workspace-shell.css` dan `workspace-tour.css` adalah **salinan** sebagian aturan dari `workspace.css` / `workspace-ui.css` (urutan kaskade halaman Workspace tetap memakai berkas asli). Jika Anda mengubah aturan shell (`.ws-dropdown`, `.ws-toast*`, `.ws-guide*`, `.ws-help`, `.ws-slideover`, `.ws-icon-btn`, dst.), **ubah juga salinannya**.

## 4. Pola lain yang perlu diketahui

- **Skrip per halaman** memakai `@pushOnce('scripts', 'kunci')` (mis. `x-workspace.groups-js`), sehingga hanya dimuat di halaman yang membutuhkannya.
- **CSS halaman** yang dulu inline kini di `public/css/pages/<nama-view>.css` (dimuat lewat `<link>` di tempat yang sama, jadi urutan kaskade tidak berubah dan berkasnya bisa di-cache). Edit CSS-nya di berkas itu, bukan di view.
- **Tab halaman kelompok** (kecuali Aktivitas) dirender saat pertama kali dibuka (`seen` pada `wsWorkspaceShow`). Komponen baru di dalam tab tidak boleh bergantung pada event yang terjadi sebelum tab dibuka (lihat contoh penanganan di `channels.js`).
- **Berpindah halaman** tidak memakai splash, melainkan bar tipis di atas layar (`public/js/splash.js`). Form yang dikirim dikunci otomatis agar tidak terkirim dua kali.

## 5. Catatan untuk server produksi (di luar frontend)

Server dev `php artisan serve` tidak mengompres dan tidak men-cache berkas statis, sehingga ukuran di dev lebih besar dari produksi. Pastikan web server produksi:

- mengaktifkan kompresi teks (gzip/brotli) untuk `css`, `js`, `svg`, dan HTML;
- memberi `Cache-Control` panjang untuk `/css`, `/js`, `/fonts`, `/vendor` (URL sudah memakai `?v=<waktu-ubah>` sehingga aman di-cache lama).

Gambar unggahan (foto profil 1 sampai 7 MB) perlu diperkecil saat diunggah. Itu pekerjaan backend dan belum dikerjakan.
