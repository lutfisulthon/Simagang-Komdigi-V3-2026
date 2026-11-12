<?php

namespace Tests\Feature;

use App\Services\InstitutionApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\WorkspaceFixtures;
use Tests\TestCase;

/** Endpoint publik: validasi input, pencegahan injeksi query, batas laju, dan alat internal. */
class PublicEndpointSecurityTest extends TestCase
{
    use RefreshDatabase, WorkspaceFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_pencarian_institusi_menolak_parameter_tidak_valid(): void
    {
        $this->getJson('/api/institutions/search?limit=abc')->assertStatus(422);
        $this->getJson('/api/institutions/search?limit=100000')->assertStatus(422);
        $this->getJson('/api/institutions/search?limit=0')->assertStatus(422);
        $this->getJson('/api/institutions/search?search='.str_repeat('a', 101))->assertStatus(422);
    }

    public function test_kata_kunci_tidak_bisa_menyusup_ke_struktur_query_graphql(): void
    {
        Http::fake(['*' => Http::response(['data' => ['perguruanTinggi' => [['nama' => 'Universitas Uji']]]])]);

        InstitutionApiService::searchUniversities('x"}}) { nama } } query jahat { a(b: "', 10);

        Http::assertSent(function (HttpRequest $request) {
            // Hapus semua literal string yang ter-escape dengan benar. Bila input berhasil menutup literalnya,
            // sisa teks injeksi akan tertinggal di luar tanda kutip.
            $withoutStrings = preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $request['query']);

            $this->assertStringContainsString('contains: ""', $withoutStrings);
            $this->assertStringNotContainsString('jahat', $withoutStrings);
            $this->assertSame(1, substr_count($withoutStrings, 'query'), 'tidak boleh muncul query kedua');

            return true;
        });
    }

    public function test_limit_dibatasi_di_dalam_query(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);

        InstitutionApiService::searchUniversities('kampus', 999999);

        Http::assertSent(fn (HttpRequest $request) => str_contains($request['query'], 'limit: 100)'));
    }

    public function test_api_institusi_publik_dibatasi_tiga_puluh_per_menit(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);

        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/institutions/search?search=k'.$i)->assertOk();
        }

        $this->getJson('/api/institutions/search?search=lagi')->assertStatus(429);
    }

    public function test_convert_font_tidak_bisa_dipanggil_publik_atau_non_admin(): void
    {
        $this->getJson('/convert-font')->assertUnauthorized();

        foreach (['intern', 'mentor', 'institusi', 'industri'] as $role) {
            $this->actingAs($this->makeUser($role, "Uji {$role}"))->getJson('/convert-font')->assertForbidden();
        }
    }
}
