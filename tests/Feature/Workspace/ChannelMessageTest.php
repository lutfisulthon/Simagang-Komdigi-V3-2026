<?php

namespace Tests\Feature\Workspace;

use App\Models\Attachment;
use App\Models\Channel;
use App\Models\Message;
use App\Notifications\AnnouncementNotification;
use App\Notifications\MessageMentionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class ChannelMessageTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function listIds(\App\Models\User $as, Channel $channel, string $query = ''): array
    {
        return $this->actingAs($as)->getJson("/workspace/channels/{$channel->id}/messages{$query}")->assertOk()->json('data.*.id');
    }

    public function test_anggota_mengirim_dan_membaca_pesan(): void
    {
        $id = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Halo semua')
            ->assertCreated()
            ->assertJsonPath('data.body', 'Halo semua')
            ->assertJsonPath('data.user.name', 'Ahmad Fauzi')
            ->json('data.id');

        $this->assertContains($id, $this->listIds($this->siti->user, $this->umumGta));
        $this->sendMessage($this->ahmad->user, $this->umumGta, '   ')->assertUnprocessable();
        $this->sendMessage($this->ahmad->user, $this->umumGta, str_repeat('x', 5001))->assertUnprocessable();
    }

    public function test_polling_after_hanya_mengembalikan_pesan_baru(): void
    {
        $first = $this->sendMessage($this->ahmad->user, $this->umumGta, 'satu')->json('data.id');

        $this->assertSame([], $this->listIds($this->siti->user, $this->umumGta, "?after={$first}"));

        $second = $this->sendMessage($this->rudy->user, $this->umumGta, 'dua')->json('data.id');
        $this->assertSame([$second], $this->listIds($this->siti->user, $this->umumGta, "?after={$first}"));
    }

    public function test_riwayat_dimuat_50_per_halaman_dengan_before(): void
    {
        $rows = [];
        for ($i = 1; $i <= 55; $i++) {
            $rows[] = ['channel_id' => $this->umumGta->id, 'user_id' => $this->rudy->user_id, 'body' => "pesan {$i}", 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('messages')->insert($rows);

        $page = $this->actingAs($this->ahmad->user)->getJson("/workspace/channels/{$this->umumGta->id}/messages")->assertOk();
        $ids = $page->json('data.*.id');

        $page->assertJsonPath('meta.has_more', true);
        $this->assertCount(50, $ids);
        $this->assertSame($ids, collect($ids)->sort()->values()->all(), 'urut dari lama ke baru');

        $older = $this->actingAs($this->ahmad->user)->getJson("/workspace/channels/{$this->umumGta->id}/messages?before={$ids[0]}");
        $older->assertJsonPath('meta.has_more', false);
        $this->assertGreaterThan(0, count($older->json('data')));
        $this->assertLessThan($ids[0], max($older->json('data.*.id')));
    }

    public function test_jumlah_belum_dibaca_menghitung_pesan_orang_lain_dan_hilang_setelah_dibuka(): void
    {
        // kosongkan pesan sambutan otomatis supaya hitungan dimulai dari nol
        foreach ([$this->ahmad, $this->siti] as $intern) {
            $this->actingAs($intern->user)->postJson("/workspace/channels/{$this->umumGta->id}/read")->assertOk();
        }

        $this->sendMessage($this->ahmad->user, $this->umumGta, 'satu');
        $this->sendMessage($this->rudy->user, $this->umumGta, 'dua');

        $row = fn ($user) => collect($this->actingAs($user)->getJson('/workspace/channels')->json('data'))->firstWhere('id', $this->umumGta->id);

        $this->assertSame(2, $row($this->siti->user)['unread_count']);
        $this->assertSame(1, $row($this->ahmad->user)['unread_count'], 'pesan sendiri tidak dihitung');
        $list = $this->actingAs($this->siti->user)->getJson('/workspace/channels');
        $this->assertSame(array_sum($list->json('data.*.unread_count')), $list->json('meta.total_unread'));

        $this->actingAs($this->siti->user)->postJson("/workspace/channels/{$this->umumGta->id}/read")
            ->assertOk()->assertJsonPath('unread_count', 0);
        $this->assertSame(0, $row($this->siti->user)['unread_count']);

        // pesan baru setelah dibaca dihitung lagi; penanda tidak pernah mundur
        $this->sendMessage($this->rudy->user, $this->umumGta, 'tiga');
        $this->assertSame(1, $row($this->siti->user)['unread_count']);
    }

    public function test_tandai_dibaca_dengan_message_id_dari_channel_lain_ditolak(): void
    {
        $other = $this->sendMessage($this->rudy->user, $this->announcement, 'info')->json('data.id');

        $this->actingAs($this->siti->user)
            ->postJson("/workspace/channels/{$this->umumGta->id}/read", ['message_id' => $other])
            ->assertUnprocessable();
    }

    public function test_balasan_thread_tidak_muncul_di_channel_utama(): void
    {
        $parent = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Pesan utama')->json('data.id');
        $reply = $this->sendMessage($this->siti->user, $this->umumGta, 'Balasan', ['parent_id' => $parent])
            ->assertCreated()->assertJsonPath('data.parent_id', $parent)->json('data.id');

        $main = $this->actingAs($this->ahmad->user)->getJson("/workspace/channels/{$this->umumGta->id}/messages");
        $this->assertNotContains($reply, $main->json('data.*.id'));
        $this->assertSame(1, collect($main->json('data'))->firstWhere('id', $parent)['replies_count']);

        $this->assertSame([$reply], $this->listIds($this->ahmad->user, $this->umumGta, "?parent_id={$parent}"));
        $this->assertContains($reply, $this->listIds($this->ahmad->user, $this->umumGta, "?after={$parent}"), 'polling memuat balasan');

        // thread satu tingkat, dan parent harus dari channel yang sama
        $this->sendMessage($this->siti->user, $this->umumGta, 'balas balasan', ['parent_id' => $reply])->assertUnprocessable();
        $this->sendMessage($this->rudy->user, $this->announcement, 'x', ['parent_id' => $parent])->assertUnprocessable();
    }

    public function test_mention_nama_menyimpan_dan_memberi_notifikasi_sekali(): void
    {
        $response = $this->sendMessage($this->rudy->user, $this->umumGta, 'Halo @Ahmad Fauzi tolong cek')->assertCreated();

        $this->assertSame([$this->ahmad->user_id], $response->json('data.mentions'));
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, MessageMentionNotification::class));
        $this->assertSame(0, $this->notificationCount($this->rudy->user, MessageMentionNotification::class), 'penulis tidak dinotifikasi');
        $this->assertSame(0, $this->notificationCount($this->siti->user, MessageMentionNotification::class));

        // edit tidak mengirim ulang ke yang sudah di-mention, tapi mention baru tetap dinotifikasi
        $id = $response->json('data.id');
        $this->actingAs($this->rudy->user)
            ->patchJson("/workspace/messages/{$id}", ['body' => 'Halo @Ahmad Fauzi dan @Siti Nurhaliza'])->assertOk();
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, MessageMentionNotification::class));
        $this->assertSame(1, $this->notificationCount($this->siti->user, MessageMentionNotification::class));
    }

    public function test_mention_bukan_anggota_channel_diabaikan(): void
    {
        $this->sendMessage($this->rudy->user, $this->umumGta, 'Halo @Budi Santoso')->assertCreated()->assertJsonPath('data.mentions', []);

        $this->assertSame(0, $this->notificationCount($this->budi->user, MessageMentionNotification::class));
    }

    public function test_mention_lewat_id_eksplisit_dari_autocomplete(): void
    {
        $this->sendMessage($this->rudy->user, $this->umumGta, 'Tolong dicek', ['mention_ids' => [$this->siti->user_id, $this->budi->user_id]])
            ->assertCreated()
            ->assertJsonCount(1, 'data.mentions');

        $this->assertSame(1, $this->notificationCount($this->siti->user, MessageMentionNotification::class));
        $this->assertSame(0, $this->notificationCount($this->budi->user, MessageMentionNotification::class));
    }

    public function test_mention_channel_hanya_untuk_mentor_dan_admin(): void
    {
        $this->sendMessage($this->ahmad->user, $this->umumGta, '@channel rapat')->assertForbidden();
        $this->sendMessage($this->ahmad->user, $this->umumGta, 'kirim ke test@channel.com')->assertCreated();

        $response = $this->sendMessage($this->rudy->user, $this->umumGta, '@channel rapat jam 10')->assertCreated();

        $this->assertCount($this->umumGta->members()->count() - 1, $response->json('data.mentions'));
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, MessageMentionNotification::class));
        $this->assertSame(1, $this->notificationCount($this->siti->user, MessageMentionNotification::class));
    }

    public function test_rujukan_tugas_dikembalikan_sebagai_task_refs(): void
    {
        $taskId = $this->postTask($this->rudy->user, $this->wsGta, ['assignee_ids' => [$this->ahmad->id]])->json('data.0.id');

        $response = $this->sendMessage($this->rudy->user, $this->umumGta, 'Cek #T-1 dan #T-99 ya')->assertCreated();

        $this->assertSame([['code' => 'T-1', 'task_id' => $taskId]], $response->json('data.task_refs'));
    }

    public function test_edit_hanya_pesan_sendiri_termasuk_untuk_admin(): void
    {
        $id = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Asli')->json('data.id');

        $this->actingAs($this->siti->user)->patchJson("/workspace/messages/{$id}", ['body' => 'hack'])->assertForbidden();
        $this->actingAs($this->rudy->user)->patchJson("/workspace/messages/{$id}", ['body' => 'hack'])->assertForbidden();
        $this->actingAs($this->admin)->patchJson("/workspace/messages/{$id}", ['body' => 'hack'])->assertForbidden();

        $edited = $this->actingAs($this->ahmad->user)->patchJson("/workspace/messages/{$id}", ['body' => 'Revisi'])->assertOk();
        $edited->assertJsonPath('data.body', 'Revisi');
        $this->assertNotNull($edited->json('data.edited_at'));
        $this->assertSame('Revisi', Message::find($id)->body);
    }

    public function test_hapus_pesan_soft_delete_tampil_sebagai_dihapus(): void
    {
        $own = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Rahasia')->json('data.id');
        $other = $this->sendMessage($this->siti->user, $this->umumGta, 'Pesan Siti')->json('data.id');

        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/messages/{$other}")->assertForbidden();
        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/messages/{$own}")->assertOk()->assertJsonPath('data.is_deleted', true)->assertJsonPath('data.body', null);
        $this->actingAs($this->rudy->user)->deleteJson("/workspace/messages/{$other}")->assertOk()->assertJsonPath('data.is_deleted', true);
        // hapus ulang idempoten untuk yang berhak; orang luar tetap ditolak
        $this->actingAs($this->rudy->user)->deleteJson("/workspace/messages/{$other}")->assertOk()->assertJsonPath('data.is_deleted', true);
        $this->actingAs($this->herman->user)->deleteJson("/workspace/messages/{$other}")->assertForbidden();

        $this->assertSoftDeleted('messages', ['id' => $own]);

        $item = collect($this->actingAs($this->siti->user)->getJson("/workspace/channels/{$this->umumGta->id}/messages")->json('data'))->firstWhere('id', $own);
        $this->assertTrue($item['is_deleted']);
        $this->assertNull($item['body']);
    }

    public function test_pin_hanya_moderator_dan_bisa_difilter(): void
    {
        $id = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Penting')->json('data.id');

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/messages/{$id}/pin", ['is_pinned' => true])->assertForbidden();
        $this->actingAs($this->tasmil->user)->patchJson("/workspace/messages/{$id}/pin", ['is_pinned' => true])
            ->assertOk()->assertJsonPath('data.is_pinned', true);

        $this->assertSame([$id], $this->listIds($this->siti->user, $this->umumGta, '?pinned=1'));

        $this->actingAs($this->rudy->user)->patchJson("/workspace/messages/{$id}/pin", ['is_pinned' => false])->assertOk();
        $this->assertSame([], $this->listIds($this->siti->user, $this->umumGta, '?pinned=1'));
    }

    public function test_pencarian_pesan_dan_wildcard_di_escape(): void
    {
        $a = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Laporan mingguan sudah selesai')->json('data.id');
        $this->sendMessage($this->ahmad->user, $this->umumGta, 'Makan siang');
        $deleted = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Laporan lama')->json('data.id');
        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/messages/{$deleted}");

        $this->assertSame([$a], $this->listIds($this->siti->user, $this->umumGta, '?q=mingguan'));
        $this->assertSame([$a], $this->listIds($this->siti->user, $this->umumGta, '?q=Laporan'), 'pesan terhapus tidak ikut');
        $this->assertSame([], $this->listIds($this->siti->user, $this->umumGta, '?q=%25'));
        $this->assertSame([], $this->listIds($this->siti->user, $this->umumGta, '?q=_____'));
    }

    public function test_pengumuman_baru_menotifikasi_semua_anggota_kecuali_penulis(): void
    {
        $expected = $this->announcement->members()->count() - 1;

        $this->sendMessage($this->rudy->user, $this->announcement, 'Libur nasional besok')->assertCreated();

        $this->assertSame($expected, DB::table('notifications')->where('type', AnnouncementNotification::class)->count());
        $this->assertSame(0, $this->notificationCount($this->rudy->user, AnnouncementNotification::class));
        $this->assertSame(1, $this->notificationCount($this->ahmad->user, AnnouncementNotification::class));

        // balasan thread di pengumuman bukan pengumuman baru
        $parent = Message::where('channel_id', $this->announcement->id)->value('id');
        $this->sendMessage($this->rudy->user, $this->announcement, 'balasan', ['parent_id' => $parent]);
        $this->assertSame($expected, DB::table('notifications')->where('type', AnnouncementNotification::class)->count());
    }

    public function test_mentor_membuat_channel_topik_dengan_anggota_pilihan(): void
    {
        $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/channels", ['name' => 'Proyek Web'])->assertUnprocessable();
        $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/channels", [
            'name' => 'proyek-web', 'member_ids' => [$this->budi->user_id],
        ])->assertUnprocessable();

        $id = $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/channels", [
            'name' => 'proyek-web', 'member_ids' => [$this->ahmad->user_id],
        ])->assertCreated()->assertJsonPath('data.type', 'topic')->json('data.id');

        $topic = Channel::findOrFail($id);
        $this->assertEqualsCanonicalizing([$this->ahmad->user_id, $this->rudy->user_id], $topic->members()->pluck('users.id')->all());

        $this->sendMessage($this->ahmad->user, $topic, 'Halo')->assertCreated();
        $this->actingAs($this->siti->user)->getJson("/workspace/channels/{$id}/messages")->assertForbidden();

        // nama unik per workspace
        $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/channels", ['name' => 'proyek-web'])->assertUnprocessable();
    }

    public function test_channel_topik_tanpa_daftar_anggota_berisi_semua_anggota_workspace(): void
    {
        $id = $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/channels", ['name' => 'semua'])->json('data.id');

        $this->assertEqualsCanonicalizing(
            [$this->rudy->user_id, $this->tasmil->user_id, $this->ahmad->user_id, $this->siti->user_id],
            Channel::find($id)->members()->pluck('users.id')->all()
        );
    }

    public function test_arsip_channel_hanya_topik_dan_membuatnya_read_only(): void
    {
        $id = $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/channels", ['name' => 'sementara'])->json('data.id');

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/channels/{$id}/archive")->assertForbidden();
        $this->actingAs($this->herman->user)->patchJson("/workspace/channels/{$id}/archive")->assertForbidden();
        $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$this->umumGta->id}/archive")->assertUnprocessable();
        $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$id}/archive")->assertOk();

        $this->sendMessage($this->ahmad->user, Channel::find($id), 'masih bisa?')->assertForbidden();
        $this->assertNotContains($id, collect($this->actingAs($this->ahmad->user)->getJson('/workspace/channels')->json('data'))->pluck('id')->all());
        $this->assertContains($id, collect($this->actingAs($this->ahmad->user)->getJson('/workspace/channels?include_archived=1')->json('data'))->pluck('id')->all());
    }

    public function test_daftar_channel_hanya_milik_user_dan_admin_bisa_melihat_semua_di_workspace(): void
    {
        $ids = collect($this->actingAs($this->ahmad->user)->getJson('/workspace/channels')->json('data'))->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$this->umumGta->id, $this->dmAhmad->id, $this->announcement->id], $ids);

        $adminIds = collect($this->actingAs($this->admin)->getJson("/workspace/channels?workspace_id={$this->spSiti->id}")->json('data'))->pluck('id')->all();
        $this->assertSame([$this->dmSiti->id], $adminIds);
    }

    public function test_lampiran_gambar_dikompres_dan_unduhan_dicek_policy(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->ahmad->user)->post("/workspace/channels/{$this->umumGta->id}/messages", [
            'body' => 'ada gambar',
            'files' => [UploadedFile::fake()->image('foto.jpg', 3000, 2000)],
        ], ['Accept' => 'application/json'])->assertCreated();

        $attachment = Attachment::findOrFail($response->json('data.attachments.0.id'));
        [$width, $height] = getimagesize(Storage::disk('local')->path($attachment->path));
        $this->assertLessThanOrEqual(1600, $width);
        $this->assertLessThanOrEqual(1600, $height);

        $this->actingAs($this->siti->user)->get("/workspace/attachments/{$attachment->id}")->assertOk();
        $this->actingAs($this->budi->user)->get("/workspace/attachments/{$attachment->id}")->assertForbidden();
    }

    public function test_batas_unggah_10_file_per_menit(): void
    {
        Storage::fake('local');
        $send = fn (int $count) => $this->actingAs($this->ahmad->user)->post("/workspace/channels/{$this->umumGta->id}/messages", [
            'body' => 'berkas',
            'files' => array_map(fn ($i) => UploadedFile::fake()->create("d{$i}.pdf", 5, 'application/pdf'), range(1, $count)),
        ], ['Accept' => 'application/json']);

        $send(6)->assertCreated();
        $send(6)->assertStatus(429);
        $send(4)->assertCreated();
        $send(1)->assertStatus(429);
        $this->assertSame(10, Attachment::count());
    }

    public function test_batas_30_pesan_per_menit(): void
    {
        $codes = [];
        for ($i = 0; $i < 32; $i++) {
            $codes[] = $this->sendMessage($this->tasmil->user, $this->umumGta, "pesan {$i}")->getStatusCode();
        }

        $this->assertSame(30, count(array_filter($codes, fn ($c) => $c === 201)));
        $this->assertSame(429, $codes[30]);

        // batas per user, bukan global
        $this->sendMessage($this->rudy->user, $this->umumGta, 'user lain tetap bisa')->assertCreated();
    }

    public function test_pesan_dari_user_yang_sudah_dihapus_tetap_tampil_tanpa_error(): void
    {
        $mentioned = $this->sendMessage($this->ahmad->user, $this->umumGta, 'Pesan lama Ahmad')->json('data.id');
        $this->sendMessage($this->siti->user, $this->umumGta, 'Balasan', ['parent_id' => $mentioned]);

        $this->ahmad->delete();
        $this->ahmad->user->delete();

        $response = $this->actingAs($this->rudy->user)->getJson("/workspace/channels/{$this->umumGta->id}/messages")->assertOk();
        $item = collect($response->json('data'))->firstWhere('id', $mentioned);

        $this->assertSame('Pesan lama Ahmad', $item['body']);
        $this->assertSame('Pengguna dihapus', $item['user']['name']);
        $this->assertSame(1, $item['replies_count'], 'thread tetap utuh');
        $this->assertNull(Message::find($mentioned)->user_id);

        // pesan itu tidak bisa diedit siapa pun
        $this->actingAs($this->rudy->user)->patchJson("/workspace/messages/{$mentioned}", ['body' => 'x'])->assertForbidden();
    }

    public function test_body_html_merender_markdown_aman_mention_dan_rujukan_tugas(): void
    {
        $taskId = $this->makeTaskForAhmad();
        $body = "**tebal** cek #T-1 ya @{$this->ahmad->user->name} <script>x</script> @channel";

        $data = $this->sendMessage($this->rudy->user, $this->dmAhmad, $body)->assertCreated()->json('data');

        $this->assertStringContainsString('<strong>tebal</strong>', $data['body_html']);
        $this->assertStringContainsString('class="ws-task-ref" data-task-id="' . $taskId . '"', $data['body_html']);
        $this->assertStringContainsString('<span class="ws-mention" data-user-id="' . $this->ahmad->user_id . '">', $data['body_html']);
        $this->assertStringContainsString('ws-mention--all', $data['body_html']);
        $this->assertStringNotContainsString('<script>', $data['body_html']);
        $this->assertTrue($data['mentions_channel']);
        $this->assertSame(['edit' => true, 'delete' => true, 'pin' => true], $data['can']);
    }
}
