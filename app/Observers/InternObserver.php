<?php

namespace App\Observers;

use App\Models\Intern;
use App\Services\WorkspaceProvisioner;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class InternObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private WorkspaceProvisioner $provisioner)
    {
    }

    public function created(Intern $intern): void
    {
        $this->sync($intern);
    }

    public function updated(Intern $intern): void
    {
        if ($intern->wasChanged(['is_active', 'mentor_id', 'team_id', 'end_date', 'user_id'])) {
            $this->sync($intern);
        }
    }

    /**
     * Kegagalan provisioning tidak boleh menggagalkan pembuatan intern.
     * Perbaiki lewat `php artisan workspace:backfill`.
     */
    private function sync(Intern $intern): void
    {
        try {
            $this->provisioner->sync($intern);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
