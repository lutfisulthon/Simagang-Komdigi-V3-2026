<?php

namespace Tests\Feature;

use App\Events\PublicChatEvent;
use App\Models\PublicChatMember;
use App\Models\PublicChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\WorkspaceFixtures;
use Tests\TestCase;

/** Daftar uji Chat Publik (docs/public-chat-backend.md bagian 6) ditambah moderasi admin. */
class PublicChatTest extends TestCase
{
    use RefreshDatabase, WorkspaceFixtures;

    private User $admin;
    private User $mentor;
    private User $mentor2;
    private User $intern;
    private User $institusi;
    private User $industri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->makeAdmin('Administrator');
        $this->mentor = $this->makeMentor('Herman')->user;
        $this->mentor2 = $this->makeMentor('Rudy')->user;
        $this->intern = $this->makeIntern('Budi Santoso')->user;
        $this->institusi = $this->makeUser('institusi', 'Kampus Uji');
        $this->industri = $this->makeUser('industri', 'PT Uji');
    }

    private function send(User $as, string $text, array $extra = []): TestResponse
    {
        $this->travel(1)->seconds(); // lewati jeda minimum 0,6 detik

        return $this->actingAs($as)->postJson('/public-chat/messages', ['text' => $text] + $extra);
    }

    private function list(User $as, string $query = ''): TestResponse
    {
        return $this->actingAs($as)->getJson('/public-chat/messages' . $query);
    }

    public function test_tamu_ditolak(): void
    {
        $this->getJson('/public-chat/messages')->assertUnauthorized();
        $this->postJson('/public-chat/messages', ['text' => 'halo'])->assertUnauthorized();
        $this->get('/chat-publik')->assertRedirect();
    }

    public function test_setiap_peran_dapat_membaca_dan_mengirim(): void
    {
        $expected = [
            [$this->admin, 'admin'],
            [$this->mentor, 'mentor'],
            [$this->intern, 'intern'],
            [$this->institusi, 'institusi'],
            [$this->industri, 'industri'],
        ];

        foreach ($expected as [$user, $role]) {
            $this->send($user, "Halo dari {$role}")
                ->assertCreated()
                ->assertJsonPath('message.uid', (string) $user->id)
                ->assertJsonPath('message.name', $user->name)
                ->assertJsonPath('message.role', $role);
            $this->list($user)->assertOk();
            $this->actingAs($user)->get('/chat-publik')->assertOk();
        }

        $this->assertSame(5, PublicChatMessage::count());
    }

    public function test_validasi_panjang_teks(): void
    {
        $this->send($this->intern, '')->assertUnprocessable();
        $this->send($this->intern, "   \n\t ")->assertUnprocessable();
        $this->send($this->intern, str_repeat('x', 10001))->assertUnprocessable();
        $this->send($this->intern, str_repeat('x', 10000))->assertCreated();
    }

    public function test_teks_dibersihkan_dan_html_disimpan_literal(): void
    {
        $text = $this->send($this->intern, "  <img src=x onerror=alert(1)>\x07\n\n\n\nbaris\u{202E}  ")
            ->assertCreated()
            ->json('message.text');

        $this->assertSame("<img src=x onerror=alert(1)>\n\nbaris", $text);
    }

    public function test_identitas_selalu_dari_user_login(): void
    {
        $this->send($this->intern, 'pesan', ['name' => 'Administrator', 'role' => 'admin', 'uid' => (string) $this->admin->id, 'user_id' => $this->admin->id])
            ->assertCreated()
            ->assertJsonPath('message.uid', (string) $this->intern->id)
            ->assertJsonPath('message.name', 'Budi Santoso')
            ->assertJsonPath('message.role', 'intern');
    }

    public function test_jeda_minimum_dan_batas_per_menit(): void
    {
        $this->send($this->intern, 'satu')->assertCreated();
        $this->actingAs($this->intern)->postJson('/public-chat/messages', ['text' => 'terlalu cepat'])->assertStatus(429);

        // mulai jendela satu menit yang baru, lalu 40 pesan berjeda 1 detik
        $this->freezeTime();
        $this->travel(61)->seconds();
        for ($i = 1; $i <= 40; $i++) {
            $this->travel(1)->seconds();
            $this->actingAs($this->intern)->postJson('/public-chat/messages', ['text' => "pesan {$i}"])->assertCreated();
        }

        $this->travel(1)->seconds();
        $this->actingAs($this->intern)->postJson('/public-chat/messages', ['text' => 'ke-41'])->assertStatus(429);
    }

    public function test_after_hanya_mengembalikan_pesan_baru_dan_riwayat_50(): void
    {
        $rows = [];
        for ($i = 1; $i <= 55; $i++) {
            $rows[] = ['user_id' => $this->mentor->id, 'text' => "pesan {$i}", 'created_at' => now(), 'updated_at' => now()];
        }
        PublicChatMessage::insert($rows);

        $ids = $this->list($this->intern)->assertOk()->json('messages.*.id');
        $this->assertCount(50, $ids);
        $this->assertSame($ids, collect($ids)->sortBy(fn ($id) => (int) $id)->values()->all());

        $last = end($ids);
        $this->assertSame([], $this->list($this->intern, "?after={$last}")->json('messages'));

        $new = $this->send($this->mentor, 'baru')->json('message.id');
        $this->assertSame([$new], $this->list($this->intern, "?after={$last}")->json('messages.*.id'));
    }

    public function test_pesan_disembunyikan_tidak_muncul(): void
    {
        $id = $this->send($this->intern, 'spam')->json('message.id');

        $this->actingAs($this->admin)->post("/admin/chat-publik/messages/{$id}/hide")->assertRedirect();

        $this->assertNotContains($id, $this->list($this->mentor)->json('messages.*.id'));
        $this->assertSame([], $this->list($this->mentor, '?after=0')->json('messages'));

        $this->actingAs($this->admin)->post("/admin/chat-publik/messages/{$id}/restore");
        $this->assertContains($id, $this->list($this->mentor)->json('messages.*.id'));
    }

    public function test_mentor_menyematkan_7_dan_30_hari(): void
    {
        $a = $this->send($this->mentor, 'Presentasi Jumat')->json('message.id');
        $b = $this->send($this->mentor, 'Libur nasional')->json('message.id');

        $this->actingAs($this->mentor)->postJson("/public-chat/messages/{$a}/pin", ['days' => 7])
            ->assertOk()
            ->assertJsonPath('pins.0.id', $a);
        $this->actingAs($this->mentor)->postJson("/public-chat/messages/{$b}/pin", ['days' => 30])
            ->assertOk()
            ->assertJsonCount(2, 'pins')
            ->assertJsonPath('pins.0.id', $b);

        foreach ([0, 1, 14, 'abc'] as $bad) {
            $this->actingAs($this->mentor)->postJson("/public-chat/messages/{$a}/pin", ['days' => $bad])->assertUnprocessable();
        }

        $pins = $this->list($this->intern)->json('pins');
        $this->assertCount(2, $pins);
        $this->assertArrayHasKey('pinned_until', $pins[0]);
    }

    public function test_mentor_mengirim_pesan_langsung_sebagai_pengumuman(): void
    {
        $this->send($this->mentor, 'Pengumuman', ['pin_days' => 7])
            ->assertCreated()
            ->assertJsonStructure(['message' => ['pinned_at', 'pinned_until']]);
        $this->assertCount(1, $this->list($this->intern)->json('pins'));

        $this->send($this->mentor, 'salah', ['pin_days' => 3])->assertUnprocessable();
        $this->send($this->intern, 'bukan mentor', ['pin_days' => 7])->assertForbidden();
    }

    public function test_selain_mentor_atau_pesan_orang_lain_tidak_boleh_menyematkan(): void
    {
        foreach ([$this->intern, $this->institusi, $this->industri, $this->admin] as $user) {
            $id = $this->send($user, 'pesan saya')->json('message.id');
            $this->actingAs($user)->postJson("/public-chat/messages/{$id}/pin", ['days' => 7])->assertForbidden();
        }

        $id = $this->send($this->mentor2, 'milik Rudy')->json('message.id');
        $this->actingAs($this->mentor)->postJson("/public-chat/messages/{$id}/pin", ['days' => 7])->assertForbidden();
    }

    public function test_maksimal_tiga_pengumuman_aktif(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->send($i === 3 ? $this->mentor2 : $this->mentor, "info {$i}", ['pin_days' => 7])->assertCreated();
        }

        $id = $this->send($this->mentor, 'info 4')->json('message.id');
        $this->actingAs($this->mentor)->postJson("/public-chat/messages/{$id}/pin", ['days' => 7])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Maksimal 3 pengumuman aktif. Hapus salah satu dulu.');
        $this->send($this->mentor, 'info 5', ['pin_days' => 30])->assertUnprocessable();

        // menyematkan ulang pengumuman yang sudah aktif (ubah durasi) tetap boleh
        $first = PublicChatMessage::orderBy('id')->first();
        $this->actingAs($this->mentor)->postJson("/public-chat/messages/{$first->id}/pin", ['days' => 30])->assertOk();
    }

    public function test_sematan_kedaluwarsa_hilang_dari_pins(): void
    {
        $this->send($this->mentor, 'Seminggu', ['pin_days' => 7]);
        $this->assertCount(1, $this->list($this->intern)->json('pins'));

        $this->travel(7)->days();
        $this->travel(1)->minutes();

        $this->assertSame([], $this->list($this->intern)->json('pins'));
    }

    public function test_hanya_penyemat_yang_dapat_menghapus_sematan(): void
    {
        $id = $this->send($this->mentor, 'Penting', ['pin_days' => 7])->json('message.id');

        $this->actingAs($this->mentor2)->deleteJson("/public-chat/messages/{$id}/pin")->assertForbidden();
        $this->actingAs($this->intern)->deleteJson("/public-chat/messages/{$id}/pin")->assertForbidden();

        $this->actingAs($this->mentor)->deleteJson("/public-chat/messages/{$id}/pin")
            ->assertOk()
            ->assertJsonPath('pins', []);

        $this->assertContains($id, $this->list($this->intern)->json('messages.*.id'));
    }

    public function test_jumlah_belum_dibaca_dan_penanda_dibaca(): void
    {
        $this->list($this->intern)->assertJsonPath('unread', 0);

        $this->send($this->mentor, 'satu');
        $this->send($this->mentor, 'dua');
        $this->send($this->intern, 'pesan sendiri tidak dihitung');

        $this->list($this->intern)->assertJsonPath('unread', 2);
        $this->list($this->intern, '?viewing=1')->assertJsonPath('unread', 0);
        $this->list($this->intern)->assertJsonPath('unread', 0);

        $this->send($this->mentor, 'tiga');
        $this->list($this->intern)->assertJsonPath('unread', 1);
        $this->actingAs($this->intern)->get('/chat-publik')->assertOk();
        $this->list($this->intern)->assertJsonPath('unread', 0);
    }

    public function test_user_baru_tidak_mewarisi_riwayat_sebagai_belum_dibaca(): void
    {
        $this->send($this->mentor, 'lama');
        $this->list($this->institusi)->assertJsonPath('unread', 0);
    }

    public function test_hitungan_online(): void
    {
        $this->list($this->intern);
        $this->list($this->mentor)->assertJsonPath('online', 2);

        $this->travel(5)->minutes();
        $this->list($this->mentor)->assertJsonPath('online', 1);
    }

    public function test_user_nonaktif_tidak_dapat_membaca_maupun_menulis(): void
    {
        $this->intern->intern->update(['is_active' => false]);

        $this->list($this->intern)->assertForbidden();
        $this->send($this->intern, 'halo')->assertForbidden();
        $this->actingAs($this->intern)->get('/chat-publik')->assertForbidden();
    }

    public function test_tautan_ditolak_kecuali_admin_dan_mentor(): void
    {
        foreach (['cek https://contoh.com', 'buka www.situs.net', 'daftar di hadiah.xyz/klaim'] as $text) {
            $this->send($this->intern, $text)->assertUnprocessable();
        }
        $this->send($this->intern, 'Rapat jam 9.30 di ruang 2.1')->assertCreated();
        $this->send($this->mentor, 'Materi: https://komdigi.go.id')->assertCreated();
        $this->send($this->admin, 'Info: https://komdigi.go.id')->assertCreated();
    }

    public function test_admin_membungkam_dan_mencabut_bungkam(): void
    {
        $this->actingAs($this->admin)->post("/admin/chat-publik/users/{$this->intern->id}/mute", ['days' => 7, 'reason' => 'spam'])->assertRedirect();

        $this->send($this->intern, 'halo')->assertForbidden();
        $this->list($this->intern)->assertOk(); // tetap bisa membaca

        $this->travel(8)->days();
        $this->send($this->intern, 'sudah bisa lagi')->assertCreated();

        $this->actingAs($this->admin)->post("/admin/chat-publik/users/{$this->intern->id}/mute", ['days' => 0]);
        $this->send($this->intern, 'dibungkam permanen')->assertForbidden();
        $this->actingAs($this->admin)->post("/admin/chat-publik/users/{$this->intern->id}/unmute");
        $this->send($this->intern, 'bebas')->assertCreated();

        $this->actingAs($this->admin)->post("/admin/chat-publik/users/{$this->admin->id}/mute", ['days' => 7]);
        $this->assertFalse(PublicChatMember::for($this->admin)->isMuted());
    }

    public function test_halaman_moderasi_hanya_untuk_admin(): void
    {
        $id = $this->send($this->intern, 'halo')->json('message.id');

        $this->actingAs($this->admin)->get('/admin/chat-publik')->assertOk()->assertSee('halo');
        $this->actingAs($this->mentor)->get('/admin/chat-publik')->assertForbidden();
        $this->actingAs($this->intern)->post("/admin/chat-publik/messages/{$id}/hide")->assertForbidden();
    }

    private function useReverb(string $host = '127.0.0.1', int $port = 8080): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'kunci-uji',
            'broadcasting.connections.reverb.secret' => 'rahasia-uji',
            'broadcasting.connections.reverb.app_id' => '1',
            'broadcasting.connections.reverb.options.host' => $host,
            'broadcasting.connections.reverb.options.port' => $port,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);
    }

    public function test_event_realtime_dikirim_saat_pesan_dan_sematan_berubah(): void
    {
        Event::fake([PublicChatEvent::class]);

        $id = $this->send($this->mentor, 'Halo semua')->json('message.id');
        Event::assertDispatched(PublicChatEvent::class, fn ($e) => $e->broadcastAs() === 'message.created'
            && $e->broadcastWith()['message']['id'] === $id
            && $e->broadcastOn()->name === 'private-public-chat');

        $this->actingAs($this->mentor)->postJson("/public-chat/messages/{$id}/pin", ['days' => 7]);
        $this->actingAs($this->mentor)->deleteJson("/public-chat/messages/{$id}/pin");
        Event::assertDispatchedTimes(PublicChatEvent::class, 3);

        $this->actingAs($this->admin)->post("/admin/chat-publik/messages/{$id}/hide");
        Event::assertDispatched(PublicChatEvent::class, fn ($e) => $e->broadcastAs() === 'message.hidden'
            && $e->broadcastWith() === ['id' => $id]);
    }

    public function test_pesan_besar_hanya_mengirim_id_lewat_websocket(): void
    {
        Event::fake([PublicChatEvent::class]);

        $id = $this->send($this->intern, str_repeat('a', 9000))->json('message.id');

        Event::assertDispatched(PublicChatEvent::class, fn ($e) => $e->broadcastWith() === ['id' => $id]);
    }

    public function test_otorisasi_channel_public_chat(): void
    {
        $this->useReverb();
        // channel didaftarkan ke driver bawaan saat boot; daftarkan juga ke driver reverb yang baru dipilih
        require base_path('routes/channels.php');
        $payload = ['socket_id' => '1234.5678', 'channel_name' => 'private-public-chat'];

        $this->actingAs($this->intern)->post('/broadcasting/auth', $payload)->assertOk()->assertJsonStructure(['auth']);

        $this->intern->intern->update(['is_active' => false]);
        $this->actingAs($this->intern->fresh())->post('/broadcasting/auth', $payload)->assertForbidden();
    }

    public function test_pesan_tetap_terkirim_walau_server_reverb_mati(): void
    {
        $this->useReverb('127.0.0.1', 1); // port tertutup

        $this->send($this->intern, 'tetap tersimpan')->assertCreated();
        $this->assertSame(1, PublicChatMessage::count());
    }

    public function test_csp_mengizinkan_websocket_reverb_bila_aktif(): void
    {
        $csp = fn () => $this->actingAs($this->intern)->get('/chat-publik')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("connect-src 'self';", $csp());

        $this->useReverb('ws.simagang.test', 8080);
        $this->assertStringContainsString("connect-src 'self' ws://ws.simagang.test:8080;", $csp());
        $this->actingAs($this->intern)->get('/chat-publik')->assertSee('pusher.min.js', false);
    }

    public function test_prune_menghapus_pesan_lama_kecuali_pengumuman_aktif(): void
    {
        $this->send($this->intern, 'lama');
        $this->send($this->mentor, 'pengumuman lama', ['pin_days' => 30]);
        $this->travel(31)->days();
        $this->send($this->intern, 'baru');

        $this->artisan('public-chat:prune', ['--days' => 30])->assertSuccessful();

        $this->assertSame(['baru'], PublicChatMessage::orderBy('id')->pluck('text')->all());

        // pengumuman 30 hari sudah berakhir di sini, jadi ikut terhapus; uji ulang dengan sematan aktif
        $this->send($this->mentor, 'aktif', ['pin_days' => 30]);
        $this->travel(29)->days();
        $this->artisan('public-chat:prune', ['--days' => 1])->assertSuccessful();
        $this->assertSame(['aktif'], PublicChatMessage::pluck('text')->all());
    }
}
