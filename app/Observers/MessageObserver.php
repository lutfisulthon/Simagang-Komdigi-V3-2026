<?php

namespace App\Observers;

use App\Models\Message;
use App\Support\WorkspaceBroadcast;

/** Pesan dibuat, disunting, disematkan, atau dihapus → sinyal realtime ke channel Workspace terkait. */
class MessageObserver
{
    public function created(Message $message): void
    {
        $this->signal($message);
    }

    public function updated(Message $message): void
    {
        $this->signal($message);
    }

    public function deleted(Message $message): void
    {
        $this->signal($message);
    }

    private function signal(Message $message): void
    {
        $channel = $message->channel;

        if ($channel) {
            WorkspaceBroadcast::messageChanged($channel);
        }
    }
}
