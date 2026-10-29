<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MessageMentionNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return 'mentioned';
    }

    public function __construct(private Message $message)
    {
    }

    public function toArray(object $notifiable): array
    {
        $channel = $this->message->channel;

        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->message->user_id,
            'actor_name' => $this->message->user?->name,
            'channel_name' => $this->message->channel->name,
            'excerpt' => \Illuminate\Support\Str::limit((string) $this->message->body, 100),
            'message' => "{$this->message->user?->name} menyebut kamu di #{$channel->name}",
            'channel_id' => $channel->id,
            'workspace_id' => $channel->workspace_id,
            'message_id' => $this->message->id,
        ];
    }
}
