<?php

namespace App\Notifications;

use App\Models\Workspace;
use Illuminate\Notifications\Notification;

class GroupJoinedNotification extends Notification
{
    use \App\Notifications\Concerns\RespectsPreferences;

    public function preferenceType(): string
    {
        return 'group_joined';
    }

    public function __construct(private Workspace $group)
    {
    }

    public function toArray(object $notifiable): array
    {
        $mentor = $this->group->members()->wherePivot('role', 'mentor')->first();

        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $mentor?->id,
            'actor_name' => $mentor?->name,
            'workspace_id' => $this->group->id,
            'workspace_name' => $this->group->name,
            'message' => "Anda dimasukkan ke kelompok {$this->group->name}",
        ];
    }
}
