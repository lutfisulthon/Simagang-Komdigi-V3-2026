# Kontrak Frontend ↔ Backend — Modul Workspace

Dokumen ini adalah acuan bersama antara frontend (Blade + Alpine) dan backend (controller, policy, resource) untuk modul Workspace di [PRD-Workspace-Mentor-Intern.md](../../PRD-Workspace-Mentor-Intern.md). Frontend dibangun lebih dulu dengan data contoh. Backend cukup memenuhi bentuk data di sini agar UI langsung berfungsi.

> Status: **draf v1.4 (Timeline, Laporan, Otomasi, 29 Okt 2026; Deadline v1.3; Ketua sprint v1.2; Sprint & Backlog v1.1; model Kelompok v1.0)**. Bagian bertanda *usulan* belum ada di PRD dan perlu disepakati.
>
> **Perubahan besar v1.0 — Kelompok menggantikan "Workspace tim" + "Space intern".** Satu-satunya wadah kerja adalah
> *kelompok* yang dibuat mentor dari anak bimbingannya. Aturan:
> - Workspace tim otomatis per unit kerja dan space privat per intern **dihapus**. Unit kerja (tabel `teams`) hanya punya channel umum `#umum-<tim>`.
> - Mentor membuat kelompok dari menu Anak Bimbingan (centang → "Buat kelompok") atau halaman Workspace, dengan nama bebas dan warna. Nama & warna bisa diubah.
> - **1 anak magang = 1 kelompok.** Anggota **tidak bisa dikeluarkan** (tidak ada endpoint/aksi hapus anggota).
> - Kelompok **selesai magang** otomatis saat semua anggotanya lewat `end_date`, atau ditandai manual oleh mentor. Selesai = `is_read_only`.
> - Intern yang belum dimasukkan ke kelompok belum punya workspace (menu Workspace menampilkan keterangan).
> - Hanya mentor/admin yang membuat tugas (to-do pribadi intern di space privat dihapus).
>
> Lihat §7 #18 untuk daftar perubahan PRD/backend.
>
> **v1.1 — Sprint & Backlog (gaya Jira).** Tab baru **Backlog** di setiap kelompok: sprint berjalan, sprint terencana,
> lalu Backlog (tugas tanpa sprint). Mentor menyusun tugas ke sprint (seret / menu "Pindah ke"), membuat, memulai, dan
> menyelesaikan sprint. **Board hanya menampilkan sprint berjalan**, dan **assign dilakukan di board** (tombol avatar di
> kartu). Anak magang: Backlog hanya baca. Deadline tetap per tugas. Lihat §7 #19.
>
> **v1.2 — Ketua sprint & perpanjang deadline.** Sprint dibuat dengan **deadline**, **ketua tim** (anggota kelompok, per
> sprint), dan **daftar tugas cepat**. **Ketua yang membagi tugas** sprint-nya ke anggota di Board (boleh beberapa orang
> per tugas); mentor tetap bisa sebagai cadangan. Sprint tidak bisa dimulai tanpa ketua. Tugas tanpa assignee ditandai
> "belum dibagi". Mentor dapat **memperpanjang deadline** tugas (membuka kunci kirim hasil). Notifikasi baru dikirim
> **lewat email + in-app**. Lihat §7 #19 bagian v1.2.
>
> **v1.3 — Tampilan deadline per tugas.** Deadline tetap milik **tugas** (`tasks.due_at`); tidak ada tabel baru. Yang baru
> hanya tampilan: (1) kartu kelompok di daftar menampilkan tugas terdekat + jumlah tugas lewat deadline, (2) daftar kelompok
> diurutkan "deadline terdekat" + filter "Perlu perhatian", (3) tab **Deadline** di tiap kelompok (agenda + kalender, semua
> peran, hanya tugas kelompok itu), (4) chip deadline di hero kelompok, (5) widget dashboard mentor "Deadline 7 Hari ke
> Depan". Lihat §7 #20.
>
> **v1.4 — Timeline, Laporan, Otomasi.** Tiga tab baru di halaman kelompok. **Timeline** (semua peran, hanya baca): jalur sprint + batang
> tugas dari `start_at` sampai `due_at`, murni dari `$initialTasks` dan `$sprints` (tanpa endpoint baru). **Laporan** (semua peran):
> burndown, tugas selesai per sprint/minggu, sebaran status, ketepatan waktu, dan (mentor/admin) beban per anggota. Anak magang hanya
> melihat data tugasnya sendiri. Dihitung di client dari data yang sama; dashboard mentor menambah dua grafik mini lewat
> `GET /workspace/summary` (§8). **Otomasi** (mentor/admin, kelompok aktif): aturan pekerjaan berulang berbentuk When → If → Then
> (jadwal → kondisi → aksi buat tugas / kirim notifikasi). Lihat §7 #21.

---

## 1. Cara kerja selama backend belum ada

| Komponen | Lokasi | Keterangan |
| --- | --- | --- |
| Route preview | `routes/workspace-preview.php` | Dimuat dari `routes/web.php` **hanya** saat `APP_ENV=local`. Hanya merender view dengan data contoh. |
| Mode mock | `public/js/workspace/mock.js` | Aktif otomatis selama route `workspace.preview.components` terdaftar. Semua panggilan `WS.api.*` dialihkan ke handler mock. |
| Lapisan data | `public/js/workspace/api.js` | Satu-satunya tempat yang tahu URL endpoint. |
| Mock tugas & channel | `mock-tasks.js`, `mock-channels.js` | Perubahan hanya di memori browser. Di channel, anggota lain "mengirim" pesan tiap 20–35 detik untuk menguji polling, badge, dan notifikasi mention. Pesan berisi `/gagal` gagal terkirim sekali (uji tampilan "Coba lagi"). |
| Uji error & lambat | Console browser | `WS.mock.failing = ['tasks.move']` membuat panggilan itu gagal 500 (`WS.mock.failStatus = 0` → offline). `WS.mock.slow = 3000` menambah jeda. Kosongkan untuk kembali normal. |

**Saat backend siap:**

1. Hapus `routes/workspace-preview.php`, `routes/workspace-preview-data.php`, dan blok `require` di akhir `routes/web.php`.
2. Daftarkan route asli dengan **nama yang sama** (`workspace.index`, `workspace.show`, `notifications.index`, …).
3. Hapus `resources/views/workspace/preview/`.
4. Mode mock otomatis mati, dan `WS.api` langsung memanggil endpoint asli.

Layout (`layouts/app.blade.php`) hanya menampilkan menu Workspace, top bar, dan lonceng bila `Route::has('workspace.index')` dan user adalah admin (`super_admin`/`admin_full`), mentor, atau intern. Di production sebelum backend rilis, tidak ada yang berubah.

---

## 2. Konvensi umum

- Semua endpoint JSON menerima header `Accept: application/json`, `X-Requested-With: XMLHttpRequest`, `X-CSRF-TOKEN`.
- Waktu dalam **ISO 8601 dengan offset**, misalnya `2026-10-28T17:00:00+08:00`. Frontend memformatnya sendiri.
- Respons resource dibungkus `data` (gaya Laravel API Resource). Daftar ber-cursor menyertakan `meta`.
- Error:
  - `403` → `{ "message": "..." }`. Ditampilkan sebagai toast.
  - `422` → format validasi Laravel `{ "message": "...", "errors": { "field": ["..."] } }`. Ditampilkan di bawah field.
  - `419`, `429`, `5xx` → toast dengan pesan umum.
- **Markdown dirender di server.** Field teks bebas dikirim dua kali: `body` (mentah, untuk form edit) dan `body_html` (sudah di-escape dan disanitasi). Frontend hanya menampilkan `*_html` lewat `x-html`.
- Hak akses per item dikirim sebagai objek `can`, dihitung dari Policy. Frontend tidak menebak hak akses dari role.

---

## 3. Enum

Disimpan sebagai string (PHP Enum `TaskStatus`, `TaskPriority`). Label harus sama dengan `WS.STATUS`/`WS.PRIORITY` di `core.js` dan komponen `x-workspace.status-badge`/`priority-badge`.

| `status` | Label |
| --- | --- |
| `todo` | To Do |
| `in_progress` | In Progress |
| `review` | Review |
| `revision` | Revisi |
| `done` | Selesai |

| `priority` | Label |
| --- | --- |
| `low` | Rendah |
| `medium` | Sedang (default) |
| `high` | Tinggi |
| `urgent` | Urgent |

"Terlambat" **bukan** status. Ia dikirim sebagai boolean `is_late` (diisi scheduler harian).

---

## 4. Bentuk data (resource)

### UserMini
```json
{ "id": 12, "name": "Budi Santoso", "role": "mentor", "avatar_url": "https://.../foto.jpg" }
```
`role`: `admin` | `mentor` | `intern`. `avatar_url` boleh `null`, sehingga frontend menampilkan inisial.

### InternMini (assignee)
```json
{ "id": 7, "user_id": 30, "name": "Andi Pratama", "avatar_url": null }
```
`id` = `interns.id` (sesuai PRD, assignee memakai `interns.id`).

### Workspace (= Kelompok)
```json
{
  "id": 3, "type": "group", "name": "Tim Redesign Website", "slug": "tim-redesign-website",
  "color": "#4f46e5", "description": "Redesain halaman publik Simagang." ,
  "team": { "id": 2, "name": "TIM DEA" },
  "mentor": UserMini,
  "status": "active", "created_at": "2026-10-06T09:00:00+07:00", "finished_at": null,
  "end_date": "2027-01-04",
  "is_archived": false, "is_read_only": false,
  "stats": { "todo": 4, "in_progress": 2, "review": 1, "revision": 0, "done": 9, "late": 1 },
  "unread_messages": 5,
  "members_count": 4,
  "members_preview": [ UserMini, UserMini, UserMini, UserMini ],
  "can": { "rename": true, "add_members": true, "finish": true },
  "active_sprint": { "id": 31, "name": "Sprint 2", "goal": "…", "start_date": "2026-10-22", "end_date": "2026-11-05" } | null,
  "deadline": { "next": { "id": 301, "code": "T-7", "title": "…", "due_at": "2026-10-31T17:00:00+07:00", "url": "/workspace/tasks/301" } | null,
                "overdue_count": 2, "due_soon_count": 1 } | null,
  "url": "/workspace/3"
}
```
- `type` selalu `"group"` (nilai lama `team`/`intern` tidak dipakai lagi).
- `color`: hex 6 digit, dipakai untuk ikon kelompok dengan teks putih — pilih warna dengan kontras ≥ 4,5:1 (daftar di `groups.js`).
- `team`: unit kerja mentor pembuat kelompok (untuk label & channel `#umum-<tim>`).
- `status`: `active` | `finished`. `finished_at` diisi saat selesai (otomatis/manual). `end_date` = `end_date` anggota paling akhir.
- `members_count` / `members_preview` termasuk mentor (urutan: mentor dulu).
- `can.rename` & `can.finish`: mentor pemilik + admin. `can.add_members`: mentor pemilik saja. Semua `false` bila `finished`.
- `stats.late` dihitung terpisah dan **tidak** ikut dijumlahkan ke total.
- `is_read_only` = kelompok selesai magang.
- `deadline` (v1.3) = ringkasan deadline **per tugas** untuk kartu di daftar kelompok; `null` bila kelompok `finished`. Hanya tugas
  berstatus `todo` / `in_progress` / `revision` yang dihitung (tugas `review` dan `done` sudah dikirim). Semua tugas kelompok ikut,
  termasuk backlog dan sprint terencana. `next` = tugas yang belum lewat dengan `due_at` terkecil (`null` bila tidak ada).
  `overdue_count` = `is_late = true` atau `due_at` < sekarang. `due_soon_count` = belum lewat dan `due_at` ≤ sekarang + 3 hari.
  Isi dengan satu query agregat per halaman (bukan satu query per kelompok). Lihat §7 #20.

### Sprint — *usulan* #19
```json
{ "id": 31, "workspace_id": 3, "name": "Sprint 2", "goal": "Selesaikan prototipe halaman utama",
  "status": "active", "start_date": "2026-10-22", "end_date": "2026-11-05",
  "leader": InternMini | null, "completed_at": null }
```
- `end_date` = **deadline sprint**, wajib sejak sprint dibuat (v1.2). `start_date` diisi saat sprint dimulai.
- `leader` (v1.2) = ketua tim sprint ini, anggota kelompok. Boleh `null` saat `planned`; **wajib** untuk memulai.
- `Workspace.active_sprint` ikut membawa `leader`.
- `TaskCard.can` menambah `assign` (mentor/admin, atau ketua sprint tugas itu bila sprint belum `completed`) dan `extend_deadline` (mentor/admin, tugas belum `done`).
- `status`: `planned` (tanggal boleh `null`) → `active` (tanggal wajib) → `completed` (`completed_at` diisi).
- **Maksimal satu sprint `active` per kelompok.** Urutan sprint `planned` = urutan dibuat.
- TaskCard menambah `sprint_id` (`null` = Backlog) dan `rank` (urutan di dalam sprint/backlog; terpisah dari `position` kolom board).

### Roster (anak bimbingan + kelompoknya) — *usulan* #18
```json
{ "id": 3, "user_id": 32, "name": "Budi Santoso", "avatar_url": null, "end_date": "2027-01-14",
  "group": { "id": 3, "name": "Tim Redesign Website", "color": "#4f46e5", "url": "/workspace/3" } | null }
```

### TaskCard (board & list)
```json
{
  "id": 41, "workspace_id": 3, "code": "T-12", "title": "Integrasi API absensi",
  "status": "review", "priority": "high", "position": 2,
  "start_at": null, "due_at": "2026-10-28T17:00:00+08:00", "completed_at": null,
  "is_late": false,
  "labels": [{ "id": 1, "name": "frontend", "color": "#6366f1" }],
  "assignees": [ InternMini ],
  "checklist": { "done": 2, "total": 5 },
  "comments_count": 3, "attachments_count": 1,
  "url": "/workspace/tasks/41",
  "can": { "update": true, "move": true, "review": false, "delete": false }
}
```
Arti setiap izin di `can`:

| Izin | Arti | Aturan (meniru `TaskPolicy`) |
| --- | --- | --- |
| `update` | Edit tugas | Mentor/admin, atau pembuat tugas (intern untuk to-do pribadinya) |
| `move` | Boleh drag & ubah status | Mentor/admin, atau intern yang di-assign |
| `review` | Tandai Selesai/Revisi | Mentor/admin |
| `delete` | Hapus tugas | Admin, atau mentor pembuat tugas |

Semua bernilai `false` bila workspace `is_read_only`. Status tujuan tetap divalidasi server. Intern hanya boleh memindahkan sampai `review`, dan tidak bisa memindahkan tugas yang sedang di `review` atau `done`. Frontend menyembunyikan target yang tidak diizinkan (`WS.tasks.allowedTargets` di `tasks.js`).

`$initialTasks` berisi semua tugas aktif + maks. 8 tugas Selesai terbaru. Angka Selesai di statistik tetap memakai `stats.done` dari server.

### TaskDetail = TaskCard +
```json
{
  "description": "**Tujuan**: ...", "description_html": "<p><strong>Tujuan</strong>: ...</p>",
  "creator": UserMini,
  "workspace": { "id": 3, "name": "Tim Aptika", "type": "team" },
  "checklist_items": [{ "id": 5, "content": "Buat endpoint", "is_done": true, "position": 0 }],
  "attachments": [ Attachment ],
  "result_links": [{ "url": "https://github.com/...", "label": "Repo" }],
  "score": null, "feedback": null, "feedback_html": null,
  "comments": [ Comment ],
  "activities": [ Activity ],
  "can": { "update": true, "move": true, "review": false, "delete": false, "comment": true }
}
```
`workspace` juga memuat `url`. `can.comment` hanya ada di TaskDetail (`false` bila read-only). `activities` urut terbaru dulu.

### Comment
```json
{ "id": 9, "user": UserMini, "body": "...", "body_html": "...", "attachments": [], "created_at": "...", "is_deleted": false,
  "can": { "delete": false } }
```

### Activity (dari `task_activities`)
```json
{ "id": 77, "user": UserMini, "action": "status_changed",
  "meta": { "from": "in_progress", "to": "review" },
  "task": { "id": 41, "code": "T-12", "title": "Integrasi API absensi", "url": "/workspace/tasks/41" },
  "created_at": "..." }
```
Kalimat ("Mentor A memindahkan T-12 ke Revisi") disusun frontend dari `action` + `meta` (`WS.activity.describe` di `workspace.js`). `user: null` ditampilkan sebagai "Sistem" (misalnya penanda terlambat oleh scheduler).

| `action` | `meta` yang dibaca frontend |
| --- | --- |
| `created`, `updated`, `commented` | — |
| `status_changed` | `{ from, to }` (nilai enum status) |
| `assigned`, `unassigned` | `{ assignee }` (nama intern) |
| `reviewed` | `{ decision: "approve" \| "revision", score? }` |
| `attachment_added` | `{ file }` (nama berkas) |
| `marked_late` | — |

### Attachment
```json
{ "id": 15, "original_name": "laporan.pdf", "mime": "application/pdf", "size": 482113,
  "is_image": false, "thumbnail_url": null, "download_url": "/workspace/attachments/15" }
```

### Channel
```json
{ "id": 4, "workspace_id": 3, "name": "umum-aptika", "type": "team",
  "description": "Channel umum Tim Aptika. Semua anggota tim otomatis bergabung.",
  "is_private": false, "is_archived": false,
  "members_count": 8, "members": null,
  "unread_count": 3, "last_read_message_id": 1200,
  "can": { "post": true, "manage": false, "pin": false } }
```
- `type`: `announcement` | `team` | `topic` | `direct`. `#pengumuman` memakai `workspace_id: null` dan `members_count: null`.
- `description` boleh `null`. Tampil di header channel.
- `members` (*usulan*) hanya diisi untuk `direct`: `UserMini[]` (intern + mentor). Frontend memakainya untuk judul DM ("Budi Santoso" dari sisi intern, "Andi & Budi" untuk admin) dan kandidat @mention. Channel lain: `null`.
- `last_read_message_id` menentukan letak penanda **"Pesan baru"** saat channel dibuka.
- `can.post`: `false` untuk intern di `#pengumuman`, semua anggota bila workspace `is_read_only`, dan admin di DM (admin hanya melihat). Frontend menampilkan keterangan sebagai ganti komposer.
- `can.manage`: ubah info channel, serta arsipkan/pulihkan channel `topic` (channel `team` dibuat sistem dan tidak bisa diarsipkan). `can.pin`: sematkan pesan. Keduanya dipakai sejak Fase 2.
- `pinned_count` (*usulan*): jumlah pesan disematkan, untuk angka di tombol 📌 header channel.
- `is_muted` (Fase 3, dari `channel_members.is_muted`): channel dibisukan oleh user ini. Badge tampil abu-abu dan tidak dihitung ke badge tab/`Workspace.unread_messages`. Mention tetap memunculkan notifikasi.
- Channel `topic` yang `is_archived: true` dipindah ke bagian "Diarsipkan" di daftar channel dan menjadi hanya baca (`can.post: false`). Bila seluruh workspace `is_read_only`, semua channel tetap di tempatnya.

### Message
```json
{
  "id": 1203, "channel_id": 4, "parent_id": null,
  "user": UserMini,
  "body": "cek #T-12 ya @Andi Pratama",
  "body_html": "<p>cek <a href=\"/workspace/tasks/41\" class=\"ws-task-ref\" data-task-id=\"41\">#T-12</a> ya <span class=\"ws-mention\" data-user-id=\"30\">@Andi Pratama</span></p>",
  "mentions": [30], "mentions_channel": false,
  "attachments": [ Attachment ],
  "is_pinned": false, "edited_at": null, "is_deleted": false,
  "reply_count": 0, "created_at": "...",
  "can": { "edit": true, "delete": true, "pin": false }
}
```
- `user: null` = pesan sistem (mis. sambutan otomatis saat intern masuk tim, PRD Fitur 1). Ditampilkan sebagai "Simagang".
- Pesan yang dihapus tetap dikirim dengan `is_deleted: true`, serta `body`/`body_html` kosong dan `attachments: []`, lalu ditampilkan "Pesan ini telah dihapus".
- `edited_at` terisi → tanda "(diedit)".
- `mentions` (*usulan*): `users.id` yang disebut. `mentions_channel`: ada `@channel` dari mentor/admin. Frontend menyorot pesan yang menyebut user yang sedang login.
- `can.delete`: pesan sendiri, atau mentor/admin (moderasi). Semua `false` bila workspace `is_read_only`.
- **Thread**: balasan punya `parent_id` (satu tingkat; balasan tidak bisa dibalas). Pesan induk membawa `reply_count` dan `last_reply_at` (*usulan*) untuk ringkasan "3 balasan · terakhir 5 menit lalu". Daftar utama channel hanya berisi pesan tanpa `parent_id`.
- **Reaksi** (Fase 3, *usulan* #15): `reactions: [{ emoji, count, me }]`, hanya emoji yang ada di daftar reaksi cepat (`👍 ❤️ 😂 🎉 🙏 👀 ✅ 🔥`). Perubahan reaksi menyentuh `updated` polling supaya anggota lain melihatnya. Intern boleh bereaksi di `#pengumuman` walau tidak boleh posting.
- **Pin**: `is_pinned`. Hanya pesan utama (bukan balasan) yang bisa disematkan. Pesan yang dihapus otomatis lepas sematan.

**Render `body_html` di server** (markdown sederhana, `html_input: escape`):

| Sumber | HTML | Catatan |
| --- | --- | --- |
| `#T-12` | `<a href="{url tugas}" class="ws-task-ref" data-task-id="41">#T-12</a>` | Nomor dicari di **workspace channel tersebut**. Tidak ditemukan atau channel global → teks biasa. Di halaman workspace, klik membuka slide-over (bukan pindah halaman). |
| `@Nama Lengkap` | `<span class="ws-mention" data-user-id="30">@Nama Lengkap</span>` | Cocokkan dengan nama anggota channel, nama terpanjang dulu. |
| `@channel` | `<span class="ws-mention ws-mention--all">@channel</span>` | Hanya bila penulis mentor/admin; dari intern dibiarkan teks biasa. |

### Template (tugas)
```json
{
  "id": 15, "team": { "id": 2, "name": "Tim Aptika" },
  "title": "Laporan mingguan", "description": "Rangkum pekerjaan…", "description_html": "<p>Rangkum pekerjaan…</p>",
  "priority": "medium", "labels": [{ "id": 3, "name": "dokumen", "color": "#f59e0b" }],
  "due_in_days": 7, "checklist": [{ "content": "Ringkasan pekerjaan" }],
  "is_onboarding": false,
  "creator": UserMini, "usage_count": 12, "updated_at": "...",
  "can": { "update": true, "delete": true, "assign": true }
}
```
- Dari tabel `task_templates` (PRD). Field tambahan *usulan*: `priority`, `label_ids`, `due_in_days` (deadline default = hari penugasan + N hari, pukul 17.00), dan `usage_count` (jumlah tugas yang pernah dibuat dari template; cukup `count` tugas dengan `template_id`).
- `is_onboarding: true` → otomatis ditugaskan `WorkspaceProvisioner` ke intern baru di tim tersebut (PRD Fitur 1, langkah 4).
- `can`: admin semua tim; mentor template timnya sendiri (PRD "Mengelola template tugas onboarding").

### Notification
```json
{ "id": "9b1c…uuid", "type": "task_assigned", "title": "Tugas baru: T-14 …", "body": "Dari Budi · deadline 28 Okt",
  "url": "/workspace/tasks/44", "actor": { "name": "Budi Santoso", "avatar_url": null },
  "read_at": null, "created_at": "..." }
```
`type` (sesuai tabel notifikasi PRD): `intern_joined`, `intern_without_mentor`, `task_assigned`, `task_in_review`, `task_approved`, `task_revision`, `task_due_soon`, `task_overdue`, `task_commented`, `mentioned`, `announcement`.
Field ini adalah hasil "flatten" kolom `data` Laravel Database Notification oleh `NotificationController`. Ikon dan warna per `type` diatur di `notifications.js`.

---

## 5. Kontrak view (variabel Blade dari controller)

Semua halaman memakai `@extends('workspace.layout')`. Hak akses halaman dikirim sebagai array `$can` (dari Policy), bukan dicek dengan `@can` di view.

**Variabel berupa array** (hasil `JsonResource::resolve()` atau `->toArray()`), bukan model Eloquent. View mengaksesnya dengan `$ws['name']`, lalu meneruskannya ke Alpine lewat `@js(...)`.

### `workspace.index` — `GET /workspace` ✅ v1.0 (daftar kelompok)
**Intern:** bila sudah punya kelompok → **redirect** ke `workspace.show` kelompoknya. Bila belum → view dengan `$noGroup = true`.

| Variabel | Tipe | Isi |
| --- | --- | --- |
| `$noGroup` | bool | Intern belum dimasukkan ke kelompok. Variabel lain boleh kosong. |
| `$mentor` | `UserMini\|null` | Mentor intern (untuk keterangan `$noGroup`). |
| `$groups` | `LengthAwarePaginator<Workspace>` | 9 per halaman. Urutan: **aktif dulu**, lalu **selesai magang (terbaru selesai)**. Urutan aktif mengikuti `?sort=` (lihat bawah). Mentor: kelompok miliknya. Admin: semua. Filter `?q=` (nama kelompok/anggota), admin juga `?team=` & `?mentor=`, dan `?attention=1`. |
| `$counts` | `{active, finished, attention}` | Jumlah tanpa filter (untuk hero & chip "Perlu perhatian"). `attention` = kelompok aktif yang perlu perhatian (v1.3). |
| `$sort` | `'deadline'\|'newest'` | v1.3. `?sort=` (default `deadline`). `newest` = aktif terbaru dibuat. `deadline` = (1) kelompok dengan `deadline.overdue_count` > 0, terbanyak dulu, (2) `deadline.next.due_at` terdekat, (3) kelompok tanpa deadline; sama kuat → terbaru dibuat. Kelompok selesai tidak terpengaruh. |
| `$attention` | bool | v1.3. `?attention=1`: hanya kelompok aktif dengan `overdue_count` > 0 **atau** `due_soon_count` > 0. |
| `$ungrouped` | `Roster[]` | Mentor: anak bimbingan aktif yang belum punya kelompok (banner "Masukkan"). |
| `$teams`, `$mentors` | `{id,name}[]` | Admin saja: isi filter. Kosong untuk mentor. |
| `$can` | array | `['create_group' => bool (mentor), 'manage_templates' => bool]`. |

### `workspace.show` — `GET /workspace/{workspace}` ✅ Fase 1A (tab Board/List/Channel diisi di 1B–1C)
| Variabel | Tipe | Isi | Fase |
| --- | --- | --- | --- |
| `$workspace` | `Workspace` | | 1A |
| `$tab` | string | `backlog` \| `board` \| `list` \| `timeline` \| `deadline` \| `laporan` \| `otomasi` \| `channel` \| `activity` (dari `?tab=`, nilai lain → `board`). Intern: tanpa `backlog` dan `otomasi` (nilai itu → `board`). `otomasi` hanya untuk mentor pemilik/admin dan kelompok yang belum selesai (`!is_read_only`). | 1A, v1.1, v1.3, v1.4 |
| `$members` | `UserMini[]` | Slide-over anggota, autocomplete mention, filter. | 1A |
| `$activities` | `Activity[]` | 20 aktivitas terbaru, urut terbaru dulu. Lanjutan lewat `GET /workspace/{id}/activities`. | 1A |
| `$can` | array | `create_task`, `manage_channels` (v1.0: selalu `false` — tidak ada channel buatan sendiri di kelompok), `create_template` (menu "Simpan sebagai template"), `manage_labels` ("Kelola label" di form tugas); mentor/admin. Semua `false` bila `is_read_only`. | 1A ✅ |
| `$sprints` | `Sprint[]` | Semua sprint kelompok (planned, active, completed), urut dibuat. Mengisi tab Backlog & bilah sprint di Board. | v1.1 ✅ |
| `$candidates` | `Roster[]` | Anak bimbingan tanpa kelompok, untuk "Tambah anggota". Kosong bila `!workspace.can.add_members`. | v1.0 ✅ |
| `$assignableInterns` | `InternMini[]` | Anggota kelompok (intern). | 1B ✅ |
| `$labels` | `{id,name,color}[]` | Label tim. | 1B ✅ |
| `$initialTasks` | `TaskCard[]` | Maks. 200 tugas aktif + Selesai terbaru. Mencegah request tambahan saat halaman dibuka. | 1B ✅ |
| `$channels` | `Channel[]` | v1.0: `#<slug-kelompok>` (type `team`) + DM mentor ↔ tiap anggota (intern hanya melihat DM miliknya) + `#umum-<tim>` (type `team`, `workspace_id: null`, dipakai bersama semua kelompok di unit) + `#pengumuman`. Channel `workspace_id: null` tampil di bagian "Umum". | 1C ✅ |
| `$templates` | `Template[]` | Template tim workspace ini untuk "Pakai template" di form tugas. Kosong untuk intern. | 2 ✅ |

Kelompok yang tidak boleh dilihat → **403** (mis. intern membuka kelompok lain). Id tidak ada → 404.

**Query string tab Channel** (dibaca frontend, backend tidak perlu memproses):
- `?tab=channel&channel={id}` membuka channel tersebut. Tanpa `channel`, dibuka channel pertama yang punya pesan belum dibaca, lalu channel workspace pertama.
- `&message={id}` menggulir ke pesan itu dan menyorotnya (memuat halaman lama bila perlu, maks. 5 × 50 pesan). **URL notifikasi `mentioned` sebaiknya memakai format ini**, mis. `/workspace/3?tab=channel&channel=4&message=1203`.
- Badge tab Channel = jumlah `unread_count` channel milik workspace (tanpa `#pengumuman`), sama dengan `Workspace.unread_messages`.

### `workspace.tasks.show` — `GET /workspace/tasks/{task}` ✅ Fase 1B
Halaman penuh, dibuka dari notifikasi, link `#T-12`, atau menu "Buka halaman penuh". Dengan `Accept: application/json`, route yang sama mengembalikan `{ "data": TaskDetail }` untuk slide-over. Tugas di workspace yang tidak boleh dilihat menghasilkan **403**.

| Variabel | Tipe | Untuk |
| --- | --- | --- |
| `$task` | `TaskDetail` | Isi halaman |
| `$workspace` | `Workspace` | Breadcrumb, tombol kembali |
| `$members` | `UserMini[]` | Mention (Fase 1C) |
| `$assignableInterns` | `InternMini[]` | Form edit |
| `$labels` | `{id,name,color}[]` | Form edit |
| `$templates` | `Template[]` | (opsional) "Pakai template" di form |
| `$can` | array | (opsional) `['create_template' => bool]` |

Slide-over di board mengubah URL menjadi `/workspace/{id}?task=T-12` (pushState). Membuka URL itu langsung juga membuka panelnya, dan tombol Back menutupnya.

### `workspace.templates.index` — `GET /workspace/templates` ✅ Fase 2
Kelola template dan tugaskan massal. PRD menulis `/admin/workspace/templates` khusus admin, padahal mentor juga boleh mengelola template timnya. *Usulan*: satu route bersama di prefix `/workspace` dengan Policy (sesuai prinsip PRD "tidak menduplikasi route per role"). Intern → **403**.

| Variabel | Tipe | Isi |
| --- | --- | --- |
| `$templates` | `Template[]` | Admin: semua tim. Mentor: tim sendiri. |
| `$teams` | `{id,name}[]` | Isi filter tim & pilihan tim di form (lebih dari 1 → filter tampil). |
| `$assignableInterns` | `[{ id, user_id, name, avatar_url, team: {id,name}, group: {id,name,url} }]` | Anak magang aktif yang **sudah punya kelompok** (admin: semua, mentor: anak bimbingannya). Tugas dibuat di kelompok masing-masing. |
| `$labels` | `{id,name,color}[]` | |
| `$can` | array | `['create' => bool, 'choose_team' => bool]` (`choose_team` = admin). |

### `notifications.index` — `GET /notifications` ✅ Fase 1D
URL yang sama melayani HTML (halaman) dan JSON (lonceng & "muat lebih banyak"), dibedakan oleh `Accept: application/json`.

| Variabel | Tipe | Isi |
| --- | --- | --- |
| `$notifications` | `{ data: Notification[], meta: { current_page, has_more } }` \| `null` | Halaman pertama (20 item) sesuai filter. `null` → frontend mengambilnya lewat JSON saat halaman dibuka (dipakai route preview). |
| `$filter` | `'all'` \| `'unread'` | Dari `?filter=`. Nilai lain → `all`. |
| `$category` | `null` \| `'task'` \| `'mention'` \| `'announcement'` \| `'intern'` | Dari `?category=`. Nilai lain → `null`. |

Kategori → `type`:

| `category` | `type` |
| --- | --- |
| `task` | semua `task_*` |
| `mention` | `mentioned` |
| `announcement` | `announcement` |
| `intern` | `intern_joined`, `intern_without_mentor` (chip tidak ditampilkan untuk intern) |

Perilaku halaman:
- Filter dan kategori mengubah URL (`replaceState`), sehingga bisa dibagikan atau di-refresh.
- Klik item menandainya dibaca (`POST /notifications/{id}/read`, menunggu maks. 400 ms), lalu membuka `url`. Ctrl/⌘-klik membuka tab baru dan tetap menandai dibaca.
- Tombol ✓ per item dan "Tandai semua dibaca" memperbarui lonceng saat itu juga (store `wsNotif` dipakai bersama).
- Bila polling lonceng membawa notifikasi baru, muncul tombol "Ada notifikasi baru · tampilkan". Daftar tidak bergeser sendiri.
- `url` sebaiknya menuju lokasi yang paling spesifik: tugas `/workspace/{id}?task=T-12` (membuka slide-over) atau `/workspace/tasks/{id}`, mention `/workspace/{id}?tab=channel&channel={c}&message={m}`, pengumuman `/workspace/{id}?tab=channel&channel={c}`.

---

## 6. Endpoint JSON

| Method | URI | Request | Respons |
| --- | --- | --- | --- |
| GET | `/workspace/{workspace}/tasks` | query: `status[]`, `priority[]`, `assignee[]`, `label[]`, `due_from`, `due_to`, `q` | `{ data: TaskCard[] }` |
| POST | `/workspace/{workspace}/tasks` | `{ title, description?, priority, assignee_ids[], label_ids[]?, start_at?, due_at, checklist_items?: [{content, is_done}], result_links?: [{url, label?}], attachment_ids?: [], template_id?, separate_copies?: bool }` | `201 { data: TaskCard[] }`. **Array**: `separate_copies: true` (≥ 2 assignee) membuat satu salinan per intern, masing-masing dengan satu assignee. `template_id` hanya dicatat (asal tugas & `usage_count`); isi tugas tetap dari payload. |
| GET | `/workspace/tasks/{task}` | — | `{ data: TaskDetail }` |
| PUT | `/workspace/tasks/{task}` | sama seperti POST (tanpa `template_id`). `checklist_items` membawa `id` untuk item lama; item yang tidak dikirim dianggap dihapus. | `{ data: TaskDetail }` |
| PATCH | `/workspace/tasks/{task}/status` | `{ status, column_order?: [taskId, …], result_links?, attachment_ids?, note? }` | `{ data: TaskCard }` |
| PATCH | `/workspace/tasks/{task}/review` | `{ decision: "approve" \| "revision", score?: 0–100, feedback? }`. `feedback` wajib bila `revision`. | `{ data: TaskDetail }` |
| DELETE | `/workspace/tasks/{task}` | — | `204` |
| POST | `/workspace/tasks/{task}/comments` | `{ body, attachment_ids?: [] }` | `201 { data: Comment }` |
| GET | `/workspace/channels/{channel}/messages` | lihat di bawah | lihat di bawah |
| POST | `/workspace/channels/{channel}/messages` | `{ body, parent_id?, attachment_ids?: [], mention_ids?: [] }` | `201 { data: Message }` |
| PATCH | `/workspace/messages/{message}` | `{ body }` | `{ data: Message }` |
| DELETE | `/workspace/messages/{message}` | — | `{ data: Message }` (dengan `is_deleted: true`) |
| POST | `/workspace/channels/{channel}/read` | `{ message_id }` | `204` |
| GET | `/workspace/messages/{message}/replies` | — | `{ data: Message[] (balasan, lama → baru), parent: Message }` (*usulan* #10) |
| PATCH | `/workspace/messages/{message}/pin` | `{ is_pinned }` | `{ data: Message }`. Mentor/admin; 422 untuk balasan thread. (*usulan* #10) |
| POST | `/workspace/{workspace}/channels` | `{ name, description?, is_private, member_ids?: [userId] }` | `201 { data: Channel }`. `name`: 2–40 karakter `^[a-z0-9][a-z0-9-]*$`, unik di workspace (termasuk channel global). Privat wajib ≥ 1 anggota lain selain pembuat. (*usulan* #11) |
| PATCH | `/workspace/channels/{channel}` | `{ name?, description?, is_archived? }` | `{ data: Channel }`. `is_archived` hanya untuk `topic`. (*usulan* #11) |
| POST | `/workspace/teams/{team}/labels` | `{ name (1–30, unik per tim), color (#rrggbb) }` | `201 { data: { id, name, color } }` (*usulan* #11) |
| PATCH / DELETE | `/workspace/labels/{label}` | `{ name, color }` / — | `{ data: Label }` / `204`. Hapus = label dilepas dari semua tugas & template. |
| GET | `/workspace/tasks/mine` | — | `{ data: [{ id, code, title, status, due_at, workspace: { id, name } }] }`. Intern saja: tugas aktif + 5 Selesai terbaru. Untuk dropdown "Tugas terkait" di logbook. (*usulan* #13) |
| GET | `/workspace/interns/{intern}/tasks` | — | `{ data: (TaskCard + { workspace, score })[], stats: { todo, in_progress, review, revision, done, late, avg_score }, space_url }`. Admin & mentor intern tersebut. (*usulan* #14) |
| POST | `/workspace/messages/{message}/reactions` | `{ emoji }` (toggle milik user) | `{ data: Reaction[] }` (*usulan* #15) |
| PATCH | `/workspace/channels/{channel}/mute` | `{ is_muted }` | `{ data: Channel }` (*usulan* #16) |
| GET / PUT | `/notifications/preferences` | PUT: `{ types: { [type]: { in_app, email } } }` | `{ data: { types: [{ type, group, label, description, in_app, email: bool \| null }], muted_channels: [{ id, name, workspace: { id, name, url } }] } }`. `email: null` = jenis ini tidak dikirim lewat email; 422 bila dikirim `email: true`. Daftar `types` ditentukan server per role. (*usulan* #16) |
| GET | `/workspace/recap` | `intern_id` (wajib untuk mentor/admin) | HTML (halaman cetak `workspace.recap`) atau JSON `{ data: { intern, period, summary: { done, active, on_time, avg_score, min_score, max_score }, tasks: [{ id, code, title, workspace, due_at, completed_at, score, on_time, url }] } }` (*usulan* #17) |
| GET | `/workspace/progress` | `intern_ids[]` | `{ interns: { [internId]: { total, done, review, late, space_url } }, teams: [{ team, workspace_url, stats, idle_mentors: UserMini[] }] }`. Admin saja. (*usulan* #12) |
| GET | `/workspace/attachments/{attachment}` | — | File (dicek Policy) |
| GET | `/notifications` (JSON, lonceng) | query: `limit` (default 10) | `{ unread_count, data: Notification[], unread_messages? }` |
| GET | `/notifications` (JSON, halaman) | query: `page`, `per_page` (20), `filter=unread`?, `category`? | `{ unread_count, data: Notification[], meta: { current_page, has_more } }` |
| POST | `/notifications/{id}/read` | — | `204` (*usulan* #2, dipakai sejak 1D) |
| POST | `/workspace/templates` | `{ team_id, title, description?, priority, label_ids?, due_in_days (0–90), checklist?: [{content}], is_onboarding }` | `201 { data: Template }` (*usulan* #9) |
| PUT | `/workspace/templates/{template}` | sama seperti POST | `{ data: Template }` |
| DELETE | `/workspace/templates/{template}` | — | `204`. Tugas yang sudah dibuat tidak berubah. |
| POST | `/workspace/templates/{template}/assign` | `{ assignee_ids: [internId], target: "personal" \| "team", workspace_id? (wajib bila team), due_at, start_at? }` | `201 { data: (TaskCard + { workspace })[], meta: { created, template: Template } }`. `personal` = space pribadi tiap intern. Setiap intern menerima notifikasi `task_assigned`. |
| POST | `/notifications/read-all` | — | `204` |

**`column_order` pada PATCH status.** Ini urutan lengkap id tugas di kolom tujuan setelah kartu dijatuhkan. Backend cukup menulis ulang `position` sesuai urutan array, sehingga frontend tidak perlu menghitung posisi pecahan. Kriteria "urutan kartu tetap sama setelah refresh" pun terpenuhi. Bila tidak dikirim (perubahan dari halaman detail), taruh tugas di posisi teratas kolom tujuan.

**Kirim hasil (intern → `review`).** Intern biasanya tidak punya `can.update`. Karena itu hasil kerja dikirim bersama perpindahan status:
- `result_links` (ditambahkan ke link yang sudah ada)
- `attachment_ids` (dari upload)
- `note` (disimpan sebagai komentar)

**Pesan error yang ditampilkan apa adanya ke user (toast).** Contoh:
- `403`: "Hanya mentor atau admin yang dapat menandai Selesai atau Revisi."
- `403`: "Anda hanya dapat memindahkan tugas yang di-assign ke Anda."
- `422` pada review: `errors.feedback` / `errors.score`.
- `422` pada form: `errors.title`, `errors.assignee_ids`, `errors.due_at`, `errors["result_links.0.url"]`. Error ini dipetakan ke field form.

**Polling pesan** — `GET /workspace/channels/{channel}/messages`:

| Query | Arti | Respons |
| --- | --- | --- |
| *(kosong)* | 50 pesan terbaru | `{ data: Message[], meta: { has_more_before, server_time } }` |
| `before={id}` | 50 pesan sebelum id (scroll ke atas) | `{ data, meta: { has_more_before } }` |
| `after={id}&since={server_time}` | Polling: pesan baru setelah id **dan** pesan lama yang diedit/dihapus sejak `since` | `{ data: Message[], updated: Message[], meta: { server_time } }` |
| `q=kata` | Cari pesan & balasan di channel (min. 2 huruf), terbaru dulu, maks. 20 | `{ data: Message[], meta: { total } }` |
| `pinned=1` | Pesan disematkan, terbaru disematkan dulu | `{ data: Message[] }` |

**Thread pada polling.** `data` berisi pesan utama **dan** balasan baru (urut `id`), sehingga satu polling melayani daftar utama dan panel thread. Saat balasan masuk, **pesan induknya ikut di `updated`** dengan `reply_count`/`last_reply_at` baru. Balasan tidak dihitung ke `unread_count` channel. Permintaan tanpa `after` (muat awal & `before`) hanya mengembalikan pesan utama.

`updated` diperlukan agar edit/hapus pesan terlihat oleh anggota lain tanpa reload. Urutan `data` dari lama ke baru. Target respons < 200 ms (index `(channel_id, id)`).

Perilaku frontend (`channels.js`):
- Polling hanya berjalan saat tab Channel terbuka dan tab browser aktif. Interval adaptif **5 → 30 detik** (`WS.createPoller`), kembali ke 5 detik setiap ada pesan baru atau setelah user mengirim.
- `after` = id terbesar yang pernah diterima dari server (`0` bila channel kosong). Pesan milik user sendiri ikut kembali di `data`, lalu digabung berdasarkan `id` (tidak dobel).
- Kirim pesan bersifat optimistis: pesan tampil "Mengirim…", diganti respons server, atau ditandai "Gagal terkirim · Coba lagi · Hapus".
- `POST /read` dikirim saat channel dibuka, dan saat pesan baru masuk ketika user berada di bagian bawah. Bila user sedang membaca pesan lama, muncul tombol "N pesan baru" dan channel baru ditandai dibaca setelah user turun ke bawah.
- Pesan lama dimuat otomatis saat scroll mendekati atas (`before`).

**Kirim pesan.**
- `mention_ids` (*usulan*): `users.id` yang dipilih lewat autocomplete `@`. Backend tetap memvalidasi bahwa id tersebut anggota channel, lalu memakainya untuk notifikasi `mentioned` (lebih andal daripada mencocokkan nama di teks).
- Rujukan `#T-12` diketik bebas atau lewat autocomplete `#` (diambil dari tugas di board).

**Pesan error channel (toast / di bawah pesan):**
- `403`: "Hanya mentor dan admin yang dapat memposting di #pengumuman."
- `403`: "Workspace sudah diarsipkan. Pesan hanya bisa dibaca."
- `403`: "Anda hanya dapat mengedit pesan sendiri." / "Anda hanya dapat menghapus pesan sendiri."
- `422`: `errors.body`, mis. "Tulis pesan atau lampirkan file." atau "Pesan maksimal 4.000 karakter."
- `429`: "Terlalu banyak pesan. Tunggu sebentar sebelum mengirim lagi." (PRD: maks. 30 pesan/menit.)

---

## 7. Celah PRD — usulan tambahan untuk backend

| # | Kebutuhan frontend | Usulan |
| --- | --- | --- |
| 1 | Upload lampiran dengan progress bar (PRD hanya punya route unduh) — **dipakai sejak 1B** | `POST /workspace/attachments` (multipart: `file`, `workspace_id`) → `201 { data: Attachment }`. File "yatim" sampai ditautkan lewat `attachment_ids`. Batas 10 MB, rate limit 10/menit. |
| 2 | Klik satu notifikasi menandainya terbaca — **dipakai sejak 1D** (lonceng & halaman) | `POST /notifications/{id}/read` → `204` |
| 3 | Badge unread channel ikut terbarui tanpa membuka channel — **dipakai sejak 1C** | Tambahkan `unread_messages: { total, by_channel: { "4": 3 } }` di respons `GET /notifications`, sehingga satu polling ringkas melayani lonceng dan badge (sesuai PRD "satu endpoint ringkas"). `by_channel` cukup berisi channel dengan pesan belum dibaca (yang tidak ada = 0). Pesan milik user sendiri tidak dihitung. |
| 4 | Centang checklist tanpa mengirim seluruh form — **dipakai sejak 1B** (assignee & mentor) | `PATCH /workspace/tasks/{task}/checklist/{item}` `{ is_done }` → `{ data: { done, total } }` |
| 5 | Aktivitas workspace (tab Aktivitas, **sudah dipakai di Fase 1A**) | `GET /workspace/{workspace}/activities?before={id}` → `{ data: Activity[20], meta: { has_more } }`, urut terbaru dulu. |
| 6 | Status read-only ketika magang selesai | Field `is_read_only` di `Workspace`. Frontend menyembunyikan composer dan tombol aksi. Server tetap menolak dengan 403. |
| 7 | Mention dan judul DM yang andal — **dipakai sejak 1C** | `Message.mentions`, `Message.mentions_channel`, `Channel.members` (khusus DM), dan `mention_ids` pada `POST` pesan. Lihat bagian Channel & Message di atas. |
| 8 | Widget dashboard mentor & intern tanpa mengubah controller dashboard — **dipakai sejak Fase 2** | `GET /workspace/summary` (JSON, untuk user yang login). Lihat bagian 8. |
| 9 | Template tugas dikelola mentor & admin, plus tugaskan massal — **dipakai sejak Fase 2** | Resource `Template`, endpoint `/workspace/templates*`, dan `separate_copies` pada `POST` tugas. Lihat bagian Template & Endpoint. |
| 10 | Thread, pin, dan pencarian pesan (PRD Fase 2) — **dipakai sejak Fase 2** | `GET …/messages/{id}/replies`, `PATCH …/messages/{id}/pin`, `?q=` & `?pinned=1` pada daftar pesan, `last_reply_at`, `pinned_count`. |
| 11 | Buat/arsip channel topik & kelola label tim — **dipakai sejak Fase 2** | `POST /workspace/{id}/channels`, `PATCH /workspace/channels/{id}`, endpoint label per tim. Perubahan label langsung tampil di kartu board, filter List, form tugas, dan template (event `ws-labels-changed`). |
| 12 | Admin → Monitoring: kolom progres & rekap per tim tanpa mengubah `AdminMonitoringController` — **dipakai sejak Fase 2** | `GET /workspace/progress`. Komponen `<x-workspace.intern-progress>` dan `<x-workspace.team-recap>`. |
| 13 | Logbook ↔ tugas (`logbooks.task_id`, PRD) — **dipakai sejak Fase 2** | Form create/edit logbook mengirim field `task_id` (opsional). Controller logbook perlu memvalidasi `nullable|exists:tasks,id` dan tugas milik intern tersebut. Pilihan dari `GET /workspace/tasks/mine`. |
| 15 | Reaksi emoji pada pesan (PRD Fase 3) — **dipakai sejak Fase 3** | `POST /workspace/messages/{id}/reactions`, field `Message.reactions`. Tabel usulan: `message_reactions (message_id, user_id, emoji)` unik per (message, user, emoji). |
| 16 | Pengaturan notifikasi per user & bisukan channel (PRD Fase 3) — **dipakai sejak Fase 3** | `GET/PUT /notifications/preferences`, `PATCH /workspace/channels/{id}/mute`, halaman `notifications.settings`. Notifikasi yang dimatikan tidak dikirim lewat kanal tersebut (cek di setiap `Notification::via()`). |
| 17 | Rekap tugas ke laporan akhir & nilai tugas sebagai masukan sertifikat (PRD Fase 3) — **dipakai sejak Fase 3** | `GET /workspace/recap` (JSON + halaman cetak). Form laporan akhir mengirim `include_task_recap` (0/1). Form sertifikat hanya menampilkan referensi (rata-rata, rentang, ketepatan waktu); **tidak** mengisi nilai otomatis selama keputusan PRD belum ada. |
| 14 | Detail intern (admin & mentor): tugas — **dipakai sejak Fase 2** | `GET /workspace/interns/{intern}/tasks` → `{ data, stats, group: {id,name,color,url} \| null }` (v1.0: `space_url` diganti `group`). Komponen `<x-workspace.intern-tasks>`. |
| 18 | **Kelompok buatan mentor (v1.0)** — **dipakai sejak 27 Okt 2026** | Lihat daftar di bawah tabel ini. |
| 19 | **Sprint & Backlog (v1.1) + ketua sprint & perpanjang deadline (v1.2)** — **dipakai sejak 28 Okt 2026** | Lihat daftar #19 di bawah tabel ini. |
| 20 | **Tampilan deadline per tugas (v1.3)** — **dipakai sejak 28 Okt 2026** | Lihat daftar #20 di bawah tabel ini. |
| 21 | **Timeline, Laporan & Otomasi (v1.4)** — **dipakai sejak 29 Okt 2026** | Lihat daftar #21 di bawah tabel ini. |

**#19 — Sprint & Backlog (v1.1)**
- **Data:** tabel `sprints (id, workspace_id, name, goal, status, start_date, end_date, completed_at, timestamps)`; kolom `tasks.sprint_id` (nullable FK, `null` = backlog) dan `tasks.rank` (int). Indeks unik parsial: satu `active` per `workspace_id`.
- **Board:** bila ada sprint aktif, board (dan `TaskCard` yang ditampilkan) hanya tugas `sprint_id = active.id`. Tanpa sprint aktif: semua tugas (kelompok yang tidak memakai sprint tetap berfungsi). Statistik kelompok tetap menghitung semua tugas.
- **Assignee opsional** saat membuat tugas (tugas backlog boleh belum di-assign). `POST /workspace/{id}/tasks` menerima `sprint_id` opsional; validasi `assignee_ids` menjadi `array` (boleh kosong). Assign/ubah assignee lewat `PUT /workspace/tasks/{id}` `{ assignee_ids }` dari tombol avatar di kartu board (mentor/admin). Intern tidak dapat memindahkan tugas yang belum di-assign kepadanya.
- **Endpoint** (semua mentor pemilik/admin; intern → 403; kelompok selesai → 403):
  - `POST /workspace/{workspace}/sprints` `{ name?, goal?, start_date?, end_date? }` → `201 { data: Sprint }` (planned; nama default "Sprint N").
  - `PATCH /workspace/sprints/{sprint}` `{ name?, goal?, start_date?, end_date? }` → `{ data: Sprint }`. Sprint `completed` → 403. Sprint `active` wajib punya kedua tanggal.
  - `POST /workspace/sprints/{sprint}/start` `{ name, goal?, start_date, end_date }` → `{ data: Sprint }`. 422 `errors.sprint` bila sudah ada sprint aktif / sprint bukan planned; 422 `end_date` bila ≤ `start_date`.
  - `POST /workspace/sprints/{sprint}/complete` `{ move_to: "backlog" | "new" | sprintId }` → `{ data: Sprint, moved: { task_ids: [..], sprint_id: number|null }, created_sprint: Sprint|null }`. Tugas berstatus selain `done` dipindah; tugas `done` tetap tercatat di sprint itu (riwayat). `move_to` sprint harus `planned`.
  - `DELETE /workspace/sprints/{sprint}` → `204`. Hanya `planned`; tugasnya kembali ke backlog (`sprint_id = null`).
  - `PATCH /workspace/tasks/{task}/sprint` `{ sprint_id: number|null, rank_order: [taskId…] }` → `{ data: TaskCard }`. `rank_order` = urutan lengkap bagian tujuan setelah dipindah; server menulis ulang `rank` 1..n. Tidak boleh ke sprint `completed`.
- **Realtime (opsional):** event `sprint.changed { sprint }` di channel `workspace.{id}`; `task.changed` cukup untuk perpindahan tugas.
- **Deadline tetap per tugas** (tidak mengikuti tanggal sprint). Tag "Belum diselesaikan" & kunci kirim hasil tidak berubah.

**#19 v1.2 — Ketua sprint, tugas cepat, perpanjang deadline**
- **Data:** `sprints.leader_intern_id` (nullable FK `interns`, harus anggota kelompok). `sprints.end_date` wajib saat dibuat.
- `POST /workspace/{workspace}/sprints` sekarang menerima `{ name?, goal?, end_date (wajib, ≥ hari ini), leader_id?, tasks?: [{ title (≤150), priority }] }`
  → `201 { data: Sprint, tasks: TaskCard[] }`. Tugas dibuat di sprint itu, **tanpa assignee**, `due_at = end_date 23:59` (zona waktu aplikasi). Error per baris: `errors["tasks.0.title"]`.
- `PATCH /workspace/sprints/{sprint}` menerima `leader_id` (ganti ketua; `null` hanya bila sprint `planned`).
- `POST /workspace/sprints/{sprint}/start` menerima `leader_id`; **422 `errors.leader_id`** bila sprint belum punya ketua. Kelompok dengan 1 anggota: frontend memilih anggota itu otomatis.
- **Bagi tugas:** `PATCH /workspace/tasks/{task}/assignees` `{ assignee_ids: [internId…] }` (boleh kosong, boleh >1) → `{ data: TaskDetail }`.
  Izin: mentor pemilik/admin, **atau intern yang menjadi ketua sprint tugas itu** (sprint `planned`/`active`). Selain itu 403. Catat `assigned`/`unassigned` di `task_activities`.
  Intern yang dibagi tugas mendapat `task_assigned` seperti biasa.
- **Perpanjang deadline:** `PATCH /workspace/tasks/{task}/deadline` `{ due_at, reason? (≤200) }` → `{ data: TaskDetail }`. Mentor/admin; tugas belum `done`.
  422 bila `due_at` ≤ deadline lama atau ≤ sekarang. Server: set `is_late = false` (kunci kirim hasil terbuka), catat aktivitas
  `deadline_extended` dengan `meta: { from, to, reason }` (frontend menampilkan "memperpanjang deadline sampai … — "alasan"").
- **Notifikasi baru (in-app + EMAIL, default email aktif; ikut pengaturan notifikasi #16):**

  | type | Penerima | Kapan | Isi email (judul · isi · tombol) |
  | --- | --- | --- | --- |
  | `sprint_leader` | intern yang ditunjuk ketua | sprint dibuat/diubah dengan `leader_id` baru | "Anda ketua {Sprint} · {Kelompok}" · deadline sprint, jumlah tugas · "Buka board" → `{workspace.url}?tab=board` |
  | `sprint_to_divide` | ketua sprint | sprint dimulai dan ada tugas tanpa assignee (ulangi H+1 bila masih ada) | "{N} tugas menunggu Anda bagi" · daftar judul (maks. 5) · "Bagi tugas" → `{workspace.url}?tab=board` |
  | `deadline_extended` | semua assignee tugas | deadline diperpanjang | "Deadline {T-12} diperpanjang" · deadline lama → baru + alasan · "Lihat tugas" → URL tugas |

  Email dikirim lewat queue (`ShouldQueue`), memakai template email Simagang yang sudah ada; `url` notifikasi in-app sama dengan tombol email.

**#21 — Timeline, Laporan & Otomasi (v1.4)**

*Timeline & Laporan — tanpa endpoint baru, tetapi bergantung pada kelengkapan data:*
- **Timeline** memakai `TaskCard.start_at` / `due_at` dan `Sprint.start_date` / `end_date`. Bila `start_at` kosong, batang dimulai dari `start_date` sprint tugas itu
  (diberi tanda "perkiraan"); bila tidak ada sprint, 3 hari sebelum `due_at`. Sprint terencana tanpa `start_date` digambar mulai saat sprint sebelumnya berakhir.
  Jika memungkinkan, isi `start_at` saat tugas dimulai (status → In Progress) supaya batang akurat.
- **Laporan** memakai `completed_at` (wajib terisi untuk status `done`; dasar burndown dan ketepatan waktu), `due_at`, `sprint_id`, `status`, `assignees`.
  Ketepatan waktu = `completed_at <= due_at`. Burndown per hari memakai tanggal `completed_at` pada rentang `start_date`–`end_date` sprint.
  **Burnup ditunda**: butuh riwayat kapan tugas masuk sprint (usulan kolom `tasks.sprint_added_at`).
- Keduanya memakai `$initialTasks` (maks. 200 tugas aktif + Selesai terbaru). Untuk laporan historis yang akurat, `$initialTasks` sebaiknya memuat semua tugas sprint berjalan dan sprint terakhir yang selesai.
- **Anak magang** hanya diberi data kelompoknya sendiri (policy `view` yang sama); filter "tugas milik sendiri" dilakukan di client dari `assignees[].user_id`.

*Otomasi — aturan pekerjaan berulang (mentor pemilik/admin; intern → 403; kelompok `finished` → 403, aturan ikut nonaktif):*
- **Data:** `automation_rules (id, workspace_id, created_by, name ≤60, enabled, trigger json, conditions json, actions json, last_run_at, timestamps)` dan
  `automation_runs (id, rule_id, scheduled_for, ran_at, ok, summary, task_ids json)`. Unik `(rule_id, scheduled_for)` supaya satu jadwal tidak berjalan dua kali.
- **Bentuk Rule:**
  ```json
  { "id": 101001, "workspace_id": 101, "name": "Laporan mingguan", "enabled": true,
    "trigger":    { "type": "schedule", "frequency": "weekly", "time": "15:00", "days": [5], "month_day": null },
    "conditions": [ { "type": "active_sprint" }, { "type": "skip_if_open" } ],
    "actions": [
      { "type": "create_task", "title": "Laporan kemajuan mingguan", "priority": "medium", "assignees": "all",
        "assignee_ids": [], "per_member": true, "due_in_days": 3, "destination": "active_sprint" },
      { "type": "notify", "to": "assignees", "message": "Tugas laporan mingguan sudah dibuat." } ],
    "next_run_at": "2026-10-30T15:00:00+08:00", "last_run_at": null,
    "runs": [ { "at": "…", "ok": true, "summary": "Membuat 3 tugas (T-23, T-24, T-25) · notifikasi dikirim ke 3 orang." } ] }
  ```
  - `trigger.frequency`: `daily` | `weekly` (`days`: 1 = Senin … 7 = Minggu) | `monthly` (`month_day`: 1–31 atau `"last"`; tanggal yang tidak ada di bulan itu → hari terakhir bulan). `time` = `HH:MM` zona waktu aplikasi. Hanya `type: schedule` untuk saat ini; struktur disiapkan untuk pemicu kejadian (mis. deadline lewat) di versi berikutnya.
  - `conditions` (opsional, masing-masing maks. sekali): `workday` (lewati Sabtu/Minggu), `active_sprint` (lewati bila tidak ada sprint berjalan), `skip_if_open` (lewati `create_task` bila di kelompok ada tugas **belum `done`** berjudul sama persis).
  - `actions` (minimal 1, dijalankan berurutan): `create_task` dan `notify` (lihat di bawah).
  - `runs`: 5 eksekusi terakhir (terbaru dulu). `next_run_at` = jadwal berikutnya (`null` bila `enabled = false`).
- **`create_task`:** tugas dibuat atas nama mentor pemilik aturan, `start_at` = waktu eksekusi, `due_at` = tanggal eksekusi + `due_in_days` pukul **17:00**.
  `destination`: `active_sprint` (bila tidak ada sprint berjalan → Backlog) atau `backlog`. `assignees`: `all` (semua anggota), `leader` (ketua sprint berjalan; tanpa ketua → tugas dibuat tanpa assignee),
  `selected` (`assignee_ids` = `interns.id`, harus anggota kelompok). `per_member = true` (dan `assignees` ≠ `leader`) → **satu tugas per anggota**; `false` → satu tugas bersama. `due_in_days` 0–60.
  Catat aktivitas `created` dengan `meta: { automation: { id, name } }`; assignee mendapat `task_assigned` seperti biasa.
- **`notify`:** `to` = `assignees` (penerima tugas yang dibuat pada eksekusi itu) | `all` | `leader` | `mentor`; `message` ≤200 karakter, teks polos. Dikirim **in-app + email** sebagai tipe notifikasi baru
  `automation_message` (ikut pengaturan notifikasi #16, default aktif; judul = nama aturan, tombol = `{workspace.url}`).
- **Endpoint** (semua mengembalikan `{ data: Rule }` kecuali disebut lain):
  - `GET /workspace/{workspace}/automations` → `{ data: Rule[] }`
  - `POST /workspace/{workspace}/automations` `{ name, enabled, trigger, conditions, actions }` → `201`
  - `PATCH /workspace/automations/{rule}` aturan penuh, atau hanya `{ enabled }` untuk saklar
  - `DELETE /workspace/automations/{rule}` → `204`
  - `POST /workspace/automations/{rule}/run` ("Jalankan sekarang", untuk uji) → `{ data: Rule, run: { at, ok, summary }, tasks: TaskCard[] }`. **Kondisi tetap dihormati** (hasil `ok: false`, `summary` "Dilewati: …"); jadwal diabaikan.
  - **422** dengan kunci per field, mis. `errors["name"]`, `errors["trigger.days"]`, `errors["actions.0.title"]`, `errors["actions.1.message"]`, `errors["actions"]` (tanpa aksi).
- **Eksekusi terjadwal:** scheduler Laravel menjalankan perintah (mis. `automation:run`) **tiap menit**: ambil aturan `enabled` yang `scheduled_for <= now` dan belum punya `automation_runs`, jalankan, simpan hasil.
  Eksekusi terlewat (server mati) boleh dikejar maksimal satu kali per aturan. Kelompok `finished` → semua aturannya di-`enabled = false`.
- **Preview lokal:** aturan disimpan di `localStorage` browser per kelompok (tidak sinkron antar akun/perangkat); "Jalankan sekarang" membuat tugas di data preview. Dua aturan contoh dibuat otomatis saat pertama dibuka.

**#20 — Tampilan deadline per tugas (v1.3)**
- **Tanpa tabel/endpoint baru.** Sumber tunggal tetap `tasks.due_at`. Tag "Belum diselesaikan" dan kunci kirim hasil tidak berubah.
- **Kartu kelompok** (`GET /workspace`): resource `Workspace.deadline` (lihat definisi di bagian Workspace). Tampil sebagai "N lewat deadline" + "Terdekat T-7 · 3 hari lagi".
  Kartu kelompok selesai tidak menampilkan deadline.
- **Urutan & filter daftar:** `?sort=deadline|newest` dan `?attention=1` (lihat `$sort`, `$attention` di §5). Default `deadline`. Semua parameter tetap ikut di tautan paginasi.
  Disarankan satu subquery agregat: `SELECT workspace_id, MIN(due_at) FILTER(...), COUNT(*) FILTER(...) FROM tasks WHERE status IN ('todo','in_progress','revision') GROUP BY workspace_id`.
- **Tab Deadline** (`?tab=deadline`, semua peran): agenda (Lewat deadline / Hari ini / Besok / Minggu ini / Lebih dari seminggu / Sudah dikirim / Selesai) dan kalender bulanan. **Murni client** dari
  `$initialTasks`, jadi tidak perlu endpoint. Backend cukup memastikan `$initialTasks` memuat `due_at` semua tugas aktif; tugas `done` hanya yang termuat (maks. 200 aktif + Selesai terbaru).
  Cakupan akses sama dengan halaman kelompok: intern hanya melihat **kelompoknya sendiri** (kelompok lain → 403 seperti biasa). Mentor/admin punya tombol "Perpanjang deadline" per baris (endpoint #19 v1.2).
- **Hero kelompok:** chip "N tugas lewat deadline" (merah) atau "Deadline terdekat T-7 · besok". Dihitung di client dari store tugas, sama dengan `Workspace.deadline`.
- **Widget dashboard mentor/admin:** `GET /workspace/summary` menambah `upcoming_deadlines` (lihat §8).

**#18 — perubahan PRD & backend untuk model Kelompok**
- **Data:** `workspaces.type` hanya `group`; tambah kolom `color`, `description`, `mentor_id` (pemilik), `status` (`active`/`finished`), `finished_at`. Keanggotaan intern unik: `workspace_members` dengan indeks unik `intern_id` untuk workspace aktif (1 anak magang = 1 kelompok). Workspace tim otomatis per `teams` dan space privat per intern **tidak dibuat lagi** (hapus listener/observer pembuatan otomatis di PRD Fitur 1).
- **Channel:** saat kelompok dibuat → channel `#<slug>` (type `team`) + DM mentor ↔ tiap anggota. Per unit kerja cukup satu channel `#umum-<tim>` (`workspace_id` null, anggota = semua mentor & intern unit itu). Endpoint buat channel topik tidak dipakai di UI.
- **Endpoint:**
  - `GET /workspace/groups/roster` → `{ interns: Roster[], groups: [{id,name,color,url,members_count}] }` (anak bimbingan mentor yang login; kelompok aktif miliknya).
  - `POST /workspace/groups` `{ name (wajib, ≤60), color (#hex), description?, intern_ids: [≥1] }` → `201 { data: Workspace }`. 422 bila ada intern yang bukan anak bimbingannya atau sudah berkelompok (`errors.intern_ids`).
  - `PATCH /workspace/groups/{id}` `{ name?, color? }` atau `{ status: "finished" }` → `{ data: Workspace }`. 403 bila tidak berhak / sudah selesai.
  - `POST /workspace/groups/{id}/members` `{ intern_ids }` → `{ data: Workspace }`. Aturan sama dengan pembuatan. **Tidak ada** endpoint hapus anggota.
- **Otomatis:** scheduler harian menandai kelompok `finished` bila semua anggotanya lewat `end_date`.
- **Notifikasi:** `intern_joined` (ke mentor: anak bimbingan baru, belum berkelompok), `group_joined` (ke intern: dimasukkan ke kelompok).
- **Template:** `POST /workspace/templates/{id}/assign` cukup `{ assignee_ids, due_at, start_at? }` — tugas dibuat di kelompok tiap intern (`target`/`workspace_id` dihapus). Intern tanpa kelompok → 422.
- **Monitoring:** `GET /workspace/progress` → `teams[]` = agregat kelompok aktif per unit (`groups_count`, `stats`), `workspace_url` = `/workspace?team={id}`; `interns[id].group_url` menggantikan `space_url`.
- **Anak Bimbingan (`mentor/intern/index`):** view menambah kolom centang & "Kelompok" (data dari roster). `InternController` tidak perlu diubah.
- **Preview lokal:** perubahan kelompok disimpan di session login (tidak sinkron antar akun). `GET /workspace/_preview/reset` mengembalikan data contoh. Akun review berpasangan diatur di `routes/workspace-preview-data.php` (`$pair`).

---

## 8. Widget dashboard — `GET /workspace/summary` (*usulan* #8)

Komponen `<x-workspace.dashboard-widgets role="intern|mentor" />` dipasang di `intern/dashboard.blade.php` dan `mentor/dashboard.blade.php`. Komponen hanya dirender bila `Route::has('workspace.index')`, lalu mengambil datanya sendiri. Controller dashboard **tidak perlu diubah**.

**Intern**
```json
{
  "role": "intern",
  "pending_mentor": false,
  "workspace_url": "/workspace/12",
  "my_tasks": {
    "data": [ TaskCard + { "workspace": { "id", "name", "type", "url" } } ],
    "counts": { "active": 9, "revision": 3, "late": 2 }
  },
  "unread_channels": [
    { "channel": { "id", "name", "type", "members" }, "workspace": { "id", "name", "type", "url" } | null,
      "url": "/workspace/3?tab=channel&channel=4", "unread_count": 2,
      "last_message": { "user": UserMini | null, "body": "teks polos, maks. ±120 karakter", "created_at": "..." } }
  ]
}
```
- `my_tasks.data`: maks. 5 tugas aktif (bukan `done`) yang di-assign ke intern, urut `due_at` terdekat. `counts` dihitung dari semua tugas aktif.
- `unread_channels`: maks. 5 channel dengan pesan belum dibaca, urut pesan terbaru. `#pengumuman` memakai `workspace: null`. `last_message.body` berupa teks polos (tanpa HTML).
- `pending_mentor: true` → intern belum punya kelompok; widget menampilkan keterangan, daftar boleh kosong. `workspace_url` = kelompoknya.

**Mentor** (admin: semua kelompok aktif)
```json
{
  "role": "mentor",
  "to_review": { "data": [ TaskCard + { "workspace", "submitted_at" } ], "total": 4 },
  "late_by_intern": [
    { "intern": InternMini, "workspace": { "id", "name", "type", "color", "url" }, "late_count": 3,
      "tasks": [ { "id", "code", "title", "due_at", "url" } ] }
  ],
  "upcoming_deadlines": { "data": [ TaskCard + { "workspace": { "id", "name", "type", "color", "url" } } ], "total": 21 },
  "group_progress": [ { "workspace": { "id", "name", "type", "color", "url" }, "total": 22, "done": 8, "overdue": 2 } ],
  "workload": [ { "intern": InternMini, "workspace": { "id", "name", "type", "color", "url" },
                  "counts": { "todo": 5, "in_progress": 1, "review": 1, "revision": 1 }, "overdue": 0 } ],
  "ungrouped_interns": [ Roster ],
  "groups_url": "/workspace"
}
```
- `ungrouped_interns` (v1.0): anak bimbingan aktif tanpa kelompok → widget "Anak bimbingan baru" dengan tombol "Masukkan". Kosong untuk admin.
- `to_review.data`: maks. 6 tugas berstatus `review` di workspace yang dibimbing, **terlama menunggu dulu** (`submitted_at` = waktu pindah ke Review, dari `task_activities`).
- `late_by_intern`: intern dengan `is_late` > 0, urut jumlah terbanyak. `workspace` = kelompok intern (link membuka `?tab=list`).
- `group_progress` (v1.4): maks. 6 kelompok aktif (`!is_read_only`), urut: terbanyak `overdue` dulu, lalu persentase `done / total` terendah. `total` = semua tugas kelompok; `overdue` = tugas belum `done` yang `is_late`. Klik membuka `{workspace.url}?tab=laporan`.
- `workload` (v1.4): maks. 8 anak bimbingan dengan tugas aktif (bukan `done`), urut jumlah tugas aktif terbanyak. `counts` per status aktif; `overdue` = tugas `is_late`. Intern yang ada di beberapa kelompok hanya muncul sekali (kelompok aktifnya).
- `upcoming_deadlines` (v1.3): tugas berstatus `todo` / `in_progress` / `revision` di kelompok aktif yang dibimbing, belum lewat deadline dan `due_at` ≤ sekarang + 7 hari. `data` maks. 6, urut `due_at` terdekat; `total` = jumlah seluruhnya. Tugas tanpa assignee ikut (ditandai "Belum dibagi").

Klik tugas di widget membuka `{workspace.url}?task={code}` (slide-over di board). Badge pesan diperbarui dari `unread_messages.by_channel` pada polling lonceng (usulan #3).

## 9. Realtime — Laravel Reverb (Fase 3)

Frontend (`public/js/workspace/realtime.js`) aktif **hanya bila** `BROADCAST_DRIVER=reverb` dan `config('broadcasting.connections.reverb.key')` terisi. Layout lalu mengirim `{ driver: 'reverb', key, host, port, scheme }` ke `WS.config.realtime`, dan Echo + pusher-js dimuat dari jsdelivr. Tanpa Reverb, aplikasi tetap memakai polling seperti biasa (tidak ada yang berubah).

Event dipakai sebagai **sinyal**: frontend lalu memanggil polling/refresh yang sudah ada. Payload cukup kecil.

| Channel privat (`routes/channels.php`) | Event (`broadcastAs`) | Payload | Efek di frontend |
| --- | --- | --- | --- |
| `workspace.{workspaceId}` | `message.changed` | `{ channel_id }` | Channel aktif → polling seketika; channel lain → refresh badge |
| `announcements` | `message.changed` | `{ channel_id }` | Sama, untuk `#pengumuman` |
| `workspace.{workspaceId}` | `task.changed` | `{ card: TaskCard }` | Kartu board/list diperbarui tanpa reload |
| `workspace.{workspaceId}` | `task.deleted` | `{ id }` | Kartu dihapus dari board |
| `App.Models.User.{id}` | notifikasi Laravel (`toBroadcast`) | bebas | Lonceng dimuat ulang |

- Otorisasi channel lewat `/broadcasting/auth` (header CSRF dikirim frontend). Aturannya sama dengan `WorkspacePolicy@view`.
- Selama terhubung, polling diperlambat menjadi minimal 60 detik sebagai jaring pengaman. Bila koneksi putus, interval normal kembali dengan sendirinya. Indikator di top bar: "Realtime" / "Menghubungkan…" / "Polling".
- **Perlu diubah di backend:** CSP `SecurityHeaders` menambah `connect-src 'self' wss://{REVERB_HOST}:{port}` (PRD Fase 2/3).
- Preview lokal tanpa server: tambahkan `?realtime=mock` di URL.

## 10. Menunggu konfirmasi (memengaruhi UI)

- Bolehkah mentor lain (co-mentor) diundang ke kelompok? v1.0: belum — satu mentor pemilik per kelompok, admin hanya memantau.
- Apakah nilai tugas masuk sertifikat/laporan akhir? Untuk saat ini nilai hanya tampil di detail tugas.
- Lama penyimpanan arsip chat. Tidak berdampak ke UI selain label "Diarsipkan".
