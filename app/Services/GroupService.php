<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Intern;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\GroupJoinedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Kelompok buatan mentor (workspaces.type = 'group'), kontrak FE #18.
 * Aturan: 1 anak magang = 1 kelompok aktif; anggota tidak bisa dikeluarkan.
 * Workspace tim otomatis dan Space pribadi tidak terpengaruh.
 */
class GroupService
{
    public const COLORS = ['#4f46e5', '#2563eb', '#0f766e', '#15803d', '#b45309', '#c2410c', '#be185d', '#7e22ce'];

    /** Anak bimbingan aktif mentor yang punya akun, siap dipilih sebagai anggota. */
    public function interns(User $mentorUser): Collection
    {
        $mentor = $mentorUser->mentor;

        if (! $mentor) {
            return collect();
        }

        return Intern::query()
            ->where('mentor_id', $mentor->id)
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Kelompok aktif per user (user_id => Workspace), untuk menandai siapa yang sudah berkelompok.
     *
     * @param  array<int, int>  $userIds
     * @return Collection<int, Workspace>
     */
    public function activeGroupsOf(array $userIds): Collection
    {
        $rows = DB::table('workspace_members')
            ->join('workspaces', 'workspaces.id', '=', 'workspace_members.workspace_id')
            ->where('workspaces.type', 'group')
            ->where('workspaces.is_archived', false)
            ->whereNull('workspace_members.left_at')
            ->where('workspace_members.role', 'member')
            ->whereIn('workspace_members.user_id', $userIds)
            ->get(['workspace_members.user_id', 'workspaces.id as workspace_id']);

        $groups = Workspace::whereIn('id', $rows->pluck('workspace_id')->unique())->get()->keyBy('id');

        return $rows->mapWithKeys(fn ($r) => [$r->user_id => $groups[$r->workspace_id]]);
    }

    /**
     * Workspace utama tiap intern: kelompok aktifnya bila ada, kalau tidak Space pribadinya.
     * `$activeOnly` mengabaikan Space yang sudah diarsipkan.
     *
     * @param  iterable<int, Intern>  $interns
     * @return Collection<int, Workspace> intern_id => Workspace (intern tanpa workspace tidak ikut)
     */
    public function homeWorkspaces(iterable $interns, bool $activeOnly = true): Collection
    {
        $interns = collect($interns);
        $groups = $this->activeGroupsOf($interns->pluck('user_id')->filter()->all());
        $spaces = Workspace::where('type', 'intern')
            ->whereIn('intern_id', $interns->pluck('id'))
            ->when($activeOnly, fn ($q) => $q->where('is_archived', false))
            ->get()->keyBy('intern_id');

        return $interns->mapWithKeys(function (Intern $i) use ($groups, $spaces) {
            $home = $groups[$i->user_id] ?? $spaces[$i->id] ?? null;

            return $home ? [$i->id => $home] : [];
        });
    }

    /**
     * Buat kelompok: workspace, anggota, channel kelompok, dan DM mentor dengan tiap anggota.
     *
     * @param  array{name: string, color?: ?string, description?: ?string}  $data
     * @param  array<int, int>  $internIds
     */
    public function create(User $mentorUser, array $data, array $internIds): Workspace
    {
        $interns = collect();

        $group = DB::transaction(function () use ($mentorUser, $data, $internIds, &$interns) {
            $interns = $this->assertAvailable($mentorUser, $internIds);

            $name = trim($data['name']);
            $group = Workspace::create([
                'type' => 'group',
                'name' => $name,
                'slug' => 'kelompok-' . Str::slug($name) . '-' . Str::lower(Str::random(6)),
                'color' => $data['color'] ?? self::COLORS[0],
                'description' => ($data['description'] ?? null) ?: null,
                'created_by' => $mentorUser->id,
                'mentor_user_id' => $mentorUser->id,
            ]);

            $group->members()->attach($mentorUser->id, ['role' => 'mentor', 'joined_at' => now()]);

            $channel = Channel::create([
                'workspace_id' => $group->id,
                'name' => Str::slug($name) ?: 'kelompok',
                'type' => 'team',
                'created_by' => $mentorUser->id,
            ]);
            $channel->members()->attach($mentorUser->id);

            $this->enroll($group, $channel, $mentorUser, $interns);

            return $group;
        });

        $this->notifyJoined($group, $interns);

        return $group;
    }

    /** Tambah anggota ke kelompok aktif. Aturan sama dengan pembuatan. */
    public function addMembers(Workspace $group, User $mentorUser, array $internIds): Workspace
    {
        $interns = DB::transaction(function () use ($group, $mentorUser, $internIds) {
            Workspace::whereKey($group->id)->lockForUpdate()->first();

            $interns = $this->assertAvailable($mentorUser, $internIds);
            $channel = Channel::where('workspace_id', $group->id)->where('type', 'team')->firstOrFail();

            $this->enroll($group, $channel, $mentorUser, $interns);

            return $interns;
        });

        $this->notifyJoined($group, $interns);

        return $group;
    }

    /** Tandai selesai magang: kelompok menjadi hanya baca. */
    public function finish(Workspace $group): void
    {
        if (! $group->is_archived) {
            $group->forceFill(['is_archived' => true, 'finished_at' => now()])->save();
        }
    }

    /**
     * Kelompok aktif yang semua anggotanya sudah selesai magang (end_date lewat atau tidak aktif).
     *
     * @return Collection<int, Workspace>
     */
    public function expired(): Collection
    {
        return Workspace::query()
            ->where('type', 'group')
            ->where('is_archived', false)
            ->whereHas('members', fn ($m) => $m->where('workspace_members.role', 'member')->whereNull('workspace_members.left_at'))
            ->whereDoesntHave('members', fn ($m) => $m
                ->where('workspace_members.role', 'member')
                ->whereNull('workspace_members.left_at')
                ->whereHas('intern', fn ($i) => $i->where('is_active', true)->where(fn ($d) => $d->whereNull('end_date')->orWhereDate('end_date', '>=', today()))))
            ->get();
    }

    /**
     * Intern harus anak bimbingan mentor ini, aktif, dan belum berkelompok.
     *
     * @param  array<int, int>  $internIds
     * @return Collection<int, Intern>
     */
    private function assertAvailable(User $mentorUser, array $internIds): Collection
    {
        $internIds = array_values(array_unique(array_map('intval', $internIds)));
        $mine = $this->interns($mentorUser)->keyBy('id');

        if (array_diff($internIds, $mine->keys()->all())) {
            throw ValidationException::withMessages(['intern_ids' => 'Ada anak magang yang bukan anak bimbingan Anda.']);
        }

        $interns = $mine->only($internIds)->values();

        // Kunci baris intern supaya dua kelompok tidak merebut anak magang yang sama bersamaan.
        Intern::whereIn('id', $internIds)->orderBy('id')->lockForUpdate()->get();

        $grouped = $this->activeGroupsOf($interns->pluck('user_id')->all());
        if ($grouped->isNotEmpty()) {
            $names = $interns->filter(fn ($i) => $grouped->has($i->user_id))->pluck('name')->implode(', ');
            throw ValidationException::withMessages(['intern_ids' => "Sudah berkelompok: {$names}."]);
        }

        return $interns;
    }

    /** @param  Collection<int, Intern>  $interns */
    private function enroll(Workspace $group, Channel $channel, User $mentorUser, Collection $interns): void
    {
        foreach ($interns as $intern) {
            $group->members()->syncWithoutDetaching([$intern->user_id => ['role' => 'member', 'joined_at' => now()]]);
            $channel->members()->syncWithoutDetaching([$intern->user_id]);

            $dm = Channel::create([
                'workspace_id' => $group->id,
                'name' => 'dm-' . Str::slug($intern->name),
                'type' => 'direct',
                'is_private' => true,
                'created_by' => $mentorUser->id,
            ]);
            $dm->members()->attach([$mentorUser->id, $intern->user_id]);
        }
    }

    /** @param  Collection<int, Intern>  $interns */
    private function notifyJoined(Workspace $group, Collection $interns): void
    {
        foreach ($interns as $intern) {
            try {
                $intern->user?->notify(new GroupJoinedNotification($group));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
