<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskCommentedNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return 'task_commented';
    }

    public function __construct(private Task $task, private TaskComment $comment)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->comment->user_id,
            'actor_name' => $this->comment->user?->name,
            'task_code' => $this->task->code,
            'task_title' => $this->task->title,
            'excerpt' => \Illuminate\Support\Str::limit((string) $this->comment->body, 100),
            'message' => "{$this->comment->user?->name} berkomentar di {$this->task->code} \"{$this->task->title}\"",
            'task_id' => $this->task->id,
            'workspace_id' => $this->task->workspace_id,
            'comment_id' => $this->comment->id,
        ];
    }
}
