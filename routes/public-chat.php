<?php

use App\Http\Controllers\Admin\AdminPublicChatController;
use App\Http\Controllers\PublicChatController;
use Illuminate\Support\Facades\Route;

/*
| Chat Publik untuk semua pengguna yang login (semua peran). Kontrak JSON: docs/public-chat-backend.md.
| Moderasi (sembunyikan pesan, bungkam user) ada di bawah /admin/chat-publik.
*/
Route::middleware(['auth', 'public-chat'])->group(function () {
    Route::get('/chat-publik', [PublicChatController::class, 'page'])->name('public-chat.index');

    Route::prefix('public-chat/messages')->name('public-chat.messages.')->group(function () {
        Route::get('/', [PublicChatController::class, 'index'])->name('index');
        Route::post('/', [PublicChatController::class, 'store'])->middleware('throttle:public-chat-messages')->name('store');
        Route::post('/{id}/pin', [PublicChatController::class, 'pin'])->middleware('throttle:public-chat-messages')->name('pin');
        Route::delete('/{id}/pin', [PublicChatController::class, 'unpin'])->name('unpin');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin/chat-publik')->name('admin.public-chat.')->group(function () {
    Route::get('/', [AdminPublicChatController::class, 'index'])->name('index');
    Route::post('/messages/{message}/hide', [AdminPublicChatController::class, 'hide'])->name('hide');
    Route::post('/messages/{message}/restore', [AdminPublicChatController::class, 'restore'])->name('restore');
    Route::post('/messages/{message}/unpin', [AdminPublicChatController::class, 'unpin'])->name('unpin');
    Route::post('/users/{user}/mute', [AdminPublicChatController::class, 'mute'])->name('mute');
    Route::post('/users/{user}/unmute', [AdminPublicChatController::class, 'unmute'])->name('unmute');
});
