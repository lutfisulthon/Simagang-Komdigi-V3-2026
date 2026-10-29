<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Channel;
use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Message;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data ringkasan untuk widget dashboard, kolom/rekap Monitoring, tab tugas di detail intern,
 * dan rekap tugas (laporan akhir). Bentuknya mengikuti docs/workspace/kontrak-frontend.md §6 dan §8.
 */
class WorkspaceInsightService
{
    private const TASK_RELATIONS = ['assignees', 'labels', 'checklistItems', 'workspace'];

    public function __construct(private WorkspacePresenter $presenter, private ChannelUnreadService $unread, private GroupService $groups)
    {
    }

    // ------------------------------------------------------------------ widget dashboard

    /** Ringkasan untuk user yang login: intern, atau mentor/admin. */
    public function summary(User $user): array
    {
        if ($user->isIntern() && $user->intern) {
            return $this->internSummary($user, $user->intern);
        }

        abort_unless($user->isMentor() || $user->isSuperAdmin() || $user->isAdminFull(), 403);

        return $this->mentorSummary($user);
    }

    private function internSummary(User $user, Intern $intern): array
    {
        $space = Workspace::where('type', 'intern')->where('intern_id', $intern->id)->orderBy('is_archived')->first();

        $active = fn () => Task::query()
            ->whereHas('assignees', fn ($q) => $q->where('interns.id', $intern->id))
            ->whereHas('workspace', fn ($q) => $q->where('is_archived', false))
            ->where('status', '!=', TaskStatus::Done->value);

        $tasks = $active()
            ->with(self::TASK_RELATIONS)
            ->withCount(['comments', 'attachments'])
            ->orderBy('due_at')
            ->limit(5)
            ->get();

        return [
            'role' => 'intern',
            'pending_mentor' => $space === null,
            'workspace_url' => $space ? route('workspace.show', $space) : route('workspace.index'),
            'my_tasks' => [
                'data' => $tasks->map(fn (Task $t) => $this->cardWithWorkspace($t, $user))->values()->all(),
                'counts' => [
                    'active' => $active()->count(),
                    'revision' => $active()->where('status', TaskStatus::Revision->value)->count(),
                    'late' => $active()->whereNotNull('overdue_at')->count(),
                ],
            ],
            'unread_channels' => $this->unreadChannels($user, $space),
        ];
    }

    /** @return array<int, array> maks. 5 channel dengan pesan belum dibaca, urut pesan terbaru */
    private function unreadChannels(User $user, ?Workspace $ownSpace): array
    {
        $channelIds = DB::table('channel_members')
            ->join('channels', 'channels.id', '=', 'channel_members.channel_id')
            ->where('channel_members.user_id', $user->id)
            ->where('channel_members.is_muted', false)
            ->where('channels.is_archived', false)
            ->pluck('channels.id')
            ->all();

        $counts = collect($this->unread->counts($user, $channelIds))->filter(fn ($n) => $n > 0);

        if ($counts->isEmpty()) {
            return [];
        }

        $latestIds = DB::table('messages')
            ->whereIn('channel_id', $counts->keys())
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->selectRaw('max(id) as id')
            ->groupBy('channel_id')
            ->pluck('id');

        $last = Message::with('user')->whereIn('id', $latestIds)->get()->keyBy('channel_id');

        $top = $counts->keys()->sortByDesc(fn ($id) => $last[$id]->id ?? 0)->take(5)->values();
        $channels = Channel::with('workspace')->whereIn('id', $top)->get()->keyBy('id');
        $minis = collect($this->presenter->channelList($top->map(fn ($id) => $channels[$id]), $user))->keyBy('id');

        return $top->map(function ($id) use ($counts, $last, $channels, $minis, $ownSpace) {
            $channel = $channels[$id];
            $workspace = $channel->workspace;
            $base = $workspace ? route('workspace.show', $workspace) : ($ownSpace ? route('workspace.show', $ownSpace) : route('workspace.index'));
            $message = $last[$id] ?? null;

            return [
                'channel' => ['id' => $channel->id, 'name' => $channel->name, 'type' => $channel->type, 'members' => $minis[$id]['members']],
                'workspace' => $workspace ? $this->presenter->workspaceMini($workspace) : null,
                'url' => $base . '?tab=channel&channel=' . $channel->id,
                'unread_count' => (int) $counts[$id],
                'last_message' => $message ? [
                    'user' => $this->presenter->user($message->user),
                    'body' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($message->body))), 120),
                    'created_at' => $this->presenter->iso($message->created_at),
                ] : null,
            ];
        })->all();
    }

    private function mentorSummary(User $user): array
    {
        $isAdmin = $user->isSuperAdmin() || $user->isAdminFull();

        $scope = fn () => Task::query()->whereHas('workspace', fn ($w) => $w
            ->where('is_archived', false)
            ->when(! $isAdmin, fn ($q) => $q->whereHas('members', fn ($m) => $m
                ->where('users.id', $user->id)
                ->where('workspace_members.role', 'mentor')
                ->whereNull('workspace_members.left_at'))));

        $review = $scope()->where('status', TaskStatus::Review->value)->with(self::TASK_RELATIONS)->withCount(['comments', 'attachments'])->get();

        // Waktu masuk Review = aktivitas pindah status terakhir ke review.
        $submitted = DB::table('task_activities')
            ->whereIn('task_id', $review->pluck('id'))
            ->where('action', 'status_changed')
            ->whereRaw("json_unquote(json_extract(meta, '$.to')) = 'review'")
            ->groupBy('task_id')
            ->selectRaw('task_id, max(created_at) as at')
            ->pluck('at', 'task_id');

        $waiting = $review
            ->each(fn (Task $t) => $t->setAttribute('submitted_at', $submitted[$t->id] ?? $t->updated_at))
            ->sortBy(fn (Task $t) => \Illuminate\Support\Carbon::parse($t->submitted_at)->timestamp);

        $late = $scope()
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereNotNull('overdue_at')
            ->with(['assignees', 'workspace'])
            ->orderBy('due_at')
            ->get();

        $lateByIntern = $late
            ->flatMap(fn (Task $t) => $t->assignees->map(fn ($i) => ['intern' => $i, 'task' => $t]))
            ->groupBy(fn ($row) => $row['intern']->id)
            ->map(fn (Collection $rows) => [
                'intern' => $this->presenter->intern($rows->first()['intern']),
                'workspace' => $this->presenter->workspaceMini($rows->first()['task']->workspace),
                'late_count' => $rows->count(),
                'tasks' => $rows->map(fn ($r) => [
                    'id' => $r['task']->id,
                    'code' => $r['task']->code,
                    'title' => $r['task']->title,
                    'due_at' => $this->presenter->iso($r['task']->due_at),
                    'url' => route('workspace.tasks.show', $r['task']),
                ])->values()->all(),
            ])
            ->sortByDesc('late_count')
            ->take(10)
            ->values()
            ->all();

        // Tugas yang akan jatuh tempo dalam 7 hari (belum lewat deadline, belum dikirim); yang belum dibagi ikut.
        $upcoming = fn () => $scope()
            ->whereIn('status', [TaskStatus::Todo->value, TaskStatus::InProgress->value, TaskStatus::Revision->value])
            ->whereNull('overdue_at')
            ->whereBetween('due_at', [now(), now()->addDays(7)]);

        $upcomingTasks = $upcoming()->with(self::TASK_RELATIONS)->withCount(['comments', 'attachments'])
            ->orderBy('due_at')->orderBy('id')->limit(6)->get();

        return [
            'role' => 'mentor',
            'upcoming_deadlines' => [
                'data' => $upcomingTasks->map(fn (Task $t) => $this->cardWithWorkspace($t, $user))->values()->all(),
                'total' => $upcoming()->count(),
            ],
            'to_review' => [
                'data' => $waiting->take(6)->map(fn (Task $t) => $this->cardWithWorkspace($t, $user) + [
                    'submitted_at' => $this->presenter->iso(\Illuminate\Support\Carbon::parse($t->submitted_at)),
                ])->values()->all(),
                'total' => $review->count(),
            ],
            'late_by_intern' => $lateByIntern,
            'group_progress' => $this->groupProgress($user),
            'workload' => $this->workload($user),
            'ungrouped_interns' => [],
            'groups_url' => route('workspace.index'),
        ];
    }

    /** Id workspace aktif yang dibimbing mentor (admin: semua), dasar widget progres dan beban kerja. */
    private function managedWorkspaceIds(User $user)
    {
        $isAdmin = $user->isSuperAdmin() || $user->isAdminFull();

        return Workspace::query()
            ->where('is_archived', false)
            ->when(! $isAdmin, fn ($q) => $q->whereHas('members', fn ($m) => $m
                ->where('users.id', $user->id)
                ->where('workspace_members.role', 'mentor')
                ->whereNull('workspace_members.left_at')))
            ->pluck('id');
    }

    /** Maks. 6 kelompok aktif: terbanyak tugas lewat deadline dulu, lalu persentase selesai terendah. */
    private function groupProgress(User $user): array
    {
        $rows = DB::table('tasks')
            ->whereNull('deleted_at')
            ->whereIn('workspace_id', $this->managedWorkspaceIds($user))
            ->groupBy('workspace_id')
            ->selectRaw("workspace_id, count(*) as total, sum(case when status = 'done' then 1 else 0 end) as done, sum(case when status != 'done' and overdue_at is not null then 1 else 0 end) as overdue")
            ->get();

        $workspaces = Workspace::whereIn('id', $rows->pluck('workspace_id'))->get()->keyBy('id');

        return $rows
            ->sort(fn ($a, $b) => ($b->overdue <=> $a->overdue) ?: (($a->done / $a->total) <=> ($b->done / $b->total)) ?: ($a->workspace_id <=> $b->workspace_id))
            ->take(6)
            ->map(fn ($r) => [
                'workspace' => $this->presenter->workspaceMini($workspaces[$r->workspace_id]),
                'total' => (int) $r->total,
                'done' => (int) $r->done,
                'overdue' => (int) $r->overdue,
            ])
            ->values()
            ->all();
    }

    /**
     * Maks. 8 anak bimbingan dengan tugas aktif, terbanyak dulu. Intern di beberapa workspace
     * muncul sekali, di workspace tempat tugas aktifnya paling banyak.
     */
    private function workload(User $user): array
    {
        $rows = DB::table('tasks')
            ->join('task_assignees', 'task_assignees.task_id', '=', 'tasks.id')
            ->whereNull('tasks.deleted_at')
            ->whereIn('tasks.workspace_id', $this->managedWorkspaceIds($user))
            ->where('tasks.status', '!=', TaskStatus::Done->value)
            ->groupBy('task_assignees.intern_id', 'tasks.workspace_id', 'tasks.status')
            ->selectRaw('task_assignees.intern_id, tasks.workspace_id, tasks.status, count(*) as total, sum(case when tasks.overdue_at is not null then 1 else 0 end) as overdue')
            ->get();

        $perIntern = $rows->groupBy('intern_id')->map(function (Collection $internRows) {
            $byWorkspace = $internRows->groupBy('workspace_id')->sortByDesc(fn ($g) => $g->sum('total'));
            $chosen = $byWorkspace->first();

            return [
                'workspace_id' => $byWorkspace->keys()->first(),
                'active' => (int) $internRows->sum('total'),
                'counts' => array_merge(
                    ['todo' => 0, 'in_progress' => 0, 'review' => 0, 'revision' => 0],
                    $chosen->pluck('total', 'status')->map(fn ($n) => (int) $n)->all()
                ),
                'overdue' => (int) $chosen->sum('overdue'),
            ];
        })->sortByDesc('active')->take(8);

        $interns = Intern::whereIn('id', $perIntern->keys())->get()->keyBy('id');
        $workspaces = Workspace::whereIn('id', $perIntern->pluck('workspace_id'))->get()->keyBy('id');

        return $perIntern->map(fn ($r, $internId) => [
            'intern' => $this->presenter->intern($interns[$internId]),
            'workspace' => $this->presenter->workspaceMini($workspaces[$r['workspace_id']]),
            'counts' => $r['counts'],
            'overdue' => $r['overdue'],
        ])->values()->all();
    }

    private function cardWithWorkspace(Task $task, User $viewer): array
    {
        return $this->presenter->taskCard($task, $viewer) + ['workspace' => $this->presenter->workspaceMini($task->workspace)];
    }

    // ------------------------------------------------------------------ Monitoring (admin)

    /**
     * Progres per intern dan rekap per unit kerja.
     *
     * @param  array<int, int>  $internIds  kosong = semua intern yang punya tugas
     */
    public function progress(array $internIds): array
    {
        $late = "sum(case when t.status != 'done' and t.overdue_at is not null then 1 else 0 end)";

        $rows = DB::table('task_assignees as ta')
            ->join('tasks as t', 't.id', '=', 'ta.task_id')
            ->whereNull('t.deleted_at')
            ->when($internIds, fn ($q) => $q->whereIn('ta.intern_id', $internIds))
            ->groupBy('ta.intern_id')
            ->selectRaw("ta.intern_id, count(*) as total, sum(case when t.status = 'done' then 1 else 0 end) as done, sum(case when t.status = 'review' then 1 else 0 end) as review, {$late} as late")
            ->get()
            ->keyBy('intern_id');

        // Tautan ke kelompok aktif intern; yang belum berkelompok ke Space pribadinya.
        $spaces = $this->groups->homeWorkspaces(
            Intern::whereIn('id', $internIds ?: $rows->keys()->all())->get(),
            activeOnly: false
        );

        $interns = collect($internIds ?: $rows->keys()->all())->mapWithKeys(function ($id) use ($rows, $spaces) {
            $r = $rows[$id] ?? null;
            $url = isset($spaces[$id]) ? route('workspace.show', $spaces[$id]) : null;

            return [$id => [
                'total' => (int) ($r->total ?? 0),
                'done' => (int) ($r->done ?? 0),
                'review' => (int) ($r->review ?? 0),
                'late' => (int) ($r->late ?? 0),
                'group_url' => $url,
                'space_url' => $url,
            ]];
        });

        return ['interns' => (object) $interns->all(), 'teams' => $this->teamRecap()];
    }

    /** @return array<int, array> rekap tugas per unit kerja dengan mentor yang belum memberi tugas minggu ini */
    private function teamRecap(): array
    {
        $teamOf = 'coalesce(w.team_id, i.team_id)';

        $stats = DB::table('tasks as t')
            ->join('workspaces as w', 'w.id', '=', 't.workspace_id')
            ->leftJoin('interns as i', 'i.id', '=', 'w.intern_id')
            ->whereNull('t.deleted_at')
            ->where('w.is_archived', false)
            ->groupByRaw("{$teamOf}, t.status")
            ->selectRaw("{$teamOf} as team_id, t.status, count(*) as total, sum(case when t.status != 'done' and t.overdue_at is not null then 1 else 0 end) as late")
            ->get()
            ->groupBy('team_id');

        $groups = DB::table('workspaces as w')
            ->leftJoin('interns as i', 'i.id', '=', 'w.intern_id')
            ->where('w.is_archived', false)
            ->groupByRaw($teamOf)
            ->selectRaw("{$teamOf} as team_id, count(*) as total")
            ->pluck('total', 'team_id');

        $weekStart = now()->startOfWeek();
        $idle = Mentor::query()
            ->with('user')
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('tasks')
                ->whereColumn('tasks.created_by', 'mentors.user_id')
                ->whereNull('tasks.deleted_at')
                ->where('tasks.created_at', '>=', $weekStart))
            ->orderBy('name')
            ->get()
            ->groupBy('team_id');

        return Team::orderBy('name')->get()->map(function (Team $team) use ($stats, $groups, $idle) {
            $group = $stats[$team->id] ?? collect();
            $byStatus = collect(TaskStatus::cases())->mapWithKeys(fn (TaskStatus $s) => [$s->value => (int) ($group->firstWhere('status', $s->value)->total ?? 0)]);

            return [
                'team' => ['id' => $team->id, 'name' => $team->name],
                'workspace_url' => route('workspace.index', ['team' => $team->id]),
                'groups_count' => (int) ($groups[$team->id] ?? 0),
                'stats' => $byStatus->all() + ['late' => (int) $group->sum('late')],
                'idle_mentors' => collect($idle[$team->id] ?? [])->map(fn (Mentor $m) => $this->presenter->user($m->user))->filter()->values()->all(),
            ];
        })->values()->all();
    }

    // ------------------------------------------------------------------ detail intern dan rekap

    /** Mentor pembimbing intern ini, atau admin. */
    public function canViewIntern(User $user, Intern $intern): bool
    {
        if ($user->isSuperAdmin() || $user->isAdminFull()) {
            return true;
        }

        return $user->isMentor() && $user->mentor && $intern->mentor_id === $user->mentor->id;
    }

    /** Tugas satu intern di semua workspace: kartu + workspace + nilai, statistik, dan Space-nya. */
    public function internTasks(Intern $intern, User $viewer): array
    {
        $tasks = Task::query()
            ->whereHas('assignees', fn ($q) => $q->where('interns.id', $intern->id))
            ->with(self::TASK_RELATIONS)
            ->withCount(['comments', 'attachments'])
            ->orderByRaw("status = 'done'")
            ->orderBy('due_at')
            ->limit(200)
            ->get();

        $stats = array_fill_keys(array_map(fn (TaskStatus $s) => $s->value, TaskStatus::cases()), 0);
        foreach ($tasks as $task) {
            $stats[$task->status->value]++;
        }

        $scores = $tasks->where('status', TaskStatus::Done)->pluck('score')->filter(fn ($s) => $s !== null);
        $stats['late'] = $tasks->filter(fn (Task $t) => $t->status !== TaskStatus::Done && $t->overdue_at !== null)->count();
        $stats['avg_score'] = $scores->isEmpty() ? null : round($scores->avg(), 1);

        $space = $this->groups->homeWorkspaces([$intern], activeOnly: false)->first();

        return [
            'data' => $tasks->map(fn (Task $t) => $this->cardWithWorkspace($t, $viewer) + ['score' => $t->score])->values()->all(),
            'stats' => $stats,
            'group' => $space ? $this->presenter->workspaceMini($space) : null,
            'space_url' => $space ? route('workspace.show', $space) : null,
        ];
    }

    /** Rekap tugas selesai satu intern untuk laporan akhir dan halaman cetak. */
    public function recap(Intern $intern): array
    {
        $assigned = fn () => Task::query()->whereHas('assignees', fn ($q) => $q->where('interns.id', $intern->id));

        $done = $assigned()->where('status', TaskStatus::Done->value)->with('workspace:id,name')->orderBy('completed_at')->get();
        $scores = $done->pluck('score')->filter(fn ($s) => $s !== null);

        $tasks = $done->map(fn (Task $t) => [
            'id' => $t->id,
            'code' => $t->code,
            'title' => $t->title,
            'workspace' => ['id' => $t->workspace->id, 'name' => $t->workspace->name],
            'due_at' => $this->presenter->iso($t->due_at),
            'completed_at' => $this->presenter->iso($t->completed_at),
            'score' => $t->score,
            'on_time' => $t->completed_at !== null && $t->due_at !== null && $t->completed_at->lte($t->due_at),
            'url' => route('workspace.tasks.show', $t),
        ])->values();

        return [
            'intern' => ['id' => $intern->id, 'name' => $intern->name],
            'period' => [
                'start' => $intern->start_date?->toDateString(),
                'end' => $intern->end_date?->toDateString(),
            ],
            'summary' => [
                'done' => $tasks->count(),
                'active' => $assigned()->where('status', '!=', TaskStatus::Done->value)->count(),
                'on_time' => $tasks->where('on_time', true)->count(),
                'avg_score' => $scores->isEmpty() ? null : round($scores->avg(), 1),
                'min_score' => $scores->isEmpty() ? null : $scores->min(),
                'max_score' => $scores->isEmpty() ? null : $scores->max(),
            ],
            'tasks' => $tasks->all(),
        ];
    }
}
