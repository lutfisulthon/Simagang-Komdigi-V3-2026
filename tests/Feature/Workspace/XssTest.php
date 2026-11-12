<?php

namespace Tests\Feature\Workspace;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** HTML/skrip yang disisipkan pengguna di judul dan komentar tugas tidak boleh sampai ke browser mentah-mentah. */
class XssTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    private const PAYLOAD = '<script>alert("xss")</script><img src=x onerror=alert(1)>';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    public function test_komentar_tugas_di_escape_pada_body_html(): void
    {
        $id = $this->makeTaskForAhmad();

        $html = $this->actingAs($this->ahmad->user)
            ->postJson("/workspace/tasks/{$id}/comments", ['body' => self::PAYLOAD])
            ->assertCreated()
            ->json('data.body_html');

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    public function test_tautan_javascript_di_komentar_tidak_menjadi_tautan_aktif(): void
    {
        $id = $this->makeTaskForAhmad();

        $html = $this->actingAs($this->ahmad->user)
            ->postJson("/workspace/tasks/{$id}/comments", ['body' => '[klik](javascript:alert(1))'])
            ->assertCreated()
            ->json('data.body_html');

        $this->assertStringNotContainsString('href="javascript:', strtolower($html));
    }

    public function test_komentar_tampil_aman_di_detail_tugas_json(): void
    {
        $id = $this->makeTaskForAhmad();
        $this->actingAs($this->ahmad->user)->postJson("/workspace/tasks/{$id}/comments", ['body' => self::PAYLOAD])->assertCreated();

        $comments = $this->actingAs($this->rudy->user)->getJson("/workspace/tasks/{$id}")->json('data.comments');

        $this->assertNotEmpty($comments);
        foreach ($comments as $comment) {
            $this->assertStringNotContainsString('<script', $comment['body_html']);
            $this->assertStringNotContainsString('<img', $comment['body_html']);
        }
    }

    public function test_judul_tugas_dengan_html_tidak_dirender_mentah_di_halaman_detail(): void
    {
        $id = $this->makeTaskForAhmad(['title' => self::PAYLOAD]);

        $page = $this->actingAs($this->rudy->user)->get("/workspace/tasks/{$id}")->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert("xss")</script>', $page);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $page);
    }

    public function test_judul_tugas_dengan_html_tidak_dirender_mentah_di_halaman_workspace(): void
    {
        $this->makeTaskForAhmad(['title' => self::PAYLOAD]);

        $page = $this->actingAs($this->rudy->user)->get("/workspace/{$this->spAhmad->id}")->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert("xss")</script>', $page);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $page);
    }
}
