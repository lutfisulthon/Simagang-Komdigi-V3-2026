<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\InternJoinedTeamNotification;
use App\Notifications\InternWithoutMentorNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Menyiapkan workspace untuk intern: Workspace Tim, Space Intern,
 * channel, pesan sambutan, dan tugas onboarding.
 *
 * Idempoten: aman dipanggil berkali-kali untuk intern yang sama.
 */
class WorkspaceProvisioner
{
    public const PROVISIONED = 'provisioned';
    public const NO_TEAM = 'no_team';
    public const PENDING_MENTOR = 'pending_mentor';
    public const PENDING_ACCOUNT = 'pending_account';
    public const ARCHIVED = 'archived';

    public const WELCOME_SETTING_KEY = 'workspace_welcome_message';

    private const DEFAULT_WELCOME = 'Selamat datang {nama} di tim {tim}! Mentor kamu: {mentor}.';
    private const ADMIN_ROLES = ['super_admin', 'admin_full'];

    private ?int $systemUserId = null;

    public function sync(Intern $intern): string
    {
        // Selalu muat ulang: relasi yang sudah ter-cache (mis. dari event created) bisa usang setelah mentor_id berubah.
        $intern->load(['user', 'mentor.user']);

        if (! $this->isActive($intern)) {
            $this->archive($intern);

            return self::ARCHIVED;
        }

        if (! $intern->user) {
            return self::PENDING_ACCOUNT;
        }

        // Mentor yang dinonaktifkan dianggap tidak ada: admin perlu menetapkan mentor pengganti.
        $mentorUser = $intern->mentor?->is_active ? $intern->mentor->user : null;

        // Intern belum (atau tidak lagi) punya mentor: pembuatan workspace ditunda.
        if (! $mentorUser) {
            $space = Workspace::where('type', 'intern')->where('intern_id', $intern->id)->first();
            if ($space) {
                $this->releaseMentors($space);
            }

            $this->notifyAdminsWithoutMentor($intern);

            return self::PENDING_MENTOR;
        }

        $team = $this->resolveTeam($intern);

        $joinedTeam = DB::transaction(function () use ($intern, $mentorUser, $team) {
            $this->syncAnnouncement([$intern->user->id, $mentorUser->id]);

            $joined = $team && $this->syncTeamWorkspace($intern, $team, $mentorUser);

            $this->syncInternSpace($intern, $mentorUser);

            return $joined;
        });

        if ($joinedTeam) {
            $this->notifyInternJoined($intern, $team, $mentorUser);
        }

        if (! $team) {
            Log::warning("Workspace: intern #{$intern->id} tidak punya tim, workspace tim dilewati.");

            return self::NO_TEAM;
        }

        return self::PROVISIONED;
    }

    /**
     * Mentor aktif masuk ke #pengumuman dan Workspace Tim miliknya; mentor nonaktif dicabut
     * aksesnya dari semua workspace. Mentor yang pindah tim keluar dari workspace tim lama
     * selama tidak lagi membimbing intern aktif di tim itu.
     *
     * @param  bool  $resyncInterns  sinkronkan ulang intern aktif bimbingannya (dipakai observer saat status/tim berubah)
     */
    public function syncMentor(Mentor $mentor, bool $resyncInterns = false): void
    {
        $mentor->load('user');

        if ($mentor->user) {
            if ($mentor->is_active) {
                $this->joinMentor($mentor);
            } else {
                $this->releaseMentorAccess($mentor->user);
            }
        }

        if ($resyncInterns) {
            $mentor->interns()->where('is_active', true)->get()->each(fn (Intern $intern) => $this->sync($intern));
        }
    }

    private function joinMentor(Mentor $mentor): void
    {
        $this->syncAnnouncement([$mentor->user->id]);

        $team = $mentor->team_id ? Team::find($mentor->team_id) : null;

        DB::transaction(function () use ($mentor, $team) {
            if ($team) {
                $workspace = $this->teamWorkspace($team);
                $channel = $this->teamChannel($workspace, $team);

                $this->addMember($workspace, $mentor->user->id, 'mentor');
                $channel->members()->syncWithoutDetaching([$mentor->user->id]);
            }

            $this->leaveStaleTeamWorkspaces($mentor);
        });
    }

    /** Keluar dari workspace tim yang bukan timnya dan tidak lagi berisi intern bimbingannya. */
    private function leaveStaleTeamWorkspaces(Mentor $mentor): void
    {
        $keepTeamIds = Intern::where('mentor_id', $mentor->id)
            ->where('is_active', true)
            ->whereNotNull('team_id')
            ->pluck('team_id')
            ->push($mentor->team_id)
            ->filter()
            ->unique()
            ->all();

        $stale = Workspace::where('type', 'team')
            ->whereNotIn('team_id', $keepTeamIds)
            ->whereHas('members', fn ($q) => $q
                ->where('users.id', $mentor->user_id)
                ->where('workspace_members.role', 'mentor')
                ->whereNull('workspace_members.left_at'))
            ->get();

        $this->leaveWorkspaces($mentor->user_id, $stale->pluck('id')->all());
    }

    /** Mentor nonaktif: keluar dari semua workspace (tim dan Space) serta #pengumuman. Riwayat tetap tersimpan. */
    private function releaseMentorAccess(User $user): void
    {
        $workspaceIds = DB::table('workspace_members')
            ->where('user_id', $user->id)
            ->where('role', 'mentor')
            ->whereNull('left_at')
            ->pluck('workspace_id')
            ->all();

        $this->leaveWorkspaces($user->id, $workspaceIds, includeAnnouncement: true);
    }

    /** @param  array<int, int>  $workspaceIds */
    private function leaveWorkspaces(int $userId, array $workspaceIds, bool $includeAnnouncement = false): void
    {
        if ($workspaceIds) {
            DB::table('workspace_members')
                ->where('user_id', $userId)
                ->whereIn('workspace_id', $workspaceIds)
                ->whereNull('left_at')
                ->update(['left_at' => now(), 'updated_at' => now()]);
        }

        $channelIds = Channel::query()
            ->when($workspaceIds, fn ($q) => $q->whereIn('workspace_id', $workspaceIds))
            ->when(! $workspaceIds, fn ($q) => $q->whereRaw('1 = 0'))
            ->pluck('id');

        if ($includeAnnouncement) {
            $channelIds = $channelIds->merge(Channel::whereNull('workspace_id')->where('type', 'announcement')->pluck('id'));
        }

        if ($channelIds->isNotEmpty()) {
            DB::table('channel_members')->where('user_id', $userId)->whereIn('channel_id', $channelIds)->delete();
        }
    }

    private function isActive(Intern $intern): bool
    {
        return $intern->is_active
            && (! $intern->end_date || $intern->end_date->gte(today()));
    }

    /** Tim intern; kalau kosong, memakai tim mentornya. */
    private function resolveTeam(Intern $intern): ?Team
    {
        $teamId = $intern->team_id ?? $intern->mentor?->team_id;

        return $teamId ? Team::find($teamId) : null;
    }

    /** Admin pertama dipakai sebagai pembuat data otomatis dan pengirim pesan sambutan. */
    private function systemUserId(): ?int
    {
        return $this->systemUserId ??= User::whereIn('role', self::ADMIN_ROLES)
            ->orderByRaw("role = 'super_admin' desc")
            ->orderBy('id')
            ->value('id');
    }

    /** @param  array<int, int>  $userIds */
    private function syncAnnouncement(array $userIds): void
    {
        // Kunci supaya dua request bersamaan tidak membuat channel global ganda.
        $announcement = Cache::lock('workspace:announcement-channel', 10)->block(5, fn () => Channel::firstOrCreate(
            ['workspace_id' => null, 'type' => 'announcement'],
            ['name' => 'pengumuman', 'created_by' => $this->systemUserId()]
        ));

        $announcement->members()->syncWithoutDetaching($userIds);
    }

    private function teamWorkspace(Team $team): Workspace
    {
        $workspace = Workspace::firstOrCreate(
            ['type' => 'team', 'team_id' => $team->id],
            [
                'name' => 'Tim ' . $team->name,
                'slug' => 'tim-' . $team->id . '-' . Str::slug($team->name),
                'created_by' => $this->systemUserId(),
            ]
        );

        // Kunci baris workspace supaya channel tidak dibuat ganda secara bersamaan.
        Workspace::whereKey($workspace->id)->lockForUpdate()->first();

        return $workspace;
    }

    private function teamChannel(Workspace $workspace, Team $team): Channel
    {
        return Channel::firstOrCreate(
            ['workspace_id' => $workspace->id, 'type' => 'team'],
            ['name' => 'umum-' . Str::slug($team->name), 'created_by' => $this->systemUserId()]
        );
    }

    /** @return bool true jika intern baru bergabung (atau bergabung kembali) ke workspace tim. */
    private function syncTeamWorkspace(Intern $intern, Team $team, User $mentorUser): bool
    {
        $workspace = $this->teamWorkspace($team);

        $this->leaveOtherTeamWorkspaces($intern->user, $team);

        $channel = $this->teamChannel($workspace, $team);

        // Workspace Tim berisi semua mentor aktif di tim itu, bukan hanya mentor intern ini.
        $mentorIds = Mentor::where('team_id', $team->id)
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->push($mentorUser->id)
            ->unique();

        foreach ($mentorIds as $mentorId) {
            $this->addMember($workspace, $mentorId, 'mentor');
        }

        $joined = $this->addMember($workspace, $intern->user->id, 'member');

        $channel->members()->syncWithoutDetaching($mentorIds->push($intern->user->id)->all());

        if ($joined) {
            $this->postWelcome($channel, $intern, $team, $mentorUser);
        }

        return $joined;
    }

    /** Pindah tim: keanggotaan workspace tim lama dicabut. */
    private function leaveOtherTeamWorkspaces(User $user, Team $team): void
    {
        $old = Workspace::where('type', 'team')
            ->where('team_id', '!=', $team->id)
            ->whereHas('members', fn ($q) => $q->whereKey($user->id)->whereNull('workspace_members.left_at'))
            ->get();

        foreach ($old as $workspace) {
            $workspace->members()->updateExistingPivot($user->id, ['left_at' => now()]);
            foreach ($workspace->channels as $channel) {
                $channel->members()->detach($user->id);
            }
        }
    }

    private function syncInternSpace(Intern $intern, User $mentorUser): Workspace
    {
        $space = Workspace::firstOrCreate(
            ['type' => 'intern', 'intern_id' => $intern->id],
            [
                'name' => 'Space ' . $intern->name,
                'slug' => 'space-' . $intern->id . '-' . Str::slug($intern->name),
                'created_by' => $this->systemUserId(),
            ]
        );

        Workspace::whereKey($space->id)->lockForUpdate()->first();

        if ($space->is_archived) {
            $space->update(['is_archived' => false]);
        }

        $dm = Channel::firstOrCreate(
            ['workspace_id' => $space->id, 'type' => 'direct'],
            ['name' => 'dm-' . Str::slug($intern->name), 'is_private' => true, 'created_by' => $this->systemUserId()]
        );

        // Mentor diganti: mentor lama keluar, riwayat tugas dan chat tetap ada.
        $this->releaseMentors($space, $mentorUser->id);

        $this->addMember($space, $intern->user->id, 'owner');
        $this->addMember($space, $mentorUser->id, 'mentor');
        $dm->members()->syncWithoutDetaching([$mentorUser->id, $intern->user->id]);

        return $space;
    }

    /** Mengeluarkan mentor dari Space Intern, kecuali $exceptUserId. */
    private function releaseMentors(Workspace $space, ?int $exceptUserId = null): void
    {
        $query = $space->members()
            ->wherePivot('role', 'mentor')
            ->wherePivotNull('left_at');

        if ($exceptUserId) {
            $query->where('users.id', '!=', $exceptUserId);
        }

        $formerMentors = $query->get();
        if ($formerMentors->isEmpty()) {
            return;
        }

        $dm = $space->channels()->where('type', 'direct')->first();

        foreach ($formerMentors as $former) {
            $space->members()->updateExistingPivot($former->id, ['left_at' => now()]);
            $dm?->members()->detach($former->id);
        }
    }

    /** Dipanggil hanya saat intern baru bergabung ke workspace tim, jadi sambutan terkirim sekali per intern. */
    private function postWelcome(Channel $channel, Intern $intern, Team $team, User $mentorUser): void
    {
        $template = DB::table('system_settings')
            ->where('key', self::WELCOME_SETTING_KEY)
            ->value('value') ?: self::DEFAULT_WELCOME;

        $body = strtr($template, [
            '{nama}' => $intern->name,
            '{tim}' => $team->name,
            '{mentor}' => $intern->mentor->name,
        ]);

        $channel->messages()->create([
            'user_id' => $this->systemUserId() ?? $mentorUser->id,
            'body' => $body,
        ]);
    }

    /** Magang selesai atau nonaktif: akses menjadi read-only, data tidak dihapus. */
    private function archive(Intern $intern): void
    {
        Workspace::where('type', 'intern')
            ->where('intern_id', $intern->id)
            ->update(['is_archived' => true]);

        if (! $intern->user_id) {
            return;
        }

        $teamWorkspaceIds = Workspace::where('type', 'team')->pluck('id');

        DB::table('workspace_members')
            ->whereIn('workspace_id', $teamWorkspaceIds)
            ->where('user_id', $intern->user_id)
            ->whereNull('left_at')
            ->update(['role' => 'viewer', 'updated_at' => now()]);
    }

    /** @return bool true jika user baru ditambahkan atau bergabung kembali setelah keluar. */
    private function addMember(Workspace $workspace, int $userId, string $role): bool
    {
        $existing = $workspace->members()->whereKey($userId)->first();

        if (! $existing) {
            $workspace->members()->attach($userId, ['role' => $role, 'joined_at' => now()]);

            return true;
        }

        $rejoined = (bool) $existing->pivot->left_at;

        if ($rejoined || $existing->pivot->role !== $role) {
            $workspace->members()->updateExistingPivot($userId, [
                'role' => $role,
                'left_at' => null,
                'joined_at' => $rejoined ? now() : $existing->pivot->joined_at,
            ]);
        }

        return $rejoined;
    }

    /** @return Collection<int, User> */
    private function admins(): Collection
    {
        return User::whereIn('role', self::ADMIN_ROLES)->get();
    }

    private function notifyInternJoined(Intern $intern, Team $team, User $mentorUser): void
    {
        try {
            $this->admins()->push($mentorUser)->unique('id')
                ->each(fn (User $user) => $user->notify(new InternJoinedTeamNotification($intern, $team)));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Satu notifikasi belum dibaca per intern, supaya tidak berulang di setiap sync. */
    private function notifyAdminsWithoutMentor(Intern $intern): void
    {
        try {
            foreach ($this->admins() as $admin) {
                $pending = $admin->notifications()
                    ->where('type', InternWithoutMentorNotification::class)
                    ->where('data->intern_id', $intern->id)
                    ->whereNull('read_at')
                    ->exists();

                if (! $pending) {
                    $admin->notify(new InternWithoutMentorNotification($intern));
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
