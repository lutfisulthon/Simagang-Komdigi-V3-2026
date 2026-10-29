<?php

namespace App\Http\Controllers\Workspace;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Models\Mentor;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskLabel;
use App\Models\TaskTemplate;
use App\Models\Team;
use App\Models\Workspace;
use App\Services\WorkspaceDeadlineService;
use App\Services\WorkspacePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Halaman HTML modul Workspace: daftar kelompok, detail kelompok, detail tugas. */
class WorkspacePageController extends Controller
{
    private const PER_PAGE = 9;
    private const ACTIVE_LIMIT = 200;
    private const DONE_LIMIT = 8;

    public function __construct(private WorkspacePresenter $presenter, private WorkspaceDeadlineService $deadlines)
    {
    }

    /** Daftar kelompok. Intern langsung diarahkan ke Space pribadinya. */
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Workspace::class);

        $user = $request->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdminFull();

        $base = Workspace::query()->when(! $isAdmin, fn ($q) => $q->whereHas(
            'members',
            fn ($m) => $m->where('users.id', $user->id)->whereNull('workspace_members.left_at')
        ));

        // Intern biasanya punya dua workspace (Space pribadi dan Workspace Tim): beri pilihan.
        // Hanya bila tinggal satu yang aktif, langsung arahkan ke sana.
        if ($user->isIntern()) {
            $mine = (clone $base)->orderBy('is_archived')->orderByRaw("type = 'intern' desc")->orderBy('id')->get();
            $active = $mine->where('is_archived', false);

            if ($mine->isEmpty()) {
                return view('workspace.index', $this->emptyIndex($user->intern?->mentor?->user));
            }

            if ($active->count() <= 1) {
                return redirect()->route('workspace.show', $active->first() ?? $mine->first());
            }
        }

        // Agregat deadline per workspace aktif: dipakai untuk urutan, filter, dan hitungan "perlu perhatian".
        $withDeadline = fn ($query) => $query->leftJoinSub($this->deadlines->aggregate(), 'dl', 'dl.workspace_id', '=', 'workspaces.id')->select('workspaces.*');
        $needsAttention = fn ($query) => $query->where(fn ($x) => $x->where('dl.overdue_count', '>', 0)->orWhere('dl.due_soon_count', '>', 0));

        $counts = [
            'active' => (clone $base)->where('is_archived', false)->count(),
            'finished' => (clone $base)->where('is_archived', true)->count(),
            'attention' => $needsAttention($withDeadline(clone $base))->where('workspaces.is_archived', false)->count(),
        ];

        $sort = $request->query('sort') === 'newest' ? 'newest' : 'deadline';
        $attention = $request->boolean('attention');

        $q = trim((string) $request->query('q'));
        $filtered = $withDeadline(clone $base)
            ->when($request->filled('team') && $isAdmin, fn ($w) => $w->where('team_id', $request->integer('team')))
            ->when($request->filled('mentor') && $isAdmin, fn ($w) => $w->whereHas(
                'members',
                fn ($m) => $m->where('users.id', $request->integer('mentor'))->where('workspace_members.role', 'mentor')
            ))
            ->when($q !== '', function ($w) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $w->where(fn ($x) => $x->where('name', 'like', $like)
                    ->orWhereHas('members', fn ($m) => $m->where('users.name', 'like', $like)));
            })
            ->when($attention, $needsAttention)
            ->orderBy('workspaces.is_archived')
            // Deadline terdekat: yang punya tugas lewat deadline dulu (terbanyak), lalu tugas terdekat, lalu yang tanpa deadline.
            ->when($sort === 'deadline', fn ($w) => $w
                ->orderByRaw('coalesce(dl.overdue_count, 0) desc')
                ->orderByRaw('dl.next_due is null')
                ->orderBy('dl.next_due'))
            ->orderByDesc('workspaces.id');

        $total = (clone $filtered)->count();
        $page = max(1, (int) $request->query('page', 1));
        $pageRows = $filtered->forPage($page, self::PER_PAGE)->get();
        $this->presenter->preloadDeadlines($pageRows->pluck('id')->all());
        $items = $pageRows->map(fn (Workspace $w) => $this->presenter->workspace($w, $user))->all();

        $groups = new LengthAwarePaginator($items, $total, self::PER_PAGE, $page, [
            'path' => route('workspace.index'),
            'query' => $request->except('page'),
        ]);

        return view('workspace.index', [
            'noGroup' => false,
            'mentor' => null,
            'groups' => $groups,
            'counts' => $counts,
            'sort' => $sort,
            'attention' => $attention,
            'ungrouped' => [],
            'teams' => $isAdmin ? Team::orderBy('name')->get(['id', 'name'])->toArray() : [],
            'mentors' => $isAdmin
                ? Mentor::whereNotNull('user_id')->orderBy('name')->get(['user_id', 'name'])
                    ->map(fn ($m) => ['id' => $m->user_id, 'name' => $m->name])->all()
                : [],
            'can' => [
                'create_group' => false,
                'manage_templates' => Gate::forUser($user)->allows('viewAny', TaskTemplate::class),
            ],
        ]);
    }

    private function emptyIndex($mentorUser): array
    {
        return [
            'noGroup' => true,
            'mentor' => $this->presenter->user($mentorUser),
            'groups' => null,
            'counts' => [],
            'ungrouped' => [],
            'teams' => [],
            'mentors' => [],
            'can' => [],
        ];
    }

    public function show(Request $request, Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        $user = $request->user();
        $gate = Gate::forUser($user);
        $isManager = $user->isSuperAdmin() || $user->isAdminFull() || $user->isMentor();

        $canSprints = $gate->allows('manageSprints', $workspace);

        // Backlog hanya untuk staf di workspace tim (Space pribadi dan intern tidak memakainya).
        $tabs = ['board', 'list', 'deadline', 'channel', 'activity'];
        if (! $user->isIntern() && $workspace->type !== 'intern') {
            array_unshift($tabs, 'backlog');
        }
        $tab = in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : 'board';

        $teamId = $this->labelTeamId($workspace);

        return view('workspace.show', [
            'workspace' => $this->presenter->workspace($workspace, $user),
            'tab' => $tab,
            'members' => $this->presenter->members($workspace),
            'activities' => $this->recentActivities($workspace),
            'candidates' => [],
            'assignableInterns' => $this->assignableInterns($workspace),
            'labels' => $teamId
                ? TaskLabel::where('team_id', $teamId)->orderBy('name')->get(['id', 'name', 'color'])->toArray()
                : [],
            'initialTasks' => $this->initialTasks($workspace, $user),
            'sprints' => $workspace->sprints()->with('leader')->get()->map(fn ($s) => $this->presenter->sprint($s))->all(),
            'channels' => $this->presenter->channels($workspace, $user),
            'templates' => $isManager && $teamId
                ? TaskTemplate::with(['team', 'creator'])->where(fn ($q) => $q->where('team_id', $teamId)->orWhereNull('team_id'))
                    ->orderBy('title')->get()->map(fn ($t) => $this->presenter->template($t, $user))->all()
                : [],
            'can' => [
                'create_task' => $gate->allows('create', [Task::class, $workspace]) && ! $user->isIntern(),
                'manage_channels' => false,
                'create_template' => $gate->allows('create', TaskTemplate::class),
                'manage_labels' => $isManager && ! $workspace->is_archived,
                'manage_sprints' => $canSprints,
            ],
        ]);
    }

    /** Aktivitas workspace berhalaman (20 per muat), terbaru dulu; lanjutan lewat ?before={id}. */
    public function activities(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $before = $request->validate(['before' => ['nullable', 'integer', 'min:1']])['before'] ?? null;

        $rows = TaskActivity::query()
            ->whereIn('task_id', Task::withTrashed()->where('workspace_id', $workspace->id)->select('id'))
            ->when($before, fn ($q) => $q->where('id', '<', $before))
            ->with(['user', 'task' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('id')
            ->limit(21)
            ->get();

        return response()->json([
            'data' => $rows->take(20)->map(fn (TaskActivity $a) => $this->presenter->activity($a))->values(),
            'meta' => ['has_more' => $rows->count() > 20],
        ]);
    }

    /** Detail tugas: HTML (halaman penuh) atau JSON (slide-over). */
    public function task(Request $request, Task $task): View|JsonResponse
    {
        $this->authorize('view', $task);

        $user = $request->user();
        $task->load(['workspace', 'creator', 'assignees', 'labels', 'checklistItems', 'comments.user', 'attachments', 'activities.user'])
            ->loadCount(['comments', 'attachments']);
        $detail = $this->presenter->taskDetail($task, $user);

        if ($request->expectsJson()) {
            return response()->json(['data' => $detail]);
        }

        $teamId = $this->labelTeamId($task->workspace);
        $isManager = $user->isSuperAdmin() || $user->isAdminFull() || $user->isMentor();

        return view('workspace.tasks.show', [
            'task' => $detail,
            'workspace' => $this->presenter->workspace($task->workspace, $user),
            'members' => $this->presenter->members($task->workspace),
            'assignableInterns' => $this->assignableInterns($task->workspace),
            'labels' => $teamId ? TaskLabel::where('team_id', $teamId)->orderBy('name')->get(['id', 'name', 'color'])->toArray() : [],
            'templates' => [],
            'can' => ['create_template' => $isManager && Gate::forUser($user)->allows('create', TaskTemplate::class)],
        ]);
    }

    /** Intern yang boleh di-assign: sama dengan aturan TaskService::allowedAssigneeIds. */
    private function assignableInterns(Workspace $workspace): array
    {
        $query = $workspace->type === 'intern'
            ? Intern::where('id', $workspace->intern_id)
            : Intern::whereIn('user_id', DB::table('workspace_members')
                ->where('workspace_id', $workspace->id)->whereNull('left_at')->where('role', 'member')->pluck('user_id'));

        return $query->orderBy('name')->get()->map(fn (Intern $i) => $this->presenter->intern($i))->all();
    }

    /** Semua tugas aktif (maks. 200) + Selesai terbaru, siap dipakai board tanpa request tambahan. */
    private function initialTasks(Workspace $workspace, $user): array
    {
        $query = fn () => Task::query()
            ->where('workspace_id', $workspace->id)
            ->with(['assignees', 'labels', 'checklistItems'])
            ->withCount(['comments', 'attachments']);

        $active = $query()->where('status', '!=', TaskStatus::Done->value)
            ->orderBy('position')->orderBy('id')->limit(self::ACTIVE_LIMIT)->get();
        $done = $query()->where('status', TaskStatus::Done->value)
            ->orderByDesc('completed_at')->limit(self::DONE_LIMIT)->get();

        return $active->concat($done)->map(fn (Task $t) => $this->presenter->taskCard($t, $user))->all();
    }

    private function recentActivities(Workspace $workspace): array
    {
        return TaskActivity::query()
            ->whereIn('task_id', Task::withTrashed()->where('workspace_id', $workspace->id)->select('id'))
            ->with(['user', 'task' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn (TaskActivity $a) => $this->presenter->activity($a))
            ->all();
    }

    private function labelTeamId(Workspace $workspace): ?int
    {
        if ($workspace->team_id) {
            return $workspace->team_id;
        }

        $intern = $workspace->intern_id ? Intern::with('mentor')->find($workspace->intern_id) : null;

        return $intern?->team_id ?? $intern?->mentor?->team_id;
    }
}
