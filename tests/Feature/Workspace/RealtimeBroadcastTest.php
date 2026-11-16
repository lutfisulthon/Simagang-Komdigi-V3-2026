<?php

namespace Tests\Feature\Workspace;

use App\Events\WorkspaceSignal;
use App\Support\WorkspaceBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/**
 * Sinyal realtime Workspace (kontrak-frontend.md bagian 9): server harus menyiarkan sinyal yang dinanti frontend,
 * dan hanya anggota yang berhak boleh berlangganan channelnya.
 */
class RealtimeBroadcastTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'kunci-uji',
            'broadcasting.connections.reverb.secret' => 'rahasia-uji',
            'broadcasting.connections.reverb.app_id' => '1',
        ]);

        Event::fake([WorkspaceSignal::class]);
    }

    private function assertSignal(string $channel, string $type, ?array $payload = null): void
    {
        Event::assertDispatched(
            WorkspaceSignal::class,
            fn (WorkspaceSignal $e) => $e->channel === $channel && $e->type === $type && ($payload === null || $e->payload === $payload),
        );
    }

    public function test_pesan_baru_menyiarkan_message_changed_ke_workspace(): void
    {
        $this->actingAs($this->ahmad->user)
            ->postJson("/workspace/channels/{$this->umumGta->id}/messages", ['body' => 'Halo tim'])
            ->assertCreated();

        $this->assertSignal('workspace.' . $this->umumGta->workspace_id, 'message.changed', ['channel_id' => $this->umumGta->id]);
    }

    public function test_pesan_di_pengumuman_menyiarkan_ke_channel_announcements(): void
    {
        $this->actingAs($this->rudy->user)
            ->postJson("/workspace/channels/{$this->announcement->id}/messages", ['body' => 'Pengumuman uji'])
            ->assertCreated();

        $this->assertSignal('announcements', 'message.changed', ['channel_id' => $this->announcement->id]);
    }

    public function test_perubahan_tugas_menyiarkan_task_changed_dan_task_deleted(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->assertSignal('workspace.' . $this->spAhmad->id, 'task.changed', ['id' => $id]);

        $this->actingAs($this->rudy->user)->deleteJson("/workspace/tasks/{$id}")->assertNoContent();

        $this->assertSignal('workspace.' . $this->spAhmad->id, 'task.deleted', ['id' => $id]);
    }

    public function test_notifikasi_baru_menyiarkan_sinyal_ke_lonceng_user(): void
    {
        $this->makeTaskForAhmad(); // Ahmad menerima notifikasi tugas baru

        $this->assertSignal('App.Models.User.' . $this->ahmad->user_id, WorkspaceBroadcast::NOTIFICATION_EVENT, []);
    }

    public function test_tidak_ada_siaran_bila_realtime_mati(): void
    {
        config(['broadcasting.default' => 'log']);

        $this->makeTaskForAhmad();

        Event::assertNotDispatched(WorkspaceSignal::class);
    }

    public function test_kegagalan_siaran_tidak_menggagalkan_request(): void
    {
        Event::fake(); // lepas fake khusus; siaran sungguhan ke Reverb yang tidak berjalan
        config(['broadcasting.connections.reverb.options' => ['host' => '127.0.0.1', 'port' => 1, 'scheme' => 'http', 'useTLS' => false]]);

        $this->actingAs($this->rudy->user)
            ->postJson("/workspace/{$this->spAhmad->id}/tasks", ['title' => 'Tetap tersimpan', 'assignee_ids' => [$this->ahmad->id], 'due_at' => $this->due()])
            ->assertCreated();
    }

    public function test_hanya_anggota_yang_boleh_berlangganan_channel_workspace(): void
    {
        // Channel didaftarkan saat boot pada driver default test (log); callback otorisasinya sama untuk Reverb.
        $channels = Broadcast::driver('log')->getChannels();
        $can = fn (string $pattern, $user, ...$params) => (bool) ($channels[$pattern])($user, ...$params);

        // Space Ahmad: pemiliknya, mentornya, dan admin boleh; intern lain dan mentor tim lain tidak.
        $space = $this->spAhmad->id;
        $this->assertTrue($can('workspace.{workspaceId}', $this->ahmad->user, $space));
        $this->assertTrue($can('workspace.{workspaceId}', $this->rudy->user, $space));
        $this->assertTrue($can('workspace.{workspaceId}', $this->admin, $space));
        $this->assertFalse($can('workspace.{workspaceId}', $this->siti->user, $space));
        $this->assertFalse($can('workspace.{workspaceId}', $this->herman->user, $space));

        // Workspace tim: anggota boleh, tim lain tidak; id yang tidak ada ditolak.
        $this->assertTrue($can('workspace.{workspaceId}', $this->siti->user, $this->wsGta->id));
        $this->assertFalse($can('workspace.{workspaceId}', $this->budi->user, $this->wsGta->id));
        $this->assertFalse($can('workspace.{workspaceId}', $this->ahmad->user, 999999));

        // Channel pengumuman terbuka untuk pengguna Workspace.
        $this->assertTrue($can('announcements', $this->ahmad->user));
        $this->assertTrue($can('announcements', $this->rudy->user));

        // Lonceng milik sendiri saja.
        $this->assertTrue($can('App.Models.User.{id}', $this->ahmad->user, $this->ahmad->user_id));
        $this->assertFalse($can('App.Models.User.{id}', $this->ahmad->user, $this->siti->user_id));
    }
}
