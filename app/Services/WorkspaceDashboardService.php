<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Data ringkasan tugas dan pesan untuk dashboard intern, mentor, dan admin. */
class WorkspaceDashboardService
{
    public function __construct(private ChannelUnreadService $unread)
    {
    }

    /** Widget "Tugas Saya" (5 tugas terdekat deadline-nya) dan "Pesan belum dibaca". */
    public function forIntern(User $user, Intern $intern): array
    {
        $assigned = fn () => Task::query()
            ->whereHas('assignees', fn ($q) => $q->where('interns.id', $intern->id))
            ->whereHas('workspace', fn ($q) => $q->where('is_archived', false));

        $nearest = $assigned()
            ->where('status', '!=', TaskStatus::Done->value)
            ->orderBy('due_at')
            ->limit(5)
            ->get();

        return [
            'my_tasks' => $nearest->map(fn (Task $t) => $this->presentTask($t))->values(),
            'counts' => [
                'open' => $assigned()->where('status', '!=', TaskStatus::Done->value)->count(),
                'overdue' => $assigned()->where('status', '!=', TaskStatus::Done->value)->where('due_at', '<', now())->count(),
                'in_revision' => $assigned()->where('status', TaskStatus::Revision->value)->count(),
            ],
            'unread_messages' => $this->unread->total($user),
        ];
    }

    /** Widget "Perlu Review" dan "Tugas Terlambat" per intern, untuk tugas di workspace tempat mentor menjadi mentor. */
    public function forMentor(User $user, Mentor $mentor): array
    {
        $scoped = fn () => Task::query()->whereHas('workspace', fn ($q) => $q
            ->where('is_archived', false)
            ->whereHas('members', fn ($m) => $m
                ->where('users.id', $user->id)
                ->where('workspace_members.role', 'mentor')
                ->whereNull('workspace_members.left_at')));

        $review = $scoped()->where('status', TaskStatus::Review->value)->with('assignees:id,name')->orderBy('updated_at')->get();

        $overdue = $scoped()
            ->where('status', '!=', TaskStatus::Done->value)
            ->where('due_at', '<', now())
            ->with('assignees:id,name')
            ->get();

        $overdueByIntern = $overdue
            ->flatMap(fn (Task $t) => $t->assignees->map(fn ($i) => ['id' => $i->id, 'name' => $i->name]))
            ->groupBy('id')
            ->map(fn (Collection $rows) => ['intern_id' => $rows->first()['id'], 'name' => $rows->first()['name'], 'overdue' => $rows->count()])
            ->sortByDesc('overdue')
            ->values();

        return [
            'needs_review' => [
                'total' => $review->count(),
                'tasks' => $review->take(10)->map(fn (Task $t) => $this->presentTask($t))->values(),
            ],
            'overdue_by_intern' => $overdueByIntern,
            'unread_messages' => $this->unread->total($user),
        ];
    }

    /**
     * Rekap admin per tim: jumlah tugas per status, tugas terlambat, dan mentor yang
     * belum memberi tugas minggu ini.
     */
    public function adminRecap(): array
    {
        $team = 'coalesce(w.team_id, i.team_id)';

        $rows = DB::table('tasks as t')
            ->join('workspaces as w', 'w.id', '=', 't.workspace_id')
            ->leftJoin('interns as i', 'i.id', '=', 'w.intern_id')
            ->whereNull('t.deleted_at')
            ->where('w.is_archived', false)
            ->groupByRaw("$team, t.status")
            ->selectRaw("$team as team_id, t.status, count(*) as total, sum(case when t.status != 'done' and t.due_at < ? then 1 else 0 end) as overdue", [now()])
            ->get();

        $teams = Team::pluck('name', 'id');

        $perTeam = $rows->groupBy('team_id')->map(function (Collection $group, $teamId) use ($teams) {
            $byStatus = collect(TaskStatus::cases())->mapWithKeys(fn (TaskStatus $s) => [$s->value => (int) ($group->firstWhere('status', $s->value)->total ?? 0)]);

            return [
                'team_id' => $teamId ?: null,
                'team' => $teams[$teamId] ?? 'Tanpa tim',
                'by_status' => $byStatus,
                'overdue' => (int) $group->sum('overdue'),
            ];
        })->values();

        $weekStart = now()->startOfWeek();

        $idleMentors = Mentor::query()
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('tasks')
                ->whereColumn('tasks.created_by', 'mentors.user_id')
                ->whereNull('tasks.deleted_at')
                ->where('tasks.created_at', '>=', $weekStart))
            ->orderBy('name')
            ->get(['id', 'name', 'team_id'])
            ->map(fn (Mentor $m) => ['mentor_id' => $m->id, 'name' => $m->name, 'team' => $teams[$m->team_id] ?? null]);

        return [
            'teams' => $perTeam,
            'mentors_without_tasks_this_week' => $idleMentors,
        ];
    }

    /**
     * Progres tugas per intern untuk kolom Monitoring: selesai / total dan jumlah terlambat.
     *
     * @param  array<int, int>  $internIds
     * @return array<int, array{total: int, done: int, overdue: int}>
     */
    public function progressByIntern(array $internIds): array
    {
        if (! $internIds) {
            return [];
        }

        return DB::table('task_assignees as ta')
            ->join('tasks as t', 't.id', '=', 'ta.task_id')
            ->whereNull('t.deleted_at')
            ->whereIn('ta.intern_id', $internIds)
            ->groupBy('ta.intern_id')
            ->selectRaw(
                "ta.intern_id, count(*) as total, sum(case when t.status = 'done' then 1 else 0 end) as done, sum(case when t.status != 'done' and t.due_at < ? then 1 else 0 end) as overdue",
                [now()]
            )
            ->get()
            ->mapWithKeys(fn ($r) => [$r->intern_id => ['total' => (int) $r->total, 'done' => (int) $r->done, 'overdue' => (int) $r->overdue]])
            ->all();
    }

    /**
     * Tugas yang bisa ditautkan ke logbook: ditugaskan ke intern, workspace belum diarsipkan,
     * belum selesai atau baru selesai 14 hari terakhir.
     *
     * @return Collection<int, Task>
     */
    public function linkableTasks(Intern $intern, ?int $alsoIncludeTaskId = null): Collection
    {
        return Task::query()
            ->where(function ($q) use ($intern) {
                $q->whereHas('assignees', fn ($a) => $a->where('interns.id', $intern->id))
                    ->whereHas('workspace', fn ($w) => $w->where('is_archived', false))
                    ->where(fn ($s) => $s->where('status', '!=', TaskStatus::Done->value)
                        ->orWhere('completed_at', '>=', now()->subDays(14)));
            })
            ->when($alsoIncludeTaskId, fn ($q) => $q->orWhere('id', $alsoIncludeTaskId))
            ->orderBy('due_at')
            ->get(['id', 'workspace_id', 'number', 'title', 'status', 'due_at']);
    }

    private function presentTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'code' => $task->code,
            'workspace_id' => $task->workspace_id,
            'title' => $task->title,
            'status' => $task->status->value,
            'status_label' => $task->status->label(),
            'priority' => $task->priority->value,
            'due_at' => $task->due_at,
            'is_overdue' => $task->status !== TaskStatus::Done && $task->due_at?->isPast() === true,
            'assignees' => $task->relationLoaded('assignees') ? $task->assignees->map(fn ($i) => ['id' => $i->id, 'name' => $i->name])->values() : [],
        ];
    }
}
