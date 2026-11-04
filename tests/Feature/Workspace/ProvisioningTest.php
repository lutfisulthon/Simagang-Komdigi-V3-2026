<?php

namespace Tests\Feature\Workspace;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Task;
use App\Models\Workspace;
use App\Notifications\InternJoinedTeamNotification;
use App\Notifications\InternWithoutMentorNotification;
use App\Services\WorkspaceProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\WorkspaceFixtures;
use Tests\TestCase;

class ProvisioningTest extends TestCase
{
    use RefreshDatabase, WorkspaceFixtures;

    private function snapshot(): array
    {
        return [
            'workspaces' => Workspace::count(),
            'members' => DB::table('workspace_members')->count(),
            'channels' => Channel::count(),
            'channel_members' => DB::table('channel_members')->count(),
            'messages' => Message::withTrashed()->count(),
            'tasks' => Task::withTrashed()->count(),
            'notifications' => DB::table('notifications')->count(),
        ];
    }

    public function test_intern_baru_dengan_mentor_dan_tim_langsung_mendapat_workspace_lengkap(): void
    {
        $admin = $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);

        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);

        // Workspace Tim: mentor + intern, channel #umum-<tim>, satu pesan sambutan
        $teamWs = $this->teamWorkspace($team);
        $this->assertEquals(
            [$mentor->user_id => 'mentor', $intern->user_id => 'member'],
            $this->activeMembers($teamWs)
        );
        $umum = Channel::where('workspace_id', $teamWs->id)->where('type', 'team')->firstOrFail();
        $this->assertSame('umum-tim-gta', $umum->name);
        $this->assertEqualsCanonicalizing([$mentor->user_id, $intern->user_id], $umum->members()->pluck('users.id')->all());
        $this->assertSame(1, $umum->messages()->count());
        $this->assertStringContainsString('Ahmad Fauzi', $umum->messages()->first()->body);

        // Space Intern: intern sebagai owner, mentor, DM privat
        $space = $this->internSpace($intern);
        $this->assertEquals([$intern->user_id => 'owner', $mentor->user_id => 'mentor'], $this->activeMembers($space));
        $dm = Channel::where('workspace_id', $space->id)->where('type', 'direct')->firstOrFail();
        $this->assertTrue($dm->is_private);
        $this->assertEqualsCanonicalizing([$mentor->user_id, $intern->user_id], $dm->members()->pluck('users.id')->all());

        // #pengumuman global berisi intern dan mentor
        $announcement = Channel::whereNull('workspace_id')->where('type', 'announcement')->firstOrFail();
        $this->assertEqualsCanonicalizing([$mentor->user_id, $intern->user_id], $announcement->members()->pluck('users.id')->all());

        // Mentor dan admin mendapat notifikasi intern baru
        $this->assertSame(1, $mentor->user->notifications()->where('type', InternJoinedTeamNotification::class)->count());
        $this->assertSame(1, $admin->notifications()->where('type', InternJoinedTeamNotification::class)->count());
    }

    public function test_provisioning_dua_kali_tidak_membuat_data_ganda(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);

        $first = $this->snapshot();

        $provisioner = app(WorkspaceProvisioner::class);
        $provisioner->sync($intern->fresh());
        $provisioner->sync($intern->fresh());

        $this->assertSame($first, $this->snapshot());
    }

    public function test_perintah_backfill_idempoten_dan_membuat_workspace_intern_lama(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);

        // Simulasikan intern lama sebelum fitur rilis: workspace belum ada
        Workspace::query()->delete();
        $this->assertSame(0, Workspace::count());

        $this->artisan('workspace:backfill')->assertSuccessful();
        $afterFirst = $this->snapshot();

        $this->assertNotNull($this->internSpace($intern));
        $this->assertNotNull($this->teamWorkspace($team));

        $this->artisan('workspace:backfill')->assertSuccessful();
        $this->assertSame($afterFirst, $this->snapshot());
    }

    public function test_backfill_dry_run_tidak_mengubah_data(): void
    {
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $this->makeIntern('Ahmad Fauzi', $mentor, $team);
        Workspace::query()->delete();

        $this->artisan('workspace:backfill --dry-run')->assertSuccessful();

        $this->assertSame(0, Workspace::count());
    }

    public function test_intern_tanpa_mentor_ditunda_dan_admin_diberi_tahu_sekali(): void
    {
        $admin = $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $intern = $this->makeIntern('Budi', null, $team);

        $this->assertSame(0, Workspace::where('intern_id', $intern->id)->count());
        $this->assertSame(0, Workspace::where('type', 'team')->count());
        $this->assertSame(1, $admin->notifications()->where('type', InternWithoutMentorNotification::class)->count());

        // Sync ulang tidak menumpuk notifikasi selama yang lama belum dibaca
        $this->assertSame(WorkspaceProvisioner::PENDING_MENTOR, app(WorkspaceProvisioner::class)->sync($intern->fresh()));
        $this->assertSame(1, $admin->notifications()->where('type', InternWithoutMentorNotification::class)->count());

        // Mentor ditetapkan -> observer langsung membuat workspace
        $mentor = $this->makeMentor('Rudy', $team);
        $intern->update(['mentor_id' => $mentor->id]);

        $this->assertNotNull($this->internSpace($intern));
        $this->assertArrayHasKey($intern->user_id, $this->activeMembers($this->teamWorkspace($team)));
    }

    public function test_mentor_diganti_mentor_lama_keluar_riwayat_tetap(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $old = $this->makeMentor('Rudy', $team);
        $new = $this->makeMentor('Tasmil', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $old, $team);

        $space = $this->internSpace($intern);
        $dm = Channel::where('workspace_id', $space->id)->where('type', 'direct')->firstOrFail();
        $dm->messages()->create(['user_id' => $old->user_id, 'body' => 'Halo dari mentor lama']);
        $welcomeBefore = Message::where('body', 'like', '%Selamat datang%')->count();
        $tasksBefore = $space->tasks()->count();

        $intern->update(['mentor_id' => $new->id]);

        $members = $this->activeMembers($space);
        $this->assertArrayNotHasKey($old->user_id, $members);
        $this->assertSame('mentor', $members[$new->user_id]);
        $this->assertNotNull(DB::table('workspace_members')->where('workspace_id', $space->id)->where('user_id', $old->user_id)->value('left_at'));
        $this->assertEqualsCanonicalizing([$new->user_id, $intern->user_id], $dm->members()->pluck('users.id')->all());

        // Riwayat chat dan tugas tetap, sambutan tidak diposting ulang
        $this->assertSame(1, $dm->messages()->count());
        $this->assertSame($tasksBefore, $space->tasks()->count());
        $this->assertSame($welcomeBefore, Message::where('body', 'like', '%Selamat datang%')->count());
    }

    public function test_mentor_dicabut_dikeluarkan_dari_space(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);
        $space = $this->internSpace($intern);

        $intern->update(['mentor_id' => null]);

        $this->assertArrayNotHasKey($mentor->user_id, $this->activeMembers($space));
    }

    public function test_pindah_tim_mencabut_tim_lama_dan_tugas_terbuka_tetap_di_space(): void
    {
        $this->makeAdmin();
        $teamA = $this->makeTeam('TIM GTA');
        $teamB = $this->makeTeam('TIM DEA');
        $mentor = $this->makeMentor('Rudy', $teamA);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $teamA);
        $space = $this->internSpace($intern);
        $tasksBefore = $space->tasks()->count();

        $intern->update(['team_id' => $teamB->id]);

        $this->assertArrayNotHasKey($intern->user_id, $this->activeMembers($this->teamWorkspace($teamA)));
        $this->assertArrayHasKey($intern->user_id, $this->activeMembers($this->teamWorkspace($teamB)));
        $umumA = Channel::where('workspace_id', $this->teamWorkspace($teamA)->id)->where('type', 'team')->first();
        $this->assertNotContains($intern->user_id, $umumA->members()->pluck('users.id')->all());
        $this->assertSame($tasksBefore, $space->fresh()->tasks()->count());
        $this->assertFalse($space->fresh()->is_archived);
    }

    public function test_magang_selesai_diarsipkan_read_only_dan_bisa_diaktifkan_lagi(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);
        $space = $this->internSpace($intern);

        $intern->update(['is_active' => false]);

        $this->assertTrue($space->fresh()->is_archived);
        $this->assertSame('viewer', $this->activeMembers($this->teamWorkspace($team))[$intern->user_id]);

        $intern->update(['is_active' => true]);

        $this->assertFalse($space->fresh()->is_archived);
        $this->assertSame('member', $this->activeMembers($this->teamWorkspace($team))[$intern->user_id]);
    }

    public function test_end_date_lewat_diarsipkan_oleh_perintah_terjadwal(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);
        $space = $this->internSpace($intern);

        // Lewatnya tanggal tidak memicu event model, jadi diubah lewat query builder
        DB::table('interns')->where('id', $intern->id)->update(['end_date' => now()->subDay()->toDateString()]);
        $this->assertFalse($space->fresh()->is_archived);

        $this->artisan('workspace:archive-expired')->assertSuccessful();

        $this->assertTrue($space->fresh()->is_archived);
        $this->assertSame('viewer', $this->activeMembers($this->teamWorkspace($team))[$intern->user_id]);
    }

    public function test_intern_tanpa_tim_tetap_dapat_space_tanpa_workspace_tim(): void
    {
        $this->makeAdmin();
        $mentor = $this->makeMentor('Rudy', null);
        $intern = $this->makeIntern('Budi', $mentor, null);

        $this->assertNotNull($this->internSpace($intern));
        $this->assertSame(0, Workspace::where('type', 'team')->count());
        $this->assertSame(
            WorkspaceProvisioner::NO_TEAM,
            app(WorkspaceProvisioner::class)->sync($intern->fresh())
        );
    }

    public function test_workspace_tim_berisi_semua_mentor_aktif_tim(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $a = $this->makeMentor('Rudy', $team);
        $b = $this->makeMentor('Tasmil', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $a, $team);

        $members = $this->activeMembers($this->teamWorkspace($team));

        $this->assertSame('mentor', $members[$a->user_id]);
        $this->assertSame('mentor', $members[$b->user_id]);
        $this->assertSame('member', $members[$intern->user_id]);
    }

    public function test_mentor_baru_langsung_masuk_pengumuman_dan_workspace_tim(): void
    {
        $team = $this->makeTeam('TIM GTA');

        $mentor = $this->makeMentor('Rudy', $team);

        $announcement = Channel::whereNull('workspace_id')->where('type', 'announcement')->firstOrFail();
        $this->assertContains($mentor->user_id, $announcement->members()->pluck('users.id')->all());
        $this->assertSame('mentor', $this->activeMembers($this->teamWorkspace($team))[$mentor->user_id]);
    }

    public function test_mentor_dinonaktifkan_kehilangan_akses_dan_pulih_saat_diaktifkan_lagi(): void
    {
        $admin = $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $a = $this->makeMentor('Rudy', $team);
        $b = $this->makeMentor('Tasmil', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $a, $team);

        $teamWs = $this->teamWorkspace($team);
        $space = $this->internSpace($intern);
        $umum = Channel::where('workspace_id', $teamWs->id)->where('type', 'team')->firstOrFail();
        $dm = Channel::where('workspace_id', $space->id)->where('type', 'direct')->firstOrFail();
        $announcement = Channel::whereNull('workspace_id')->where('type', 'announcement')->firstOrFail();

        $a->update(['is_active' => false]);

        $this->assertArrayNotHasKey($a->user_id, $this->activeMembers($teamWs));
        $this->assertArrayNotHasKey($a->user_id, $this->activeMembers($space));
        foreach ([$umum, $dm, $announcement] as $channel) {
            $this->assertNotContains($a->user_id, $channel->members()->pluck('users.id')->all());
        }
        // mentor lain di tim tidak terpengaruh, dan intern dianggap tanpa mentor aktif
        $this->assertArrayHasKey($b->user_id, $this->activeMembers($teamWs));
        $this->assertContains($b->user_id, $announcement->members()->pluck('users.id')->all());
        $this->assertSame(1, $admin->notifications()->where('type', InternWithoutMentorNotification::class)->count());

        // diaktifkan lagi: akses pulih tanpa data ganda
        $a->update(['is_active' => true]);

        $this->assertSame('mentor', $this->activeMembers($teamWs)[$a->user_id]);
        $this->assertSame('mentor', $this->activeMembers($space)[$a->user_id]);
        $this->assertContains($a->user_id, $dm->members()->pluck('users.id')->all());
        $this->assertContains($a->user_id, $announcement->members()->pluck('users.id')->all());
    }

    public function test_mentor_pindah_tim_keluar_dari_tim_lama_kecuali_masih_membimbing_intern_di_sana(): void
    {
        $this->makeAdmin();
        $teamA = $this->makeTeam('TIM GTA');
        $teamB = $this->makeTeam('TIM DEA');
        $free = $this->makeMentor('Rudy', $teamA);
        $busy = $this->makeMentor('Tasmil', $teamA);
        $this->makeIntern('Ahmad Fauzi', $busy, $teamA);

        $free->update(['team_id' => $teamB->id]);
        $busy->update(['team_id' => $teamB->id]);

        $wsA = $this->teamWorkspace($teamA);
        $wsB = $this->teamWorkspace($teamB);

        $this->assertArrayNotHasKey($free->user_id, $this->activeMembers($wsA), 'tanpa intern di tim lama: keluar');
        $this->assertArrayHasKey($free->user_id, $this->activeMembers($wsB));
        $this->assertArrayHasKey($busy->user_id, $this->activeMembers($wsA), 'masih membimbing intern di tim lama: tetap');
        $this->assertArrayHasKey($busy->user_id, $this->activeMembers($wsB));
    }

    public function test_menghapus_intern_mengarsipkan_space_dan_riwayat_pesan_tetap(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);
        $space = $this->internSpace($intern);
        $umum = Channel::where('workspace_id', $this->teamWorkspace($team)->id)->where('type', 'team')->firstOrFail();
        $message = $umum->messages()->create(['user_id' => $intern->user_id, 'body' => 'Halo dari intern']);

        // urutan seperti AdminInternController@destroy: intern dulu, lalu user
        $userId = $intern->user_id;
        $intern->delete();
        \App\Models\User::find($userId)->delete();

        $this->assertTrue($space->fresh()->is_archived);
        $this->assertNull($space->fresh()->intern_id);
        $this->assertDatabaseHas('messages', ['id' => $message->id, 'user_id' => null, 'body' => 'Halo dari intern']);
    }

    public function test_menghapus_user_langsung_juga_mengarsipkan_space_intern(): void
    {
        $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);
        $space = $this->internSpace($intern);

        // cascade database menghapus intern tanpa event model
        $intern->user->delete();

        $this->assertDatabaseMissing('interns', ['id' => $intern->id]);
        $this->assertTrue($space->fresh()->is_archived);
    }

    public function test_menghapus_mentor_membuat_intern_bimbingannya_dianggap_tanpa_mentor(): void
    {
        $admin = $this->makeAdmin();
        $team = $this->makeTeam('TIM GTA');
        $mentor = $this->makeMentor('Rudy', $team);
        $intern = $this->makeIntern('Ahmad Fauzi', $mentor, $team);
        $space = $this->internSpace($intern);
        $mentorUserId = $mentor->user_id;

        // urutan seperti AdminMentorController@destroy: mentor dulu, lalu user
        $mentor->delete();
        \App\Models\User::find($mentorUserId)->delete();

        $this->assertNull($intern->fresh()->mentor_id);
        $this->assertSame(1, $admin->notifications()->where('type', InternWithoutMentorNotification::class)->count());
        $this->assertArrayNotHasKey($mentorUserId, $this->activeMembers($space));
        // Space dan riwayat intern tetap ada
        $this->assertFalse($space->fresh()->is_archived);
    }
}
