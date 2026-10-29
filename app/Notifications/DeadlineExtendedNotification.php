<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class DeadlineExtendedNotification extends Notification
{
    use \App\Notifications\Concerns\RespectsPreferences;

    public function preferenceType(): string
    {
        return 'deadline_extended';
    }

    public function __construct(private Task $task, private User $actor, private Carbon $from, private ?string $reason)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'task_id' => $this->task->id,
            'task_code' => $this->task->code,
            'task_title' => $this->task->title,
            'workspace_id' => $this->task->workspace_id,
            'from' => $this->from->toIso8601String(),
            'due_at' => $this->task->due_at?->toIso8601String(),
            'reason' => $this->reason,
            'message' => "Deadline {$this->task->code} diperpanjang",
        ];
    }
}
