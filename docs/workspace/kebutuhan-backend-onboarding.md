# Kebutuhan Backend — Penyimpanan Tutorial Pertama Kali (Onboarding)

Untuk: tim backend. Pelengkap [kontrak-frontend.md](kontrak-frontend.md).

## 1. Latar belakang

Frontend sudah menampilkan **tutorial pertama kali** (tur sorotan) untuk mentor, anak magang, dan admin. Tutorial tampil sekali per pengguna, lalu bisa diulang dari panel Panduan.

Saat ini status "sudah pernah lihat" hanya disimpan di **browser** (localStorage, kunci per ID pengguna). Akibatnya tutorial muncul lagi bila pengguna berganti browser/perangkat atau membersihkan data browser.

Agar permanen per **akun**, frontend butuh satu kolom di database, dua endpoint, dan dua nilai tambahan pada konfigurasi halaman. **Frontend tidak perlu diubah lagi**: kodenya (`public/js/workspace/tour.js`) otomatis memakai server begitu kedua nilai konfigurasi di §4 tersedia.

## 2. Database

Satu migration, tabel `users`:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `onboarding` | `json`, nullable | Status tutorial per nama tutorial. Cast `array` di model `User`. |

Isi kolom (kunci = nama tutorial):

```json
{
  "dasar":            { "version": 1, "status": "completed", "at": "2026-11-06T15:49:08Z" },
  "daftar-kelompok":  { "version": 1, "status": "skipped",   "at": "2026-11-06T15:49:22Z" },
  "halaman-kelompok": { "version": 1, "status": "completed", "at": "2026-11-06T15:49:39Z" }
}
```

- `status`: `completed` (sampai langkah terakhir) atau `skipped` (ditutup / "Lewati").
- `version`: nomor versi tutorial saat dilihat. Bila frontend menaikkan versi sebuah tutorial, tutorial itu tampil lagi ke semua orang. **Server tidak perlu membandingkan versi**, cukup menyimpan apa adanya.
- `at`: waktu ISO 8601 (UTC).

## 3. Endpoint

Hanya untuk pengguna yang sedang login (`auth`), hanya mengubah data **miliknya sendiri**. Tidak perlu policy per record. Rute bebas, asal alamat dasarnya dikirim sebagai `onboardingUrl` (§4). Usulan: `/onboarding`.

### `POST /onboarding` — simpan hasil satu tutorial

Request (JSON):

```json
{ "tour": "dasar", "version": 1, "status": "completed", "at": "2026-11-06T15:49:08.667Z" }
```

Validasi:

| Field | Aturan |
|---|---|
| `tour` | wajib, salah satu dari daftar tetap: `dasar`, `daftar-kelompok`, `halaman-kelompok` |
| `version` | wajib, integer, min 1 |
| `status` | wajib, `completed` atau `skipped` |
| `at` | opsional, tanggal ISO 8601; bila kosong pakai waktu server |

Perilaku: menggabungkan (merge) ke kolom `onboarding` pengguna, menimpa kunci `tour` yang sama. Respons `200` atau `204` (isi bebas, frontend tidak membacanya). Galat validasi `422` format standar Laravel.

### `DELETE /onboarding/{tour}` — reset satu tutorial (tombol "Ulangi tutorial")

Menghapus kunci `{tour}` dari kolom `onboarding` pengguna. `{tour}` divalidasi terhadap daftar tetap yang sama. Respons `200`/`204`. Bila kunci tidak ada, tetap sukses (idempoten).

Frontend memanggil `DELETE` ini sebelum memutar ulang tutorial, jadi tutorial yang diulang dari Panduan dicatat ulang saat selesai.

### Daftar tutorial

Daftar nama ada di frontend (`TOURS` di `public/js/workspace/tour.js`). Bila frontend menambah tutorial baru, daftar tetap di validasi server **harus ikut ditambah**. Usulan: simpan sebagai konstanta/enum tunggal di backend, mis. `OnboardingTour`.

## 4. Data awal di halaman

Frontend membaca konfigurasi dari `window.SimagangWorkspace`, yang disusun di `resources/views/layouts/partials/workspace-head.blade.php` (variabel `$wsConfig`). Tambahkan dua kunci:

```php
$wsConfig = [
    // ...kunci yang sudah ada...
    'onboarding'    => $wsUser->onboarding ?? [],          // object kosong bila belum ada
    'onboardingUrl' => route('onboarding.store'),          // alamat dasar, mis. https://host/onboarding
];
```

Catatan:

- `onboarding` harus berupa **object JSON** (bukan array list). Pastikan `[]` kosong terkirim sebagai `{}`; di PHP gunakan `(object) []` atau `->onboarding ?: new \stdClass`.
- Frontend memakai `onboardingUrl` untuk `POST` (simpan) dan `DELETE onboardingUrl + '/' + tour` (reset), jadi route `DELETE` harus berada di bawah alamat dasar yang sama.
- Bila `onboardingUrl` tidak ada, frontend kembali memakai localStorage seperti sekarang. Jadi penerapannya bisa bertahap.
- Data dari server diutamakan atas data browser.

## 5. Contoh implementasi singkat (usulan)

```php
// database/migrations/xxxx_add_onboarding_to_users_table.php
Schema::table('users', fn (Blueprint $t) => $t->json('onboarding')->nullable());

// app/Models/User.php  → $casts: 'onboarding' => 'array'

// routes/web.php (grup auth)
Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
Route::delete('onboarding/{tour}', [OnboardingController::class, 'destroy'])->name('onboarding.destroy');
```

## 6. Daftar uji untuk backend

- [ ] `POST` dengan `tour` tidak dikenal → `422`.
- [ ] `POST` dua kali untuk `tour` yang sama → kunci tertimpa, kunci lain tetap.
- [ ] `DELETE` kunci yang tidak ada → sukses, tidak error.
- [ ] Pengguna A tidak bisa mengubah data pengguna B (endpoint tidak menerima ID pengguna).
- [ ] Pengguna belum login → `401`/redirect login.
- [ ] `window.SimagangWorkspace.onboarding` bertipe object (`{}`) saat kosong, bukan `[]`.
- [ ] Setelah menyelesaikan tutorial lalu login di browser lain, tutorial tidak muncul lagi.

## 7. Catatan frontend

- Jalur server di `tour.js` (`storage.set` / `storage.reset`) **belum diuji end-to-end**, karena endpoint belum ada. Kegagalan memanggil server tidak mengganggu pengguna (data tetap tersimpan di browser).
- Saklar pengujian otomatis: `localStorage['ws-tour-disabled'] = '1'` mematikan tampil-otomatis.
