<?php

namespace Tests\Feature\Workspace;

use App\Models\Sprint;
use App\Models\Task;
use App\Notifications\DeadlineExtendedNotification;
use App\Notifications\SprintLeaderNotification;
use App\Notifications\SprintToDivideNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class SprintTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function mentor()
    {
        return $this->rudy->user;
    }

    private function endDate(int $days = 14): string
    {
        return now()->addDays($days)->toDateString();
    }

    private function makeSprint(array $payload = [])
    {
        return $this->actingAs($this->mentor())
            ->postJson("/workspace/{$this->wsGta->id}/sprints", $payload + ['end_date' => $this->endDate()]);
    }

    private function startSprint(int $id, array $payload = [])
    {
        return $this->actingAs($this->mentor())->postJson("/workspace/sprints/{$id}/start", $payload + [
            'name' => 'Sprint Uji',
            'start_date' => now()->toDateString(),
            'end_date' => $this->endDate(),
            'leader_id' => $this->ahmad->id,
        ]);
    }

    public function test_mentor_membuat_sprint_dengan_tugas_cepat_tanpa_assignee(): void
    {
        $res = $this->makeSprint([
            'goal' => 'Prototipe',
            'leader_id' => $this->ahmad->id,
            'tasks' => [['title' => 'Rancang layar', 'priority' => 'high'], ['title' => 'Slicing']],
        ])->assertCreated();

        $res->assertJsonPath('data.name', 'Sprint 1')
            ->assertJsonPath('data.status', 'planned')
            ->assertJsonPath('data.leader.id', $this->ahmad->id)
            ->assertJsonPath('data.end_date', $this->endDate())
            ->assertJsonCount(2, 'tasks')
            ->assertJsonPath('tasks.0.sprint_id', $res->json('data.id'))
            ->assertJsonPath('tasks.0.assignees', [])
            ->assertJsonPath('tasks.0.priority', 'high');

        $this->assertStringStartsWith($this->endDate() . ' 23:59', Task::first()->due_at->toDateTimeString());
        $this->makeSprint()->assertJsonPath('data.name', 'Sprint 2');
    }

    public function test_validasi_pembuatan_sprint(): void
    {
        $this->makeSprint(['end_date' => null])->assertUnprocessable()->assertJsonValidationErrors('end_date');
        $this->makeSprint(['end_date' => now()->subDay()->toDateString()])->assertUnprocessable();
        $this->makeSprint(['tasks' => [['title' => '']]])->assertUnprocessable()->assertJsonValidationErrors('tasks.0.title');
        // Siti anggota tim GTA juga, tetapi Budi dari tim lain tidak boleh jadi ketua.
        $this->makeSprint(['leader_id' => $this->budi->id])->assertUnprocessable()->assertJsonValidationErrors('leader_id');
        $this->makeSprint(['leader_id' => $this->siti->id])->assertCreated();
    }

    public function test_intern_dan_space_pribadi_tidak_bisa_mengatur_sprint(): void
    {
        $this->actingAs($this->ahmad->user)->postJson("/workspace/{$this->wsGta->id}/sprints", ['end_date' => $this->endDate()])->assertForbidden();
        $this->actingAs($this->herman->user)->postJson("/workspace/{$this->wsGta->id}/sprints", ['end_date' => $this->endDate()])->assertForbidden();
        $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->spAhmad->id}/sprints", ['end_date' => $this->endDate()])->assertForbidden();

        $id = $this->makeSprint()->json('data.id');
        $this->actingAs($this->ahmad->user)->postJson("/workspace/sprints/{$id}/start", [])->assertForbidden();
        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/sprints/{$id}")->assertForbidden();
    }

    public function test_sprint_tidak_bisa_dimulai_tanpa_ketua_dan_hanya_satu_aktif(): void
    {
        $a = $this->makeSprint()->json('data.id');
        $b = $this->makeSprint()->json('data.id');

        $this->startSprint($a, ['leader_id' => null])->assertUnprocessable()->assertJsonValidationErrors('leader_id');
        $this->startSprint($a, ['end_date' => now()->subDay()->toDateString()])->assertUnprocessable()->assertJsonValidationErrors('end_date');

        $this->startSprint($a)->assertOk()->assertJsonPath('data.status', 'active')->assertJsonPath('data.leader.id', $this->ahmad->id);
        $this->startSprint($b)->assertUnprocessable()->assertJsonValidationErrors('sprint');
        $this->startSprint($a)->assertUnprocessable()->assertJsonValidationErrors('sprint');
    }

    public function test_sprint_aktif_dibawa_di_workspace_dan_halaman(): void
    {
        $id = $this->makeSprint()->json('data.id');
        $this->startSprint($id)->assertOk();

        $this->actingAs($this->mentor())->get("/workspace/{$this->wsGta->id}")
            ->assertOk()
            ->assertViewHas('workspace', fn ($w) => $w['active_sprint']['id'] === $id && $w['active_sprint']['leader']['id'] === $this->ahmad->id)
            ->assertViewHas('sprints', fn ($s) => count($s) === 1 && $s[0]['status'] === 'active')
            ->assertViewHas('can', fn ($c) => $c['manage_sprints'] === true);

        $this->actingAs($this->ahmad->user)->get("/workspace/{$this->wsGta->id}?tab=backlog")
            ->assertOk()->assertViewHas('tab', 'board')->assertViewHas('can', fn ($c) => $c['manage_sprints'] === false);

        $this->actingAs($this->mentor())->get("/workspace/{$this->wsGta->id}?tab=backlog")->assertViewHas('tab', 'backlog');
    }

    public function test_mulai_sprint_mengingatkan_ketua_membagi_tugas(): void
    {
        $id = $this->makeSprint(['tasks' => [['title' => 'A'], ['title' => 'B']]])->json('data.id');
        $this->startSprint($id)->assertOk();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, SprintToDivideNotification::class));
    }

    public function test_menunjuk_ketua_mengirim_notifikasi_sekali_per_penggantian(): void
    {
        $id = $this->makeSprint(['leader_id' => $this->ahmad->id])->json('data.id');
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, SprintLeaderNotification::class));

        $this->actingAs($this->mentor())->patchJson("/workspace/sprints/{$id}", ['name' => 'Ganti nama'])->assertOk();
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, SprintLeaderNotification::class));

        $this->actingAs($this->mentor())->patchJson("/workspace/sprints/{$id}", ['leader_id' => $this->siti->id])
            ->assertOk()->assertJsonPath('data.leader.id', $this->siti->id);
        $this->assertSame(1, $this->notificationCount($this->siti->user, SprintLeaderNotification::class));
    }

    public function test_ubah_sprint_aturan_tanggal_dan_ketua(): void
    {
        $id = $this->makeSprint(['leader_id' => $this->ahmad->id])->json('data.id');
        $mentor = $this->mentor();

        $this->actingAs($mentor)->patchJson("/workspace/sprints/{$id}", ['start_date' => $this->endDate(10), 'end_date' => $this->endDate(5)])
            ->assertUnprocessable()->assertJsonValidationErrors('end_date');
        // planned boleh tanpa ketua
        $this->actingAs($mentor)->patchJson("/workspace/sprints/{$id}", ['leader_id' => null])->assertOk()->assertJsonPath('data.leader', null);

        $this->startSprint($id)->assertOk();
        $this->actingAs($mentor)->patchJson("/workspace/sprints/{$id}", ['leader_id' => null])->assertUnprocessable();
        $this->actingAs($mentor)->patchJson("/workspace/sprints/{$id}", ['end_date' => null])->assertUnprocessable();
    }

    public function test_selesaikan_sprint_memindahkan_tugas_belum_selesai(): void
    {
        $id = $this->makeSprint(['tasks' => [['title' => 'A'], ['title' => 'B'], ['title' => 'C']]])->json('data.id');
        $this->startSprint($id)->assertOk();
        [$a, $b, $c] = Task::orderBy('id')->pluck('id')->all();
        Task::whereKey($c)->update(['status' => 'done', 'completed_at' => now()]);

        $res = $this->actingAs($this->mentor())->postJson("/workspace/sprints/{$id}/complete", ['move_to' => 'new'])->assertOk();

        $new = $res->json('created_sprint.id');
        $res->assertJsonPath('data.status', 'completed')->assertJsonPath('moved.sprint_id', $new)->assertJsonPath('moved.task_ids', [$a, $b]);
        $this->assertSame($new, Task::find($a)->sprint_id);
        $this->assertSame($id, Task::find($c)->sprint_id);
        $this->assertSame('planned', Sprint::find($new)->status);

        // sprint yang sudah selesai tidak bisa diubah, dimulai ulang, atau dihapus
        $this->actingAs($this->mentor())->patchJson("/workspace/sprints/{$id}", ['name' => 'x'])->assertForbidden();
        $this->actingAs($this->mentor())->deleteJson("/workspace/sprints/{$id}")->assertForbidden();
        $this->actingAs($this->mentor())->postJson("/workspace/sprints/{$id}/complete", ['move_to' => 'backlog'])->assertUnprocessable();
    }

    public function test_selesaikan_sprint_ke_backlog_dan_validasi_tujuan(): void
    {
        $planned = $this->makeSprint()->json('data.id');
        $id = $this->makeSprint(['tasks' => [['title' => 'A']]])->json('data.id');
        $this->startSprint($id)->assertOk();
        $mentor = $this->mentor();

        $this->actingAs($mentor)->postJson("/workspace/sprints/{$id}/complete", ['move_to' => 'ngawur'])->assertUnprocessable();
        $this->actingAs($mentor)->postJson("/workspace/sprints/{$id}/complete", ['move_to' => $id])->assertUnprocessable();
        $this->actingAs($mentor)->postJson("/workspace/sprints/{$id}/complete", ['move_to' => 'backlog'])
            ->assertOk()->assertJsonPath('moved.sprint_id', null);

        $this->assertNull(Task::first()->sprint_id);
        // sprint planned lain tidak ikut berubah
        $this->assertSame('planned', Sprint::find($planned)->status);
    }

    public function test_hapus_sprint_planned_mengembalikan_tugas_ke_backlog(): void
    {
        $id = $this->makeSprint(['tasks' => [['title' => 'A']]])->json('data.id');

        $this->actingAs($this->mentor())->deleteJson("/workspace/sprints/{$id}")->assertNoContent();

        $this->assertNull(Task::first()->sprint_id);
        $this->assertDatabaseMissing('sprints', ['id' => $id]);

        $active = $this->makeSprint()->json('data.id');
        $this->startSprint($active)->assertOk();
        $this->actingAs($this->mentor())->deleteJson("/workspace/sprints/{$active}")->assertForbidden();
    }

    public function test_pindah_tugas_antar_sprint_dan_urutan_rank(): void
    {
        $id = $this->makeSprint(['tasks' => [['title' => 'A'], ['title' => 'B']]])->json('data.id');
        $other = $this->makeSprint()->json('data.id');
        [$a, $b] = Task::orderBy('id')->pluck('id')->all();
        $mentor = $this->mentor();

        // susun ulang di sprint yang sama
        $this->actingAs($mentor)->patchJson("/workspace/tasks/{$a}/sprint", ['sprint_id' => $id, 'rank_order' => [$b, $a]])->assertOk();
        $this->assertSame([1, 2], [Task::find($b)->rank, Task::find($a)->rank]);

        // pindah ke sprint lain, lalu ke backlog
        $this->actingAs($mentor)->patchJson("/workspace/tasks/{$a}/sprint", ['sprint_id' => $other, 'rank_order' => [$a]])
            ->assertOk()->assertJsonPath('data.sprint_id', $other)->assertJsonPath('data.rank', 1);
        $this->actingAs($mentor)->patchJson("/workspace/tasks/{$a}/sprint", ['sprint_id' => null, 'rank_order' => [$a]])
            ->assertOk()->assertJsonPath('data.sprint_id', null);

        // sprint selesai dan sprint milik workspace lain ditolak
        $done = $this->makeSprint()->json('data.id');
        Sprint::whereKey($done)->update(['status' => 'completed']);
        $this->actingAs($mentor)->patchJson("/workspace/tasks/{$a}/sprint", ['sprint_id' => $done, 'rank_order' => [$a]])->assertUnprocessable();
        $foreign = Sprint::create(['workspace_id' => $this->wsDea->id, 'name' => 'Lain', 'status' => 'planned']);
        $this->actingAs($mentor)->patchJson("/workspace/tasks/{$a}/sprint", ['sprint_id' => $foreign->id, 'rank_order' => [$a]])->assertUnprocessable();

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/tasks/{$a}/sprint", ['sprint_id' => $id, 'rank_order' => [$a]])->assertForbidden();
    }

    public function test_tugas_baru_boleh_tanpa_assignee_di_tim_dan_masuk_sprint(): void
    {
        $sprint = $this->makeSprint()->json('data.id');

        $this->postTask($this->mentor(), $this->wsGta, ['sprint_id' => $sprint])
            ->assertCreated()->assertJsonPath('data.0.sprint_id', $sprint)->assertJsonPath('data.0.assignees', []);
        $this->postTask($this->mentor(), $this->wsGta, ['sprint_id' => 999999])->assertUnprocessable();
        // Space pribadi tetap wajib punya assignee
        $this->postTask($this->mentor(), $this->spAhmad, ['assignee_ids' => []])->assertUnprocessable();
    }

    public function test_ketua_sprint_membagi_tugas_dan_dicatat_di_aktivitas(): void
    {
        $id = $this->makeSprint(['leader_id' => $this->ahmad->id, 'tasks' => [['title' => 'A']]])->json('data.id');
        $this->startSprint($id)->assertOk();
        $task = Task::first();

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/tasks/{$task->id}/assignees", ['assignee_ids' => [$this->ahmad->id, $this->siti->id]])
            ->assertOk()->assertJsonCount(2, 'data.assignees');

        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'action' => 'assigned']);
        $this->assertSame(1, $this->notificationCount($this->siti->user, \App\Notifications\TaskAssignedNotification::class));

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/tasks/{$task->id}/assignees", ['assignee_ids' => [$this->ahmad->id]])->assertOk();
        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'action' => 'unassigned']);

        // intern yang bukan ketua, atau mentor/admin boleh? Hanya ketua dan staf.
        $this->actingAs($this->siti->user)->patchJson("/workspace/tasks/{$task->id}/assignees", ['assignee_ids' => [$this->siti->id]])->assertForbidden();
        $this->actingAs($this->mentor())->patchJson("/workspace/tasks/{$task->id}/assignees", ['assignee_ids' => []])->assertOk()->assertJsonCount(0, 'data.assignees');
        // intern dari tim lain tidak boleh dibagi tugas
        $this->actingAs($this->ahmad->user)->patchJson("/workspace/tasks/{$task->id}/assignees", ['assignee_ids' => [$this->budi->id]])->assertUnprocessable();
    }

    public function test_ketua_kehilangan_hak_membagi_setelah_sprint_selesai(): void
    {
        $id = $this->makeSprint(['leader_id' => $this->ahmad->id, 'tasks' => [['title' => 'A']]])->json('data.id');
        $this->startSprint($id)->assertOk();
        $task = Task::first();
        Task::whereKey($task->id)->update(['status' => 'done', 'completed_at' => now()]);
        $this->actingAs($this->mentor())->postJson("/workspace/sprints/{$id}/complete", ['move_to' => 'backlog'])->assertOk();

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/tasks/{$task->id}/assignees", ['assignee_ids' => [$this->ahmad->id]])->assertForbidden();
    }

    public function test_can_assign_dan_extend_deadline_pada_task_card(): void
    {
        $id = $this->makeSprint(['leader_id' => $this->ahmad->id, 'tasks' => [['title' => 'A']]])->json('data.id');
        $this->startSprint($id)->assertOk();

        $this->actingAs($this->mentor())->getJson("/workspace/{$this->wsGta->id}/tasks")
            ->assertJsonPath('data.0.can.assign', true)->assertJsonPath('data.0.can.extend_deadline', true);
        $this->actingAs($this->ahmad->user)->getJson("/workspace/{$this->wsGta->id}/tasks")
            ->assertJsonPath('data.0.can.assign', true)->assertJsonPath('data.0.can.extend_deadline', false);
        $this->actingAs($this->siti->user)->getJson("/workspace/{$this->wsGta->id}/tasks")
            ->assertJsonPath('data.0.can.assign', false);
    }

    public function test_perpanjang_deadline_mereset_terlambat_dan_memberi_tahu_assignee(): void
    {
        $taskId = $this->postTask($this->mentor(), $this->wsGta, ['assignee_ids' => [$this->ahmad->id], 'due_at' => $this->due(1)])
            ->assertCreated()->json('data.0.id');
        Task::whereKey($taskId)->update(['overdue_at' => now(), 'reminded_at' => now()]);
        $new = now()->addDays(5)->toIso8601String();

        $this->actingAs($this->mentor())->patchJson("/workspace/tasks/{$taskId}/deadline", ['due_at' => $new, 'reason' => 'Menunggu data'])
            ->assertOk()->assertJsonPath('data.is_late', false);

        $task = Task::find($taskId);
        $this->assertNull($task->overdue_at);
        $this->assertNull($task->reminded_at);
        $activity = $task->activities()->where('action', 'deadline_extended')->first();
        $this->assertSame('Menunggu data', $activity->meta['reason']);
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, DeadlineExtendedNotification::class));
    }

    public function test_perpanjang_deadline_validasi_dan_izin(): void
    {
        $taskId = $this->postTask($this->mentor(), $this->wsGta, ['assignee_ids' => [$this->ahmad->id], 'due_at' => $this->due(3)])->json('data.0.id');

        $this->actingAs($this->mentor())->patchJson("/workspace/tasks/{$taskId}/deadline", ['due_at' => $this->due(2)])->assertUnprocessable();
        $this->actingAs($this->mentor())->patchJson("/workspace/tasks/{$taskId}/deadline", ['due_at' => null])->assertUnprocessable();
        $this->actingAs($this->mentor())->patchJson("/workspace/tasks/{$taskId}/deadline", ['due_at' => $this->due(6), 'reason' => str_repeat('x', 201)])->assertUnprocessable();
        $this->actingAs($this->ahmad->user)->patchJson("/workspace/tasks/{$taskId}/deadline", ['due_at' => $this->due(6)])->assertForbidden();

        Task::whereKey($taskId)->update(['status' => 'done', 'completed_at' => now()]);
        $this->actingAs($this->mentor())->patchJson("/workspace/tasks/{$taskId}/deadline", ['due_at' => $this->due(6)])->assertForbidden();
    }

    public function test_scheduler_mengingatkan_ketua_setiap_24_jam_selama_masih_ada_tugas_belum_dibagi(): void
    {
        $id = $this->makeSprint(['tasks' => [['title' => 'A']]])->json('data.id');
        $this->startSprint($id)->assertOk();
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, SprintToDivideNotification::class));

        // Baru diingatkan: belum waktunya lagi.
        $this->artisan('workspace:check-deadlines')->assertSuccessful();
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, SprintToDivideNotification::class));

        Sprint::whereKey($id)->update(['divide_reminded_at' => now()->subHours(25)]);
        $this->artisan('workspace:check-deadlines')->assertSuccessful();
        $this->assertSame(2, $this->notificationCount($this->ahmad->user, SprintToDivideNotification::class));

        // Semua tugas sudah dibagi: berhenti.
        Task::first()->assignees()->sync([$this->ahmad->id]);
        Sprint::whereKey($id)->update(['divide_reminded_at' => now()->subHours(25)]);
        $this->artisan('workspace:check-deadlines')->assertSuccessful();
        $this->assertSame(2, $this->notificationCount($this->ahmad->user, SprintToDivideNotification::class));
    }
}
