<?php

namespace Tests\Feature\Workspace;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Email untuk interaksi workspace (tugas, komentar, mention) dan pengingat chat belum dibaca. */
class EmailNotificationTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function mailsTo(string $email): array
    {
        return array_values(array_filter(
            app('mailer')->getSymfonyTransport()->messages()->all(),
            fn ($m) => collect($m->getEnvelope()->getRecipients())->contains(fn ($r) => $r->getAddress() === $email)
        ));
    }

    private function postFrom(Channel $channel, int $userId, int $times, $at = null): void
    {
        foreach (range(1, $times) as $i) {
            $message = Message::create(['channel_id' => $channel->id, 'user_id' => $userId, 'body' => "pesan {$i}"]);

            if ($at) {
                DB::table('messages')->where('id', $message->id)->update(['created_at' => $at, 'updated_at' => $at]);
            }
        }
    }

    public function test_menugaskan_tugas_mengirim_email_ke_intern(): void
    {
        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id]])->assertCreated();

        $mails = $this->mailsTo($this->ahmad->user->email);
        $this->assertNotEmpty($mails);
        $this->assertStringContainsString('menugaskan kamu', $mails[0]->getOriginalMessage()->getSubject());
    }

    public function test_email_tugas_tidak_dikirim_bila_dimatikan_di_pengaturan(): void
    {
        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', [
            'types' => ['task_assigned' => ['email' => false]],
        ])->assertOk();

        $this->postTask($this->rudy->user, $this->spAhmad, ['assignee_ids' => [$this->ahmad->id]])->assertCreated();

        $this->assertEmpty($this->mailsTo($this->ahmad->user->email));
        $this->assertSame(1, $this->ahmad->user->notifications()->where('data->kind', 'task_assigned')->count());
    }

    public function test_chat_belum_dibaca_lima_pesan_mengirim_email_sekali(): void
    {
        $this->postFrom($this->dmAhmad, $this->rudy->user->id, 5);

        Artisan::call('workspace:notify-unread-chat');
        $this->assertCount(1, $this->mailsTo($this->ahmad->user->email));

        // Tanpa pesan baru, tidak diingatkan lagi.
        Artisan::call('workspace:notify-unread-chat');
        $this->assertCount(1, $this->mailsTo($this->ahmad->user->email));

        // Pengirim sendiri tidak diingatkan atas pesannya.
        $this->assertEmpty($this->mailsTo($this->rudy->user->email));
    }

    public function test_chat_kurang_dari_lima_pesan_dan_belum_lima_jam_tidak_diingatkan(): void
    {
        $this->postFrom($this->dmAhmad, $this->rudy->user->id, 4);

        Artisan::call('workspace:notify-unread-chat');

        $this->assertEmpty($this->mailsTo($this->ahmad->user->email));
    }

    public function test_satu_pesan_yang_sudah_lima_jam_belum_dibaca_diingatkan(): void
    {
        $this->postFrom($this->dmAhmad, $this->rudy->user->id, 1, now()->subHours(6));

        Artisan::call('workspace:notify-unread-chat');

        $this->assertCount(1, $this->mailsTo($this->ahmad->user->email));
    }

    public function test_chat_yang_sudah_dibaca_atau_dibisukan_tidak_diingatkan(): void
    {
        $channel = $this->dmAhmad;
        $this->postFrom($channel, $this->rudy->user->id, 6);

        DB::table('channel_members')->where('channel_id', $channel->id)->where('user_id', $this->ahmad->user->id)
            ->update(['last_read_message_id' => Message::where('channel_id', $channel->id)->max('id')]);

        Artisan::call('workspace:notify-unread-chat');
        $this->assertEmpty($this->mailsTo($this->ahmad->user->email));

        DB::table('channel_members')->where('channel_id', $channel->id)->where('user_id', $this->ahmad->user->id)
            ->update(['last_read_message_id' => null, 'is_muted' => true]);

        Artisan::call('workspace:notify-unread-chat');
        $this->assertEmpty($this->mailsTo($this->ahmad->user->email));
    }

    public function test_pengingat_chat_mengikuti_pengaturan_email_user(): void
    {
        $this->actingAs($this->ahmad->user)->putJson('/notifications/preferences', [
            'types' => ['chat_unread' => ['email' => false]],
        ])->assertOk();
        $this->postFrom($this->dmAhmad, $this->rudy->user->id, 5);

        Artisan::call('workspace:notify-unread-chat');

        $this->assertEmpty($this->mailsTo($this->ahmad->user->email));
    }
}
