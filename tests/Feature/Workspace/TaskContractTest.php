<?php

namespace Tests\Feature\Workspace;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Bentuk request/response modul tugas sesuai kontrak frontend. */
class TaskContractTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        Storage::fake('local');
    }

    private function upload($user, $workspace, string $name = 'laporan.pdf')
    {
        return $this->actingAs($user)->post('/workspace/attachments', [
            'file' => UploadedFile::fake()->create($name, 5, 'application/pdf'),
            'workspace_id' => $workspace->id,
        ], ['Accept' => 'application/json']);
    }

    private function makeLabel(string $name = 'frontend', string $color = '#6366f1'): int
    {
        return $this->actingAs($this->rudy->user)
            ->postJson("/workspace/teams/{$this->gta->id}/labels", ['name' => $name, 'color' => $color])
            ->assertCreated()
            ->json('data.id');
    }

    public function test_buat_tugas_dengan_payload_frontend_mengembalikan_task_card(): void
    {
        $label = $this->makeLabel();
        $attachment = $this->upload($this->rudy->user, $this->spAhmad)->assertCreated()->json('data.id');

        $response = $this->postTask($this->rudy->user, $this->spAhmad, [
            'assignee_ids' => [$this->ahmad->id],
            'label_ids' => [$label],
            'checklist_items' => [['content' => 'Buat endpoint', 'is_done' => true], ['content' => 'Tulis test']],
            'result_links' => [['url' => 'https://github.com/x/y', 'label' => 'Repo']],
            'attachment_ids' => [$attachment],
        ])->assertCreated();

        $response->assertJsonPath('data.0.code', 'T-1')
            ->assertJsonPath('data.0.labels.0.id', $label)
            ->assertJsonPath('data.0.checklist', ['done' => 1, 'total' => 2])
            ->assertJsonPath('data.0.attachments_count', 1)
            ->assertJsonPath('data.0.is_late', false)
            ->assertJsonPath('data.0.can.review', true);

        $task = Task::first();
        $this->assertSame([['url' => 'https://github.com/x/y', 'label' => 'Repo']], $task->result_links);
        $this->assertSame($task->id, Attachment::find($attachment)->attachable_id);
    }

    public function test_banyak_assignee_satu_tugas_dan_separate_copies_membuat_salinan(): void
    {
        $payload = ['assignee_ids' => [$this->ahmad->id, $this->siti->id]];

        $this->postTask($this->rudy->user, $this->wsGta, $payload)->assertCreated()->assertJsonCount(1, 'data');
        $this->postTask($this->rudy->user, $this->wsGta, $payload + ['separate_copies' => true])->assertCreated()->assertJsonCount(2, 'data');
    }

    public function test_label_id_dari_tim_lain_diabaikan(): void
    {
        $other = $this->actingAs($this->herman->user)
            ->postJson("/workspace/teams/{$this->dea->id}/labels", ['name' => 'dea', 'color' => '#111111'])
            ->assertCreated()->json('data.id');

        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id], 'label_ids' => [$other]])
            ->assertCreated()
            ->assertJsonCount(0, 'data.0.labels');
    }

    public function test_upload_lampiran_hanya_untuk_anggota_dan_tidak_bisa_diunduh_orang_lain(): void
    {
        $this->upload($this->budi->user, $this->spAhmad)->assertForbidden();

        $id = $this->upload($this->ahmad->user, $this->spAhmad)->assertCreated()
            ->assertJsonPath('data.original_name', 'laporan.pdf')
            ->assertJsonPath('data.is_image', false)
            ->json('data.id');

        $this->actingAs($this->ahmad->user)->get("/workspace/attachments/{$id}")->assertOk();
        $this->actingAs($this->rudy->user)->get("/workspace/attachments/{$id}")->assertForbidden();
    }

    public function test_upload_ditolak_di_workspace_arsip_dan_berkas_tidak_valid(): void
    {
        $this->actingAs($this->rudy->user)->postJson('/workspace/attachments', [
            'file' => UploadedFile::fake()->create('virus.exe', 5),
            'workspace_id' => $this->spAhmad->id,
        ])->assertUnprocessable();

        $this->spAhmad->update(['is_archived' => true]);
        $this->upload($this->rudy->user, $this->spAhmad)->assertForbidden();
    }

    public function test_attachment_ids_milik_orang_lain_tidak_ditautkan(): void
    {
        $theirs = $this->upload($this->ahmad->user, $this->spAhmad)->json('data.id');

        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id], 'attachment_ids' => [$theirs]])
            ->assertCreated()
            ->assertJsonPath('data.0.attachments_count', 0);

        $this->assertNull(Attachment::find($theirs)->attachable_id);
    }

    public function test_update_checklist_items_memakai_id_dan_menghapus_yang_tidak_dikirim(): void
    {
        $id = $this->postTask($this->rudy->user, $this->spAhmad, [
            'assignee_ids' => [$this->ahmad->id],
            'checklist_items' => [['content' => 'A'], ['content' => 'B'], ['content' => 'C']],
        ])->json('data.0.id');

        $items = $this->actingAs($this->rudy->user)->getJson("/workspace/tasks/{$id}")->json('data.checklist_items');

        $response = $this->actingAs($this->rudy->user)->putJson("/workspace/tasks/{$id}", [
            'checklist_items' => [
                ['id' => $items[0]['id'], 'content' => 'A diubah', 'is_done' => true],
                ['content' => 'Baru'],
            ],
            'result_links' => [['url' => 'https://example.com/a']],
        ])->assertOk();

        $response->assertJsonCount(2, 'data.checklist_items')
            ->assertJsonPath('data.checklist_items.0.id', $items[0]['id'])
            ->assertJsonPath('data.checklist_items.0.content', 'A diubah')
            ->assertJsonPath('data.checklist_items.0.is_done', true)
            ->assertJsonPath('data.checklist_items.1.content', 'Baru')
            ->assertJsonPath('data.result_links.0.url', 'https://example.com/a');
    }

    public function test_column_order_menentukan_posisi_kartu(): void
    {
        $a = $this->makeTaskForAhmad(['title' => 'A']);
        $b = $this->makeTaskForAhmad(['title' => 'B']);
        $c = $this->makeTaskForAhmad(['title' => 'C']);

        // C dijatuhkan di antara A dan B.
        $this->setStatus($this->rudy->user, $c, 'todo', ['column_order' => [$a, $c, $b]])->assertOk();

        $order = collect($this->actingAs($this->rudy->user)->getJson("/workspace/{$this->spAhmad->id}/tasks?status=todo")->json('data'))->pluck('id')->all();

        $this->assertSame([$a, $c, $b], $order);
    }

    public function test_intern_mengirim_hasil_bersama_pindah_ke_review(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $id, 'in_progress')->assertOk();
        $attachment = $this->upload($this->ahmad->user, $this->spAhmad)->json('data.id');

        $this->setStatus($this->ahmad->user, $id, 'review', [
            'result_links' => [['url' => 'https://github.com/x/pr/1']],
            'attachment_ids' => [$attachment],
            'note' => 'Sudah selesai, mohon dicek.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'review')
            ->assertJsonPath('data.result_links.0.url', 'https://github.com/x/pr/1')
            ->assertJsonCount(1, 'data.attachments')
            ->assertJsonPath('data.comments.0.body', 'Sudah selesai, mohon dicek.');

        $this->assertSame(1, TaskComment::where('task_id', $id)->count());
        $this->assertSame(1, $this->notificationCount($this->rudy->user, 'App\Notifications\TaskReviewRequestedNotification'));
    }

    public function test_review_approve_menandai_selesai_dengan_nilai(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $id, 'review')->assertOk();

        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'approve', 'score' => 90])
            ->assertOk()
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.score', 90);
    }

    public function test_toggle_checklist_mengembalikan_progres(): void
    {
        $id = $this->makeTaskForAhmad(['checklist_items' => [['content' => 'A'], ['content' => 'B']]]);
        $item = $this->actingAs($this->rudy->user)->getJson("/workspace/tasks/{$id}")->json('data.checklist_items.0.id');

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/tasks/{$id}/checklist/{$item}", ['is_done' => true])
            ->assertOk()
            ->assertJsonPath('data', ['done' => 1, 'total' => 2]);
    }

    public function test_filter_array_dan_pencarian_pada_daftar_tugas(): void
    {
        $this->makeTaskForAhmad(['title' => 'Integrasi API', 'priority' => 'high']);
        $other = $this->makeTaskForAhmad(['title' => 'Desain logo', 'priority' => 'low']);
        $this->setStatus($this->rudy->user, $other, 'in_progress')->assertOk();

        $titles = fn (string $query) => collect(
            $this->actingAs($this->rudy->user)->getJson("/workspace/{$this->spAhmad->id}/tasks?{$query}")->json('data')
        )->pluck('title')->all();

        $this->assertSame(['Integrasi API'], $titles('status[]=todo'));
        $this->assertEqualsCanonicalizing(['Integrasi API', 'Desain logo'], $titles('status[]=todo&status[]=in_progress'));
        $this->assertSame(['Desain logo'], $titles('priority[]=low'));
        $this->assertSame(['Desain logo'], $titles('q=logo'));
    }

    public function test_tugas_milik_intern_untuk_dropdown_logbook(): void
    {
        $mine = $this->makeTaskForAhmad(['title' => 'Tugas Ahmad']);
        $this->postTask($this->tasmil->user, $this->spSiti, ['assignee_ids' => [$this->siti->id], 'title' => 'Tugas Siti'])->assertCreated();

        $this->actingAs($this->ahmad->user)->getJson('/workspace/tasks/mine')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine)
            ->assertJsonPath('data.0.workspace.id', $this->spAhmad->id);

        $this->actingAs($this->rudy->user)->getJson('/workspace/tasks/mine')->assertForbidden();
    }

    public function test_aktivitas_workspace_berhalaman(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $id = $this->makeTaskForAhmad();
            $this->setStatus($this->rudy->user, $id, 'in_progress')->assertOk();
        }

        $first = $this->actingAs($this->rudy->user)->getJson("/workspace/{$this->spAhmad->id}/activities")->assertOk();
        $first->assertJsonCount(20, 'data')->assertJsonPath('meta.has_more', true);

        $last = collect($first->json('data'))->last()['id'];
        $second = $this->actingAs($this->rudy->user)->getJson("/workspace/{$this->spAhmad->id}/activities?before={$last}")->assertOk();

        $this->assertSame(4, count($second->json('data')));
        $second->assertJsonPath('meta.has_more', false);

        $this->actingAs($this->budi->user)->getJson("/workspace/{$this->spAhmad->id}/activities")->assertForbidden();
    }

    public function test_kelola_label_tim(): void
    {
        $label = $this->makeLabel('docs', '#10B981');

        $this->actingAs($this->rudy->user)->postJson("/workspace/teams/{$this->gta->id}/labels", ['name' => 'docs', 'color' => '#111111'])
            ->assertUnprocessable();
        $this->actingAs($this->rudy->user)->postJson("/workspace/teams/{$this->gta->id}/labels", ['name' => 'x', 'color' => 'merah'])
            ->assertUnprocessable();

        $this->actingAs($this->herman->user)->patchJson("/workspace/labels/{$label}", ['name' => 'docs2', 'color' => '#111111'])->assertForbidden();
        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/labels/{$label}")->assertForbidden();

        $this->actingAs($this->tasmil->user)->patchJson("/workspace/labels/{$label}", ['name' => 'dokumen', 'color' => '#F59E0B'])
            ->assertOk()->assertJsonPath('data.color', '#f59e0b');

        $id = $this->makeTaskForAhmad(['label_ids' => [$label]]);
        $this->actingAs($this->admin)->deleteJson("/workspace/labels/{$label}")->assertNoContent();
        $this->assertCount(0, Task::find($id)->labels);
    }

    public function test_komentar_tugas_mengembalikan_comment_dan_menautkan_lampiran(): void
    {
        $id = $this->makeTaskForAhmad();
        $attachment = $this->upload($this->ahmad->user, $this->spAhmad)->json('data.id');

        $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'Halo **mentor**', 'attachment_ids' => [$attachment]])
            ->assertCreated()
            ->assertJsonPath('data.body_html', '<p>Halo <strong>mentor</strong></p>')
            ->assertJsonPath('data.user.id', $this->ahmad->user_id)
            ->assertJsonCount(1, 'data.attachments');
    }
}
