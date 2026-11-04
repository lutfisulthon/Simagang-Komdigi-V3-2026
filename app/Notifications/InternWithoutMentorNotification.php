<?php

namespace App\Notifications;

use App\Models\Intern;
use Illuminate\Notifications\Notification;

class InternWithoutMentorNotification extends Notification
{
    use \App\Notifications\Concerns\RespectsPreferences;

    public function preferenceType(): string
    {
        return 'intern_without_mentor';
    }

    public function __construct(private Intern $intern)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'intern_name' => $this->intern->name,
            'message' => "Intern aktif {$this->intern->name} belum punya mentor",
            'intern_id' => $this->intern->id,
        ];
    }
}
