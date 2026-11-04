<?php

namespace Tests\Concerns;

use App\Models\Channel;
use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

/**
 * Skenario baku untuk test HTTP Workspace:
 *  - TIM GTA: mentor Rudy dan Tasmil, intern Ahmad (mentor Rudy) dan Siti (mentor Tasmil)
 *  - TIM DEA: mentor Herman, intern Budi
 *  - satu admin; semua workspace, channel, dan Space Intern dibuat oleh observer provisioning
 */
trait WorkspaceScenario
{
    use WorkspaceFixtures;

    protected User $admin;
    protected Team $gta;
    protected Team $dea;
    protected Mentor $rudy;
    protected Mentor $tasmil;
    protected Mentor $herman;
    protected Intern $ahmad;
    protected Intern $siti;
    protected Intern $budi;
    protected Workspace $wsGta;
    protected Workspace $wsDea;
    protected Workspace $spAhmad;
    protected Workspace $spSiti;
    protected Channel $umumGta;
    protected Channel $dmAhmad;
    protected Channel $dmSiti;
    protected Channel $announcement;

    protected function setUpScenario(): void
    {
        $this->admin = $this->makeAdmin('Admin Uji');
        $this->gta = $this->makeTeam('TIM GTA');
        $this->dea = $this->makeTeam('TIM DEA');

        $this->rudy = $this->makeMentor('Rudy Hermayadi', $this->gta);
        $this->tasmil = $this->makeMentor('Tasmil', $this->gta);
        $this->herman = $this->makeMentor('Herman', $this->dea);

        $this->ahmad = $this->makeIntern('Ahmad Fauzi', $this->rudy, $this->gta);
        $this->siti = $this->makeIntern('Siti Nurhaliza', $this->tasmil, $this->gta);
        $this->budi = $this->makeIntern('Budi Santoso', $this->herman, $this->dea);

        $this->wsGta = $this->teamWorkspace($this->gta);
        $this->wsDea = $this->teamWorkspace($this->dea);
        $this->spAhmad = $this->internSpace($this->ahmad);
        $this->spSiti = $this->internSpace($this->siti);

        $this->umumGta = Channel::where('workspace_id', $this->wsGta->id)->where('type', 'team')->firstOrFail();
        $this->dmAhmad = Channel::where('workspace_id', $this->spAhmad->id)->where('type', 'direct')->firstOrFail();
        $this->dmSiti = Channel::where('workspace_id', $this->spSiti->id)->where('type', 'direct')->firstOrFail();
        $this->announcement = Channel::whereNull('workspace_id')->where('type', 'announcement')->firstOrFail();
    }

    protected function due(int $days = 3): string
    {
        return now()->addDays($days)->toDateTimeString();
    }

    /** Buat tugas lewat API sebagai $as; mengembalikan respons. */
    protected function postTask(User $as, Workspace $workspace, array $payload = []): TestResponse
    {
        return $this->actingAs($as)->postJson("/workspace/{$workspace->id}/tasks", $payload + [
            'title' => 'Tugas uji',
            'due_at' => $this->due(),
        ]);
    }

    /** Buat tugas di Space Ahmad oleh Rudy dan kembalikan id-nya. */
    protected function makeTaskForAhmad(array $payload = []): int
    {
        return $this->postTask($this->rudy->user, $this->spAhmad, $payload + ['assignee_ids' => [$this->ahmad->id]])
            ->assertCreated()
            ->json('data.0.id');
    }

    protected function setStatus(User $as, int $taskId, string $status, array $extra = []): TestResponse
    {
        return $this->actingAs($as)->patchJson("/workspace/tasks/{$taskId}/status", ['status' => $status] + $extra);
    }

    protected function notificationCount(User $user, string $class): int
    {
        return DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', $user->getMorphClass())
            ->where('type', $class)
            ->count();
    }

    protected function sendMessage(User $as, Channel $channel, string $body, array $extra = []): TestResponse
    {
        return $this->actingAs($as)->postJson("/workspace/channels/{$channel->id}/messages", ['body' => $body] + $extra);
    }
}
