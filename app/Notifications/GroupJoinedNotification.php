<?php

namespace App\Notifications;

use App\Models\Workspace;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupJoinedNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

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

    public function toMail(object $notifiable): MailMessage
    {
        $mentor = $this->group->members()->wherePivot('role', 'mentor')->first();

        return (new MailMessage)
            ->subject("Anda dimasukkan ke kelompok {$this->group->name}")
            ->greeting('Halo, ' . $notifiable->name)
            ->line(($mentor ? "{$mentor->name} memasukkan" : 'Anda dimasukkan') . " Anda ke kelompok {$this->group->name}.")
            ->line('Di kelompok ini Anda mengerjakan tugas dan berdiskusi bersama mentor dan anggota lain.')
            ->action('Buka kelompok', route('workspace.show', $this->group));
    }
}
