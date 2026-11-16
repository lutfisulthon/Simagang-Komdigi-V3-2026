<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Sinyal realtime Workspace di channel privat (lihat docs/workspace/kontrak-frontend.md, bagian 9).
 * Payload sengaja sekecil mungkin (id saja): klien memuat data lewat endpoint biasa sebagai dirinya sendiri,
 * sehingga hak akses dan flag `can` per pengguna tetap dihitung server.
 *  - workspace.{id}   message.changed { channel_id } | task.changed { id } | task.deleted { id }
 *  - announcements    message.changed { channel_id }
 *  - App.Models.User.{id}  notifikasi (nama event Echo `.notification()`), memuat ulang lonceng
 * Dikirim lewat WorkspaceBroadcast::send, yang menelan galat bila server Reverb mati.
 */
class WorkspaceSignal implements ShouldBroadcastNow
{
    public function __construct(
        public string $channel,
        public string $type,
        public array $payload = [],
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel($this->channel);
    }

    public function broadcastAs(): string
    {
        return $this->type;
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
