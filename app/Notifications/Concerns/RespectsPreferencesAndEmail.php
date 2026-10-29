<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use App\Services\NotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

/**
 * Notifikasi in-app + email. Tiap kanal mengikuti Pengaturan Notifikasi user (default aktif).
 * Notifikasi in-app dikirim langsung (sync) supaya lonceng langsung terisi; email lewat queue
 * supaya request tidak menunggu SMTP. Kelas pemakai wajib mengimplementasikan ShouldQueue dan
 * preferenceType(). toMail() bawaan dibentuk dari toArray(); kelas boleh menimpanya.
 */
trait RespectsPreferencesAndEmail
{
    use Queueable;

    abstract public function preferenceType(): string;

    public function via(object $notifiable): array
    {
        $prefs = app(NotificationPreferences::class);
        $channels = [];

        if ($prefs->allows($notifiable, $this->preferenceType())) {
            $channels[] = 'database';
        }

        if ($notifiable instanceof User && $notifiable->email && $prefs->allowsEmail($notifiable, $this->preferenceType())) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /** @return array<string, string> */
    public function viaConnections(): array
    {
        return ['database' => 'sync', 'mail' => config('queue.default')];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->toArray($notifiable);

        $mail = (new MailMessage)
            ->subject($data['message'])
            ->greeting('Halo, ' . $notifiable->name)
            ->line($data['message']);

        if (! empty($data['excerpt'])) {
            $mail->line('"' . $data['excerpt'] . '"');
        }

        if (! empty($data['due_at'])) {
            $mail->line('Deadline: ' . Carbon::parse($data['due_at'])->timezone('Asia/Makassar')->locale('id')->translatedFormat('j F Y H:i') . ' WITA');
        }

        return $mail->action('Buka di Simagang', $this->emailUrl($data));
    }

    /** @param  array<string, mixed>  $data */
    private function emailUrl(array $data): string
    {
        return match (true) {
            ! empty($data['task_id']) => route('workspace.tasks.show', $data['task_id']),
            ! empty($data['workspace_id']) => route('workspace.show', $data['workspace_id']),
            default => route('workspace.index'),
        };
    }
}
