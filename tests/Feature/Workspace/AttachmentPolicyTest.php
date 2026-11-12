<?php

namespace Tests\Feature\Workspace;

use App\Models\Attachment;
use App\Models\Message;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** AttachmentPolicy: hak unduh lampiran mengikuti hak lihat induknya (tugas, komentar, pesan). */
class AttachmentPolicyTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function attachment(?object $parent, User $uploader, array $override = []): Attachment
    {
        return Attachment::create($override + [
            'attachable_type' => $parent?->getMorphClass(),
            'attachable_id' => $parent?->getKey(),
            'user_id' => $uploader->id,
            'workspace_id' => $this->spAhmad->id,
            'disk' => 'local',
            'path' => 'workspace/attachments/uji.pdf',
            'original_name' => 'uji.pdf',
            'mime' => 'application/pdf',
            'size' => 10,
        ]);
    }

    private function can(User $user, Attachment $attachment): bool
    {
        return Gate::forUser($user)->allows('view', $attachment);
    }

    public function test_lampiran_belum_ditautkan_hanya_terlihat_oleh_pengunggahnya(): void
    {
        $attachment = $this->attachment(null, $this->ahmad->user);

        $this->assertTrue($this->can($this->ahmad->user, $attachment));
        $this->assertFalse($this->can($this->rudy->user, $attachment), 'mentor pun tidak boleh sebelum lampiran ditautkan');
        $this->assertFalse($this->can($this->admin, $attachment));
    }

    public function test_lampiran_tugas_mengikuti_hak_lihat_tugas(): void
    {
        $task = Task::findOrFail($this->makeTaskForAhmad());
        $attachment = $this->attachment($task, $this->ahmad->user);

        $this->assertTrue($this->can($this->ahmad->user, $attachment));
        $this->assertTrue($this->can($this->rudy->user, $attachment));
        $this->assertTrue($this->can($this->admin, $attachment));
        $this->assertFalse($this->can($this->siti->user, $attachment));
        $this->assertFalse($this->can($this->budi->user, $attachment));
        $this->assertFalse($this->can($this->herman->user, $attachment), 'mentor tim lain bukan anggota Space Ahmad');
    }

    public function test_lampiran_komentar_tugas_mengikuti_hak_lihat_tugas_induknya(): void
    {
        $task = Task::findOrFail($this->makeTaskForAhmad());
        $comment = TaskComment::create(['task_id' => $task->id, 'user_id' => $this->ahmad->user_id, 'body' => 'Lihat lampiran']);
        $attachment = $this->attachment($comment, $this->ahmad->user);

        $this->assertTrue($this->can($this->ahmad->user, $attachment));
        $this->assertTrue($this->can($this->rudy->user, $attachment));
        $this->assertFalse($this->can($this->siti->user, $attachment));
        $this->assertFalse($this->can($this->budi->user, $attachment));
    }

    public function test_lampiran_pesan_mengikuti_keanggotaan_channel(): void
    {
        $message = Message::findOrFail($this->sendMessage($this->ahmad->user, $this->dmAhmad, 'Ini berkasnya')->assertCreated()->json('data.id'));
        $attachment = $this->attachment($message, $this->ahmad->user);

        $this->assertTrue($this->can($this->ahmad->user, $attachment));
        $this->assertTrue($this->can($this->rudy->user, $attachment));
        $this->assertFalse($this->can($this->siti->user, $attachment), 'DM Ahmad tertutup bagi intern lain');
        $this->assertFalse($this->can($this->budi->user, $attachment));
    }

    public function test_induk_yang_sudah_hilang_ditolak(): void
    {
        $task = Task::findOrFail($this->makeTaskForAhmad());

        $orphan = $this->attachment($task, $this->ahmad->user, ['attachable_id' => 999999]);
        $this->assertFalse($this->can($this->ahmad->user, $orphan), 'induk sudah terhapus: tidak ada yang boleh mengunduh');
    }

    public function test_unduhan_via_http_mengikuti_policy_untuk_lampiran_pesan(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('workspace/attachments/uji.pdf', 'isi');
        $message = Message::findOrFail($this->sendMessage($this->ahmad->user, $this->dmAhmad, 'Ini berkasnya')->assertCreated()->json('data.id'));
        $attachment = $this->attachment($message, $this->ahmad->user);

        $this->actingAs($this->rudy->user)->get("/workspace/attachments/{$attachment->id}")->assertOk();
        $this->actingAs($this->siti->user)->get("/workspace/attachments/{$attachment->id}")->assertForbidden();
    }
}
