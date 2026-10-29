<?php

namespace Tests\Feature\Workspace;

use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\Workspace;
use App\Notifications\DeadlineExtendedNotification;
use App\Notifications\GroupJoinedNotification;
use App\Notifications\SprintLeaderNotification;
use App\Notifications\SprintToDivideNotification;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Kelompok di Monitoring, assign template, dan notifikasi email (in-app + email). */
class GroupIntegrationTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function makeGroup(array $internIds, $mentor = null): Workspace
    {
        $id = $this->actingAs($mentor ?? $this->rudy->user)->postJson('/workspace/groups', [
            'name' => 'Tim Redesign', 'color' => '#4f46e5', 'intern_ids' => $internIds,
        ])->assertCreated()->json('data.id');

        return Workspace::findOrFail($id);
    }

    /** Email yang terkirim lewat mailer array (phpunit.xml), terbaru terakhir. */
    private function sentMails(): array
    {
        return app('mailer')->getSymfonyTransport()->messages()->all();
    }

    private function mailsTo(string $email): array
    {
        return array_values(array_filter($this->sentMails(), fn ($m) => collect($m->getEnvelope()->getRecipients())->contains(fn ($r) => $r->getAddress() === $email)));
    }

    // ------------------------------------------------------------------ Monitoring

    public function test_monitoring_menunjuk_ke_kelompok_bila_ada_dan_ke_space_bila_belum(): void
    {
        $group = $this->makeGroup([$this->ahmad->id]);
        $this->postTask($this->admin, $this->spSiti, ['assignee_ids' => [$this->siti->id]])->assertCreated();
        $this->postTask($this->admin, $group, ['assignee_ids' => [$this->ahmad->id]])->assertCreated();

        $res = $this->actingAs($this->admin)->getJson("/workspace/progress?intern_ids[]={$this->ahmad->id}&intern_ids[]={$this->siti->id}")->assertOk();

        $res->assertJsonPath("interns.{$this->ahmad->id}.group_url", route('workspace.show', $group))
            ->assertJsonPath("interns.{$this->ahmad->id}.space_url", route('workspace.show', $group))
            ->assertJsonPath("interns.{$this->siti->id}.group_url", route('workspace.show', $this->spSiti));
    }

    public function test_tugas_intern_membawa_kelompok_bukan_space_bila_berkelompok(): void
    {
        $group = $this->makeGroup([$this->ahmad->id]);
        $this->postTask($this->admin, $group, ['assignee_ids' => [$this->ahmad->id]])->assertCreated();

        $this->actingAs($this->admin)->getJson("/workspace/interns/{$this->ahmad->id}/tasks")->assertOk()
            ->assertJsonPath('group.id', $group->id)
            ->assertJsonPath('space_url', route('workspace.show', $group));

        // Kelompok selesai: kembali ke Space pribadi
        $this->actingAs($this->rudy->user)->patchJson("/workspace/groups/{$group->id}", ['status' => 'finished'])->assertOk();
        $this->actingAs($this->admin)->getJson("/workspace/interns/{$this->ahmad->id}/tasks")
            ->assertJsonPath('group.id', $this->spAhmad->id);
    }

    // ------------------------------------------------------------------ Assign template

    private function template(): int
    {
        return TaskTemplate::create(['team_id' => $this->gta->id, 'title' => 'Laporan mingguan', 'created_by' => $this->rudy->user_id])->id;
    }

    public function test_assign_template_membuat_tugas_di_kelompok_aktif_intern(): void
    {
        $group = $this->makeGroup([$this->ahmad->id]);
        $template = $this->template();

        $res = $this->actingAs($this->rudy->user)->postJson("/workspace/templates/{$template}/assign", [
            'assignee_ids' => [$this->ahmad->id], 'due_at' => $this->due(),
        ])->assertCreated();

        $res->assertJsonPath('data.0.workspace.id', $group->id);
        $this->assertSame($group->id, Task::latest('id')->first()->workspace_id);
        $this->assertSame(0, Task::where('workspace_id', $this->spAhmad->id)->count());
    }

    public function test_assign_template_campuran_kelompok_dan_space_hanya_oleh_yang_berhak(): void
    {
        $group = $this->makeGroup([$this->ahmad->id]);
        $template = $this->template();

        // Rudy hanya anak bimbingan Ahmad; Siti di Space Tasmil: Rudy tidak boleh membuat tugas di sana.
        $this->actingAs($this->rudy->user)->postJson("/workspace/templates/{$template}/assign", [
            'assignee_ids' => [$this->ahmad->id, $this->siti->id], 'due_at' => $this->due(),
        ])->assertForbidden();
        $this->assertSame(0, Task::count());

        // Admin boleh: Ahmad ke kelompoknya, Siti ke Space-nya.
        $this->actingAs($this->admin)->postJson("/workspace/templates/{$template}/assign", [
            'assignee_ids' => [$this->ahmad->id, $this->siti->id], 'due_at' => $this->due(),
        ])->assertCreated()->assertJsonCount(2, 'data');
        $this->assertSame([$group->id, $this->spSiti->id], Task::orderBy('id')->pluck('workspace_id')->all());
    }

    public function test_intern_tanpa_kelompok_atau_space_aktif_ditolak(): void
    {
        $this->spAhmad->update(['is_archived' => true]);
        $template = $this->template();

        $this->actingAs($this->admin)->postJson("/workspace/templates/{$template}/assign", [
            'assignee_ids' => [$this->ahmad->id], 'due_at' => $this->due(),
        ])->assertUnprocessable()->assertJsonValidationErrors('assignee_ids');
    }

    public function test_daftar_intern_untuk_assign_memakai_kelompok_sebagai_tujuan(): void
    {
        $group = $this->makeGroup([$this->ahmad->id]);

        $this->actingAs($this->rudy->user)->get('/workspace/templates')->assertOk()
            ->assertViewHas('assignableInterns', fn ($interns) => collect($interns)->firstWhere('id', $this->ahmad->id)['group']['id'] === $group->id);
    }

    // ------------------------------------------------------------------ Email

    public function test_preferensi_email_hanya_untuk_jenis_yang_dikirim_lewat_email(): void
    {
        $types = collect($this->actingAs($this->ahmad->user)->getJson('/notifications/preferences')->assertOk()->json('data.types'));

        foreach (['sprint_leader', 'sprint_to_divide', 'deadline_extended', 'group_joined'] as $type) {
            $this->assertTrue($types->firstWhere('type', $type)['email'], "{$type} default aktif");
        }
        $this->assertNull($types->firstWhere('type', 'task_assigned')['email']);

        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['sprint_leader' => ['email' => false]]])
            ->assertOk()->assertJsonPath('data.types.' . $types->search(fn ($t) => $t['type'] === 'sprint_leader') . '.email', false);

        // in_app tidak ikut berubah, dan jenis lain tetap menolak email
        $this->assertTrue(collect($this->actingAs($this->ahmad->user)->getJson('/notifications/preferences')->json('data.types'))->firstWhere('type', 'sprint_leader')['in_app']);
        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['task_assigned' => ['email' => true]]])->assertUnprocessable();
    }

    public function test_ketua_sprint_menerima_email_dan_notifikasi_in_app(): void
    {
        $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/sprints", [
            'end_date' => now()->addWeek()->toDateString(), 'leader_id' => $this->ahmad->id,
        ])->assertCreated();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, SprintLeaderNotification::class));

        $mails = $this->mailsTo($this->ahmad->user->email);
        $this->assertCount(1, $mails);
        $this->assertStringContainsString('Anda ketua Sprint 1', $mails[0]->getOriginalMessage()->getSubject());
        $this->assertStringContainsString("/workspace/{$this->wsGta->id}?tab=board", $mails[0]->getOriginalMessage()->getHtmlBody());
    }

    public function test_email_sprint_ke_divide_memuat_daftar_tugas(): void
    {
        $sprint = $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/sprints", [
            'end_date' => now()->addWeek()->toDateString(), 'leader_id' => $this->ahmad->id,
            'tasks' => [['title' => 'Slicing halaman'], ['title' => 'Integrasi API']],
        ])->json('data.id');

        $this->actingAs($this->rudy->user)->postJson("/workspace/sprints/{$sprint}/start", [
            'name' => 'Sprint 1', 'start_date' => now()->toDateString(), 'end_date' => now()->addWeek()->toDateString(), 'leader_id' => $this->ahmad->id,
        ])->assertOk();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, SprintToDivideNotification::class));
        $divide = collect($this->mailsTo($this->ahmad->user->email))->first(fn ($m) => str_contains($m->getOriginalMessage()->getSubject(), 'menunggu Anda bagi'));
        $this->assertNotNull($divide);
        $this->assertStringContainsString('Slicing halaman', $divide->getOriginalMessage()->getHtmlBody());
        $this->assertStringContainsString('Integrasi API', $divide->getOriginalMessage()->getHtmlBody());
    }

    public function test_email_deadline_diperpanjang_berisi_alasan_dan_tautan_tugas(): void
    {
        $taskId = $this->postTask($this->rudy->user, $this->wsGta, ['assignee_ids' => [$this->ahmad->id], 'due_at' => $this->due(1)])->json('data.0.id');

        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$taskId}/deadline", ['due_at' => $this->due(5), 'reason' => 'Menunggu data klien'])->assertOk();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, DeadlineExtendedNotification::class));
        $mail = collect($this->mailsTo($this->ahmad->user->email))->first(fn ($m) => str_contains($m->getOriginalMessage()->getSubject(), 'diperpanjang'));
        $this->assertNotNull($mail);
        $this->assertStringContainsString('Menunggu data klien', $mail->getOriginalMessage()->getHtmlBody());
        $this->assertStringContainsString("/workspace/tasks/{$taskId}", $mail->getOriginalMessage()->getHtmlBody());
    }

    public function test_email_dimasukkan_ke_kelompok(): void
    {
        $group = $this->makeGroup([$this->ahmad->id]);

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, GroupJoinedNotification::class));
        $mails = $this->mailsTo($this->ahmad->user->email);
        $this->assertCount(1, $mails);
        $this->assertStringContainsString('Tim Redesign', $mails[0]->getOriginalMessage()->getSubject());
        $this->assertStringContainsString("/workspace/{$group->id}", $mails[0]->getOriginalMessage()->getHtmlBody());
    }

    public function test_mematikan_email_tidak_mematikan_in_app_dan_sebaliknya(): void
    {
        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['group_joined' => ['email' => false]]])->assertOk();
        $this->makeGroup([$this->ahmad->id]);
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, GroupJoinedNotification::class));
        $this->assertCount(0, $this->mailsTo($this->ahmad->user->email), 'email dimatikan');

        $this->actingAs($this->siti->user)->putJson('/notifications/preferences', ['types' => ['group_joined' => ['in_app' => false]]])->assertOk();
        $this->makeGroup([$this->siti->id], $this->tasmil->user);
        $this->assertSame(0, $this->notificationCount($this->siti->user, GroupJoinedNotification::class), 'in-app dimatikan');
        $this->assertCount(1, $this->mailsTo($this->siti->user->email), 'email tetap terkirim');
    }

    public function test_pengguna_tanpa_email_tetap_mendapat_notifikasi_in_app(): void
    {
        DB::table('users')->where('id', $this->ahmad->user_id)->update(['email' => '']);
        $this->makeGroup([$this->ahmad->id]);

        $this->assertSame(1, $this->notificationCount($this->ahmad->user->fresh(), GroupJoinedNotification::class));
        $this->assertCount(0, $this->sentMails());
    }

    public function test_email_tidak_dikirim_untuk_jenis_in_app_saja(): void
    {
        $this->postTask($this->rudy->user, $this->wsGta, ['assignee_ids' => [$this->ahmad->id]])->assertCreated();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, TaskAssignedNotification::class));
        $this->assertCount(0, $this->mailsTo($this->ahmad->user->email));
    }
}
