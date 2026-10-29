<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return 'task_assigned';
    }

    public function __construct(private Task $task, private User $actor)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'task_code' => $this->task->code,
            'task_title' => $this->task->title,
            'message' => "{$this->actor->name} menugaskan kamu: {$this->task->code} \"{$this->task->title}\"",
            'task_id' => $this->task->id,
            'workspace_id' => $this->task->workspace_id,
            'due_at' => $this->task->due_at?->toIso8601String(),
        ];
    }
}
