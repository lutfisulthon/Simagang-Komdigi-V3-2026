<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return 'task_overdue';
    }

    public function __construct(private Task $task)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'task_code' => $this->task->code,
            'task_title' => $this->task->title,
            'message' => "{$this->task->code} \"{$this->task->title}\" terlambat",
            'task_id' => $this->task->id,
            'workspace_id' => $this->task->workspace_id,
            'due_at' => $this->task->due_at?->toIso8601String(),
        ];
    }
}
