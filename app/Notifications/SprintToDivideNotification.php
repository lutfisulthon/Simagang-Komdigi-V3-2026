<?php

namespace App\Notifications;

use App\Models\Sprint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SprintToDivideNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return 'sprint_to_divide';
    }

    /** @param  array<int, string>  $titles  judul tugas yang belum dibagi (maks. 5) */
    public function __construct(private Sprint $sprint, private int $count, private array $titles)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'sprint_id' => $this->sprint->id,
            'sprint_name' => $this->sprint->name,
            'workspace_id' => $this->sprint->workspace_id,
            'task_count' => $this->count,
            'task_titles' => array_slice($this->titles, 0, 5),
            'message' => "{$this->count} tugas menunggu Anda bagi di {$this->sprint->name}",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("{$this->count} tugas menunggu Anda bagi")
            ->greeting('Halo, ' . $notifiable->name)
            ->line("Masih ada {$this->count} tugas di {$this->sprint->name} yang belum dibagi ke anggota:");

        foreach (array_slice($this->titles, 0, 5) as $title) {
            $mail->line('- ' . $title);
        }

        if ($this->count > 5) {
            $mail->line('... dan ' . ($this->count - 5) . ' tugas lainnya.');
        }

        return $mail->action('Bagi tugas', route('workspace.show', $this->sprint->workspace_id) . '?tab=board');
    }
}
