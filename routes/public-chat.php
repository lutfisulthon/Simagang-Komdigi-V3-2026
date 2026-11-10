<?php

use Illuminate\Support\Facades\Route;

/*
| Halaman Chat Publik (frontend). Hanya menampilkan view untuk pengguna yang sudah login (semua peran).
| Pesan masih berupa data contoh di browser; endpoint daftar/kirim pesan menyusul dari backend
| (lihat docs/public-chat-backend.md). Setelah controller backend tersedia, closure ini cukup diganti controller.
*/
Route::middleware('auth')->get('/chat-publik', fn () => view('chat-publik.index'))->name('public-chat.index');
