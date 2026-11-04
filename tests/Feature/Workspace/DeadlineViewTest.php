<?php

namespace Tests\Feature\Workspace;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class DeadlineViewTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function task($workspace, string $title, string $due, array $attributes = [], array $assignees = []): Task
    {
        $id = $this->postTask($this->admin, $workspace, ['title' => $title, 'due_at' => $due, 'assignee_ids' => $assignees])
            ->assertCreated()->json('data.0.id');

        $task = Task::findOrFail($id);
        $task->forceFill($attributes)->save();

        return $task;
    }

    private function index(array $query = [], $as = null)
    {
        return $this->actingAs($as ?? $this->rudy->user)->get('/workspace?' . http_build_query($query));
    }

    public function test_workspace_deadline_berisi_tugas_terdekat_dan_hitungan(): void
    {
        $this->task($this->wsGta, 'Lewat bertanda', $this->due(-2), ['overdue_at' => now()]);
        $this->task($this->wsGta, 'Lewat belum bertanda', now()->subHour()->toDateTimeString());
        $soon = $this->task($this->wsGta, 'Segera', $this->due(2));
        $this->task($this->wsGta, 'Nanti', $this->due(9));
        // Review dan Selesai tidak dihitung
        $this->task($this->wsGta, 'Review', $this->due(1), ['status' => 'review']);
        $this->task($this->wsGta, 'Selesai', $this->due(-3), ['status' => 'done', 'completed_at' => now()]);

        $this->index()->assertOk()->assertViewHas('groups', function ($groups) use ($soon) {
            $dl = collect($groups->items())->firstWhere('name', $this->wsGta->name)['deadline'];

            return $dl['overdue_count'] === 2
                && $dl['due_soon_count'] === 1
                && $dl['next']['id'] === $soon->id
                && $dl['next']['code'] === $soon->code;
        });
    }

    public function test_workspace_tanpa_tugas_dan_workspace_selesai(): void
    {
        $this->wsDea->forceFill(['is_archived' => true])->save();
        $this->task($this->wsGta, 'Segera', $this->due(1));

        $this->index([], $this->admin)->assertOk()->assertViewHas('groups', function ($groups) {
            $items = collect($groups->items());

            return $items->firstWhere('name', $this->wsDea->name)['deadline'] === null
                && $items->firstWhere('name', $this->spSiti->name)['deadline'] === ['next' => null, 'overdue_count' => 0, 'due_soon_count' => 0];
        });
    }

    public function test_urutan_deadline_terdekat_dan_terbaru(): void
    {
        $this->task($this->wsGta, 'Dua hari', $this->due(2));              // GTA: terdekat 2 hari
        $this->task($this->spAhmad, 'Satu hari', $this->due(1), [], [$this->ahmad->id]); // Space Ahmad: 1 hari
        $this->task($this->spSiti, 'Lewat', $this->due(-1), ['overdue_at' => now()], [$this->siti->id]); // lewat deadline

        $names = fn ($response) => collect($response->viewData('groups')->items())->pluck('name')->all();

        // Admin melihat semuanya: yang lewat deadline dulu, lalu terdekat, lalu tanpa deadline.
        $deadline = $names($this->index(['sort' => 'deadline'], $this->admin));
        $this->assertSame(
            [$this->spSiti->name, $this->spAhmad->name, $this->wsGta->name],
            array_slice($deadline, 0, 3)
        );
        $this->assertSame($this->wsDea->name, end($deadline), 'workspace tanpa deadline paling akhir');

        // Default sama dengan sort=deadline
        $this->assertSame($deadline, $names($this->index([], $this->admin)));

        // Terbaru: id terbesar dulu
        $newest = $names($this->index(['sort' => 'newest'], $this->admin));
        $ids = collect($this->index(['sort' => 'newest'], $this->admin)->viewData('groups')->items())->pluck('id')->all();
        $this->assertSame($ids, collect($ids)->sortDesc()->values()->all());
        $this->assertCount(count($deadline), $newest);
    }

    public function test_filter_perlu_perhatian_dan_hitungannya(): void
    {
        $this->task($this->wsGta, 'Segera', $this->due(2));                 // jatuh tempo <= 3 hari
        $this->task($this->spAhmad, 'Jauh', $this->due(20), [], [$this->ahmad->id]);
        $this->task($this->spSiti, 'Lewat', $this->due(-1), ['overdue_at' => now()], [$this->siti->id]);

        $response = $this->index(['attention' => 1], $this->admin)->assertOk();

        $this->assertEqualsCanonicalizing(
            [$this->wsGta->name, $this->spSiti->name],
            collect($response->viewData('groups')->items())->pluck('name')->all()
        );
        $response->assertViewHas('attention', true)->assertViewHas('counts', fn ($c) => $c['attention'] === 2);

        // Hitungan "perlu perhatian" tidak ikut terfilter
        $this->index(['q' => 'tidak-ada'], $this->admin)->assertViewHas('counts', fn ($c) => $c['attention'] === 2);
    }

    public function test_mentor_hanya_melihat_deadline_workspace_miliknya(): void
    {
        $this->task($this->wsGta, 'Segera', $this->due(1));
        $this->task($this->spSiti, 'Lewat', $this->due(-1), ['overdue_at' => now()], [$this->siti->id]); // mentor Tasmil, bukan Rudy

        $this->index(['attention' => 1], $this->herman->user)->assertViewHas('counts', fn ($c) => $c['attention'] === 0);
        // Rudy memang anggota workspace tim GTA, tetapi bukan Space Siti.
        $names = collect($this->index(['attention' => 1])->viewData('groups')->items())->pluck('name')->all();
        $this->assertSame([$this->wsGta->name], $names);
    }

    public function test_tab_deadline_dibuka_semua_peran(): void
    {
        foreach ([$this->rudy->user, $this->admin] as $viewer) {
            $this->actingAs($viewer)->get("/workspace/{$this->wsGta->id}?tab=deadline")->assertOk()->assertViewHas('tab', 'deadline');
        }

        $this->actingAs($this->ahmad->user)->get("/workspace/{$this->wsGta->id}?tab=deadline")->assertOk()->assertViewHas('tab', 'deadline');
        // intern tidak bisa membuka workspace tim lain
        $this->actingAs($this->budi->user)->get("/workspace/{$this->wsGta->id}?tab=deadline")->assertForbidden();
    }

    public function test_widget_dashboard_mentor_deadline_7_hari(): void
    {
        $this->task($this->wsGta, 'Besok', $this->due(1), [], [$this->ahmad->id]);
        $this->task($this->wsGta, 'Belum dibagi', $this->due(5));
        $this->task($this->wsGta, 'Terlalu jauh', $this->due(8));
        $this->task($this->wsGta, 'Sudah lewat', $this->due(-1), ['overdue_at' => now()]);
        $this->task($this->wsGta, 'Dikirim', $this->due(2), ['status' => 'review']);
        // Tugas tim lain (dibuat langsung, Rudy tidak berhak membuatnya) tidak boleh muncul di widget Rudy.
        $other = $this->task($this->wsGta, 'Tim lain', $this->due(2));
        $other->forceFill(['workspace_id' => $this->wsDea->id])->save();

        $this->actingAs($this->rudy->user)->getJson('/workspace/summary')
            ->assertOk()
            ->assertJsonPath('upcoming_deadlines.total', 2)
            ->assertJsonPath('upcoming_deadlines.data.0.title', 'Besok')
            ->assertJsonPath('upcoming_deadlines.data.1.title', 'Belum dibagi')
            ->assertJsonPath('upcoming_deadlines.data.1.assignees', [])
            ->assertJsonPath('upcoming_deadlines.data.0.workspace.id', $this->wsGta->id);

        $this->actingAs($this->admin)->getJson('/workspace/summary')->assertJsonPath('upcoming_deadlines.total', 3);
    }

    public function test_widget_dibatasi_enam_tugas_tetapi_total_lengkap(): void
    {
        foreach (range(1, 8) as $i) {
            $this->task($this->wsGta, "T{$i}", now()->addHours($i + 1)->toDateTimeString());
        }

        $this->actingAs($this->rudy->user)->getJson('/workspace/summary')
            ->assertJsonCount(6, 'upcoming_deadlines.data')
            ->assertJsonPath('upcoming_deadlines.total', 8)
            ->assertJsonPath('upcoming_deadlines.data.0.title', 'T1');
    }
}
