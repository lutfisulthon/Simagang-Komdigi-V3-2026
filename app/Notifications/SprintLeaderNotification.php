<?php

namespace App\Notifications;

use App\Models\Sprint;
use App\Models\User;
use Illuminate\Notifications\Notification;

class SprintLeaderNotification extends Notification
{
    use \App\Notifications\Concerns\RespectsPreferences;

    public function preferenceType(): string
    {
        return 'sprint_leader';
    }

    public function __construct(private Sprint $sprint, private User $actor, private int $taskCount)
    {
    }

    public function toArray(object $notifiable): array
    {
        $workspace = $this->sprint->workspace;

        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'sprint_id' => $this->sprint->id,
            'sprint_name' => $this->sprint->name,
            'workspace_id' => $this->sprint->workspace_id,
            'workspace_name' => $workspace?->name,
            'end_date' => $this->sprint->end_date?->toDateString(),
            'task_count' => $this->taskCount,
            'message' => "Anda ketua {$this->sprint->name}" . ($workspace ? " · {$workspace->name}" : ''),
        ];
    }
}
