<?php

namespace Tests\Feature\Workspace;

use App\Models\Channel;
use App\Models\Workspace;
use App\Notifications\GroupJoinedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    private $ahmad2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();
        // Rudy punya dua anak bimbingan; Siti milik Tasmil.
        $this->ahmad2 = $this->makeIntern('Dewi Lestari', $this->rudy, $this->gta);
    }

    private function mentor()
    {
        return $this->rudy->user;
    }

    private function createGroup(array $payload = [], $as = null)
    {
        return $this->actingAs($as ?? $this->mentor())->postJson('/workspace/groups', $payload + [
            'name' => 'Tim Redesign Website',
            'color' => '#4f46e5',
            'description' => 'Fokus UI',
            'intern_ids' => [$this->ahmad->id],
        ]);
    }

    public function test_roster_menampilkan_anak_bimbingan_dan_kelompoknya(): void
    {
        $res = $this->actingAs($this->mentor())->getJson('/workspace/groups/roster')->assertOk();

        $res->assertJsonCount(2, 'interns')->assertJsonCount(0, 'groups')
            ->assertJsonPath('interns.0.name', 'Ahmad Fauzi')
            ->assertJsonPath('interns.0.group', null);

        $id = $this->createGroup()->json('data.id');

        $res = $this->actingAs($this->mentor())->getJson('/workspace/groups/roster');
        $res->assertJsonPath('interns.0.group.id', $id)
            ->assertJsonPath('interns.0.group.name', 'Tim Redesign Website')
            ->assertJsonPath('interns.1.group', null)
            ->assertJsonPath('groups.0.id', $id)
            ->assertJsonPath('groups.0.members_count', 1);
    }

    public function test_mentor_membuat_kelompok_lengkap_dengan_anggota_dan_channel(): void
    {
        $res = $this->createGroup(['intern_ids' => [$this->ahmad->id, $this->ahmad2->id]])->assertCreated();

        $res->assertJsonPath('data.type', 'group')
            ->assertJsonPath('data.name', 'Tim Redesign Website')
            ->assertJsonPath('data.color', '#4f46e5')
            ->assertJsonPath('data.description', 'Fokus UI')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.is_read_only', false)
            ->assertJsonPath('data.interns_count', 2)
            ->assertJsonPath('data.mentor.id', $this->mentor()->id)
            ->assertJsonPath('data.team.id', $this->gta->id)
            ->assertJsonPath('data.can', ['rename' => true, 'add_members' => true, 'finish' => true]);

        $group = Workspace::findOrFail($res->json('data.id'));
        $this->assertSame($this->mentor()->id, $group->mentor_user_id);

        // satu channel kelompok + satu DM per anggota
        $this->assertSame(1, Channel::where('workspace_id', $group->id)->where('type', 'team')->count());
        $this->assertSame(2, Channel::where('workspace_id', $group->id)->where('type', 'direct')->count());
        $this->assertSame(3, Channel::where('workspace_id', $group->id)->where('type', 'team')->first()->members()->count());

        // Ahmad hanya melihat DM miliknya, Dewi tidak melihat DM Ahmad
        $dmAhmad = Channel::where('workspace_id', $group->id)->where('type', 'direct')->whereHas('members', fn ($m) => $m->where('users.id', $this->ahmad->user_id))->first();
        $this->actingAs($this->ahmad2->user)->getJson("/workspace/channels/{$dmAhmad->id}/messages")->assertForbidden();
        $this->actingAs($this->ahmad->user)->getJson("/workspace/channels/{$dmAhmad->id}/messages")->assertOk();

        $this->assertSame(1, $this->notificationCount($this->ahmad->user, GroupJoinedNotification::class));
        $this->assertSame(1, $this->notificationCount($this->ahmad2->user, GroupJoinedNotification::class));
    }

    public function test_warna_default_dan_validasi_pembuatan(): void
    {
        $this->createGroup(['color' => null])->assertCreated()->assertJsonPath('data.color', '#4f46e5');

        $this->createGroup(['name' => ''])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->createGroup(['name' => str_repeat('x', 61)])->assertUnprocessable();
        $this->createGroup(['color' => 'merah'])->assertUnprocessable()->assertJsonValidationErrors('color');
        $this->createGroup(['intern_ids' => []])->assertUnprocessable()->assertJsonValidationErrors('intern_ids');
        $this->createGroup(['intern_ids' => [$this->ahmad2->id, $this->ahmad2->id]])->assertUnprocessable();
    }

    public function test_hanya_anak_bimbingan_sendiri_dan_belum_berkelompok(): void
    {
        // Siti anak bimbingan Tasmil, bukan Rudy
        $this->createGroup(['intern_ids' => [$this->siti->id]])->assertUnprocessable()->assertJsonValidationErrors('intern_ids');
        $this->assertSame(0, Workspace::where('type', 'group')->count());

        $this->createGroup()->assertCreated();
        // Ahmad sudah berkelompok: ditolak, dan tidak ada kelompok setengah jadi
        $this->createGroup(['name' => 'Lain', 'intern_ids' => [$this->ahmad2->id, $this->ahmad->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('intern_ids');
        $this->assertSame(1, Workspace::where('type', 'group')->count());
        $this->assertSame(0, $this->notificationCount($this->ahmad2->user, GroupJoinedNotification::class), 'kelompok gagal tidak mengirim notifikasi');
    }

    public function test_intern_admin_dan_mentor_tanpa_profil_tidak_bisa_membuat_kelompok(): void
    {
        $this->createGroup([], $this->ahmad->user)->assertForbidden();
        $this->createGroup([], $this->admin)->assertForbidden();
        $this->actingAs($this->ahmad->user)->getJson('/workspace/groups/roster')->assertForbidden();
    }

    public function test_kelompok_muncul_di_daftar_mentor_dan_intern_beserta_banner_belum_berkelompok(): void
    {
        $id = $this->createGroup()->json('data.id');

        $this->actingAs($this->mentor())->get('/workspace')->assertOk()
            ->assertViewHas('can', fn ($c) => $c['create_group'] === true)
            ->assertViewHas('groups', fn ($g) => collect($g->items())->contains(fn ($w) => $w['id'] === $id))
            ->assertViewHas('ungrouped', fn ($u) => count($u) === 1 && $u[0]['id'] === $this->ahmad2->id);

        // Intern punya Space, Workspace Tim, dan kelompok: daftar pilihan, bukan redirect
        $this->actingAs($this->ahmad->user)->get('/workspace')->assertOk()
            ->assertViewHas('groups', fn ($g) => collect($g->items())->contains(fn ($w) => $w['id'] === $id) && $g->total() === 3)
            ->assertViewHas('ungrouped', []);

        // Mentor lain tidak melihatnya
        $this->actingAs($this->tasmil->user)->get('/workspace')
            ->assertViewHas('groups', fn ($g) => ! collect($g->items())->contains(fn ($w) => $w['id'] === $id));
        // Intern bukan anggota: 403 saat membuka
        $this->actingAs($this->siti->user)->get("/workspace/{$id}")->assertForbidden();
        $this->actingAs($this->ahmad->user)->get("/workspace/{$id}")->assertOk();
    }

    public function test_halaman_kelompok_membawa_kandidat_anggota_hanya_untuk_pemilik(): void
    {
        $id = $this->createGroup()->json('data.id');

        $this->actingAs($this->mentor())->get("/workspace/{$id}")->assertOk()
            ->assertViewHas('candidates', fn ($c) => count($c) === 1 && $c[0]['id'] === $this->ahmad2->id)
            ->assertViewHas('workspace', fn ($w) => $w['can']['add_members'] === true);

        $this->actingAs($this->admin)->get("/workspace/{$id}")->assertOk()
            ->assertViewHas('candidates', [])
            ->assertViewHas('workspace', fn ($w) => $w['can']['add_members'] === false && $w['can']['rename'] === true);

        $this->actingAs($this->ahmad->user)->get("/workspace/{$id}")
            ->assertViewHas('workspace', fn ($w) => $w['can'] === ['rename' => false, 'add_members' => false, 'finish' => false]);
    }

    public function test_ganti_nama_dan_warna(): void
    {
        $id = $this->createGroup()->json('data.id');

        $this->actingAs($this->mentor())->patchJson("/workspace/groups/{$id}", ['name' => 'Nama Baru', 'color' => '#0f766e'])
            ->assertOk()->assertJsonPath('data.name', 'Nama Baru')->assertJsonPath('data.color', '#0f766e');

        $this->actingAs($this->mentor())->patchJson("/workspace/groups/{$id}", ['name' => ''])->assertUnprocessable();
        $this->actingAs($this->mentor())->patchJson("/workspace/groups/{$id}", ['color' => 'x'])->assertUnprocessable();
        $this->actingAs($this->admin)->patchJson("/workspace/groups/{$id}", ['name' => 'Oleh admin'])->assertOk();
        $this->actingAs($this->tasmil->user)->patchJson("/workspace/groups/{$id}", ['name' => 'Curang'])->assertForbidden();
        $this->actingAs($this->ahmad->user)->patchJson("/workspace/groups/{$id}", ['name' => 'Curang'])->assertForbidden();
    }

    public function test_hanya_kelompok_yang_bisa_diubah_lewat_endpoint_ini(): void
    {
        $this->actingAs($this->mentor())->patchJson("/workspace/groups/{$this->wsGta->id}", ['name' => 'Ubah tim'])->assertNotFound();
        $this->actingAs($this->mentor())->postJson("/workspace/groups/{$this->spAhmad->id}/members", ['intern_ids' => [$this->ahmad2->id]])->assertNotFound();
    }

    public function test_tandai_selesai_membuat_kelompok_hanya_baca(): void
    {
        $id = $this->createGroup()->json('data.id');

        $this->actingAs($this->mentor())->patchJson("/workspace/groups/{$id}", ['status' => 'finished'])
            ->assertOk()->assertJsonPath('data.status', 'finished')->assertJsonPath('data.is_read_only', true)
            ->assertJsonPath('data.can', ['rename' => false, 'add_members' => false, 'finish' => false]);

        $this->assertNotNull(Workspace::find($id)->finished_at);
        $this->actingAs($this->mentor())->patchJson("/workspace/groups/{$id}", ['name' => 'Lagi'])->assertForbidden();
        $this->postTask($this->mentor(), Workspace::find($id), ['assignee_ids' => [$this->ahmad->id]])->assertForbidden();

        // Anggota yang sudah selesai kelompoknya boleh masuk kelompok baru
        $this->createGroup(['name' => 'Kelompok Dua'])->assertCreated();
    }

    public function test_tambah_anggota_oleh_mentor_pemilik(): void
    {
        $id = $this->createGroup()->json('data.id');

        $this->actingAs($this->mentor())->postJson("/workspace/groups/{$id}/members", ['intern_ids' => [$this->ahmad2->id]])
            ->assertOk()->assertJsonPath('data.interns_count', 2);

        $this->assertSame(1, $this->notificationCount($this->ahmad2->user, GroupJoinedNotification::class));
        $this->assertSame(2, Channel::where('workspace_id', $id)->where('type', 'direct')->count());

        // Sudah berkelompok, bukan anak bimbingan, bukan pemilik, kosong
        $this->actingAs($this->mentor())->postJson("/workspace/groups/{$id}/members", ['intern_ids' => [$this->ahmad2->id]])->assertUnprocessable();
        $this->actingAs($this->mentor())->postJson("/workspace/groups/{$id}/members", ['intern_ids' => [$this->siti->id]])->assertUnprocessable();
        $this->actingAs($this->mentor())->postJson("/workspace/groups/{$id}/members", ['intern_ids' => []])->assertUnprocessable();
        $this->actingAs($this->admin)->postJson("/workspace/groups/{$id}/members", ['intern_ids' => [$this->ahmad2->id]])->assertForbidden();
        $this->actingAs($this->tasmil->user)->postJson("/workspace/groups/{$id}/members", ['intern_ids' => [$this->siti->id]])->assertForbidden();
    }

    public function test_tugas_dan_sprint_berjalan_di_kelompok(): void
    {
        $group = Workspace::find($this->createGroup(['intern_ids' => [$this->ahmad->id, $this->ahmad2->id]])->json('data.id'));

        $this->postTask($this->mentor(), $group, ['assignee_ids' => [$this->ahmad->id, $this->ahmad2->id]])->assertCreated();
        // anggota tim GTA yang bukan anggota kelompok ditolak
        $this->postTask($this->mentor(), $group, ['assignee_ids' => [$this->siti->id]])->assertUnprocessable();
        // intern tidak membuat tugas di kelompok
        $this->postTask($this->ahmad->user, $group)->assertForbidden();

        $this->actingAs($this->mentor())->postJson("/workspace/{$group->id}/sprints", ['end_date' => now()->addWeek()->toDateString(), 'leader_id' => $this->ahmad->id])
            ->assertCreated();

        // label memakai tim mentor pemilik
        $this->assertSame($this->gta->id, $group->labelTeamId());
        $this->actingAs($this->ahmad2->user)->get("/workspace/{$group->id}")->assertOk()
            ->assertViewHas('initialTasks', fn ($t) => count($t) === 1);
        $this->actingAs($this->ahmad->user)->get('/workspace/summary')->assertOk();
    }

    public function test_scheduler_menyelesaikan_kelompok_yang_semua_anggotanya_selesai_magang(): void
    {
        $group = Workspace::find($this->createGroup(['intern_ids' => [$this->ahmad->id, $this->ahmad2->id]])->json('data.id'));

        $this->ahmad->update(['end_date' => now()->subDay()->toDateString()]);
        $this->artisan('workspace:archive-expired')->assertSuccessful();
        $this->assertFalse($group->fresh()->is_archived, 'masih ada anggota yang aktif');

        $this->ahmad2->update(['is_active' => false]);
        $this->artisan('workspace:archive-expired')->assertSuccessful();
        $this->assertTrue($group->fresh()->is_archived);
        $this->assertNotNull($group->fresh()->finished_at);
    }
}
