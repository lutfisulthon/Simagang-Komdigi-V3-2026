<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\NotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Ringkasan email chat belum dibaca. Hanya email: lonceng sudah menghitung chat belum dibaca sendiri. */
class ChatUnreadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param  array<int, array{channel: string, workspace_id: int, count: int}>  $channels */
    public function __construct(private array $channels)
    {
    }

    public function via(object $notifiable): array
    {
        return $notifiable instanceof User && $notifiable->email
            && app(NotificationPreferences::class)->allowsEmail($notifiable, 'chat_unread')
            ? ['mail']
            : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = array_sum(array_column($this->channels, 'count'));

        $mail = (new MailMessage)
            ->subject("{$total} pesan belum Anda baca")
            ->greeting('Halo, ' . $notifiable->name)
            ->line('Ada pesan di workspace yang belum Anda baca:');

        foreach ($this->channels as $c) {
            $mail->line("- {$c['channel']}: {$c['count']} pesan");
        }

        return $mail->action('Buka workspace', route('workspace.show', $this->channels[0]['workspace_id']));
    }
}
