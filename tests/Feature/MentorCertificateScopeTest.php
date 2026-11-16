<?php

namespace Tests\Feature;

use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/** Mentor hanya boleh membuat, mengubah, dan mencetak sertifikat intern bimbingannya sendiri. */
class MentorCertificateScopeTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
    }

    private function scores(array $extra = []): array
    {
        return $extra + [
            'issue_date' => now()->addDays(10)->toDateString(),
            'discipline_attendance' => 90,
            'responsibility' => 90,
            'teamwork_communication' => 90,
            'technical_skill' => 90,
            'work_ethic' => 90,
            'initiative_creativity' => 90,
        ];
    }

    private function certificateFor($intern): Certificate
    {
        return Certificate::create([
            'intern_id' => $intern->id,
            'certificate_number' => 'CERT-UJI-' . $intern->id,
            'issue_date' => now()->addDays(10),
        ]);
    }

    public function test_form_hanya_menampilkan_intern_bimbingan_sendiri(): void
    {
        $interns = $this->actingAs($this->rudy->user)->get('/mentor/certificates/create')->assertOk()->viewData('interns');

        $this->assertSame([$this->ahmad->id], $interns->pluck('id')->all());
    }

    public function test_form_intern_milik_mentor_lain_ditolak(): void
    {
        $this->actingAs($this->rudy->user)->get('/mentor/certificates/create?intern_id=' . $this->siti->id)->assertForbidden();
        $this->actingAs($this->rudy->user)->get('/mentor/certificates/create?intern_id=' . $this->ahmad->id)->assertOk();
    }

    public function test_menerbitkan_sertifikat_untuk_intern_mentor_lain_ditolak(): void
    {
        $this->actingAs($this->rudy->user)
            ->post('/mentor/certificates', $this->scores(['intern_id' => $this->siti->id]))
            ->assertForbidden();
        $this->assertSame(0, Certificate::count());

        $this->actingAs($this->rudy->user)
            ->post('/mentor/certificates', $this->scores(['intern_id' => $this->ahmad->id]))
            ->assertRedirect();
        $this->assertSame([$this->ahmad->id], Certificate::pluck('intern_id')->all());
    }

    public function test_mengubah_dan_mencetak_sertifikat_intern_mentor_lain_ditolak(): void
    {
        $milikSiti = $this->certificateFor($this->siti);

        $this->actingAs($this->rudy->user)->put('/mentor/certificates/' . $milikSiti->id, $this->scores())->assertForbidden();
        $this->actingAs($this->rudy->user)->get('/mentor/certificates/' . $milikSiti->id . '/print')->assertForbidden();
    }
}
