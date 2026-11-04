<?php

namespace Tests\Feature\Workspace;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Regresi untuk temuan code review modul Workspace. */
class ReviewFixesTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    public function test_super_admin_tidak_menembus_status_arsip(): void
    {
        $super = $this->makeAdmin('Super Uji', 'super_admin');
        $id = $this->makeTaskForAhmad();

        // Sebelum diarsipkan super admin tetap bisa menulis.
        $this->postTask($super, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id]])->assertCreated();
        $this->actingAs($super)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'ok'])->assertCreated();
        $this->sendMessage($super, $this->dmAhmad, 'halo')->assertCreated();

        $this->spAhmad->update(['is_archived' => true]);
        $this->wsGta->update(['is_archived' => true]);

        $this->postTask($super, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id]])->assertForbidden();
        $this->actingAs($super)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'masih bisa?'])->assertForbidden();
        $this->setStatus($super, $id, 'in_progress')->assertForbidden();
        $this->sendMessage($super, $this->umumGta, 'masih bisa?')->assertForbidden();

        // Membaca tetap boleh.
        $this->actingAs($super)->getJson("/workspace/{$this->spAhmad->id}/tasks")->assertOk();
    }

    public function test_pesan_dari_user_yang_dihapus_tetap_dihitung_belum_dibaca(): void
    {
        $this->sendMessage($this->ahmad->user, $this->umumGta, 'Pesan lama Ahmad')->assertCreated();

        $unread = fn () => collect($this->actingAs($this->rudy->user)->getJson('/workspace/channels')->json('data'))
            ->firstWhere('id', $this->umumGta->id)['unread_count'];

        $before = $unread();
        $this->assertGreaterThanOrEqual(1, $before);

        $this->ahmad->delete();
        $this->ahmad->user->delete();

        $this->assertSame($before, $unread(), 'menghapus penulis tidak boleh mengurangi hitungan belum dibaca');
    }

    public function test_tugas_di_review_tidak_ditandai_terlambat_tapi_revisi_ditandai(): void
    {
        $review = $this->makeTaskForAhmad(['title' => 'Menunggu review']);
        $revision = $this->makeTaskForAhmad(['title' => 'Perlu revisi']);
        Task::whereKey($review)->update(['due_at' => now()->subHour(), 'status' => 'review']);
        Task::whereKey($revision)->update(['due_at' => now()->subHour(), 'status' => 'revision']);

        $this->artisan('workspace:check-deadlines')->assertSuccessful();

        $this->assertNull(Task::find($review)->overdue_at, 'keterlambatan reviewer bukan salah intern');
        $this->assertNotNull(Task::find($revision)->overdue_at);
    }

    public function test_assignee_berupa_string_numerik_diterima_di_space_intern(): void
    {
        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [(string) $this->ahmad->id]])
            ->assertCreated()
            ->assertJsonPath('data.0.assignees.0.id', $this->ahmad->id);
    }

    public function test_filter_due_to_menyertakan_seluruh_hari_terakhir(): void
    {
        $id = $this->makeTaskForAhmad();
        $day = now()->addDays(2)->setTime(14, 0);
        Task::whereKey($id)->update(['due_at' => $day]);

        $ids = fn (string $to) => collect(
            $this->actingAs($this->rudy->user)->getJson("/workspace/{$this->spAhmad->id}/tasks?due_to={$to}")->json('data')
        )->pluck('id')->all();

        $this->assertContains($id, $ids($day->toDateString()));
        $this->assertNotContains($id, $ids($day->copy()->subDay()->toDateString()));
    }

    public function test_board_menyertakan_meta_truncated_kosong_jika_tidak_ada_pemotongan(): void
    {
        $this->makeTaskForAhmad();

        $this->actingAs($this->rudy->user)->getJson("/workspace/{$this->spAhmad->id}/tasks")
            ->assertOk()
            ->assertJsonPath('meta.truncated', []);
    }

    public function test_daftar_channel_tidak_menampilkan_channel_workspace_yang_sudah_ditinggalkan(): void
    {
        // Tasmil keluar dari workspace tim, jadi channel workspace itu hilang dari daftarnya.
        \DB::table('workspace_members')
            ->where('workspace_id', $this->wsGta->id)
            ->where('user_id', $this->tasmil->user_id)
            ->update(['left_at' => now()]);

        $ids = collect($this->actingAs($this->tasmil->user)->getJson('/workspace/channels')->json('data'))->pluck('id')->all();

        $this->assertNotContains($this->umumGta->id, $ids);
        $this->assertContains($this->announcement->id, $ids, 'channel global tetap tampil');
    }
}
