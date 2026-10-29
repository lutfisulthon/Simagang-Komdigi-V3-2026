<?php

use App\Http\Controllers\Workspace\AttachmentController;
use App\Http\Controllers\Workspace\ChannelController;
use App\Http\Controllers\Workspace\DashboardController;
use App\Http\Controllers\Workspace\InsightController;
use App\Http\Controllers\Workspace\LabelController;
use App\Http\Controllers\Workspace\MessageController;
use App\Http\Controllers\Workspace\NotificationController;
use App\Http\Controllers\Workspace\SprintController;
use App\Http\Controllers\Workspace\TaskCommentController;
use App\Http\Controllers\Workspace\TaskController;
use App\Http\Controllers\Workspace\TaskTemplateController;
use App\Http\Controllers\Workspace\WorkspacePageController;
use Illuminate\Support\Facades\Route;

/*
| Modul Workspace. Satu prefix bersama untuk admin, mentor, dan intern;
| pembatasan akses lewat Policy, bukan prefix route per role.
*/
Route::middleware('auth')->prefix('workspace')->name('workspace.')->group(function () {
    // Halaman: daftar kelompok dan detail kelompok
    Route::get('/', [WorkspacePageController::class, 'index'])->name('index');
    Route::get('{workspace}', [WorkspacePageController::class, 'show'])->whereNumber('workspace')->name('show');

    // Ringkasan untuk dashboard (intern / mentor / admin)
    Route::get('dashboard', [DashboardController::class, 'summary'])->name('dashboard');

    // Widget dashboard, Monitoring, detail intern, dan rekap tugas (bentuk data frontend)
    Route::get('summary', [InsightController::class, 'summary'])->name('summary');
    Route::get('progress', [InsightController::class, 'progress'])->name('progress');
    Route::get('recap', [InsightController::class, 'recap'])->name('recap');
    Route::get('interns/{intern}/tasks', [InsightController::class, 'internTasks'])->whereNumber('intern')->name('interns.tasks');

    // Template tugas (onboarding dan assign massal)
    Route::get('templates', [TaskTemplateController::class, 'index'])->name('templates.index');
    Route::post('templates', [TaskTemplateController::class, 'store'])->name('templates.store');
    Route::put('templates/{template}', [TaskTemplateController::class, 'update'])->whereNumber('template')->name('templates.update');
    Route::post('templates/{template}/assign', [TaskTemplateController::class, 'assign'])->whereNumber('template')->name('templates.assign');
    Route::delete('templates/{template}', [TaskTemplateController::class, 'destroy'])->whereNumber('template')->name('templates.destroy');

    // Tugas milik intern yang login (dropdown logbook); didaftarkan sebelum tasks/{task}
    Route::get('tasks/mine', [TaskController::class, 'mine'])->name('tasks.mine');

    // Unggah lampiran lebih dulu, ditautkan lewat attachment_ids
    Route::post('attachments', [AttachmentController::class, 'upload'])->name('attachments.upload');

    // Aktivitas workspace berhalaman
    Route::get('{workspace}/activities', [WorkspacePageController::class, 'activities'])->whereNumber('workspace')->name('activities.index');

    // Label per tim
    Route::post('teams/{team}/labels', [LabelController::class, 'store'])->whereNumber('team')->name('labels.store');
    Route::patch('labels/{label}', [LabelController::class, 'update'])->whereNumber('label')->name('labels.update');
    Route::delete('labels/{label}', [LabelController::class, 'destroy'])->whereNumber('label')->name('labels.destroy');

    // Tugas
    Route::get('{workspace}/tasks', [TaskController::class, 'index'])->whereNumber('workspace')->name('tasks.index');
    Route::post('{workspace}/tasks', [TaskController::class, 'store'])->whereNumber('workspace')->name('tasks.store');

    // Sprint dan backlog
    Route::post('{workspace}/sprints', [SprintController::class, 'store'])->whereNumber('workspace')->name('sprints.store');
    Route::prefix('sprints/{sprint}')->whereNumber('sprint')->group(function () {
        Route::patch('/', [SprintController::class, 'update'])->name('sprints.update');
        Route::delete('/', [SprintController::class, 'destroy'])->name('sprints.destroy');
        Route::post('start', [SprintController::class, 'start'])->name('sprints.start');
        Route::post('complete', [SprintController::class, 'complete'])->name('sprints.complete');
    });

    Route::prefix('tasks/{task}')->whereNumber('task')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'task'])->name('tasks.show');
        Route::put('/', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::patch('status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::patch('review', [TaskController::class, 'review'])->name('tasks.review');
        Route::patch('sprint', [TaskController::class, 'plan'])->name('tasks.plan');
        Route::patch('assignees', [TaskController::class, 'assignees'])->name('tasks.assignees');
        Route::patch('deadline', [TaskController::class, 'extendDeadline'])->name('tasks.deadline');
        Route::patch('checklist/{item}', [TaskController::class, 'toggleChecklistItem'])->whereNumber('item')->name('tasks.checklist');
        Route::post('template', [TaskTemplateController::class, 'storeFromTask'])->name('tasks.save-template');

        // Komentar dan lampiran (batas unggah 10 file per menit dicek di AttachmentService)
        Route::post('comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
        Route::post('attachments', [AttachmentController::class, 'store'])->name('tasks.attachments.store');
    });

    // Channel dan pesan
    Route::get('channels', [ChannelController::class, 'index'])->name('channels.index');
    Route::post('{workspace}/channels', [ChannelController::class, 'store'])->whereNumber('workspace')->name('channels.store');

    Route::prefix('channels/{channel}')->whereNumber('channel')->group(function () {
        Route::get('messages', [MessageController::class, 'index'])->name('channels.messages.index');
        Route::post('messages', [MessageController::class, 'store'])->middleware('throttle:workspace-messages')->name('channels.messages.store');
        Route::post('read', [ChannelController::class, 'markRead'])->name('channels.read');
        Route::patch('/', [ChannelController::class, 'update'])->name('channels.update');
        Route::patch('mute', [ChannelController::class, 'mute'])->name('channels.mute');
        Route::patch('archive', [ChannelController::class, 'archive'])->name('channels.archive');
    });

    Route::prefix('messages/{message}')->whereNumber('message')->group(function () {
        Route::patch('/', [MessageController::class, 'update'])->middleware('throttle:workspace-messages')->name('messages.update');
        // withTrashed: hapus ulang pesan yang sudah terhapus tidak 404 (idempoten).
        Route::delete('/', [MessageController::class, 'destroy'])->withTrashed()->name('messages.destroy');
        Route::patch('pin', [MessageController::class, 'pin'])->name('messages.pin');
        Route::get('replies', [MessageController::class, 'replies'])->name('messages.replies');
        Route::post('reactions', [MessageController::class, 'react'])->name('messages.react');
    });

    // Unduh lampiran (storage private, dicek Policy)
    Route::get('attachments/{attachment}', [AttachmentController::class, 'download'])->whereNumber('attachment')->name('attachments.download');
});

// Lonceng notifikasi in-app (semua role yang login)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('settings', [NotificationController::class, 'settings'])->name('settings');
    Route::get('preferences', [NotificationController::class, 'preferences'])->name('preferences');
    Route::put('preferences', [NotificationController::class, 'savePreferences'])->name('preferences.save');
    Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::post('{id}/read', [NotificationController::class, 'markRead'])->name('read');
});
