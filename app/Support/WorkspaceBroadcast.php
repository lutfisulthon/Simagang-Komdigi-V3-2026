<?php

namespace App\Support;

use App\Events\WorkspaceSignal;
use App\Models\Channel;
use Illuminate\Support\Facades\DB;

/**
 * Pengirim sinyal realtime Workspace. Tidak melakukan apa pun bila realtime mati, dan tidak pernah
 * menggagalkan request: bila Reverb tidak terjangkau, data tetap tersimpan dan klien menerimanya lewat polling.
 * Sinyal dikirim setelah transaksi (jika ada) di-commit supaya klien tidak memuat data yang belum tersimpan.
 */
class WorkspaceBroadcast
{
    /** Nama event Echo untuk `.notification()`; lonceng klien memuat ulang dirinya saat menerimanya. */
    public const NOTIFICATION_EVENT = 'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated';

    public static function send(string $channel, string $type, array $payload = []): void
    {
        if (! Realtime::enabled()) {
            return;
        }

        DB::afterCommit(function () use ($channel, $type, $payload) {
            // Tanpa return: PendingBroadcast dikirim saat objeknya dibuang, jadi harus terjadi di dalam rescue.
            rescue(function () use ($channel, $type, $payload) {
                broadcast(new WorkspaceSignal($channel, $type, $payload));
            });
        });
    }

    /** Pesan di channel berubah: channel workspace → `workspace.{id}`, channel global (#pengumuman) → `announcements`. */
    public static function messageChanged(Channel $channel): void
    {
        self::send(
            $channel->workspace_id ? 'workspace.' . $channel->workspace_id : 'announcements',
            'message.changed',
            ['channel_id' => $channel->id],
        );
    }

    public static function taskChanged(int $workspaceId, int $taskId): void
    {
        self::send('workspace.' . $workspaceId, 'task.changed', ['id' => $taskId]);
    }

    public static function taskDeleted(int $workspaceId, int $taskId): void
    {
        self::send('workspace.' . $workspaceId, 'task.deleted', ['id' => $taskId]);
    }

    /** Ada notifikasi baru untuk user: lonceng klien memuat ulang. */
    public static function notified(int $userId): void
    {
        self::send('App.Models.User.' . $userId, self::NOTIFICATION_EVENT, []);
    }
}
