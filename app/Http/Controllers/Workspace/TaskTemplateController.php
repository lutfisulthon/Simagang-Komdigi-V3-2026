<?php

namespace App\Http\Controllers\Workspace;

use App\Enums\TaskPriority;
use App\Http\Controllers\Controller;
use App\Models\Intern;
use App\Models\Task;
use App\Models\TaskLabel;
use App\Models\TaskTemplate;
use App\Models\Team;
use App\Models\Workspace;
use App\Services\GroupService;
use App\Services\TaskNotifier;
use App\Services\TaskService;
use App\Services\WorkspacePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskTemplateController extends Controller
{
    public function __construct(
        private WorkspacePresenter $presenter,
        private TaskService $tasks,
        private TaskNotifier $notifier,
        private GroupService $groups,
    ) {
    }

    /** Daftar template: HTML (halaman kelola template) atau JSON `{data: Template[]}`. */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', TaskTemplate::class);

        $user = $request->user();
        $templates = $this->visibleTemplates($request)->map(fn (TaskTemplate $t) => $this->presenter->template($t, $user))->values()->all();

        if ($request->expectsJson()) {
            return response()->json(['data' => $templates]);
        }

        $isAdmin = $this->isAdmin($request);
        $teamId = optional($user->mentor)->team_id;

        return view('workspace.templates.index', [
            'templates' => $templates,
            'teams' => Team::query()->when(! $isAdmin, fn ($q) => $q->where('id', $teamId))->orderBy('name')->get(['id', 'name'])->toArray(),
            'assignableInterns' => $this->assignableInterns($request),
            'labels' => TaskLabel::query()->when(! $isAdmin, fn ($q) => $q->where('team_id', $teamId))->orderBy('name')->get(['id', 'name', 'color'])->toArray(),
            'can' => [
                'create' => Gate::forUser($user)->allows('create', TaskTemplate::class),
                'choose_team' => $isAdmin,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', TaskTemplate::class);

        $template = TaskTemplate::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return $this->respond($template, 201);
    }

    /** Simpan tugas yang sudah ada sebagai template (judul, deskripsi, prioritas, label, checklist). */
    public function storeFromTask(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        $this->authorize('create', TaskTemplate::class);

        $task->loadMissing(['checklistItems', 'labels']);

        $template = TaskTemplate::create([
            'team_id' => $this->resolveTeamId($request, $request->input('team_id')),
            'created_by' => $request->user()->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority->value,
            'label_ids' => $task->labels->pluck('id')->all(),
            'checklist' => $task->checklistItems->pluck('content')->all(),
            'is_onboarding' => false,
        ]);

        return $this->respond($template, 201);
    }

    public function update(Request $request, TaskTemplate $template): JsonResponse
    {
        $this->authorize('update', $template);

        $template->update($this->validated($request, $template));

        return $this->respond($template->fresh(), 200);
    }

    public function destroy(TaskTemplate $template): JsonResponse
    {
        $this->authorize('delete', $template);

        $template->delete();

        return response()->json(null, 204);
    }

    /**
     * Tugaskan template ke banyak intern sekaligus: satu tugas per intern, dibuat di kelompok aktif
     * masing-masing (atau Space pribadinya bila belum berkelompok). Intern tanpa keduanya membuat
     * seluruh permintaan ditolak (422).
     */
    public function assign(Request $request, TaskTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        $data = $request->validate([
            'assignee_ids' => ['required', 'array', 'min:1', 'max:100'],
            'assignee_ids.*' => ['integer', 'distinct', 'exists:interns,id'],
            'due_at' => ['required', 'date'],
            'start_at' => ['nullable', 'date', 'before_or_equal:due_at'],
        ]);

        $user = $request->user();
        $interns = Intern::whereIn('id', $data['assignee_ids'])->get();
        $spaces = $this->groups->homeWorkspaces($interns);

        $missing = collect($data['assignee_ids'])->filter(fn ($id) => ! isset($spaces[$id]));
        if ($missing->isNotEmpty()) {
            $names = $interns->whereIn('id', $missing)->pluck('name')->implode(', ');

            throw ValidationException::withMessages(['assignee_ids' => "Intern berikut belum punya kelompok atau Space aktif: {$names}."]);
        }

        foreach ($spaces->unique('id') as $space) {
            $this->authorize('create', [Task::class, $space]);
        }

        $payload = [
            'title' => $template->title,
            'description' => $template->description,
            'priority' => $template->priority ?: TaskPriority::Medium->value,
            'start_at' => $data['start_at'] ?? null,
            'due_at' => $data['due_at'],
            'template_id' => $template->id,
            'label_ids' => $template->label_ids ?? [],
            'checklist' => $template->checklist ?? [],
        ];

        $created = DB::transaction(fn () => collect($data['assignee_ids'])->map(
            fn (int $internId) => $this->tasks->create($spaces[$internId], $user, $payload, [$internId], ['source' => 'template'])
        ));

        $cards = $created->map(function (Task $task) use ($user) {
            $task->load(['assignees', 'labels', 'checklistItems', 'creator', 'workspace'])->loadCount(['comments', 'attachments']);
            $this->notifier->assigned($task, $task->assignees->pluck('id')->all(), $user);

            return $this->presenter->taskCard($task, $user) + [
                'workspace' => ['id' => $task->workspace->id, 'name' => $task->workspace->name, 'url' => route('workspace.show', $task->workspace)],
            ];
        })->values();

        return response()->json([
            'data' => $cards,
            'meta' => ['created' => $cards->count(), 'template' => $this->presenter->template($template->loadMissing(['team', 'creator']), $user)],
        ], 201);
    }

    private function respond(TaskTemplate $template, int $status): JsonResponse
    {
        $template->loadMissing(['team', 'creator']);

        return response()->json(['data' => $this->presenter->template($template, request()->user())], $status);
    }

    /** @return \Illuminate\Support\Collection<int, TaskTemplate> */
    private function visibleTemplates(Request $request)
    {
        $user = $request->user();

        return TaskTemplate::query()
            ->with(['team', 'creator'])
            ->when(! $this->isAdmin($request), function ($q) use ($user) {
                $teamId = optional($user->mentor)->team_id;
                $q->where(fn ($w) => $w->whereNull('team_id')->when($teamId, fn ($x) => $x->orWhere('team_id', $teamId)));
            })
            ->orderBy('title')
            ->get();
    }

    /** Anak magang aktif yang punya Space (admin: semua, mentor: anak bimbingannya). */
    private function assignableInterns(Request $request): array
    {
        $user = $request->user();

        return Intern::query()
            ->with('team')
            ->where('is_active', true)
            ->when(! $this->isAdmin($request), fn ($q) => $q->where('mentor_id', optional($user->mentor)->id))
            ->whereHas('user')
            ->orderBy('name')
            ->get()
            ->pipe(function ($interns) {
                $homes = $this->groups->homeWorkspaces($interns);

                return $interns->map(function (Intern $intern) use ($homes) {
                    $space = $homes[$intern->id] ?? null;

                    return $space ? $this->presenter->intern($intern) + [
                        'team' => $intern->team ? ['id' => $intern->team->id, 'name' => $intern->team->name] : null,
                        'group' => ['id' => $space->id, 'name' => $space->name, 'url' => route('workspace.show', $space)],
                    ] : null;
                });
            })
            ->filter()
            ->values()
            ->all();
    }

    private function validated(Request $request, ?TaskTemplate $template = null): array
    {
        $data = $request->validate([
            'title' => [$template ? 'sometimes' : 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:10000'],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'label_ids' => ['nullable', 'array', 'max:20'],
            'label_ids.*' => ['integer'],
            'due_in_days' => ['nullable', 'integer', 'between:0,90'],
            // Item checklist boleh teks biasa atau objek {content}.
            'checklist' => ['nullable', 'array', 'max:50'],
            'checklist.*' => ['required'],
            'is_onboarding' => ['sometimes', 'boolean'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ]);

        if (array_key_exists('checklist', $data)) {
            $data['checklist'] = collect($data['checklist'] ?? [])
                ->map(fn ($item) => trim((string) (is_array($item) ? ($item['content'] ?? '') : $item)))
                ->filter(fn ($text) => $text !== '')
                ->each(function ($text) {
                    if (mb_strlen($text) > 255) {
                        throw ValidationException::withMessages(['checklist' => 'Item checklist maksimal 255 karakter.']);
                    }
                })
                ->values()
                ->all();
        }

        // Mentor selalu terikat ke timnya; hanya admin yang boleh membuat template global (team_id kosong).
        if ($template === null || array_key_exists('team_id', $data) || ! $this->isAdmin($request)) {
            $data['team_id'] = $this->resolveTeamId($request, $data['team_id'] ?? null);
        }

        if (array_key_exists('label_ids', $data)) {
            // Hanya label milik tim template yang disimpan.
            $teamId = array_key_exists('team_id', $data) ? $data['team_id'] : $template?->team_id;
            $data['label_ids'] = $teamId
                ? TaskLabel::where('team_id', $teamId)->whereIn('id', $data['label_ids'] ?? [])->pluck('id')->all()
                : [];
        }

        foreach (['priority', 'due_in_days'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === null) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    private function resolveTeamId(Request $request, mixed $requested): ?int
    {
        return $this->isAdmin($request)
            ? ($requested ? (int) $requested : null)
            : optional($request->user()->mentor)->team_id;
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->user();

        return $user->isSuperAdmin() || $user->isAdminFull();
    }
}
