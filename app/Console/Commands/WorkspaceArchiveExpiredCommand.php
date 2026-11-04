<?php

namespace App\Console\Commands;

use App\Models\Workspace;
use App\Services\WorkspaceProvisioner;
use Illuminate\Console\Command;

class WorkspaceArchiveExpiredCommand extends Command
{
    protected $signature = 'workspace:archive-expired';

    protected $description = 'Arsipkan Space Intern milik intern yang magangnya sudah selesai atau nonaktif (read-only)';

    public function handle(WorkspaceProvisioner $provisioner): int
    {
        $archived = 0;
        $failed = 0;

        // end_date yang lewat tidak memicu event model, jadi harus disapu terjadwal.
        Workspace::query()
            ->where('type', 'intern')
            ->where('is_archived', false)
            ->whereHas('intern', fn ($q) => $q
                ->where('is_active', false)
                ->orWhereDate('end_date', '<', today()))
            ->with('intern')
            ->chunkById(100, function ($workspaces) use ($provisioner, &$archived, &$failed) {
                foreach ($workspaces as $workspace) {
                    try {
                        $provisioner->sync($workspace->intern);
                        $archived++;
                    } catch (\Throwable $e) {
                        $failed++;
                        $this->error("Workspace #{$workspace->id}: {$e->getMessage()}");
                    }
                }
            });

        // Kelompok buatan mentor selesai bila semua anggotanya sudah selesai magang.
        $groups = app(\App\Services\GroupService::class);
        foreach ($groups->expired() as $group) {
            $groups->finish($group);
            $archived++;
        }

        $this->info("Diarsipkan: {$archived}");

        if ($failed) {
            $this->warn("Gagal: {$failed}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
