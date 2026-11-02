<?php

namespace Tests\Feature\Workspace;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\TaskActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class TaskFlowTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    public function test_mentor_membuat_tugas_dengan_kode_berurutan_dan_assignee(): void
    {
        $first = $this->postTask($this->rudy->user, $this->spAhmad, [
            'assignee_ids' => [$this->ahmad->id],
            'labels' => ['frontend'],
            'checklist' => ['a', 'b'],
            'priority' => 'high',
        ])->assertCreated();

        $first->assertJsonPath('data.0.code', 'T-1')
            ->assertJsonPath('data.0.status', 'todo')
            ->assertJsonPath('data.0.priority', 'high')
            ->assertJsonPath('data.0.assignees.0.id', $this->ahmad->id)
            ->assertJsonPath('data.0.labels.0.name', 'frontend')
            ->assertJsonCount(2, 'data.0.checklist');

        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id]])
            ->assertJsonPath('data.0.code', 'T-2');
    }

    public function test_validasi_pembuatan_tugas(): void
    {
        $mentor = $this->rudy->user;

        $this->postTask($mentor, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id], 'title' => ''])->assertUnprocessable();
        $this->postTask($mentor, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id], 'title' => str_repeat('x', 151)])->assertUnprocessable();
        $this->postTask($mentor, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id], 'due_at' => null])->assertUnprocessable();
        $this->postTask($mentor, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id], 'priority' => 'sangat'])->assertUnprocessable();
        $this->postTask($mentor, $this->spAhmad, ['assignee_ids' => []])->assertUnprocessable();
        $this->postTask($mentor, $this->spAhmad, [
            'assignee_ids' => [$this->ahmad->id],
            'start_at' => $this->due(5),
            'due_at' => $this->due(1),
        ])->assertUnprocessable();
    }

    public function test_assignee_harus_anggota_workspace(): void
    {
        // Siti bukan pemilik Space Ahmad
        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [$this->siti->id]])->assertUnprocessable();
        // Budi di tim lain
        $this->postTask($this->rudy->user, $this->wsGta, ['assignee_ids' => [$this->budi->id]])->assertUnprocessable();
        // anggota tim sendiri boleh
        $this->postTask($this->rudy->user, $this->wsGta, ['assignee_ids' => [$this->ahmad->id, $this->siti->id]])->assertCreated();
    }

    public function test_intern_hanya_bisa_memindahkan_tugas_sampai_review(): void
    {
        $id = $this->makeTaskForAhmad();
        $ahmad = $this->ahmad->user;

        $this->setStatus($ahmad, $id, 'in_progress')->assertOk()->assertJsonPath('data.status', 'in_progress');
        $this->setStatus($ahmad, $id, 'done')->assertForbidden();
        $this->setStatus($ahmad, $id, 'revision')->assertForbidden();
        $this->assertSame('in_progress', Task::find($id)->status->value);

        $this->setStatus($this->siti->user, $id, 'review')->assertForbidden();

        $this->setStatus($ahmad, $id, 'review', ['result_link' => 'https://github.com/x/y'])->assertOk();
        $this->assertSame([['url' => 'https://github.com/x/y', 'label' => null]], Task::find($id)->result_links);

        // dari Review, intern tidak bisa menarik kembali
        $this->setStatus($ahmad, $id, 'in_progress')->assertForbidden();
    }

    public function test_intern_tidak_bisa_menandai_selesai_lewat_review(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $id, 'review')->assertOk();

        $this->actingAs($this->ahmad->user)
            ->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'done'])
            ->assertForbidden();
        $this->actingAs($this->tasmil->user)
            ->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'done'])
            ->assertForbidden();

        $this->assertSame('review', Task::find($id)->status->value);
    }

    public function test_review_revisi_wajib_catatan_lalu_kembali_ke_in_progress(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $id, 'review')->assertOk();
        $mentor = $this->rudy->user;

        $this->actingAs($mentor)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'revision'])->assertUnprocessable();
        $this->actingAs($mentor)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'revision', 'feedback' => 'Perbaiki bagian A'])
            ->assertOk()->assertJsonPath('data.status', 'revision')->assertJsonPath('data.feedback', 'Perbaiki bagian A');

        // dari Revisi hanya boleh kembali ke In Progress
        $this->setStatus($this->ahmad->user, $id, 'review')->assertForbidden();
        $this->setStatus($this->ahmad->user, $id, 'todo')->assertForbidden();
        $this->setStatus($this->ahmad->user, $id, 'in_progress')->assertOk();
    }

    public function test_review_selesai_menyimpan_nilai_dan_waktu_selesai(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $id, 'review')->assertOk();

        $this->actingAs($this->rudy->user)
            ->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'done', 'score' => 101])->assertUnprocessable();
        $this->actingAs($this->rudy->user)
            ->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'done', 'score' => 90, 'feedback' => 'Bagus'])
            ->assertOk()->assertJsonPath('data.status', 'done')->assertJsonPath('data.score', 90);

        $task = Task::find($id);
        $this->assertNotNull($task->completed_at);

        // hanya tugas berstatus Review yang bisa dinilai
        $this->actingAs($this->rudy->user)
            ->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'done'])->assertUnprocessable();
    }

    public function test_setiap_perubahan_status_tercatat_di_riwayat_aktivitas(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $id, 'in_progress');
        $this->setStatus($this->ahmad->user, $id, 'review');
        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'revision', 'feedback' => 'x']);
        $this->setStatus($this->ahmad->user, $id, 'in_progress');
        $this->setStatus($this->ahmad->user, $id, 'review');
        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'done']);

        $activities = TaskActivity::where('task_id', $id)->orderBy('id')->get();
        $changes = $activities->where('action', 'status_changed')->map(fn ($a) => $a->meta['from'] . '>' . $a->meta['to'])->values()->all();

        $this->assertSame(1, $activities->where('action', 'created')->count());
        $this->assertSame([
            'todo>in_progress', 'in_progress>review', 'review>revision',
            'revision>in_progress', 'in_progress>review', 'review>done',
        ], $changes);

        $this->actingAs($this->ahmad->user)->getJson("/workspace/tasks/{$id}")
            ->assertOk()->assertJsonCount(7, 'data.activities');
    }

    public function test_urutan_kartu_tetap_setelah_dipindah_dan_dimuat_ulang(): void
    {
        $ids = [];
        foreach (['K1', 'K2', 'K3'] as $title) {
            $ids[$title] = $this->postTask($this->rudy->user, $this->spAhmad, [
                'title' => $title,
                'assignee_ids' => [$this->ahmad->id],
            ])->json('data.0.id');
        }

        $this->setStatus($this->rudy->user, $ids['K3'], 'todo', ['position' => 0])->assertOk();

        $order = $this->actingAs($this->ahmad->user)
            ->getJson("/workspace/{$this->spAhmad->id}/tasks?status=todo")
            ->json('data.*.title');
        $this->assertSame(['K3', 'K1', 'K2'], $order);

        // pindah kolom: posisi di kolom tujuan rapat
        $this->setStatus($this->ahmad->user, $ids['K1'], 'in_progress')->assertOk();
        $this->assertSame(['K3', 'K2'], $this->actingAs($this->ahmad->user)
            ->getJson("/workspace/{$this->spAhmad->id}/tasks?status=todo")->json('data.*.title'));
        // kolom tujuan rapat dari 0; kolom asal boleh punya celah selama urutannya tetap
        $this->assertSame([0], Task::where('status', 'in_progress')->pluck('position')->all());
    }

    public function test_filter_board_berdasarkan_prioritas_status_dan_assignee(): void
    {
        $this->postTask($this->rudy->user, $this->wsGta, ['title' => 'Urgent A', 'priority' => 'urgent', 'assignee_ids' => [$this->ahmad->id]]);
        $this->postTask($this->rudy->user, $this->wsGta, ['title' => 'Rendah S', 'priority' => 'low', 'assignee_ids' => [$this->siti->id]]);

        $as = fn () => $this->actingAs($this->rudy->user);

        $this->assertSame(['Urgent A'], $as()->getJson("/workspace/{$this->wsGta->id}/tasks?priority=urgent")->json('data.*.title'));
        $this->assertSame(['Rendah S'], $as()->getJson("/workspace/{$this->wsGta->id}/tasks?assignee={$this->siti->id}")->json('data.*.title'));
        $this->assertCount(2, $as()->getJson("/workspace/{$this->wsGta->id}/tasks")->json('data'));
        $this->assertCount(0, $as()->getJson("/workspace/{$this->wsGta->id}/tasks?status=done")->json('data'));
        $as()->getJson("/workspace/{$this->wsGta->id}/tasks?status=ngawur")->assertUnprocessable();
    }

    public function test_update_tugas_dan_perpanjangan_deadline_mereset_penanda(): void
    {
        $id = $this->makeTaskForAhmad();
        Task::whereKey($id)->update(['overdue_at' => now(), 'reminded_at' => now()]);

        $this->actingAs($this->rudy->user)->putJson("/workspace/tasks/{$id}", ['title' => 'Judul baru', 'priority' => 'urgent', 'due_at' => $this->due(10)])
            ->assertOk()->assertJsonPath('data.title', 'Judul baru');

        $task = Task::find($id);
        $this->assertNull($task->overdue_at);
        $this->assertNull($task->reminded_at);

        // intern tidak boleh mengubah tugas dari mentor
        $this->actingAs($this->ahmad->user)->putJson("/workspace/tasks/{$id}", ['title' => 'x'])->assertForbidden();
    }

    public function test_hapus_tugas_soft_delete_hanya_mentor_pembuat_atau_admin(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/tasks/{$id}")->assertForbidden();
        $this->actingAs($this->tasmil->user)->deleteJson("/workspace/tasks/{$id}")->assertForbidden();
        $this->actingAs($this->rudy->user)->deleteJson("/workspace/tasks/{$id}")->assertNoContent();

        $this->assertSoftDeleted('tasks', ['id' => $id]);
        $this->actingAs($this->rudy->user)->getJson("/workspace/tasks/{$id}")->assertNotFound();
    }

    public function test_intern_hanya_bisa_membuat_to_do_pribadi_di_space_sendiri(): void
    {
        $response = $this->postTask($this->ahmad->user, $this->spAhmad, ['title' => 'To-do pribadi', 'assignee_ids' => [$this->siti->id]])
            ->assertCreated();

        // assignee dipaksa dirinya sendiri
        $response->assertJsonPath('data.0.assignees.0.id', $this->ahmad->id);
        $id = $response->json('data.0.id');

        $this->actingAs($this->ahmad->user)->putJson("/workspace/tasks/{$id}", ['title' => 'Ubah sendiri'])->assertOk();
        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/tasks/{$id}")->assertForbidden();
    }

    public function test_komentar_tugas_oleh_anggota_dan_ditolak_untuk_non_anggota(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'Sudah dikerjakan'])
            ->assertCreated()->assertJsonPath('user.name', 'Ahmad Fauzi');
        $this->actingAs($this->rudy->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'Mantap'])->assertCreated();
        $this->actingAs($this->siti->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'nyelonong'])->assertForbidden();
        $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => ''])->assertUnprocessable();

        $this->actingAs($this->ahmad->user)->getJson("/workspace/tasks/{$id}")->assertJsonCount(2, 'data.comments');
    }

    public function test_lampiran_tugas_disimpan_private_dan_hanya_bisa_diunduh_anggota(): void
    {
        Storage::fake('local');
        $id = $this->makeTaskForAhmad();

        $upload = $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$id}/attachments", [
            'files' => [UploadedFile::fake()->create('hasil.pdf', 100, 'application/pdf')],
        ])->assertCreated();

        $attachment = Attachment::findOrFail($upload->json('data.0.id'));
        Storage::disk('local')->assertExists($attachment->path);
        $this->assertSame('hasil.pdf', $attachment->original_name);
        $this->assertStringStartsWith('workspace/attachments/', $attachment->path);

        $this->actingAs($this->ahmad->user)->get("/workspace/attachments/{$attachment->id}")->assertOk();
        $this->actingAs($this->rudy->user)->get("/workspace/attachments/{$attachment->id}")->assertOk();
        $this->actingAs($this->siti->user)->get("/workspace/attachments/{$attachment->id}")->assertForbidden();
        $this->actingAs($this->budi->user)->get("/workspace/attachments/{$attachment->id}")->assertForbidden();
    }

    public function test_lampiran_ditolak_jika_jenis_atau_ukuran_tidak_sesuai(): void
    {
        Storage::fake('local');
        $id = $this->makeTaskForAhmad();
        $post = fn (UploadedFile $file) => $this->actingAs($this->ahmad->user)
            ->postJson("/workspace/tasks/{$id}/attachments", ['files' => [$file]]);

        $post(UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'))->assertUnprocessable();
        $post(UploadedFile::fake()->create('script.php', 10, 'text/x-php'))->assertUnprocessable();
        $post(UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf'))->assertUnprocessable();
        $post(UploadedFile::fake()->create('pas.pdf', 10240, 'application/pdf'))->assertCreated();
        $this->assertSame(1, Attachment::count());
    }

    public function test_checklist_bisa_dicentang_assignee_dan_menghitung_progres(): void
    {
        $id = $this->makeTaskForAhmad(['checklist' => ['a', 'b', 'c', 'd']]);
        $items = Task::find($id)->checklistItems;

        $this->actingAs($this->ahmad->user)
            ->patchJson("/workspace/tasks/{$id}/checklist/{$items[0]->id}", ['is_done' => true])
            ->assertOk()->assertJsonPath('checklist_progress', 25);
        $this->actingAs($this->ahmad->user)
            ->patchJson("/workspace/tasks/{$id}/checklist/{$items[1]->id}", ['is_done' => true])
            ->assertJsonPath('checklist_progress', 50);

        $this->actingAs($this->siti->user)
            ->patchJson("/workspace/tasks/{$id}/checklist/{$items[2]->id}", ['is_done' => true])
            ->assertForbidden();
    }

    public function test_scheduler_menandai_terlambat_dan_pengingat_h1_masing_masing_sekali(): void
    {
        $late = $this->makeTaskForAhmad(['title' => 'Terlambat']);
        $soon = $this->makeTaskForAhmad(['title' => 'Segera']);
        $done = $this->makeTaskForAhmad(['title' => 'Sudah selesai']);
        $review = $this->makeTaskForAhmad(['title' => 'Sedang direview']);
        Task::whereKey($late)->update(['due_at' => now()->subHour()]);
        Task::whereKey($soon)->update(['due_at' => now()->addHours(5)]);
        Task::whereKey($done)->update(['due_at' => now()->subDay(), 'status' => 'done']);
        Task::whereKey($review)->update(['due_at' => now()->addHours(5), 'status' => 'review']);

        $this->artisan('workspace:check-deadlines')->assertSuccessful();

        $this->assertNotNull(Task::find($late)->overdue_at);
        $this->assertNull(Task::find($done)->overdue_at);
        $this->assertNotNull(Task::find($soon)->reminded_at);
        $this->assertNull(Task::find($review)->reminded_at, 'tugas yang sudah dikirim tidak perlu diingatkan');
        $this->assertSame(1, TaskActivity::where('task_id', $late)->where('action', 'overdue')->count());

        $before = \DB::table('notifications')->count();
        $this->artisan('workspace:check-deadlines')->assertSuccessful();
        $this->assertSame($before, \DB::table('notifications')->count());
    }

    public function test_scheduler_mengabaikan_workspace_yang_diarsipkan(): void
    {
        $id = $this->makeTaskForAhmad();
        Task::whereKey($id)->update(['due_at' => now()->subDay()]);
        $this->spAhmad->update(['is_archived' => true]);

        $this->artisan('workspace:check-deadlines')->assertSuccessful();

        $this->assertNull(Task::find($id)->overdue_at);
    }

    public function test_mentor_menyeret_kartu_ke_selesai_mengisi_waktu_selesai_dan_menotifikasi_intern(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->setStatus($this->rudy->user, $id, 'done', ['score' => 80, 'feedback' => 'Langsung ditutup'])
            ->assertOk()->assertJsonPath('data.status', 'done')->assertJsonPath('data.score', 80);

        $task = Task::find($id);
        $this->assertNotNull($task->completed_at, 'metrik tepat waktu butuh completed_at');
        $this->assertSame(1, \DB::table('notifications')->where('notifiable_id', $this->ahmad->user_id)
            ->where('type', \App\Notifications\TaskReviewedNotification::class)->count());

        // dikembalikan dari Selesai: waktu selesai dihapus lagi
        $this->setStatus($this->rudy->user, $id, 'in_progress')->assertOk();
        $this->assertNull(Task::find($id)->completed_at);
    }

    public function test_mentor_menyeret_kartu_ke_revisi_wajib_catatan(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->setStatus($this->rudy->user, $id, 'revision')->assertUnprocessable();
        $this->assertSame('todo', Task::find($id)->status->value);

        $this->setStatus($this->rudy->user, $id, 'revision', ['feedback' => 'Perbaiki bagian B'])
            ->assertOk()->assertJsonPath('data.status', 'revision')->assertJsonPath('data.feedback', 'Perbaiki bagian B');
    }

    public function test_intern_yang_meminta_selesai_atau_revisi_tetap_mendapat_403_bukan_422(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->setStatus($this->ahmad->user, $id, 'revision')->assertForbidden();
        $this->setStatus($this->ahmad->user, $id, 'done', ['score' => 100])->assertForbidden();
        $this->assertSame('todo', Task::find($id)->status->value);
    }

    public function test_komentar_tugas_dari_user_yang_sudah_dihapus_tampil_sebagai_pengguna_dihapus(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'Komentar lama'])->assertCreated();

        $this->ahmad->delete();
        $this->ahmad->user->delete();

        $comments = $this->actingAs($this->rudy->user)->getJson("/workspace/tasks/{$id}")->assertOk()->json('data.comments');
        $this->assertSame('Komentar lama', $comments[0]['body']);
        $this->assertSame('Pengguna dihapus', $comments[0]['user']['name']);
    }
}
