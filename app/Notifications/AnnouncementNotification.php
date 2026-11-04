<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferences;

    public function preferenceType(): string
    {
        return 'announcement';
    }

    use Queueable, SerializesModels;

    public function __construct(private Message $message)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->message->user_id,
            'actor_name' => $this->message->user?->name,
            'excerpt' => \Illuminate\Support\Str::limit((string) $this->message->body, 100),
            'message' => "Pengumuman baru dari {$this->message->user?->name}: " . \Illuminate\Support\Str::limit($this->message->body, 80),
            'channel_id' => $this->message->channel_id,
            'message_id' => $this->message->id,
        ];
    }
}
