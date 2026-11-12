<?php

namespace App\Observers;

use App\Models\Mentor;
use App\Services\WorkspaceProvisioner;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class MentorObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private WorkspaceProvisioner $provisioner)
    {
    }

    public function created(Mentor $mentor): void
    {
        $this->sync($mentor, resyncInterns: false);
    }

    /**
     * Status aktif / tim / akun berubah: perbarui akses mentor dan sinkronkan ulang intern
     * bimbingannya (mentor nonaktif membuat intern dianggap tanpa mentor).
     */
    public function updated(Mentor $mentor): void
    {
        if ($mentor->wasChanged(['is_active', 'team_id', 'user_id'])) {
            $this->sync($mentor, resyncInterns: true);
        }

        // Profil dialihkan ke akun lain: akun lama tidak boleh tetap menjadi mentor di workspace.
        $previousUserId = $mentor->getPrevious()['user_id'] ?? null;

        if ($mentor->wasChanged('user_id') && $previousUserId) {
            try {
                $this->provisioner->revokeFormerMentorUser((int) $previousUserId);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /** Kegagalan tidak boleh menggagalkan penyimpanan mentor; perbaiki lewat `workspace:backfill`. */
    private function sync(Mentor $mentor, bool $resyncInterns): void
    {
        try {
            $this->provisioner->syncMentor($mentor, $resyncInterns);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
