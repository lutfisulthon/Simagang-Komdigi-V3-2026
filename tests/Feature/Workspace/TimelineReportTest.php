<?php

namespace Tests\Feature\Workspace;

use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class TimelineReportTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function task($workspace, string $title, array $attributes = [], array $assignees = []): Task
    {
        $id = $this->postTask($this->admin, $workspace, ['title' => $title, 'assignee_ids' => $assignees])->assertCreated()->json('data.0.id');
        $task = Task::findOrFail($id);
        $task->forceFill($attributes)->save();

        return $task;
    }

    public function test_start_at_terisi_saat_tugas_mulai_dikerjakan_dan_tidak_menimpa_yang_sudah_ada(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->assertNull(Task::find($id)->start_at);

        $this->setStatus($this->ahmad->user, $id, 'in_progress')->assertOk()->assertJsonPath('data.start_at', fn ($v) => $v !== null);
        $started = Task::find($id)->start_at;

        // Kembali ke To Do lalu dikerjakan lagi: tanggal mulai pertama dipertahankan.
        $this->setStatus($this->ahmad->user, $id, 'todo')->assertOk();
        $this->setStatus($this->ahmad->user, $id, 'in_progress')->assertOk();
        $this->assertTrue($started->equalTo(Task::find($id)->start_at));

        // Tanggal mulai yang ditentukan mentor tidak ditimpa.
        $planned = $this->postTask($this->rudy->user, $this->spAhmad, [
            'assignee_ids' => [$this->ahmad->id],
            'start_at' => now()->addDays(2)->toDateTimeString(),
            'due_at' => now()->addDays(6)->toDateTimeString(),
        ])->json('data.0.id');
        $before = Task::find($planned)->start_at;
        $this->setStatus($this->ahmad->user, $planned, 'in_progress')->assertOk();
        $this->assertTrue($before->equalTo(Task::find($planned)->start_at));
    }

    public function test_tab_timeline_dan_laporan_terbuka_untuk_semua_peran(): void
    {
        foreach (['timeline', 'laporan'] as $tab) {
            foreach ([$this->rudy->user, $this->admin, $this->ahmad->user] as $viewer) {
                $this->actingAs($viewer)->get("/workspace/{$this->wsGta->id}?tab={$tab}")->assertOk()->assertViewHas('tab', $tab);
            }
        }
    }

    public function test_tugas_selesai_di_sprint_berjalan_dan_sprint_terakhir_ikut_dimuat(): void
    {
        $old = Sprint::create(['workspace_id' => $this->wsGta->id, 'name' => 'Sprint 1', 'status' => 'completed', 'completed_at' => now()->subDays(20)]);
        $last = Sprint::create(['workspace_id' => $this->wsGta->id, 'name' => 'Sprint 2', 'status' => 'completed', 'completed_at' => now()->subDays(5)]);
        $active = Sprint::create(['workspace_id' => $this->wsGta->id, 'name' => 'Sprint 3', 'status' => 'active', 'start_date' => now()->toDateString(), 'end_date' => now()->addDays(7)->toDateString()]);

        $done = fn (string $title, ?Sprint $sprint, int $daysAgo) => $this->task($this->wsGta, $title, [
            'status' => 'done', 'completed_at' => now()->subDays($daysAgo), 'sprint_id' => $sprint?->id,
        ]);

        // 10 tugas Selesai tanpa sprint lebih baru dari yang di sprint lama
        foreach (range(1, 10) as $i) {
            $done("Bebas {$i}", null, 1);
        }
        $done('Di sprint aktif', $active, 30);
        $done('Di sprint terakhir', $last, 31);
        $done('Di sprint lama', $old, 40);

        $titles = collect($this->actingAs($this->rudy->user)->get("/workspace/{$this->wsGta->id}")->viewData('initialTasks'))->pluck('title');

        $this->assertTrue($titles->contains('Di sprint aktif'));
        $this->assertTrue($titles->contains('Di sprint terakhir'));
        $this->assertFalse($titles->contains('Di sprint lama'), 'sprint selesai yang lebih lama tidak dimuat');
        $this->assertSame(8, $titles->filter(fn ($t) => str_starts_with($t, 'Bebas'))->count(), 'tugas Selesai lepas tetap dibatasi 8 terbaru');
    }

    public function test_widget_progres_kelompok_urut_terlambat_lalu_persentase(): void
    {
        // GTA: 4 tugas, 1 selesai, 1 terlambat. Space Ahmad: 2 tugas, 0 selesai, 0 terlambat.
        $this->task($this->wsGta, 'A', ['status' => 'done', 'completed_at' => now()]);
        $this->task($this->wsGta, 'B', ['overdue_at' => now()]);
        $this->task($this->wsGta, 'C');
        $this->task($this->wsGta, 'D');
        $this->task($this->spAhmad, 'E', [], [$this->ahmad->id]);
        $this->task($this->spAhmad, 'F', [], [$this->ahmad->id]);

        $res = $this->actingAs($this->rudy->user)->getJson('/workspace/summary')->assertOk();

        $res->assertJsonCount(2, 'group_progress')
            ->assertJsonPath('group_progress.0.workspace.id', $this->wsGta->id)
            ->assertJsonPath('group_progress.0.total', 4)
            ->assertJsonPath('group_progress.0.done', 1)
            ->assertJsonPath('group_progress.0.overdue', 1)
            ->assertJsonPath('group_progress.1.workspace.id', $this->spAhmad->id)
            ->assertJsonPath('group_progress.1.overdue', 0);

        // Mentor lain tidak melihat workspace ini.
        $this->actingAs($this->herman->user)->getJson('/workspace/summary')->assertJsonCount(0, 'group_progress');
    }

    public function test_progres_kelompok_hanya_kelompok_aktif_dan_maks_enam(): void
    {
        $this->task($this->wsGta, 'A');
        $this->wsGta->forceFill(['is_archived' => true])->save();

        $this->actingAs($this->admin)->getJson('/workspace/summary')->assertJsonCount(0, 'group_progress');
    }

    public function test_widget_beban_kerja_per_intern(): void
    {
        $this->task($this->wsGta, 'A', [], [$this->ahmad->id, $this->siti->id]);
        $this->task($this->wsGta, 'B', ['status' => 'in_progress', 'overdue_at' => now()], [$this->ahmad->id]);
        $this->task($this->wsGta, 'C', ['status' => 'review'], [$this->ahmad->id]);
        $this->task($this->wsGta, 'D', ['status' => 'done', 'completed_at' => now()], [$this->siti->id]);
        $this->task($this->wsGta, 'E'); // belum dibagi: tidak masuk beban siapa pun

        $res = $this->actingAs($this->rudy->user)->getJson('/workspace/summary')->assertOk();

        $res->assertJsonCount(2, 'workload')
            ->assertJsonPath('workload.0.intern.id', $this->ahmad->id)
            ->assertJsonPath('workload.0.counts', ['todo' => 1, 'in_progress' => 1, 'review' => 1, 'revision' => 0])
            ->assertJsonPath('workload.0.overdue', 1)
            ->assertJsonPath('workload.0.workspace.id', $this->wsGta->id)
            ->assertJsonPath('workload.1.intern.id', $this->siti->id)
            ->assertJsonPath('workload.1.counts.todo', 1);

        $this->actingAs($this->herman->user)->getJson('/workspace/summary')->assertJsonCount(0, 'workload');
    }

    public function test_beban_kerja_intern_di_dua_workspace_muncul_sekali_di_yang_tugasnya_terbanyak(): void
    {
        $this->task($this->wsGta, 'A', [], [$this->ahmad->id]);
        $this->task($this->spAhmad, 'B', [], [$this->ahmad->id]);
        $this->task($this->spAhmad, 'C', [], [$this->ahmad->id]);

        $res = $this->actingAs($this->rudy->user)->getJson('/workspace/summary')->assertOk();

        $res->assertJsonCount(1, 'workload')
            ->assertJsonPath('workload.0.workspace.id', $this->spAhmad->id)
            ->assertJsonPath('workload.0.counts.todo', 2);
    }
}
