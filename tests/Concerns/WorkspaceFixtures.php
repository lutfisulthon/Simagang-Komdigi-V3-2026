<?php

namespace Tests\Concerns;

use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Pembuat data uji untuk modul Workspace. Membuat Mentor dan Intern lewat Eloquent,
 * jadi observer provisioning ikut berjalan seperti di aplikasi.
 */
trait WorkspaceFixtures
{
    protected function makeUser(string $role, string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => Str::slug($name) . '-' . Str::random(6) . '@test.local',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    protected function makeAdmin(string $name = 'Admin Uji', string $role = 'admin_full'): User
    {
        return $this->makeUser($role, $name);
    }

    protected function makeTeam(string $name): Team
    {
        return Team::create(['name' => $name]);
    }

    protected function makeMentor(string $name, ?Team $team = null): Mentor
    {
        $user = $this->makeUser('mentor', $name);

        return Mentor::create([
            'name' => $name,
            'email' => $user->email,
            'user_id' => $user->id,
            'team_id' => $team?->id,
            'is_active' => true,
        ])->load('user');
    }

    protected function makeIntern(string $name, ?Mentor $mentor = null, ?Team $team = null, array $attributes = []): Intern
    {
        $user = $this->makeUser('intern', $name);

        return Intern::create($attributes + [
            'user_id' => $user->id,
            'name' => $name,
            'gender' => 'Laki-laki',
            'education_level' => 'S1/D4',
            'institution' => 'Universitas Uji',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'is_active' => true,
            'mentor_id' => $mentor?->id,
            'team_id' => $team?->id,
        ])->load('user');
    }

    protected function teamWorkspace(Team $team): Workspace
    {
        return Workspace::where('type', 'team')->where('team_id', $team->id)->firstOrFail();
    }

    protected function internSpace(Intern $intern): Workspace
    {
        return Workspace::where('type', 'intern')->where('intern_id', $intern->id)->firstOrFail();
    }

    /** Anggota aktif (belum left_at) suatu workspace, beserta role-nya: [user_id => role]. */
    protected function activeMembers(Workspace $workspace): array
    {
        return $workspace->members()
            ->wherePivotNull('left_at')
            ->get()
            ->mapWithKeys(fn (User $u) => [$u->id => $u->pivot->role])
            ->all();
    }
}
