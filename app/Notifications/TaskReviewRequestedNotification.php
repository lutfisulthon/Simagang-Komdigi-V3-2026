<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskReviewRequestedNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return 'task_in_review';
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
            'message' => "{$this->actor->name} mengirim {$this->task->code} \"{$this->task->title}\" untuk direview",
            'task_id' => $this->task->id,
            'workspace_id' => $this->task->workspace_id,
        ];
    }
}
