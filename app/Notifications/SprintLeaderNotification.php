<?php

namespace App\Notifications;

use App\Models\Sprint;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SprintLeaderNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

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

    public function toMail(object $notifiable): MailMessage
    {
        $workspace = $this->sprint->workspace;
        $mail = (new MailMessage)
            ->subject("Anda ketua {$this->sprint->name}" . ($workspace ? " · {$workspace->name}" : ''))
            ->greeting('Halo, ' . $notifiable->name)
            ->line("{$this->actor->name} menunjuk Anda sebagai ketua tim {$this->sprint->name}.");

        if ($this->sprint->end_date) {
            $mail->line('Deadline sprint: ' . $this->sprint->end_date->locale('id')->translatedFormat('j F Y') . '.');
        }

        return $mail
            ->line("Jumlah tugas di sprint ini: {$this->taskCount}. Sebagai ketua, Anda membagi tugas ke anggota kelompok di Board.")
            ->action('Buka board', route('workspace.show', $this->sprint->workspace_id) . '?tab=board');
    }
}
