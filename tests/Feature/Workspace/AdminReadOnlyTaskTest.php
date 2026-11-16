<?php

namespace Tests\Feature\Workspace;

use App\Models\Task;
use App\Models\TaskChecklistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/**
 * Admin hanya memantau tugas: boleh melihat dan berkomentar, tidak boleh mengubah apa pun pada tugas atau sprint.
 * (Keputusan produk; mengubah tabel hak akses di PRD yang semula memberi admin ✓.)
 */
class AdminReadOnlyTaskTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    /** @return array<string, \App\Models\User> */
    private function admins(): array
    {
        return ['admin_full' => $this->admin, 'super_admin' => $this->makeAdmin('Super Uji', 'super_admin')];
    }

    public function test_admin_tidak_bisa_mengubah_tugas_dalam_bentuk_apa_pun(): void
    {
        // Tugas di workspace tim, sudah di Review supaya jalur review bisa diuji juga.
        $created = $this->postTask($this->rudy->user, $this->wsGta, [
            'assignee_ids' => [$this->ahmad->id],
            'checklist' => ['langkah satu'],
        ])->assertCreated();
        $id = $created->json('data.0.id');
        $item = TaskChecklistItem::where('task_id', $id)->value('id');
        $this->assertNotNull($item);
        $this->setStatus($this->ahmad->user, $id, 'review')->assertOk();

        foreach ($this->admins() as $label => $admin) {
            $this->postTask($admin, $this->wsGta, ['assignee_ids' => [$this->ahmad->id]])->assertForbidden("{$label}: buat tugas");
            $this->actingAs($admin)->putJson("/workspace/tasks/{$id}", ['title' => 'Diubah admin'])->assertForbidden("{$label}: edit");
            $this->setStatus($admin, $id, 'in_progress')->assertForbidden("{$label}: pindah status");
            $this->actingAs($admin)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'approve'])->assertForbidden("{$label}: setujui");
            $this->actingAs($admin)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'revision', 'note' => 'x'])->assertForbidden("{$label}: revisi");
            $this->actingAs($admin)->patchJson("/workspace/tasks/{$id}/assignees", ['assignee_ids' => [$this->siti->id]])->assertForbidden("{$label}: bagi tugas");
            $this->actingAs($admin)->patchJson("/workspace/tasks/{$id}/deadline", ['due_at' => $this->due(30)])->assertForbidden("{$label}: perpanjang deadline");
            $this->actingAs($admin)->patchJson("/workspace/tasks/{$id}/checklist/{$item}", ['is_done' => true])->assertForbidden("{$label}: centang checklist");
            $this->actingAs($admin)->deleteJson("/workspace/tasks/{$id}")->assertForbidden("{$label}: hapus");
            $this->actingAs($admin)->postJson("/workspace/{$this->wsGta->id}/sprints", ['name' => 'Sprint admin', 'end_date' => now()->addDays(7)->toDateString()])
                ->assertForbidden("{$label}: buat sprint");
        }

        // Tidak ada yang berubah.
        $task = Task::findOrFail($id);
        $this->assertSame('review', $task->status->value);
        $this->assertSame('Tugas uji', $task->title);
        $this->assertNull($task->deleted_at);
        $this->assertSame(1, Task::where('workspace_id', $this->wsGta->id)->count());
    }

    public function test_admin_tetap_bisa_melihat_dan_berkomentar(): void
    {
        $id = $this->makeTaskForAhmad();

        foreach ($this->admins() as $label => $admin) {
            $this->actingAs($admin)->getJson("/workspace/{$this->spAhmad->id}/tasks")->assertOk();
            $this->actingAs($admin)->getJson("/workspace/tasks/{$id}")->assertOk();
            $this->actingAs($admin)->postJson("/workspace/tasks/{$id}/comments", ['body' => "Pantauan {$label}"])->assertCreated();
        }
    }

    public function test_flag_can_untuk_admin_semua_false_agar_tombol_tidak_tampil(): void
    {
        $this->makeTaskForAhmad();

        $card = $this->actingAs($this->admin)->getJson("/workspace/{$this->spAhmad->id}/tasks")->assertOk()->json('data.0');

        foreach (['update', 'move', 'review', 'delete', 'assign', 'extend_deadline'] as $ability) {
            $this->assertFalse($card['can'][$ability], "can.{$ability} harus false untuk admin");
        }

        // Mentor pemilik tetap punya hak penuh (kontrol).
        $mentorCard = $this->actingAs($this->rudy->user)->getJson("/workspace/{$this->spAhmad->id}/tasks")->json('data.0');
        $this->assertTrue($mentorCard['can']['update']);
        $this->assertTrue($mentorCard['can']['move']);
        $this->assertTrue($mentorCard['can']['review']);
    }

    public function test_halaman_workspace_admin_tidak_menawarkan_buat_tugas_dan_sprint(): void
    {
        $page = $this->actingAs($this->admin)->get("/workspace/{$this->wsGta->id}")->assertOk();

        // Konfigurasi halaman disisipkan sebagai JSON.parse('...') dengan tanda kutip ter-escape (").
        // Flag halaman memicu blok Blade: tombol "Buat tugas" (dispatch ws-open-task-form) dan dialog sprint.
        $page->assertDontSee("\$dispatch('ws-open-task-form'", false);
        $page->assertDontSee('wsSprintDialogs(', false);

        // Kontrol: mentor pemilik mendapat tombol dan dialognya.
        $mentorPage = $this->actingAs($this->rudy->user)->get("/workspace/{$this->wsGta->id}")->assertOk();
        $mentorPage->assertSee("\$dispatch('ws-open-task-form'", false);
        $mentorPage->assertSee('wsSprintDialogs(', false);
    }
}
