<?php

namespace Tests\Feature\Workspace;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Bentuk notifikasi, lonceng, halaman, dan pengaturan sesuai kontrak frontend. */
class NotificationContractTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function bell($user, string $query = '')
    {
        return $this->actingAs($user)->getJson('/notifications' . ($query ? "?{$query}" : ''));
    }

    public function test_notifikasi_tugas_baru_membawa_title_body_url_dan_actor(): void
    {
        $this->makeTaskForAhmad(['title' => 'Integrasi API', 'due_at' => '2026-12-05 17:00:00']);

        $item = $this->bell($this->ahmad->user)->assertOk()->json('data.0');

        $this->assertSame('task_assigned', $item['type']);
        $this->assertSame('Tugas baru: T-1 Integrasi API', $item['title']);
        $this->assertStringContainsString('Dari Rudy Hermayadi', $item['body']);
        $this->assertStringContainsString('deadline 5 Des', $item['body']);
        $this->assertSame(route('workspace.show', $this->spAhmad) . '?task=T-1', $item['url']);
        $this->assertSame('Rudy Hermayadi', $item['actor']['name']);
        $this->assertNull($item['read_at']);
        $this->assertNotEmpty($item['created_at']);
    }

    public function test_hasil_review_dibedakan_menjadi_approved_dan_revision(): void
    {
        $a = $this->makeTaskForAhmad();
        $b = $this->makeTaskForAhmad();
        foreach ([$a, $b] as $id) {
            $this->setStatus($this->ahmad->user, $id, 'review')->assertOk();
        }
        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$a}/review", ['decision' => 'approve', 'score' => 88])->assertOk();
        $this->actingAs($this->rudy->user)->patchJson("/workspace/tasks/{$b}/review", ['decision' => 'revision', 'feedback' => 'Tambah test'])->assertOk();

        $items = collect($this->bell($this->ahmad->user, 'limit=50')->json('data'));
        $approved = $items->firstWhere('type', 'task_approved');
        $revision = $items->firstWhere('type', 'task_revision');

        $this->assertSame('T-1 dinyatakan Selesai', $approved['title']);
        $this->assertStringContainsString('Nilai 88', $approved['body']);
        $this->assertSame('T-2 perlu direvisi', $revision['title']);
        $this->assertStringContainsString('Tambah test', $revision['body']);
        $this->assertSame('Rudy Hermayadi', $approved['actor']['name']);

        $review = collect($this->bell($this->rudy->user, 'limit=50')->json('data'))->firstWhere('type', 'task_in_review');
        $this->assertStringContainsString('menunggu review', $review['title']);
    }

    public function test_mention_di_channel_membuka_channel_dan_pesannya(): void
    {
        $messageId = $this->sendMessage($this->rudy->user, $this->umumGta, "Halo @{$this->ahmad->user->name}")->json('data.id');

        $item = $this->bell($this->ahmad->user)->json('data.0');

        $this->assertSame('mentioned', $item['type']);
        $this->assertSame('Rudy Hermayadi menyebut kamu', $item['title']);
        $this->assertStringContainsString('di #' . $this->umumGta->name, $item['body']);
        $this->assertSame(
            route('workspace.show', $this->wsGta) . "?tab=channel&channel={$this->umumGta->id}&message={$messageId}",
            $item['url']
        );
    }

    public function test_pengumuman_global_membuka_workspace_milik_penerima(): void
    {
        $messageId = $this->sendMessage($this->rudy->user, $this->announcement, 'Besok libur nasional')->assertCreated()->json('data.id');

        $item = $this->bell($this->ahmad->user)->json('data.0');

        $this->assertSame('announcement', $item['type']);
        $this->assertSame('Pengumuman baru', $item['title']);
        $this->assertStringContainsString('Besok libur nasional', $item['body']);
        $this->assertSame(route('workspace.show', $this->spAhmad) . "?tab=channel&channel={$this->announcement->id}&message={$messageId}", $item['url']);
    }

    public function test_lonceng_membawa_unread_messages_dan_halaman_tidak(): void
    {
        // Pesan sambutan sistem sudah ada di channel; yang diuji adalah selisih dua pesan baru.
        $baseline = $this->bell($this->ahmad->user)->json('unread_messages.by_channel.' . $this->umumGta->id) ?? 0;
        $this->sendMessage($this->rudy->user, $this->umumGta, 'satu');
        $this->sendMessage($this->rudy->user, $this->umumGta, 'dua');

        $bell = $this->bell($this->ahmad->user)->assertOk();
        $count = $baseline + 2;

        $this->assertSame($count, $bell->json('unread_messages.by_channel.' . $this->umumGta->id));
        $this->assertGreaterThanOrEqual($count, $bell->json('unread_messages.total'));
        $this->assertSame($bell->json('unread_count'), $bell->json('meta.unread_count'));

        $this->assertArrayNotHasKey('unread_messages', $this->bell($this->ahmad->user, 'page=1&per_page=20')->json());

        // dibisukan: tetap di by_channel tetapi tidak masuk total
        $this->actingAs($this->ahmad->user)->patchJson("/workspace/channels/{$this->umumGta->id}/mute", ['is_muted' => true])->assertOk();
        $muted = $this->bell($this->ahmad->user);
        $this->assertSame($count, $muted->json('unread_messages.by_channel.' . $this->umumGta->id));
        $this->assertSame($bell->json('unread_messages.total') - $count, $muted->json('unread_messages.total'));
    }

    public function test_by_channel_kosong_tetap_berupa_objek_json(): void
    {
        foreach ($this->actingAs($this->budi->user)->getJson('/workspace/channels')->json('data') as $channel) {
            $this->actingAs($this->budi->user)->postJson("/workspace/channels/{$channel['id']}/read")->assertOk();
        }

        $raw = $this->bell($this->budi->user)->getContent();

        $this->assertStringContainsString('"by_channel":{}', $raw);
    }

    public function test_halaman_notifikasi_json_dengan_paging_filter_dan_kategori(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->makeTaskForAhmad();
        }
        $this->sendMessage($this->rudy->user, $this->announcement, 'info');

        $first = $this->bell($this->ahmad->user, 'page=1&per_page=20')->assertOk();
        $first->assertJsonCount(20, 'data')->assertJsonPath('meta.current_page', 1)->assertJsonPath('meta.has_more', true);

        $second = $this->bell($this->ahmad->user, 'page=2&per_page=20');
        $second->assertJsonCount(6, 'data')->assertJsonPath('meta.has_more', false);

        $this->assertSame(1, count($this->bell($this->ahmad->user, 'category=announcement')->json('data')));
        $this->assertSame(10, count($this->bell($this->ahmad->user, 'category=task')->json('data')));
        $this->assertSame(0, count($this->bell($this->ahmad->user, 'category=mention')->json('data')));
        $this->bell($this->ahmad->user, 'category=ngawur')->assertUnprocessable();

        $id = $this->bell($this->ahmad->user)->json('data.0.id');
        $this->actingAs($this->ahmad->user)->postJson("/notifications/{$id}/read")->assertOk();
        $this->assertSame(25, $this->bell($this->ahmad->user, 'filter=unread&per_page=50')->json('meta.total'));
    }

    public function test_halaman_html_notifikasi_dan_pengaturan_dirender(): void
    {
        $this->makeTaskForAhmad(['title' => 'Tugas pertama']);

        foreach ([$this->ahmad->user, $this->rudy->user, $this->admin] as $user) {
            $this->actingAs($user)->get('/notifications')->assertOk();
            $this->actingAs($user)->get('/notifications?filter=unread&category=task')->assertOk();
            $this->actingAs($user)->get('/notifications/settings')->assertOk();
        }

        $this->actingAs($this->ahmad->user)->get('/notifications')->assertSee('Tugas baru: T-1 Tugas pertama');
        $this->get('/logout');
        $this->app['auth']->forgetGuards();
        $this->get('/notifications')->assertRedirect();
    }

    public function test_pengaturan_menampilkan_jenis_sesuai_role(): void
    {
        $intern = collect($this->actingAs($this->ahmad->user)->getJson('/notifications/preferences')->assertOk()->json('data.types'));
        $this->assertNotNull($intern->firstWhere('type', 'task_assigned'));
        $this->assertNull($intern->firstWhere('type', 'intern_joined'));
        $this->assertTrue($intern->every(fn ($t) => $t['in_app'] === true));
        // Email hanya untuk jenis yang dikirim lewat email (default aktif); lainnya null.
        $this->assertTrue($intern->every(fn ($t) => in_array($t['type'], \App\Services\NotificationPreferences::EMAIL_TYPES, true) ? $t['email'] === true : $t['email'] === null));

        $mentor = collect($this->actingAs($this->rudy->user)->getJson('/notifications/preferences')->json('data.types'));
        $this->assertNotNull($mentor->firstWhere('type', 'task_in_review'));
        $this->assertNull($mentor->firstWhere('type', 'task_assigned'));

        $admin = collect($this->actingAs($this->admin)->getJson('/notifications/preferences')->json('data.types'));
        $this->assertNotNull($admin->firstWhere('type', 'intern_without_mentor'));
    }

    public function test_jenis_yang_dimatikan_tidak_dikirim_lagi(): void
    {
        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['task_assigned' => ['in_app' => false]]])
            ->assertOk();

        $types = collect($this->actingAs($this->ahmad->user)->getJson('/notifications/preferences')->json('data.types'));
        $this->assertFalse($types->firstWhere('type', 'task_assigned')['in_app']);
        $this->assertTrue($types->firstWhere('type', 'task_commented')['in_app']);

        $id = $this->makeTaskForAhmad();
        $this->assertSame(0, $this->notificationCount($this->ahmad->user, 'App\Notifications\TaskAssignedNotification'));

        // jenis lain tetap terkirim, dan Siti tidak terpengaruh pengaturan Ahmad
        $this->actingAs($this->rudy->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => 'Cek ya'])->assertCreated();
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, 'App\Notifications\TaskCommentedNotification'));

        $this->postTask($this->tasmil->user, $this->spSiti, ['assignee_ids' => [$this->siti->id]])->assertCreated();
        $this->assertSame(1, $this->notificationCount($this->siti->user, 'App\Notifications\TaskAssignedNotification'));

        // dinyalakan lagi
        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['task_assigned' => ['in_app' => true]]])->assertOk();
        $this->makeTaskForAhmad();
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, 'App\Notifications\TaskAssignedNotification'));
    }

    public function test_pengaturan_menolak_email_jenis_asing_dan_jenis_role_lain(): void
    {
        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['task_assigned' => ['in_app' => true, 'email' => true]]])
            ->assertUnprocessable()->assertJsonValidationErrors('types.task_assigned.email');

        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['intern_joined' => ['in_app' => false]]])
            ->assertUnprocessable()->assertJsonValidationErrors('types.intern_joined');

        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', ['types' => ['ngawur' => ['in_app' => false]]])
            ->assertUnprocessable();

        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', [])->assertUnprocessable();
    }

    public function test_pengaturan_menampilkan_channel_yang_dibisukan(): void
    {
        $this->actingAs($this->ahmad->user)->patchJson("/workspace/channels/{$this->umumGta->id}/mute", ['is_muted' => true])->assertOk();

        $muted = $this->actingAs($this->ahmad->user)->getJson('/notifications/preferences')->json('data.muted_channels');

        $this->assertSame([[
            'id' => $this->umumGta->id,
            'name' => $this->umumGta->name,
            'workspace' => ['id' => $this->wsGta->id, 'name' => $this->wsGta->name, 'url' => route('workspace.show', $this->wsGta)],
        ]], $muted);
    }

    public function test_mention_tetap_menotifikasi_walau_channel_dibisukan(): void
    {
        $this->actingAs($this->ahmad->user)->patchJson("/workspace/channels/{$this->umumGta->id}/mute", ['is_muted' => true])->assertOk();

        $this->sendMessage($this->rudy->user, $this->umumGta, "Halo @{$this->ahmad->user->name}")->assertCreated();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, 'App\Notifications\MessageMentionNotification'));
    }
}
