<?php

namespace Tests\Feature\Workspace;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Uji keamanan lintas endpoint: tanpa login, IDOR antar tim, pembatasan role, unggahan palsu, dan mass assignment. */
class SecuritySweepTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        Storage::fake('local');
    }

    /** @return array<int, array{string, string}> [method, uri] untuk semua route modul yang diuji. */
    private function sweptRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();

            if (! preg_match('/^(workspace\.|public-chat\.|admin\.public-chat\.)/', $name)) {
                continue;
            }

            $uri = preg_replace('/\{[^}]+\}/', '1', $route->uri());
            $method = collect($route->methods())->first(fn ($m) => ! in_array($m, ['HEAD', 'OPTIONS'], true));
            $routes[$name] = [$method, '/'.ltrim($uri, '/')];
        }

        return $routes;
    }

    public function test_semua_route_modul_menolak_tamu_tanpa_login(): void
    {
        $routes = $this->sweptRoutes();
        $this->assertGreaterThan(60, count($routes), 'sapuan route harus mencakup seluruh modul');

        foreach ($routes as $name => [$method, $uri]) {
            $response = $this->json($method, $uri);

            $this->assertContains(
                $response->getStatusCode(),
                [401, 302],
                "route {$name} ({$method} {$uri}) tidak boleh dapat diakses tamu, status: {$response->getStatusCode()}"
            );
        }
    }

    public function test_user_tim_lain_tidak_bisa_menyentuh_sumber_daya_space_intern(): void
    {
        $taskId = $this->makeTaskForAhmad();
        $messageId = $this->sendMessage($this->ahmad->user, $this->dmAhmad, 'rahasia')->assertCreated()->json('data.id');
        $attachment = $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$taskId}/attachments", [
            'files' => [UploadedFile::fake()->create('hasil.pdf', 10, 'application/pdf')],
        ])->assertCreated()->json('data.0.id');

        $requests = [
            ['GET', "/workspace/{$this->spAhmad->id}/tasks"],
            ['POST', "/workspace/{$this->spAhmad->id}/tasks", ['title' => 'x', 'due_at' => $this->due()]],
            ['GET', "/workspace/{$this->spAhmad->id}/activities"],
            ['GET', "/workspace/tasks/{$taskId}"],
            ['PUT', "/workspace/tasks/{$taskId}", ['title' => 'dibajak']],
            ['DELETE', "/workspace/tasks/{$taskId}"],
            ['PATCH', "/workspace/tasks/{$taskId}/status", ['status' => 'in_progress']],
            ['PATCH', "/workspace/tasks/{$taskId}/review", ['result' => 'done']],
            ['PATCH', "/workspace/tasks/{$taskId}/assignees", ['assignee_ids' => [$this->budi->id]]],
            ['PATCH', "/workspace/tasks/{$taskId}/deadline", ['due_at' => $this->due(30)]],
            ['POST', "/workspace/tasks/{$taskId}/comments", ['body' => 'nyelonong']],
            ['POST', "/workspace/tasks/{$taskId}/attachments", ['files' => [UploadedFile::fake()->create('x.pdf', 5, 'application/pdf')]]],
            ['GET', "/workspace/attachments/{$attachment}"],
            ['GET', "/workspace/interns/{$this->ahmad->id}/tasks"],
            ['GET', "/workspace/channels/{$this->dmAhmad->id}/messages"],
            ['POST', "/workspace/channels/{$this->dmAhmad->id}/messages", ['body' => 'nyelonong']],
            ['POST', "/workspace/channels/{$this->dmAhmad->id}/read"],
            ['PATCH', "/workspace/channels/{$this->dmAhmad->id}/mute"],
            ['PATCH', "/workspace/channels/{$this->dmAhmad->id}/archive"],
            ['PATCH', "/workspace/channels/{$this->dmAhmad->id}", ['name' => 'dibajak']],
            ['PATCH', "/workspace/messages/{$messageId}", ['body' => 'dibajak']],
            ['DELETE', "/workspace/messages/{$messageId}"],
            ['PATCH', "/workspace/messages/{$messageId}/pin"],
            ['GET', "/workspace/messages/{$messageId}/replies"],
            ['POST', "/workspace/messages/{$messageId}/reactions", ['emoji' => '👍']],
        ];

        // Budi: tim DEA. Siti: tim GTA tapi intern lain. Herman: mentor tim lain.
        foreach ([$this->budi->user, $this->siti->user, $this->herman->user] as $outsider) {
            foreach ($requests as $request) {
                [$method, $uri, $payload] = $request + [2 => []];
                $status = $this->actingAs($outsider)->json($method, $uri, $payload)->getStatusCode();

                $this->assertContains(
                    $status,
                    [403, 404],
                    "{$outsider->name} seharusnya ditolak pada {$method} {$uri}, status: {$status}"
                );
            }
        }

        // Tidak ada perubahan yang lolos.
        $this->assertSame('Tugas uji', Task::findOrFail($taskId)->title);
        $this->assertNull(Task::withTrashed()->findOrFail($taskId)->deleted_at);
    }

    public function test_endpoint_moderasi_chat_publik_hanya_untuk_admin(): void
    {
        $message = $this->actingAs($this->ahmad->user)
            ->postJson('/public-chat/messages', ['text' => 'halo semua'])
            ->assertCreated()
            ->json('message.id');

        $requests = [
            ['POST', "/admin/chat-publik/messages/{$message}/hide"],
            ['POST', "/admin/chat-publik/messages/{$message}/restore"],
            ['POST', "/admin/chat-publik/messages/{$message}/unpin"],
            ['POST', "/admin/chat-publik/users/{$this->siti->user_id}/mute", ['minutes' => 60]],
            ['POST', "/admin/chat-publik/users/{$this->siti->user_id}/unmute"],
            ['GET', '/admin/chat-publik'],
        ];

        foreach ([$this->ahmad->user, $this->rudy->user] as $user) {
            foreach ($requests as $request) {
                [$method, $uri, $payload] = $request + [2 => []];
                $status = $this->actingAs($user)->json($method, $uri, $payload)->getStatusCode();

                $this->assertContains($status, [302, 403, 404], "{$user->role} tidak boleh {$method} {$uri}, status: {$status}");
            }
        }
    }

    public function test_unggahan_dengan_ekstensi_berbahaya_ditolak(): void
    {
        $upload = fn (UploadedFile $file) => $this->actingAs($this->ahmad->user)->post('/workspace/attachments', [
            'file' => $file,
            'workspace_id' => $this->spAhmad->id,
        ], ['Accept' => 'application/json']);

        $upload(UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>'))->assertUnprocessable();
        $upload(UploadedFile::fake()->createWithContent('halaman.html', '<script>alert(1)</script>'))->assertUnprocessable();
        $upload(UploadedFile::fake()->createWithContent('gambar.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>'))->assertUnprocessable();
        $upload(UploadedFile::fake()->createWithContent('skrip.js', 'alert(1)'))->assertUnprocessable();

        $this->assertSame(0, Attachment::count());
    }

    /**
     * Berkas asli di disk (bukan fake): validasi `mimes` harus menilai isi, bukan nama.
     * HTML yang diberi ekstensi .pdf tidak boleh tersimpan.
     */
    public function test_html_yang_menyamar_sebagai_pdf_ditolak_berdasarkan_isi(): void
    {
        $dir = storage_path('framework/testing');
        File::ensureDirectoryExists($dir);
        $path = $dir.'/menyamar-'.uniqid().'.pdf';
        file_put_contents($path, '<html><body><script>alert(1)</script></body></html>');

        try {
            $file = new UploadedFile($path, 'laporan.pdf', null, null, true);

            $this->actingAs($this->ahmad->user)->post('/workspace/attachments', [
                'file' => $file,
                'workspace_id' => $this->spAhmad->id,
            ], ['Accept' => 'application/json'])->assertUnprocessable();
        } finally {
            @unlink($path);
        }

        $this->assertSame(0, Attachment::count());
    }

    public function test_unduhan_selalu_attachment_dan_nama_berkas_tidak_bisa_menembus_direktori(): void
    {
        $taskId = $this->makeTaskForAhmad();

        $id = $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$taskId}/attachments", [
            'files' => [UploadedFile::fake()->create('../../../.env.pdf', 5, 'application/pdf')],
        ])->assertCreated()->json('data.0.id');

        $attachment = Attachment::findOrFail($id);
        $this->assertStringNotContainsString('..', $attachment->original_name);
        $this->assertStringNotContainsString('/', $attachment->original_name);
        $this->assertStringStartsWith('workspace/attachments/', $attachment->path);
        $this->assertStringNotContainsString('..', $attachment->path);

        $response = $this->actingAs($this->rudy->user)->get("/workspace/attachments/{$id}")->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_mass_assignment_pada_tugas_dan_pesan_diabaikan(): void
    {
        $response = $this->postTask($this->rudy->user, $this->spAhmad, [
            'assignee_ids' => [$this->ahmad->id],
            'workspace_id' => $this->wsDea->id,
            'created_by' => $this->budi->user_id,
            'status' => 'done',
            'score' => 100,
            'completed_at' => now()->toDateTimeString(),
            'overdue_at' => now()->toDateTimeString(),
            'code' => 'HACK-1',
        ])->assertCreated();

        $task = Task::findOrFail($response->json('data.0.id'));
        $this->assertSame($this->spAhmad->id, $task->workspace_id);
        $this->assertSame($this->rudy->user_id, (int) $task->created_by);
        $this->assertNotSame('done', $task->status->value);
        $this->assertNull($task->score);
        $this->assertNull($task->completed_at);
        $this->assertNull($task->overdue_at);
        $this->assertNotSame('HACK-1', $task->code);

        $message = $this->sendMessage($this->ahmad->user, $this->dmAhmad, 'asli', [
            'user_id' => $this->rudy->user_id,
            'channel_id' => $this->umumGta->id,
            'pinned_at' => now()->toDateTimeString(),
        ])->assertCreated();

        $stored = \App\Models\Message::findOrFail($message->json('data.id'));
        $this->assertSame($this->ahmad->user_id, (int) $stored->user_id, 'penulis pesan selalu user yang login');
        $this->assertSame($this->dmAhmad->id, $stored->channel_id);
        $this->assertNull($stored->pinned_at);
    }

    public function test_intern_tidak_bisa_menaikkan_diri_lewat_payload_tugas_pribadi(): void
    {
        $id = $this->makeTaskForAhmad();

        $this->setStatus($this->ahmad->user, $id, 'in_progress')->assertOk();
        $this->actingAs($this->ahmad->user)->putJson("/workspace/tasks/{$id}", [
            'title' => 'Tugas uji',
            'due_at' => $this->due(),
            'status' => 'done',
            'score' => 100,
        ]);

        $task = Task::findOrFail($id);
        $this->assertNotSame('done', $task->status->value, 'intern tidak boleh menyelesaikan tugas lewat PUT');
        $this->assertNull($task->score);
    }
}
