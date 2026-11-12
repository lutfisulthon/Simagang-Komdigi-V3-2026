<?php

namespace Tests\Feature\Workspace;

use App\Models\Mentor;
use App\Observers\MentorObserver;
use App\Services\WorkspaceProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\WorkspaceFixtures;
use Tests\TestCase;

/** MentorObserver: kapan sinkronisasi akses dijalankan, dan kegagalan tidak menggagalkan penyimpanan mentor. */
class MentorObserverTest extends TestCase
{
    use RefreshDatabase, WorkspaceFixtures;

    private function mentor(): Mentor
    {
        $this->makeAdmin();

        return $this->makeMentor('Rudy', $this->makeTeam('TIM GTA'));
    }

    public function test_ganti_akun_user_mentor_memberi_akses_ke_akun_baru(): void
    {
        $mentor = $this->mentor();
        $workspace = $this->teamWorkspace($mentor->team);
        $newUser = $this->makeUser('mentor', 'Akun Pengganti');

        $mentor->update(['user_id' => $newUser->id]);

        $members = $this->activeMembers($workspace);
        $this->assertArrayHasKey($newUser->id, $members);
        $this->assertSame('mentor', $members[$newUser->id]);
    }

    public function test_ganti_akun_user_mentor_mencabut_akses_akun_lama(): void
    {
        $mentor = $this->mentor();
        $workspace = $this->teamWorkspace($mentor->team);
        $oldUserId = $mentor->user_id;
        $newUser = $this->makeUser('mentor', 'Akun Pengganti');

        $mentor->update(['user_id' => $newUser->id]);

        $this->assertArrayNotHasKey($oldUserId, $this->activeMembers($workspace), 'akun lama tidak boleh tersisa di workspace tim');
    }

    public function test_perubahan_yang_tidak_terkait_akses_tidak_memicu_sinkronisasi(): void
    {
        $mentor = $this->mentor();
        $mentor->touch();

        $provisioner = Mockery::mock(WorkspaceProvisioner::class);
        $provisioner->shouldNotReceive('syncMentor');

        (new MentorObserver($provisioner))->updated($mentor);

        $this->addToAssertionCount(1);
    }

    public function test_perubahan_status_aktif_memicu_sinkronisasi_ulang_intern(): void
    {
        $mentor = $this->mentor();
        $mentor->is_active = false;
        $mentor->save();

        $provisioner = Mockery::mock(WorkspaceProvisioner::class);
        $provisioner->shouldReceive('syncMentor')->once()->with(Mockery::on(fn ($m) => $m->is($mentor)), true);

        (new MentorObserver($provisioner))->updated($mentor);
    }

    public function test_mentor_baru_disinkronkan_tanpa_sinkronisasi_ulang_intern(): void
    {
        $mentor = $this->mentor();

        $provisioner = Mockery::mock(WorkspaceProvisioner::class);
        $provisioner->shouldReceive('syncMentor')->once()->with(Mockery::on(fn ($m) => $m->is($mentor)), false);

        (new MentorObserver($provisioner))->created($mentor);
    }

    public function test_kegagalan_provisioning_tidak_menggagalkan_penyimpanan_mentor(): void
    {
        $mentor = $this->mentor();
        $mentor->is_active = false;
        $mentor->save();

        $provisioner = Mockery::mock(WorkspaceProvisioner::class);
        $provisioner->shouldReceive('syncMentor')->twice()->andThrow(new \RuntimeException('provisioning gagal'));
        $observer = new MentorObserver($provisioner);

        try {
            $observer->created($mentor);
            $observer->updated($mentor);
        } catch (\Throwable $e) {
            $this->fail('observer tidak boleh melempar: '.$e->getMessage());
        }

        $this->assertFalse($mentor->fresh()->is_active, 'perubahan mentor tetap tersimpan');
    }
}
