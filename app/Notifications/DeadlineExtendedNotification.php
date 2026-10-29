<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class DeadlineExtendedNotification extends Notification implements ShouldQueue
{
    use \App\Notifications\Concerns\RespectsPreferencesAndEmail;

    public function preferenceType(): string
    {
        return 'deadline_extended';
    }

    public function __construct(private Task $task, private User $actor, private Carbon $from, private ?string $reason)
    {
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->preferenceType(),
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'task_id' => $this->task->id,
            'task_code' => $this->task->code,
            'task_title' => $this->task->title,
            'workspace_id' => $this->task->workspace_id,
            'from' => $this->from->toIso8601String(),
            'due_at' => $this->task->due_at?->toIso8601String(),
            'reason' => $this->reason,
            'message' => "Deadline {$this->task->code} diperpanjang",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $format = fn (Carbon $date) => $date->copy()->locale('id')->translatedFormat('j F Y H:i');

        $mail = (new MailMessage)
            ->subject("Deadline {$this->task->code} diperpanjang")
            ->greeting('Halo, ' . $notifiable->name)
            ->line("{$this->actor->name} memperpanjang deadline tugas {$this->task->code} \"{$this->task->title}\".")
            ->line('Deadline lama: ' . $format($this->from) . '. Deadline baru: ' . $format($this->task->due_at) . '.');

        if ($this->reason) {
            $mail->line("Alasan: {$this->reason}");
        }

        return $mail->action('Lihat tugas', route('workspace.tasks.show', $this->task));
    }
}
