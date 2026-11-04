<?php

namespace Tests\Feature\Workspace;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    public function test_tamu_tanpa_login_ditolak(): void
    {
        $this->getJson("/workspace/{$this->wsGta->id}/tasks")->assertUnauthorized();
        $this->getJson('/notifications')->assertUnauthorized();
    }

    public function test_intern_hanya_melihat_tim_sendiri_dan_space_pribadi(): void
    {
        $ahmad = $this->ahmad->user;

        $this->actingAs($ahmad)->getJson("/workspace/{$this->wsGta->id}/tasks")->assertOk();
        $this->actingAs($ahmad)->getJson("/workspace/{$this->spAhmad->id}/tasks")->assertOk();

        // tim lain dan Space intern lain: 403
        $this->actingAs($ahmad)->getJson("/workspace/{$this->wsDea->id}/tasks")->assertForbidden();
        $this->actingAs($ahmad)->getJson("/workspace/{$this->spSiti->id}/tasks")->assertForbidden();
    }

    public function test_mentor_melihat_tim_sendiri_dan_space_intern_bimbingan_saja(): void
    {
        $rudy = $this->rudy->user;

        $this->actingAs($rudy)->getJson("/workspace/{$this->wsGta->id}/tasks")->assertOk();
        $this->actingAs($rudy)->getJson("/workspace/{$this->spAhmad->id}/tasks")->assertOk();

        $this->actingAs($rudy)->getJson("/workspace/{$this->spSiti->id}/tasks")->assertForbidden();
        $this->actingAs($rudy)->getJson("/workspace/{$this->wsDea->id}/tasks")->assertForbidden();
        $this->actingAs($this->herman->user)->getJson("/workspace/{$this->wsGta->id}/tasks")->assertForbidden();
    }

    public function test_admin_dan_super_admin_melihat_semua_workspace(): void
    {
        $super = $this->makeAdmin('Super Uji', 'super_admin');

        foreach ([$this->admin, $super] as $user) {
            foreach ([$this->wsGta, $this->wsDea, $this->spAhmad, $this->spSiti] as $workspace) {
                $this->actingAs($user)->getJson("/workspace/{$workspace->id}/tasks")->assertOk();
            }
        }
    }

    public function test_role_institusi_dan_industri_belum_punya_akses(): void
    {
        foreach (['institusi', 'industri'] as $role) {
            $user = $this->makeUser($role, "User {$role}");

            $this->actingAs($user)->getJson("/workspace/{$this->wsGta->id}/tasks")->assertForbidden();
            $this->actingAs($user)->getJson('/workspace/channels')->assertForbidden();
            $this->actingAs($user)->getJson("/workspace/channels/{$this->announcement->id}/messages")->assertForbidden();
        }
    }

    public function test_intern_tidak_bisa_membuka_channel_tim_lain_atau_dm_intern_lain(): void
    {
        $ahmad = $this->ahmad->user;

        $this->actingAs($ahmad)->getJson("/workspace/channels/{$this->umumGta->id}/messages")->assertOk();
        $this->actingAs($ahmad)->getJson("/workspace/channels/{$this->dmAhmad->id}/messages")->assertOk();

        $this->actingAs($ahmad)->getJson("/workspace/channels/{$this->dmSiti->id}/messages")->assertForbidden();
        $this->actingAs($this->budi->user)->getJson("/workspace/channels/{$this->umumGta->id}/messages")->assertForbidden();
        $this->actingAs($this->budi->user)->postJson("/workspace/channels/{$this->umumGta->id}/messages", ['body' => 'x'])->assertForbidden();
    }

    public function test_dm_bisa_dibuka_intern_mentornya_dan_admin(): void
    {
        $this->actingAs($this->siti->user)->getJson("/workspace/channels/{$this->dmSiti->id}/messages")->assertOk();
        $this->actingAs($this->tasmil->user)->getJson("/workspace/channels/{$this->dmSiti->id}/messages")->assertOk();
        $this->actingAs($this->admin)->getJson("/workspace/channels/{$this->dmSiti->id}/messages")->assertOk();

        // mentor lain di tim yang sama bukan peserta DM
        $this->actingAs($this->rudy->user)->getJson("/workspace/channels/{$this->dmSiti->id}/messages")->assertForbidden();
    }

    public function test_pengumuman_terbuka_untuk_semua_tapi_hanya_admin_dan_mentor_yang_posting(): void
    {
        $this->actingAs($this->ahmad->user)->getJson("/workspace/channels/{$this->announcement->id}/messages")->assertOk();
        $this->sendMessage($this->ahmad->user, $this->announcement, 'x')->assertForbidden();

        $this->sendMessage($this->rudy->user, $this->announcement, 'Rapat besok')->assertCreated();
        $this->sendMessage($this->admin, $this->announcement, 'Libur')->assertCreated();
    }

    public function test_intern_tidak_bisa_membuat_channel(): void
    {
        $this->actingAs($this->ahmad->user)
            ->postJson("/workspace/{$this->wsGta->id}/channels", ['name' => 'proyek-web'])
            ->assertForbidden();
    }

    public function test_intern_tidak_bisa_membuat_tugas_di_workspace_tim(): void
    {
        $this->postTask($this->ahmad->user, $this->wsGta)->assertForbidden();
        $this->postTask($this->ahmad->user, $this->spSiti)->assertForbidden();
    }

    public function test_workspace_diarsipkan_menjadi_read_only(): void
    {
        $taskId = $this->makeTaskForAhmad();
        $this->spAhmad->update(['is_archived' => true]);

        $this->actingAs($this->ahmad->user)
            ->postJson("/workspace/tasks/{$taskId}/comments", ['body' => 'halo'])->assertForbidden();
        $this->sendMessage($this->ahmad->user, $this->dmAhmad, 'halo')->assertForbidden();
        $this->setStatus($this->ahmad->user, $taskId, 'in_progress')->assertForbidden();
        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id]])->assertForbidden();

        // masih bisa dibaca
        $this->actingAs($this->ahmad->user)->getJson("/workspace/{$this->spAhmad->id}/tasks")->assertOk();
        $this->actingAs($this->ahmad->user)->getJson("/workspace/channels/{$this->dmAhmad->id}/messages")->assertOk();
    }

    public function test_intern_viewer_pasca_magang_tidak_bisa_menulis_di_workspace_tim(): void
    {
        $this->ahmad->update(['is_active' => false]);

        $this->actingAs($this->ahmad->user)->getJson("/workspace/{$this->wsGta->id}/tasks")->assertOk();
        $this->sendMessage($this->ahmad->user, $this->umumGta, 'masih bisa?')->assertForbidden();
    }

    public function test_tugas_dan_lampiran_tidak_terlihat_oleh_non_anggota(): void
    {
        $taskId = $this->makeTaskForAhmad();

        $this->actingAs($this->siti->user)->getJson("/workspace/tasks/{$taskId}")->assertForbidden();
        $this->actingAs($this->budi->user)->getJson("/workspace/tasks/{$taskId}")->assertForbidden();
        $this->actingAs($this->tasmil->user)->getJson("/workspace/tasks/{$taskId}")->assertForbidden();
        $this->actingAs($this->ahmad->user)->getJson("/workspace/tasks/{$taskId}")->assertOk();

        $this->assertSame(1, Task::count());
    }
}
