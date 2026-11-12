<?php

namespace Tests\Feature;

use App\Models\Industri;
use App\Models\Institusi;
use App\Models\Lowongan;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceFixtures;
use Tests\TestCase;

/**
 * Isolasi data antar tenant: industri hanya mengurus pengajuan ke lowongannya sendiri,
 * dan institusi hanya mengurus pengajuan miliknya sendiri.
 */
class CrossTenantAccessTest extends TestCase
{
    use RefreshDatabase, WorkspaceFixtures;

    private User $industriUserA;
    private User $industriUserB;
    private User $institusiUserX;
    private User $institusiUserY;
    private Pengajuan $pengajuan;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->industriUserA, $industriA] = $this->makeIndustri('Industri A');
        [$this->industriUserB] = $this->makeIndustri('Industri B');
        [$this->institusiUserX, $institusiX] = $this->makeInstitusi('Kampus X');
        [$this->institusiUserY] = $this->makeInstitusi('Kampus Y');

        $lowongan = Lowongan::create([
            'industri_id' => $industriA->id,
            'judul_lowongan' => 'Magang Backend',
            'posisi_magang' => 'Backend Developer',
            'divisi' => 'TI',
            'deskripsi_pekerjaan' => 'Membangun API',
            'requirements' => 'PHP',
            'fasilitas' => 'Sertifikat',
            'kuota_peserta' => 2,
            'status' => 'dibuka',
            'status_verifikasi' => 'disetujui',
        ]);

        $this->pengajuan = Pengajuan::create([
            'institusi_id' => $institusiX->id,
            'lowongan_id' => $lowongan->id,
            'surat_path' => 'surat_magang/uji.pdf',
            'status' => 'pending',
        ]);
    }

    /** @return array{User, Industri} */
    private function makeIndustri(string $name): array
    {
        $user = $this->makeUser('industri', $name);
        $industri = Industri::create([
            'user_id' => $user->id,
            'nama_industri' => $name,
            'bidang_industri' => 'Teknologi',
            'alamat_industri' => 'Jl. Uji No. 1',
            'kota_kabupaten' => 'Makassar',
            'email_industri' => strtolower(str_replace(' ', '-', $name)).'@industri.test',
            'nomor_telepon_industri' => '0411123456',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        return [$user, $industri];
    }

    /** @return array{User, Institusi} */
    private function makeInstitusi(string $name): array
    {
        $user = $this->makeUser('institusi', $name);
        $institusi = Institusi::create([
            'user_id' => $user->id,
            'nama_institusi' => $name,
            'jenis_institusi' => 'kampus',
            'no_hp' => '0812345678',
        ]);

        return [$user, $institusi];
    }

    public function test_industri_lain_tidak_bisa_melihat_pengajuan_ke_lowongan_industri_lain(): void
    {
        $this->actingAs($this->industriUserB)
            ->get("/industri/pengajuan/{$this->pengajuan->id}")
            ->assertNotFound();
    }

    public function test_industri_lain_tidak_bisa_menyetujui_atau_menolak_pengajuan(): void
    {
        foreach (['approved', 'rejected'] as $status) {
            $this->actingAs($this->industriUserB)
                ->putJson("/industri/pengajuan/{$this->pengajuan->id}/update-status", ['status' => $status])
                ->assertForbidden();
        }

        $this->assertSame('pending', $this->pengajuan->fresh()->status, 'status pengajuan tidak boleh diubah industri lain');
    }

    public function test_industri_lain_tidak_bisa_mencetak_surat_balasan(): void
    {
        $this->actingAs($this->industriUserB)
            ->get("/industri/pengajuan/surat-balasan/{$this->pengajuan->id}")
            ->assertForbidden();
    }

    public function test_industri_pemilik_lowongan_tetap_bisa_membuka_dan_mengubah_status(): void
    {
        $this->actingAs($this->industriUserA)
            ->get("/industri/pengajuan/{$this->pengajuan->id}")
            ->assertOk();

        $this->actingAs($this->industriUserA)
            ->putJson("/industri/pengajuan/{$this->pengajuan->id}/update-status", ['status' => 'revised'])
            ->assertSuccessful();

        $this->assertSame('revised', $this->pengajuan->fresh()->status);
    }

    public function test_institusi_lain_tidak_bisa_mencetak_surat_balasan_pengajuan_orang_lain(): void
    {
        $this->actingAs($this->institusiUserY)
            ->get("/institusi/surat-balasan/{$this->pengajuan->id}")
            ->assertForbidden();
    }

    public function test_institusi_lain_tidak_bisa_membuka_mengubah_atau_menghapus_pengajuan(): void
    {
        $this->actingAs($this->institusiUserY)->get("/institusi/pengajuan/{$this->pengajuan->id}")->assertNotFound();
        $this->actingAs($this->institusiUserY)->get("/institusi/pengajuan/{$this->pengajuan->id}/edit")->assertNotFound();
        $this->actingAs($this->institusiUserY)->delete("/institusi/pengajuan/{$this->pengajuan->id}")->assertNotFound();

        $this->assertNotNull(Pengajuan::find($this->pengajuan->id), 'pengajuan tidak boleh terhapus oleh institusi lain');
    }

    public function test_unduhan_surat_pengajuan_hanya_untuk_pihak_yang_berhak(): void
    {
        // Berkas tidak ada: yang berhak lolos otorisasi (404), pihak lain ditolak (403).
        $this->actingAs($this->institusiUserY)->get("/pengajuan/{$this->pengajuan->id}/surat")->assertForbidden();
        $this->actingAs($this->industriUserB)->get("/pengajuan/{$this->pengajuan->id}/surat")->assertForbidden();
        $this->actingAs($this->makeUser('intern', 'Intern Asing'))->get("/pengajuan/{$this->pengajuan->id}/surat")->assertForbidden();

        $this->actingAs($this->institusiUserX)->get("/pengajuan/{$this->pengajuan->id}/surat")->assertNotFound();
        $this->actingAs($this->industriUserA)->get("/pengajuan/{$this->pengajuan->id}/surat")->assertNotFound();
    }
}
