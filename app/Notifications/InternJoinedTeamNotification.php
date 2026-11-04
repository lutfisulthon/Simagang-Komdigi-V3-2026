<?php

namespace App\Notifications;

use App\Models\Intern;
use App\Models\Team;
use Illuminate\Notifications\Notification;

class InternJoinedTeamNotification extends Notification
{
    use \App\Notifications\Concerns\RespectsPreferences;

    public function preferenceType(): string
    {
        return 'intern_joined';
    }

    public function __construct(private Intern $intern, private Team $team)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'intern_name' => $this->intern->name,
            'team_name' => $this->team->name,
            'message' => "Intern baru {$this->intern->name} masuk tim {$this->team->name}",
            'intern_id' => $this->intern->id,
            'team_id' => $this->team->id,
        ];
    }
}
