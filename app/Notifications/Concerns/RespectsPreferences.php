<?php

namespace App\Notifications\Concerns;

use App\Services\NotificationPreferences;

/**
 * Kanal in-app hanya dipakai bila user tidak mematikan jenis notifikasinya
 * (Pengaturan Notifikasi). Kelas pemakai wajib mendefinisikan preferenceType().
 */
trait RespectsPreferences
{
    /** Kunci jenis di NotificationPreferences::CATALOG. */
    abstract public function preferenceType(): string;

    public function via(object $notifiable): array
    {
        return app(NotificationPreferences::class)->allows($notifiable, $this->preferenceType())
            ? ['database']
            : [];
    }
}
