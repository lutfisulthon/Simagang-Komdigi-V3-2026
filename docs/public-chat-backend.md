# Kebutuhan Backend: Chat Publik (dashboard, pengguna login)

Untuk: tim backend. Frontend sudah selesai dan **saat ini berjalan dengan data contoh** di browser (tanda "Mode contoh" tampil di header halaman). Dokumen ini menjelaskan yang dibutuhkan agar obrolan benar-benar tersambung.

Berkas frontend: halaman `resources/views/chat-publik/index.blade.php`, pemuat `resources/views/public-chat.blade.php` (di-include `layouts/app` untuk semua halaman login), menu `resources/views/layouts/partials/nav-item.blade.php`, `public/js/public-chat.js`, `public/css/public-chat.css`.

**Route.** Halaman ada di `GET /chat-publik` (nama `public-chat.index`, middleware `auth`), didaftarkan frontend di `routes/public-chat.php` sebagai closure yang hanya mengembalikan view (di-`require` dari `routes/web.php`). Saat controller backend siap, closure itu cukup diganti controller; tidak ada perubahan tampilan.

Tampilan dua panel: kiri = pencarian, saringan peran, dan **daftar orang di ruang**; kanan = percakapan dengan bilah pengumuman tersemat di bawah header. Daftar orang, pencarian, dan saringan peran **dibentuk frontend dari pesan yang sudah dimuat** (pengirim unik, pesan terakhirnya, perannya), jadi **tidak butuh endpoint tambahan**. Catatan: dengan cara ini hanya orang yang pernah berpesan dalam 50 pesan terakhir yang muncul; bila ingin daftar anggota lengkap, backend dapat menambahkan `members` di respons `GET` (frontend belum memakainya).

Chat Publik adalah **halaman penuh** (bukan panel mengambang) dengan menu di sidebar semua dashboard (admin, mentor, peserta, institusi, industri), grup "Utama" tepat setelah Dashboard. Menu itu menampilkan **lencana merah jumlah pesan belum dibaca** (maks. 99+) di semua halaman; di HP, tombol menu (hamburger) diberi titik merah. Landing page (tamu) **tidak** memuatnya.

Jumlah belum dibaca dihitung frontend dari pesan orang lain yang lebih baru dari penanda "terakhir dibaca" per pengguna (saat ini disimpan di browser). Agar akurat lintas perangkat, backend dapat menyimpan penanda itu per user dan mengirim `unread` di respons `GET` (opsional; frontend memakainya bila ada).

## 1. Cara menyambungkan

Isi konfigurasi sebelum `public-chat.js` dimuat, di `public-chat.blade.php`. Blok `user` (id, nama, peran) sudah diisi otomatis dari sesi login, jadi cukup tambahkan `mock` dan `endpoints`:

```html
<script>
    window.PublicChatConfig = Object.assign({ mock: false }, {
        endpoints: {
            list: "{{ route('public-chat.messages.index') }}",
            send: "{{ route('public-chat.messages.store') }}",
        },
    }, { user: @js([...]) });
</script>
```

Tanpa `endpoints` (atau dengan `mock: true`) chat tetap memakai data contoh. Tidak ada perubahan tampilan yang diperlukan.

## 2. Endpoint

Kedua endpoint harus berada di grup middleware `auth` (bukan terbuka untuk tamu). Identitas penulis (nama, peran) **diambil server dari sesi login**, bukan dari isi permintaan, sehingga tidak bisa dipalsukan.

### `GET /public-chat/messages?after=<id>`

- Tanpa `after`: kembalikan **50 pesan terakhir** (urut lama ke baru). Dengan `after`: hanya pesan yang lebih baru dari id itu.
- Respons `200`:

```json
{
  "messages": [
    { "id": "812", "uid": "32", "name": "Budi Santoso", "role": "intern", "text": "Halo kak...", "ts": 1794400000000 },
    { "id": "813", "uid": "2",  "name": "Administrator", "role": "admin",  "text": "Halo Budi!", "ts": 1794400060000 }
  ],
  "online": 14,
  "pins": [
    { "id": "790", "uid": "5", "name": "Herman", "role": "mentor", "text": "Pengumuman: presentasi progres Jumat 09.00...", "ts": 1794000000000, "pinned_at": 1794000100000, "pinned_until": 1794604900000 }
  ]
}
```

`pins` = **seluruh pengumuman yang masih aktif** (bukan hanya yang baru), selalu dikirim penuh di setiap respons, urut terbaru disematkan lebih dulu. Frontend mengganti daftarnya setiap kali menerima `pins`, jadi sematan yang dihapus atau berakhir otomatis hilang di semua pengguna pada polling berikutnya.

| Field | Keterangan |
|---|---|
| `id` | Urut naik, dipakai untuk `after`. String atau angka. |
| `uid` | **ID user** penulis (string). Frontend membandingkannya dengan ID user yang sedang login untuk menandai pesan milik sendiri ("Anda"). |
| `name` | Nama tampilan (nama akun). |
| `role` | `admin`, `mentor`, `intern`, `institusi`, atau `industri`; tampil sebagai lencana peran. |
| `text` | Teks polos. **Frontend merender sebagai teks (bukan HTML)**; backend tetap wajib membersihkannya. |
| `ts` | Milidetik epoch (UTC). |
| `online` | Perkiraan jumlah pengguna aktif (opsional, mis. jumlah user yang polling dalam 30 detik terakhir). |

Frontend memanggil endpoint ini setiap **5 detik saat panel terbuka** dan **30 detik saat tertutup** (untuk lencana pesan baru).

### `POST /public-chat/messages`

Request (JSON), hanya teks:

```json
{ "text": "Syaratnya apa saja?" }
```

Respons `201`: `{ "message": { ...bentuk pesan di atas... } }`. Galat: `422` (validasi), `429` (terlalu cepat); frontend menampilkan pesan galat-nya.

Khusus mentor, `pin_days` (opsional) mengirim pesan langsung sebagai pengumuman: `{ "text": "...", "pin_days": 7 }`.

### Pengumuman tersemat

Mentor dapat menyematkan **pesannya sendiri** sebagai pengumuman di bagian atas panel, terlihat oleh semua peran.

| Aturan | Nilai |
|---|---|
| Lama sematan | hanya `7` atau `30` hari; berakhir otomatis pada `pinned_until` (server tidak lagi menyertakannya di `pins` setelah lewat). |
| Siapa yang boleh menyematkan | hanya peran `mentor`, dan hanya pesan miliknya (`user_id` = user login). Selain itu → `403`. |
| Batas | maksimal **3 pengumuman aktif** sekaligus (seluruh ruang). Melebihi → `422` dengan `message` yang ditampilkan apa adanya, mis. "Maksimal 3 pengumuman aktif. Hapus salah satu dulu." |
| Menghapus | kapan saja oleh penulisnya (mentor yang sama). Yang dihapus hanya sematannya; pesan tetap ada di obrolan. |

- `POST /public-chat/messages/{id}/pin` dengan `{ "days": 7 }` atau `{ "days": 30 }` → `200` `{ "pins": [...] }` (daftar aktif terbaru).
- `DELETE /public-chat/messages/{id}/pin` → `200` `{ "pins": [...] }`.
- Kolom yang disarankan pada `public_chat_messages`: `pinned_at` dan `pinned_until` (nullable, di-index pada `pinned_until`).

## 3. Aturan validasi dan keamanan (wajib di server)

Frontend sudah membatasi, tetapi **tidak boleh dipercaya**.

| Aturan | Nilai |
|---|---|
| `text` | 1 sampai 10000 karakter setelah di-trim; buang karakter kontrol; ringkas baris kosong beruntun. **Simpan polos, jangan izinkan HTML.** |
| Penulis | `name`, `role`, dan `uid` selalu dari user yang login; abaikan field serupa dari klien. |
| Laju | Per user: maks. 1 pesan / 0,6 detik dan 40 / menit (`throttle`, balas `429`). |
| Tautan | Disarankan menolak/menyaring URL di pesan (mencegah spam dan phishing). Frontend tidak membuat tautan otomatis. |
| Akses | Hanya user terautentikasi; user nonaktif/ditangguhkan tidak boleh membaca maupun menulis. |

## 4. Moderasi (belum ada di tampilan)

Frontend belum menyediakan tombol laporkan/hapus. Disarankan:

- tabel `public_chat_messages` dengan kolom `id`, `user_id`, `text`, `hidden_at`, `created_at` (nama dan peran diambil dari relasi user);
- pesan yang disembunyikan moderator tidak dikembalikan oleh `GET`;
- halaman admin untuk menyembunyikan pesan atau membungkam user tertentu;
- retensi (mis. hapus pesan lebih dari 30 hari) dan kebijakan privasi, karena pesan terlihat oleh seluruh pengguna yang login.

## 5. Realtime (opsional)

Polling 5 detik sudah cukup untuk awal. Bila ingin realtime, pakai Reverb yang sudah ada di Workspace (channel privat `public-chat`, otorisasi: user login, event `message.created`); frontend dapat ditambah pendengar tanpa mengubah bentuk data di atas.

## 6. Daftar uji untuk backend

- [ ] Tamu (belum login) ditolak (`401`/redirect) pada `GET` dan `POST`.
- [ ] Setiap peran (admin, mentor, peserta, institusi, industri) dapat `GET` dan `POST`.
- [ ] `POST` dengan `text` kosong atau >10000 karakter → `422`.
- [ ] 41 `POST` dalam semenit dari user yang sama → `429`.
- [ ] `text` berisi `<img src=x onerror=...>` tersimpan sebagai teks dan tampil literal.
- [ ] Nama, peran, dan `uid` pada pesan selalu milik user yang login (field palsu dari klien diabaikan).
- [ ] `GET ?after=<id terakhir>` hanya mengembalikan pesan yang lebih baru.
- [ ] Pesan yang disembunyikan moderator tidak muncul di `GET`.
- [ ] Mentor dapat menyematkan pesannya dengan `days` 7 dan 30; nilai lain → `422`.
- [ ] Selain mentor (peserta, institusi, industri, admin) menyematkan → `403`; mentor menyematkan pesan orang lain → `403`.
- [ ] Sematan ke-4 saat sudah ada 3 aktif → `422`.
- [ ] Sematan yang melewati `pinned_until` tidak lagi muncul di `pins`.
- [ ] Hanya penyemat yang dapat `DELETE` sematannya; pesan tetap ada setelah sematan dihapus.
