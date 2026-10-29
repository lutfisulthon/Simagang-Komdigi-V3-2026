<?php

namespace App\Notifications;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskReviewedNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return $this->task->status === \App\Enums\TaskStatus::Done ? 'task_approved' : 'task_revision';
    }

    public function __construct(private Task $task, private ?\App\Models\User $actor = null)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->actor?->id,
            'actor_name' => $this->actor?->name,
            'task_code' => $this->task->code,
            'task_title' => $this->task->title,
            'message' => $this->task->status === TaskStatus::Done
                ? "{$this->task->code} \"{$this->task->title}\" dinyatakan Selesai"
                : "{$this->task->code} \"{$this->task->title}\" perlu direvisi",
            'task_id' => $this->task->id,
            'workspace_id' => $this->task->workspace_id,
            'status' => $this->task->status->value,
            'score' => $this->task->score,
            'feedback' => $this->task->feedback,
        ];
    }
}
