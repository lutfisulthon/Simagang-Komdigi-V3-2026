<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use App\Services\NotificationPreferences;
use Illuminate\Bus\Queueable;

/**
 * Notifikasi in-app + email. Tiap kanal mengikuti Pengaturan Notifikasi user (default aktif).
 * Notifikasi in-app dikirim langsung (sync) supaya lonceng langsung terisi; email lewat queue
 * supaya request tidak menunggu SMTP. Kelas pemakai wajib mengimplementasikan ShouldQueue,
 * preferenceType(), dan toMail().
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
}
