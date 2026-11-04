<?php

namespace Tests\Feature\Workspace;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Bentuk request/response channel dan pesan sesuai kontrak frontend. */
class ChannelContractTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        Storage::fake('local');
    }

    private function messages($user, Channel $channel, string $query = '')
    {
        return $this->actingAs($user)->getJson("/workspace/channels/{$channel->id}/messages" . ($query ? "?{$query}" : ''));
    }

    public function test_muat_awal_membawa_server_time_dan_has_more_before(): void
    {
        $this->sendMessage($this->ahmad->user, $this->umumGta, 'halo')->assertCreated();

        $response = $this->messages($this->rudy->user, $this->umumGta)->assertOk();

        $this->assertNotEmpty($response->json('meta.server_time'));
        $this->assertFalse($response->json('meta.has_more_before'));
        $this->assertArrayNotHasKey('updated', $response->json());
    }

    public function test_polling_membawa_pesan_baru_dan_pesan_lama_yang_berubah_di_updated(): void
    {
        $old = $this->sendMessage($this->ahmad->user, $this->umumGta, 'pesan lama')->json('data.id');
        $edited = $this->sendMessage($this->ahmad->user, $this->umumGta, 'akan diedit')->json('data.id');
        $deleted = $this->sendMessage($this->ahmad->user, $this->umumGta, 'akan dihapus')->json('data.id');

        $after = $deleted;
        $since = now()->addSecond()->toIso8601String();
        $this->travel(5)->seconds();

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/messages/{$edited}", ['body' => 'sudah diedit'])->assertOk();
        $this->actingAs($this->ahmad->user)->deleteJson("/workspace/messages/{$deleted}")->assertOk()->assertJsonPath('data.is_deleted', true);
        $new = $this->sendMessage($this->rudy->user, $this->umumGta, 'pesan baru')->json('data.id');

        $response = $this->messages($this->siti->user, $this->umumGta, "after={$after}&since=" . urlencode($since))->assertOk();

        $this->assertSame([$new], collect($response->json('data'))->pluck('id')->all());
        $updated = collect($response->json('updated'));
        $this->assertEqualsCanonicalizing([$edited, $deleted], $updated->pluck('id')->all());
        $this->assertTrue($updated->firstWhere('id', $deleted)['is_deleted']);
        $this->assertNotNull($updated->firstWhere('id', $edited)['edited_at']);
        $this->assertNotContains($old, $updated->pluck('id')->all());
    }

    public function test_balasan_baru_membawa_pesan_induk_di_updated_dan_tidak_dihitung_belum_dibaca(): void
    {
        $parent = $this->sendMessage($this->ahmad->user, $this->umumGta, 'induk')->json('data.id');
        $since = now()->addSecond()->toIso8601String();
        $this->travel(5)->seconds();

        $reply = $this->sendMessage($this->siti->user, $this->umumGta, 'balasan', ['parent_id' => $parent])->json('data.id');

        $response = $this->messages($this->rudy->user, $this->umumGta, "after={$parent}&since=" . urlencode($since))->assertOk();

        $this->assertSame([$reply], collect($response->json('data'))->pluck('id')->all());
        $this->assertSame([$parent], collect($response->json('updated'))->pluck('id')->all());
        $this->assertSame(1, $response->json('updated.0.reply_count'));
        $this->assertNotNull($response->json('updated.0.last_reply_at'));

        $before = collect($this->actingAs($this->rudy->user)->getJson('/workspace/channels')->json('data'))->firstWhere('id', $this->umumGta->id)['unread_count'];
        $this->sendMessage($this->siti->user, $this->umumGta, 'balasan lagi', ['parent_id' => $parent])->assertCreated();
        $after = collect($this->actingAs($this->rudy->user)->getJson('/workspace/channels')->json('data'))->firstWhere('id', $this->umumGta->id)['unread_count'];

        $this->assertSame($before, $after, 'balasan thread tidak menambah belum dibaca');
    }

    public function test_replies_mengembalikan_balasan_dan_induk(): void
    {
        $parent = $this->sendMessage($this->ahmad->user, $this->umumGta, 'induk')->json('data.id');
        $r1 = $this->sendMessage($this->siti->user, $this->umumGta, 'satu', ['parent_id' => $parent])->json('data.id');
        $r2 = $this->sendMessage($this->rudy->user, $this->umumGta, 'dua', ['parent_id' => $parent])->json('data.id');

        $response = $this->actingAs($this->rudy->user)->getJson("/workspace/messages/{$parent}/replies")->assertOk();

        $this->assertSame([$r1, $r2], collect($response->json('data'))->pluck('id')->all());
        $response->assertJsonPath('parent.id', $parent)->assertJsonPath('parent.reply_count', 2);

        $this->actingAs($this->rudy->user)->getJson("/workspace/messages/{$r1}/replies")->assertNotFound();
        $this->actingAs($this->budi->user)->getJson("/workspace/messages/{$parent}/replies")->assertForbidden();
    }

    public function test_kirim_pesan_dengan_attachment_ids(): void
    {
        $upload = $this->actingAs($this->ahmad->user)->post('/workspace/attachments', [
            'file' => UploadedFile::fake()->create('laporan.pdf', 5, 'application/pdf'),
            'workspace_id' => $this->wsGta->id,
        ], ['Accept' => 'application/json'])->assertCreated()->json('data.id');

        $this->sendMessage($this->ahmad->user, $this->umumGta, '', ['attachment_ids' => [$upload]])
            ->assertCreated()
            ->assertJsonCount(1, 'data.attachments')
            ->assertJsonPath('data.attachments.0.original_name', 'laporan.pdf');

        $this->sendMessage($this->ahmad->user, $this->umumGta, '')->assertUnprocessable();
    }

    public function test_reaksi_toggle_dengan_emoji_terdaftar(): void
    {
        $id = $this->sendMessage($this->ahmad->user, $this->umumGta, 'selamat')->json('data.id');

        $this->actingAs($this->rudy->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '🎉'])
            ->assertOk()->assertJsonPath('data', [['emoji' => '🎉', 'count' => 1, 'me' => true]]);

        $this->actingAs($this->siti->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '🎉'])
            ->assertJsonPath('data.0.count', 2)->assertJsonPath('data.0.me', true);

        $list = $this->messages($this->ahmad->user, $this->umumGta)->json('data');
        $this->assertSame([['emoji' => '🎉', 'count' => 2, 'me' => false]], collect($list)->firstWhere('id', $id)['reactions']);

        $this->actingAs($this->rudy->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '🎉'])
            ->assertJsonPath('data.0.count', 1);

        $this->actingAs($this->rudy->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '💩'])->assertUnprocessable();
        $this->actingAs($this->budi->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '👍'])->assertForbidden();
    }

    public function test_intern_boleh_bereaksi_di_pengumuman_walau_tidak_boleh_posting(): void
    {
        $id = $this->sendMessage($this->rudy->user, $this->announcement, 'Libur')->assertCreated()->json('data.id');

        $this->sendMessage($this->ahmad->user, $this->announcement, 'balas')->assertForbidden();
        $this->actingAs($this->ahmad->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '👍'])
            ->assertOk()->assertJsonPath('data.0.me', true);
    }

    public function test_reaksi_ditolak_di_channel_arsip_dan_perubahannya_ikut_polling(): void
    {
        $id = $this->sendMessage($this->ahmad->user, $this->umumGta, 'tes')->json('data.id');
        $since = now()->addSecond()->toIso8601String();
        $this->travel(5)->seconds();

        $this->actingAs($this->rudy->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '✅'])->assertOk();

        $updated = $this->messages($this->ahmad->user, $this->umumGta, "after={$id}&since=" . urlencode($since))->json('updated');
        $this->assertSame([$id], collect($updated)->pluck('id')->all());

        $this->wsGta->update(['is_archived' => true]);
        $this->actingAs($this->rudy->user)->postJson("/workspace/messages/{$id}/reactions", ['emoji' => '🔥'])->assertForbidden();
    }

    public function test_pin_hanya_pesan_utama_dan_pinned_terbaru_dulu(): void
    {
        $a = $this->sendMessage($this->ahmad->user, $this->umumGta, 'A')->json('data.id');
        $b = $this->sendMessage($this->ahmad->user, $this->umumGta, 'B')->json('data.id');
        $reply = $this->sendMessage($this->siti->user, $this->umumGta, 'balas', ['parent_id' => $a])->json('data.id');

        $this->actingAs($this->rudy->user)->patchJson("/workspace/messages/{$reply}/pin", ['is_pinned' => true])->assertUnprocessable();
        $this->actingAs($this->rudy->user)->patchJson("/workspace/messages/{$a}/pin", ['is_pinned' => true])->assertOk()->assertJsonPath('data.is_pinned', true);
        $this->actingAs($this->rudy->user)->patchJson("/workspace/messages/{$b}/pin", ['is_pinned' => true])->assertOk();

        $this->assertSame([$b, $a], collect($this->messages($this->rudy->user, $this->umumGta, 'pinned=1')->json('data'))->pluck('id')->all());

        $channel = collect($this->actingAs($this->rudy->user)->getJson('/workspace/channels')->json('data'))->firstWhere('id', $this->umumGta->id);
        $this->assertSame(2, $channel['pinned_count']);
    }

    public function test_pencarian_membawa_total_dan_hasil_terbaru_dulu(): void
    {
        $first = $this->sendMessage($this->ahmad->user, $this->umumGta, 'rapat senin')->json('data.id');
        $second = $this->sendMessage($this->ahmad->user, $this->umumGta, 'rapat selasa')->json('data.id');
        $this->sendMessage($this->ahmad->user, $this->umumGta, 'makan siang');

        $response = $this->messages($this->rudy->user, $this->umumGta, 'q=rapat')->assertOk();

        $this->assertSame([$second, $first], collect($response->json('data'))->pluck('id')->all());
        $response->assertJsonPath('meta.total', 2);
    }

    public function test_mute_channel_mengubah_badge_dan_total_belum_dibaca(): void
    {
        $this->sendMessage($this->ahmad->user, $this->umumGta, 'halo');

        $response = $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$this->umumGta->id}/mute", ['is_muted' => true])
            ->assertOk()
            ->assertJsonPath('data.is_muted', true);
        $this->assertGreaterThan(0, $response->json('data.unread_count'), 'badge tetap dihitung, hanya tampil abu-abu');

        $list = $this->actingAs($this->rudy->user)->getJson('/workspace/channels');
        $muted = collect($list->json('data'))->where('is_muted', true)->sum('unread_count');
        $others = collect($list->json('data'))->where('is_muted', false)->sum('unread_count');
        $this->assertSame($others, $list->json('meta.total_unread'));
        $this->assertGreaterThan(0, $muted);

        $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$this->umumGta->id}/mute", ['is_muted' => false])
            ->assertJsonPath('data.is_muted', false);

        // admin yang bukan anggota tidak punya baris untuk dibisukan
        $this->actingAs($this->admin)->patchJson("/workspace/channels/{$this->umumGta->id}/mute", ['is_muted' => true])->assertForbidden();
    }

    public function test_buat_channel_privat_butuh_anggota_lain_dan_nama_unik_global(): void
    {
        $url = "/workspace/{$this->wsGta->id}/channels";

        $this->actingAs($this->rudy->user)->postJson($url, ['name' => 'rahasia', 'is_private' => true, 'member_ids' => [$this->rudy->user_id]])
            ->assertUnprocessable()->assertJsonValidationErrors('member_ids');

        $this->actingAs($this->rudy->user)->postJson($url, ['name' => 'a'])->assertUnprocessable();
        $this->actingAs($this->rudy->user)->postJson($url, ['name' => str_repeat('a', 41)])->assertUnprocessable();
        $this->actingAs($this->rudy->user)->postJson($url, ['name' => $this->announcement->name])->assertUnprocessable();

        $this->actingAs($this->rudy->user)->postJson($url, [
            'name' => 'proyek-x',
            'description' => 'Diskusi proyek X',
            'is_private' => true,
            'member_ids' => [$this->rudy->user_id, $this->ahmad->user_id],
        ])->assertCreated()
            ->assertJsonPath('data.name', 'proyek-x')
            ->assertJsonPath('data.description', 'Diskusi proyek X')
            ->assertJsonPath('data.type', 'topic')
            ->assertJsonPath('data.can.post', true);
    }

    public function test_ubah_info_dan_arsipkan_channel_topik(): void
    {
        $id = $this->actingAs($this->rudy->user)->postJson("/workspace/{$this->wsGta->id}/channels", ['name' => 'lama'])->json('data.id');

        $this->actingAs($this->ahmad->user)->patchJson("/workspace/channels/{$id}", ['description' => 'x'])->assertForbidden();

        $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$id}", ['name' => 'baru', 'description' => 'Info baru'])
            ->assertOk()->assertJsonPath('data.name', 'baru')->assertJsonPath('data.description', 'Info baru');

        $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$id}", ['is_archived' => true])
            ->assertOk()->assertJsonPath('data.is_archived', true)->assertJsonPath('data.can.post', false);

        // channel sistem tidak bisa diganti nama atau diarsipkan
        $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$this->umumGta->id}", ['is_archived' => true])->assertUnprocessable();
        $this->actingAs($this->rudy->user)->patchJson("/workspace/channels/{$this->umumGta->id}", ['description' => 'Info'])->assertOk();
    }

    public function test_dm_membawa_daftar_anggota(): void
    {
        $channel = collect($this->actingAs($this->ahmad->user)->getJson('/workspace/channels')->json('data'))->firstWhere('id', $this->dmAhmad->id);

        $this->assertSame('direct', $channel['type']);
        $this->assertEqualsCanonicalizing([$this->ahmad->user_id, $this->rudy->user_id], collect($channel['members'])->pluck('id')->all());
        $this->assertSame(['intern', 'mentor'], collect($channel['members'])->pluck('role')->sort()->values()->all());

        $team = collect($this->actingAs($this->ahmad->user)->getJson('/workspace/channels')->json('data'))->firstWhere('id', $this->umumGta->id);
        $this->assertNull($team['members']);
    }
}
