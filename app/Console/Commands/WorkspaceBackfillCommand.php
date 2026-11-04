<?php

namespace App\Console\Commands;

use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Workspace;
use App\Services\WorkspaceProvisioner;
use Illuminate\Console\Command;

class WorkspaceBackfillCommand extends Command
{
    protected $signature = 'workspace:backfill {--dry-run : Hanya tampilkan intern dan mentor yang akan diproses}';

    protected $description = 'Buat workspace untuk mentor dan intern aktif yang belum punya (aman dijalankan berulang)';

    public function handle(WorkspaceProvisioner $provisioner): int
    {
        $dryRun = $this->option('dry-run');
        $counts = [
            WorkspaceProvisioner::PROVISIONED => 0,
            WorkspaceProvisioner::NO_TEAM => 0,
            WorkspaceProvisioner::PENDING_MENTOR => 0,
            WorkspaceProvisioner::PENDING_ACCOUNT => 0,
            WorkspaceProvisioner::ARCHIVED => 0,
        ];
        $mentors = 0;
        $failed = 0;

        // Mentor dulu, supaya Workspace Tim sudah berisi semua mentor sebelum intern masuk.
        Mentor::query()
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->with('user')
            ->orderBy('id')
            ->chunkById(100, function ($batch) use ($provisioner, $dryRun, &$mentors, &$failed) {
                /** @var \App\Models\Mentor $mentor */
                foreach ($batch as $mentor) {
                    if ($dryRun) {
                        $this->line("- mentor #{$mentor->id} {$mentor->name}");
                        continue;
                    }

                    try {
                        $provisioner->syncMentor($mentor);
                        $mentors++;
                    } catch (\Throwable $e) {
                        $failed++;
                        $this->error("mentor #{$mentor->id} {$mentor->name}: {$e->getMessage()}");
                    }
                }
            });

        Intern::query()
            ->where('is_active', true)
            ->with(['user', 'mentor.user'])
            ->orderBy('id')
            ->chunkById(100, function ($interns) use ($provisioner, $dryRun, &$counts, &$failed) {
                /** @var \App\Models\Intern $intern */
                foreach ($interns as $intern) {
                    if ($dryRun) {
                        $hasSpace = Workspace::where('type', 'intern')->where('intern_id', $intern->id)->exists();
                        $label = $hasSpace ? 'sinkron ulang' : 'baru';
                        $this->line("- intern #{$intern->id} {$intern->name} ({$label})");
                        continue;
                    }

                    try {
                        $counts[$provisioner->sync($intern)]++;
                    } catch (\Throwable $e) {
                        $failed++;
                        $this->error("intern #{$intern->id} {$intern->name}: {$e->getMessage()}");
                    }
                }
            });

        if ($dryRun) {
            $this->info('Dry-run selesai, tidak ada data diubah.');

            return self::SUCCESS;
        }

        $this->info("Mentor disinkronkan: {$mentors}");
        $this->info("Intern diproses: {$counts[WorkspaceProvisioner::PROVISIONED]}");
        $this->info("Intern tanpa tim (workspace tim dilewati): {$counts[WorkspaceProvisioner::NO_TEAM]}");
        $this->info("Menunggu mentor: {$counts[WorkspaceProvisioner::PENDING_MENTOR]}");
        $this->info("Menunggu akun user: {$counts[WorkspaceProvisioner::PENDING_ACCOUNT]}");
        $this->info("Diarsipkan (magang selesai): {$counts[WorkspaceProvisioner::ARCHIVED]}");

        if ($failed) {
            $this->warn("Gagal: {$failed}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
