<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\CertificateScore;
use App\Models\Intern;
use App\Services\CertificatePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

/**
 * Tombol "Unduh Sertifikat Magang" di dashboard intern: intern hanya boleh mengunduh sertifikat
 * miliknya sendiri yang sudah terbit. Admin dan mentor memakai service PDF yang sama.
 */
class InternCertificatePrintTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();

        // Tes akses memakai mock agar cepat; render PDF asli diuji terpisah di test_service_merender_pdf_asli_dengan_font_dari_repo.
        $this->mock(CertificatePdfService::class, function ($mock) {
            $mock->shouldReceive('response')->andReturnUsing(
                fn (Certificate $certificate) => response('%PDF-uji-' . $certificate->id, 200, ['Content-Type' => 'application/pdf'])
            );
        });
    }

    private function certificateFor(Intern $intern, string $issueDate): Certificate
    {
        $certificate = Certificate::create([
            'intern_id' => $intern->id,
            'certificate_number' => 'CERT-UJI-' . $intern->id,
            'issue_date' => $issueDate,
        ]);

        CertificateScore::create([
            'certificate_id' => $certificate->id,
            'discipline_attendance' => 90,
            'responsibility' => 85,
            'teamwork_communication' => 80,
            'technical_skill' => 88,
            'work_ethic' => 92,
            'initiative_creativity' => 75,
            'micro_skill' => 3,
        ]);

        return $certificate;
    }

    private function assertPdf($response): void
    {
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_intern_bisa_mengunduh_sertifikat_sendiri_yang_sudah_terbit(): void
    {
        $certificate = $this->certificateFor($this->ahmad, now()->subDay()->toDateString());

        $this->assertPdf($this->actingAs($this->ahmad->user)->get('/intern/certificates/' . $certificate->id . '/print'));
    }

    public function test_intern_bisa_mengunduh_pada_hari_terbit(): void
    {
        $certificate = $this->certificateFor($this->ahmad, now()->toDateString());

        $this->assertPdf($this->actingAs($this->ahmad->user)->get('/intern/certificates/' . $certificate->id . '/print'));
    }

    public function test_intern_tidak_bisa_mengunduh_sertifikat_intern_lain(): void
    {
        $milikSiti = $this->certificateFor($this->siti, now()->subDay()->toDateString());

        $this->actingAs($this->ahmad->user)->get('/intern/certificates/' . $milikSiti->id . '/print')->assertForbidden();
    }

    public function test_intern_tidak_bisa_mengunduh_sebelum_tanggal_terbit(): void
    {
        $certificate = $this->certificateFor($this->ahmad, now()->addDays(3)->toDateString());

        $this->actingAs($this->ahmad->user)->get('/intern/certificates/' . $certificate->id . '/print')->assertForbidden();
    }

    public function test_service_merender_pdf_asli_dengan_font_dari_repo(): void
    {
        $certificate = $this->certificateFor($this->ahmad, now()->subDay()->toDateString());

        $pdf = (new CertificatePdfService())->render($certificate);

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertStringContainsString('Poppins', $pdf);
    }

    public function test_mentor_dan_admin_tetap_bisa_mencetak_lewat_service_yang_sama(): void
    {
        $certificate = $this->certificateFor($this->ahmad, now()->addDays(3)->toDateString());

        $this->assertPdf($this->actingAs($this->rudy->user)->get('/mentor/certificates/' . $certificate->id . '/print'));
        $this->assertPdf($this->actingAs($this->admin)->get('/admin/certificates/' . $certificate->id . '/print'));
    }
}
