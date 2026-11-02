<?php

namespace Tests\Feature\Workspace;

use App\Models\Logbook;
use App\Models\Task;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentedNotification;
use App\Notifications\TaskCommentMentionNotification;
use App\Notifications\TaskReviewedNotification;
use App\Notifications\TaskReviewRequestedNotification;
use App\Services\WorkspaceDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class NotificationDashboardTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    // --- notifikasi tugas ---

    public function test_tugas_baru_menotifikasi_assignee_bukan_pembuat(): void
    {
        $this->makeTaskForAhmad();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, TaskAssignedNotification::class));
        $this->assertSame(0, $this->notificationCount($this->rudy->user, TaskAssignedNotification::class));
    }

    public function test_tambah_assignee_hanya_menotifikasi_yang_baru(): void
    {
        $id = $this->postTask($this->rudy->user, $this->wsGta, ['assignee_ids' => [$this->ahmad->id]])->json('data.0.id');
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, TaskAssignedNotification::class));

        $this->actingAs($this->rudy->user)->putJson("/workspace/tasks/{$id}", ['assignee_ids' => [$this->ahmad->id, $this->siti->id]])->assertOk();

        $this->assertSame(1, $this->notificationCount($this->siti->user, TaskAssignedNotification::class));
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, TaskAssignedNotification::class));
    }

    public function test_assign_massal_menotifikasi_setiap_intern(): void
    {
        $this->postTask($this->rudy->user, $this->wsGta, [
            'title' => 'Mingguan', 'assignee_ids' => [$this->ahmad->id, $this->siti->id],
        ])->assertCreated();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, TaskAssignedNotification::class));
        $this->assertSame(1, $this->notificationCount($this->siti->user, TaskAssignedNotification::class));
    }

    public function test_masuk_review_menotifikasi_mentor_pembuat(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->setStatus($this->ahmad->user, $id, 'in_progress');
        $this->assertSame(0, $this->notificationCount($this->rudy->user, TaskReviewRequestedNotification::class));

        $this->setStatus($this->ahmad->user, $id, 'review');
        $this->assertSame(1, $this->notificationCount($this->rudy->user, TaskReviewRequestedNotification::class));
        $this->assertSame(0, $this->notificationCount($this->ahmad->user, TaskReviewRequestedNotification::class));
    }

    public function test_selesai_dan_revisi_menotifikasi_intern_dengan_pesan_berbeda(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $id, 'review');

        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'revision', 'feedback' => 'Perbaiki']);
        $this->setStatus($this->ahmad->user, $id, 'in_progress');
        $this->setStatus($this->ahmad->user, $id, 'review');
        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$id}/review", ['decision' => 'done', 'score' => 85]);

        $messages = $this->ahmad->user->notifications()
            ->where('type', TaskReviewedNotification::class)->get()->pluck('data.message')->implode('|');

        $this->assertSame(2, $this->notificationCount($this->ahmad->user, TaskReviewedNotification::class));
        $this->assertStringContainsString('perlu direvisi', $messages);
        $this->assertStringContainsString('dinyatakan Selesai', $messages);
    }

    public function test_komentar_menotifikasi_pembuat_dan_assignee_kecuali_penulis_dan_yang_di_mention(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'Sudah saya kerjakan']);
        $this->assertSame(1, $this->notificationCount($this->rudy->user, TaskCommentedNotification::class));
        $this->assertSame(0, $this->notificationCount($this->ahmad->user, TaskCommentedNotification::class));

        $this->actingAs($this->rudy->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'Mantap @Ahmad Fauzi']);
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, TaskCommentMentionNotification::class));
        $this->assertSame(0, $this->notificationCount($this->ahmad->user, TaskCommentedNotification::class), 'yang di-mention tidak dobel');

        $this->assertSame(0, \DB::table('notifications')->where('notifiable_id', $this->siti->user_id)
            ->whereIn('type', [TaskCommentedNotification::class, TaskAssignedNotification::class])->count(), 'non-anggota tidak diberi tahu');
    }

    // --- lonceng ---

    public function test_lonceng_menampilkan_jumlah_belum_dibaca_dan_notifikasi_terbaru(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->makeTaskForAhmad(['title' => "Tugas {$i}"]);
        }

        $bell = $this->actingAs($this->ahmad->user)->getJson('/notifications')->assertOk();

        $bell->assertJsonPath('meta.unread_count', 12)->assertJsonCount(10, 'data');
        $this->assertSame('task_assigned', $bell->json('data.0.type'));
        $this->assertStringContainsString('Tugas', $bell->json('data.0.message'));
        $this->assertNull($bell->json('data.0.read_at'));

        $this->actingAs($this->ahmad->user)->getJson('/notifications?per_page=2')->assertJsonCount(2, 'data');
        $this->actingAs($this->ahmad->user)->getJson('/notifications?per_page=500')->assertUnprocessable();
    }

    public function test_tandai_dibaca_satu_dan_semua(): void
    {
        $this->makeTaskForAhmad();
        $this->makeTaskForAhmad();
        $id = $this->actingAs($this->ahmad->user)->getJson('/notifications')->json('data.0.id');

        $this->actingAs($this->ahmad->user)->postJson("/notifications/{$id}/read")->assertOk()->assertJsonPath('unread_count', 1);
        $this->actingAs($this->ahmad->user)->getJson('/notifications?unread=1')->assertJsonPath('meta.total', 1);

        $this->actingAs($this->ahmad->user)->postJson('/notifications/read-all')->assertOk()->assertJsonPath('unread_count', 0);
        $this->actingAs($this->ahmad->user)->getJson('/notifications')->assertJsonPath('meta.unread_count', 0);
    }

    public function test_notifikasi_orang_lain_tidak_bisa_dibaca_atau_ditandai(): void
    {
        $this->makeTaskForAhmad();
        $id = $this->actingAs($this->ahmad->user)->getJson('/notifications')->json('data.0.id');

        $this->actingAs($this->siti->user)->postJson("/notifications/{$id}/read")->assertNotFound();
        $this->actingAs($this->siti->user)->getJson('/notifications')->assertJsonPath('meta.total', 0);
        $this->actingAs($this->ahmad->user)->getJson('/notifications')->assertJsonPath('meta.unread_count', 1);
    }

    // --- dashboard ---

    public function test_dashboard_intern_memuat_lima_tugas_terdekat_dan_pesan_belum_dibaca(): void
    {
        foreach ([9, 2, 7, 1, 5, 3] as $days) {
            $this->makeTaskForAhmad(['title' => "Tugas {$days} hari", 'due_at' => $this->due($days)]);
        }

        $response = $this->actingAs($this->ahmad->user)->getJson('/workspace/dashboard')->assertOk()->assertJsonPath('role', 'intern');

        $this->assertSame(
            ['Tugas 1 hari', 'Tugas 2 hari', 'Tugas 3 hari', 'Tugas 5 hari', 'Tugas 7 hari'],
            $response->json('data.my_tasks.*.title')
        );
        $response->assertJsonPath('data.counts.open', 6);
        $this->assertIsInt($response->json('data.unread_messages'));
    }

    public function test_dashboard_mentor_memuat_perlu_review_dan_terlambat_per_intern(): void
    {
        $review = $this->makeTaskForAhmad();
        $late = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $review, 'review');
        Task::whereKey($late)->update(['due_at' => now()->subDay()]);

        $response = $this->actingAs($this->rudy->user)->getJson('/workspace/dashboard')->assertOk()->assertJsonPath('role', 'mentor');

        $response->assertJsonPath('data.needs_review.total', 1);
        $this->assertSame([$review], $response->json('data.needs_review.tasks.*.id'));
        $response->assertJsonPath('data.overdue_by_intern.0.intern_id', $this->ahmad->id)
            ->assertJsonPath('data.overdue_by_intern.0.overdue', 1);

        // mentor lain tidak melihat tugas ini
        $this->actingAs($this->tasmil->user)->getJson('/workspace/dashboard')->assertJsonPath('data.needs_review.total', 0);
    }

    public function test_dashboard_admin_memuat_rekap_per_tim_dan_mentor_tanpa_tugas(): void
    {
        $this->makeTaskForAhmad();
        $this->postTask($this->herman->user, $this->internSpace($this->budi), ['assignee_ids' => [$this->budi->id]]);

        $response = $this->actingAs($this->admin)->getJson('/workspace/dashboard')->assertOk()->assertJsonPath('role', 'admin');

        $teams = collect($response->json('data.teams'))->keyBy('team');
        $this->assertSame(1, $teams['TIM GTA']['by_status']['todo']);
        $this->assertSame(1, $teams['TIM DEA']['by_status']['todo']);
        $this->assertCount(5, $teams['TIM GTA']['by_status']);

        // Rudy dan Herman sudah memberi tugas minggu ini, Tasmil belum
        $this->assertSame(['Tasmil'], $response->json('data.mentors_without_tasks_this_week.*.name'));
    }

    public function test_dashboard_menolak_role_di_luar_workspace(): void
    {
        $this->actingAs($this->makeUser('institusi', 'Kampus'))->getJson('/workspace/dashboard')->assertForbidden();
    }

    public function test_progres_per_intern_menghitung_selesai_total_dan_terlambat(): void
    {
        $done = $this->makeTaskForAhmad();
        $late = $this->makeTaskForAhmad();
        $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $done, 'review');
        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$done}/review", ['decision' => 'done']);
        Task::whereKey($late)->update(['due_at' => now()->subDay()]);

        $progress = app(WorkspaceDashboardService::class)->progressByIntern([$this->ahmad->id, $this->siti->id]);

        $this->assertSame(['total' => 3, 'done' => 1, 'overdue' => 1], $progress[$this->ahmad->id]);
        $this->assertArrayNotHasKey($this->siti->id, $progress);
    }

    // --- tautan logbook ---

    public function test_logbook_bisa_ditautkan_ke_tugas_milik_sendiri(): void
    {
        $taskId = $this->makeTaskForAhmad(['title' => 'Tugas untuk logbook']);

        $this->actingAs($this->ahmad->user)->get(route('intern.logbook.create'))
            ->assertOk()->assertSee('Tugas Terkait')->assertSee('Tugas untuk logbook');

        $this->actingAs($this->ahmad->user)
            ->post(route('intern.logbook.store'), ['date' => '2026-01-02', 'activity' => 'Mengerjakan tugas', 'task_id' => $taskId])
            ->assertRedirect();

        $logbook = Logbook::where('intern_id', $this->ahmad->id)->firstOrFail();
        $this->assertSame($taskId, $logbook->task_id);

        $this->actingAs($this->ahmad->user)->get(route('intern.logbook.edit', $logbook))
            ->assertOk()->assertSee('selected', false);
    }

    public function test_logbook_menolak_tugas_intern_lain_dan_tetap_bisa_tanpa_tugas(): void
    {
        $milikSiti = $this->postTask($this->tasmil->user, $this->spSiti, ['assignee_ids' => [$this->siti->id]])->json('data.0.id');

        $this->actingAs($this->ahmad->user)
            ->postJson(route('intern.logbook.store'), ['date' => '2026-01-03', 'activity' => 'x', 'task_id' => $milikSiti])
            ->assertUnprocessable();

        $this->actingAs($this->ahmad->user)
            ->post(route('intern.logbook.store'), ['date' => '2026-01-04', 'activity' => 'Tanpa tugas'])
            ->assertRedirect();

        $this->assertNull(Logbook::where('intern_id', $this->ahmad->id)->firstOrFail()->task_id);
    }

    // --- Monitoring admin ---

    public function test_monitoring_admin_menampilkan_kolom_progres_tugas(): void
    {
        $done = $this->makeTaskForAhmad();
        $late = $this->makeTaskForAhmad();
        $this->setStatus($this->ahmad->user, $done, 'review');
        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$done}/review", ['decision' => 'done']);
        Task::whereKey($late)->update(['due_at' => now()->subDay()]);

        $this->actingAs($this->admin)->get(route('admin.monitoring.index'))
            ->assertOk()
            ->assertSee('>Tugas</th>', false)
            ->assertSee('1/2', false)
            ->assertSee('1 terlambat');
    }
}
