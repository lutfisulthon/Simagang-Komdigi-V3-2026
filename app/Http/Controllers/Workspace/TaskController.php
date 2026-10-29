<?php

namespace App\Http\Controllers\Workspace;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskChecklistItem;
use App\Models\TaskComment;
use App\Models\TaskTemplate;
use App\Models\Workspace;
use App\Services\AttachmentService;
use App\Services\SprintService;
use App\Services\TaskNotifier;
use App\Services\TaskService;
use App\Services\WorkspacePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    /** Batas tugas aktif yang dimuat di board per workspace. */
    private const BOARD_ACTIVE_LIMIT = 200;
    private const BOARD_DONE_LIMIT = 50;

    private const RELATIONS = ['assignees', 'labels', 'checklistItems', 'creator'];

    public function __construct(
        private TaskService $tasks,
        private TaskNotifier $notifier,
        private WorkspacePresenter $presenter,
        private AttachmentService $attachments,
        private SprintService $sprints,
    ) {
    }

    /** TaskCard: bentuk ringkas untuk board dan list. */
    private function card(Task $task, Request $request): array
    {
        $task->load(self::RELATIONS)->loadCount(['comments', 'attachments']);

        return $this->presenter->taskCard($task, $request->user());
    }

    /** TaskDetail: dikirim setelah perubahan supaya slide-over langsung segar. */
    private function detail(Task $task, Request $request): JsonResponse
    {
        $task->load([...self::RELATIONS, 'workspace', 'comments.user', 'attachments', 'activities.user'])
            ->loadCount(['comments', 'attachments']);

        return response()->json(['data' => $this->presenter->taskDetail($task, $request->user())]);
    }

    /**
     * Rapikan tautan hasil: `result_links` [{url, label?}] ditambah `result_link` lama,
     * tanpa duplikat url.
     *
     * @return array<int, array{url: string, label: ?string}>
     */
    private function links(array $data, array $existing = []): array
    {
        $all = array_merge($existing, $data['result_links'] ?? [], isset($data['result_link']) ? [['url' => $data['result_link']]] : []);

        return collect($all)
            ->filter(fn ($l) => ! empty($l['url']))
            ->unique('url')
            ->map(fn ($l) => ['url' => $l['url'], 'label' => $l['label'] ?? null])
            ->values()
            ->all();
    }

    /** Daftar nilai dari query/body yang boleh berupa skalar atau array. */
    private function listOf(mixed $value): array
    {
        return array_values(array_filter(Arr::wrap($value), fn ($v) => $v !== null && $v !== ''));
    }

    /** Board / list tugas (JSON). Filter: status[], priority[], assignee[], label[], due_from, due_to, q. */
    public function index(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        // Filter boleh skalar (?status=todo) atau array (?status[]=todo); samakan jadi array sebelum validasi.
        foreach (['assignee', 'status', 'priority', 'label'] as $key) {
            if ($request->has($key)) {
                $request->merge([$key => $this->listOf($request->input($key))]);
            }
        }

        $filters = $request->validate([
            'assignee' => ['nullable'],
            'assignee.*' => ['integer'],
            'status' => ['nullable'],
            'status.*' => [Rule::enum(TaskStatus::class)],
            'priority' => ['nullable'],
            'priority.*' => [Rule::enum(TaskPriority::class)],
            'label' => ['nullable'],
            'label.*' => ['string', 'max:50'],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $assignees = $this->listOf($filters['assignee'] ?? null);
        $priorities = $this->listOf($filters['priority'] ?? null);
        $labels = $this->listOf($filters['label'] ?? null);
        $q = trim((string) ($filters['q'] ?? ''));

        $query = fn () => Task::query()
            ->where('workspace_id', $workspace->id)
            ->with(self::RELATIONS)
            ->withCount(['comments', 'attachments'])
            ->when($assignees, fn ($w) => $w->whereHas('assignees', fn ($a) => $a->whereIn('interns.id', $assignees)))
            ->when($priorities, fn ($w) => $w->whereIn('priority', $priorities))
            ->when($labels, fn ($w) => $w->whereHas('labels', fn ($l) => $l->whereIn('task_labels.id', array_filter($labels, 'is_numeric'))
                ->orWhereIn('name', array_filter($labels, fn ($v) => ! is_numeric($v)))))
            ->when($filters['due_from'] ?? null, fn ($w, $d) => $w->where('due_at', '>=', $d))
            ->when($filters['due_to'] ?? null, fn ($w, $d) => $w->where('due_at', '<=', Carbon::parse($d)->endOfDay()))
            ->when($q !== '', function ($w) use ($q) {
                $like = '%' . addcslashes($q, '%_' . chr(92)) . '%';
                $w->where(fn ($x) => $x->where('title', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->orderBy('position')
            ->orderBy('id');

        // Batas per kolom (bukan lintas kolom) supaya kartu di satu kolom tidak menggusur kolom lain.
        $wanted = $this->listOf($filters['status'] ?? null);
        $statuses = $wanted ? array_map(fn ($v) => TaskStatus::from($v), $wanted) : TaskStatus::cases();
        $tasks = collect();
        $truncated = [];

        foreach ($statuses as $status) {
            $isDone = $status === TaskStatus::Done;
            $limit = $isDone ? self::BOARD_DONE_LIMIT : self::BOARD_ACTIVE_LIMIT;

            $column = $query()->where('status', $status->value)
                ->when($isDone, fn ($w) => $w->reorder('completed_at', 'desc'))
                ->limit($limit + 1)
                ->get();

            if ($column->count() > $limit) {
                $truncated[] = $status->value;
                $column = $column->take($limit);
            }

            $tasks = $tasks->concat($column);
        }

        $viewer = $request->user();

        // meta.truncated = daftar status yang kartunya dipotong oleh batas board.
        return response()->json([
            'data' => $tasks->map(fn (Task $t) => $this->presenter->taskCard($t, $viewer))->values(),
            'meta' => ['truncated' => $truncated],
        ]);
    }

    /** Tugas milik intern yang login (aktif + 5 Selesai terbaru), untuk dropdown "Tugas terkait" di logbook. */
    public function mine(Request $request): JsonResponse
    {
        $intern = $request->user()->intern;
        abort_unless($request->user()->isIntern() && $intern, 403, 'Hanya intern yang memiliki daftar tugas pribadi.');

        $query = fn () => Task::query()
            ->whereHas('assignees', fn ($a) => $a->where('interns.id', $intern->id))
            ->whereHas('workspace', fn ($w) => $w->where('is_archived', false))
            ->with('workspace:id,name');

        $active = $query()->where('status', '!=', TaskStatus::Done->value)->orderBy('due_at')->get();
        $done = $query()->where('status', TaskStatus::Done->value)->orderByDesc('completed_at')->limit(5)->get();

        return response()->json(['data' => $active->concat($done)->map(fn (Task $t) => [
            'id' => $t->id,
            'code' => $t->code,
            'title' => $t->title,
            'status' => $t->status->value,
            'due_at' => $t->due_at?->toIso8601String(),
            'workspace' => ['id' => $t->workspace->id, 'name' => $t->workspace->name],
        ])->values()]);
    }

    /** Aturan validasi bersama untuk store dan update (payload FE). */
    private function payloadRules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:10000'],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'start_at' => ['nullable', 'date'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'distinct'],
            'label_ids' => ['nullable', 'array', 'max:20'],
            'label_ids.*' => ['integer'],
            'labels' => ['nullable', 'array', 'max:20'],
            'labels.*' => ['string', 'max:50'],
            'checklist_items' => ['nullable', 'array', 'max:50'],
            'checklist_items.*.id' => ['nullable', 'integer'],
            'checklist_items.*.content' => ['required', 'string', 'max:255'],
            'checklist_items.*.is_done' => ['nullable', 'boolean'],
            'checklist' => ['nullable', 'array', 'max:50'],
            'checklist.*' => ['string', 'max:255'],
            'result_links' => ['nullable', 'array', 'max:10'],
            'result_links.*.url' => ['required', 'url', 'max:500'],
            'result_links.*.label' => ['nullable', 'string', 'max:100'],
            'attachment_ids' => ['nullable', 'array', 'max:10'],
            'attachment_ids.*' => ['integer'],
        ];
    }

    /**
     * Buat tugas. `separate_copies` dengan banyak assignee membuat satu salinan per intern.
     * `template_id` hanya mencatat asal tugas (dan mengisi judul/deskripsi/checklist yang kosong).
     * Respons: array TaskCard.
     */
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('create', [Task::class, $workspace]);

        $data = $request->validate($this->payloadRules() + [
            'title' => ['required_without:template_id', 'nullable', 'string', 'max:150'],
            'due_at' => array_filter(['required', 'date', $request->filled('start_at') ? 'after_or_equal:start_at' : null]),
            'template_id' => ['nullable', 'integer', 'exists:task_templates,id'],
            'separate_copies' => ['nullable', 'boolean'],
            'sprint_id' => ['nullable', 'integer'],
        ]);

        $data['sprint_id'] = $this->resolveSprint($workspace, $data['sprint_id'] ?? null);

        $user = $request->user();
        $template = null;

        if (! empty($data['template_id'])) {
            $template = TaskTemplate::findOrFail($data['template_id']);
            $this->authorize('view', $template);

            $data['title'] = $data['title'] ?? $template->title;
            $data['description'] = $data['description'] ?? $template->description;

            if (! array_key_exists('checklist_items', $data) && ! array_key_exists('checklist', $data)) {
                $data['checklist'] = $template->checklist ?? [];
            }
        }

        $data['result_links'] = $this->links($data) ?: null;

        $internIds = $this->resolveAssignees($request, $workspace, $data['assignee_ids'] ?? []);
        $separate = ! empty($data['separate_copies']) && count($internIds) > 1;

        $created = DB::transaction(function () use ($workspace, $user, $data, $internIds, $template, $separate) {
            $groups = $separate ? array_map(fn ($id) => [$id], $internIds) : [$internIds];

            return collect($groups)->map(function (array $ids, int $i) use ($workspace, $user, $data, $template, $separate) {
                $task = $this->tasks->create($workspace, $user, $data, $ids, $template ? ['source' => 'template'] : []);

                // Satu berkas hanya bisa menempel ke satu tugas: salinan pertama yang mendapatkannya.
                if ($i === 0) {
                    $this->claimAttachments($task, $data['attachment_ids'] ?? [], $user);
                }

                return $task;
            });
        });

        $created->each(function (Task $task) use ($user) {
            $task->load(self::RELATIONS);
            $this->notifier->assigned($task, $task->assignees->pluck('id')->all(), $user);
        });

        return response()->json([
            'data' => $created->map(fn (Task $t) => $this->card($t, $request))->values(),
        ], 201);
    }

    /** Sprint tujuan harus milik workspace ini dan belum selesai; null = backlog. */
    private function resolveSprint(Workspace $workspace, mixed $sprintId): ?int
    {
        if ($sprintId === null || $sprintId === '') {
            return null;
        }

        $sprint = $workspace->sprints()->whereKey((int) $sprintId)->first();

        if (! $sprint || $sprint->status === Sprint::COMPLETED) {
            throw ValidationException::withMessages(['sprint_id' => 'Pilih sprint yang belum selesai.']);
        }

        return $sprint->id;
    }

    /** Tautkan lampiran yatim ke tugas dan catat aktivitasnya. */
    private function claimAttachments(Task $task, array $ids, $user): void
    {
        $claimed = $this->attachments->claim($task, $ids, $user, $task->workspace_id);

        if ($claimed) {
            $this->tasks->record($task, $user, 'attachment_added', [
                'files' => array_map(fn ($a) => $a->original_name, $claimed),
                'file' => $claimed[0]->original_name,
            ]);
        }
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $rules = $this->payloadRules();
        $rules['assignee_ids'] = ['sometimes', 'array'];
        $data = $request->validate($rules + [
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'due_at' => ['sometimes', 'required', 'date'],
            'result_link' => ['sometimes', 'nullable', 'url', 'max:500'],
        ]);

        $start = array_key_exists('start_at', $data) ? $data['start_at'] : $task->start_at;
        $due = $data['due_at'] ?? $task->due_at;
        if ($start && $due && Carbon::parse($due)->lt(Carbon::parse($start))) {
            throw ValidationException::withMessages(['due_at' => 'Deadline tidak boleh sebelum tanggal mulai.']);
        }

        // Intern hanya mengubah to-do pribadinya; assignee tetap dirinya sendiri.
        if ($request->user()->isIntern()) {
            unset($data['assignee_ids']);
        }

        $addedInternIds = [];

        DB::transaction(function () use ($task, $data, $request, &$addedInternIds) {
            $task->fill(collect($data)->only(['title', 'description', 'priority', 'start_at', 'due_at'])->all());

            // PUT mengganti seluruh daftar tautan hasil bila dikirim.
            if (array_key_exists('result_links', $data) || array_key_exists('result_link', $data)) {
                $task->result_links = $this->links($data) ?: null;
                $task->result_link = null;
            }

            // Deadline diperpanjang: penanda terlambat dan pengingat boleh berlaku lagi.
            if ($task->isDirty('due_at')) {
                $task->overdue_at = null;
                $task->reminded_at = null;
            }

            $changed = array_keys($task->getDirty());
            $task->save();

            if (isset($data['assignee_ids'])) {
                $ids = $this->resolveAssignees($request, $task->workspace, $data['assignee_ids']);
                $diff = $task->assignees()->sync($ids);

                $addedInternIds = $diff['attached'];

                if ($diff['attached'] || $diff['detached']) {
                    $this->tasks->record($task, $request->user(), 'assignees_changed', [
                        'added' => $diff['attached'],
                        'removed' => $diff['detached'],
                    ]);
                }
            }

            if (array_key_exists('label_ids', $data) || array_key_exists('labels', $data)) {
                $this->tasks->applyLabels($task, $data);
            }

            if (array_key_exists('checklist_items', $data) || array_key_exists('checklist', $data)) {
                $this->tasks->applyChecklist($task, $data);
                $changed[] = 'checklist';
            }

            $this->claimAttachments($task, $data['attachment_ids'] ?? [], $request->user());

            if ($changed) {
                $this->tasks->record($task, $request->user(), 'updated', ['fields' => array_values(array_diff($changed, ['updated_at', 'overdue_at', 'reminded_at']))]);
            }
        });

        if ($addedInternIds) {
            $this->notifier->assigned($task, $addedInternIds, $request->user());
        }

        return $this->detail($task->fresh(), $request);
    }

    /**
     * Drag and drop: pindah status dan posisi kartu. Intern maksimal sampai Review.
     * `column_order` = urutan lengkap id kolom tujuan; posisi kartu ini diambil dari indeksnya.
     * Intern mengirim hasil bersama pindah ke Review: `result_links`, `attachment_ids`, dan `note` (komentar).
     * Admin/mentor yang menyeret kartu ke Selesai atau Revisi mengikuti aturan review yang sama:
     * Revisi wajib disertai catatan, Selesai boleh membawa nilai, dan intern diberi tahu.
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $statusOnly = $request->validate(['status' => ['required', Rule::enum(TaskStatus::class)]]);
        $to = TaskStatus::from($statusOnly['status']);

        // Otorisasi lebih dulu supaya yang tidak berhak mendapat 403, bukan galat validasi.
        $this->authorize('updateStatus', [$task, $to]);

        $data = $request->validate([
            'position' => ['nullable', 'integer', 'min:0'],
            'column_order' => ['nullable', 'array', 'max:500'],
            'column_order.*' => ['integer'],
            'result_link' => ['nullable', 'url', 'max:500'],
            'result_links' => ['nullable', 'array', 'max:10'],
            'result_links.*.url' => ['required', 'url', 'max:500'],
            'result_links.*.label' => ['nullable', 'string', 'max:100'],
            'attachment_ids' => ['nullable', 'array', 'max:10'],
            'attachment_ids.*' => ['integer'],
            'note' => ['nullable', 'string', 'max:5000'],
            'feedback' => [Rule::requiredIf($to === TaskStatus::Revision), 'nullable', 'string', 'max:5000'],
            'score' => ['nullable', 'integer', 'between:0,100'],
        ]);

        $from = $task->status;
        $isReviewDecision = in_array($to, [TaskStatus::Done, TaskStatus::Revision], true);
        $user = $request->user();

        $position = $data['position'] ?? null;
        if (! empty($data['column_order'])) {
            $index = array_search($task->id, array_map('intval', $data['column_order']), true);
            $position = $index === false ? $position : $index;
        }

        $note = null;

        DB::transaction(function () use ($task, $to, $data, $isReviewDecision, $position, $user, &$note) {
            // Tautan baru ditambahkan ke tautan yang sudah ada.
            $links = $this->links($data, $task->result_links ?? ($task->result_link ? [['url' => $task->result_link]] : []));
            if ($links) {
                $task->result_links = $links;
                $task->result_link = null;
            }

            if ($isReviewDecision) {
                $task->feedback = $data['feedback'] ?? $task->feedback;
                $task->score = $to === TaskStatus::Done ? ($data['score'] ?? $task->score) : null;
                $task->completed_at = $to === TaskStatus::Done ? ($task->completed_at ?? now()) : null;
            }

            $this->tasks->move($task, $to, $position, $user);
            $this->claimAttachments($task, $data['attachment_ids'] ?? [], $user);

            if (! empty($data['note'])) {
                $note = $task->comments()->create(['user_id' => $user->id, 'body' => $data['note']]);
                $this->tasks->record($task, $user, 'commented', ['comment_id' => $note->id]);
            }
        });

        if ($note instanceof TaskComment) {
            $note->setRelation('user', $user);
            $this->notifier->commented($task, $note);
        }

        if ($to === TaskStatus::Review && $from !== TaskStatus::Review) {
            $this->notifier->reviewRequested($task, $user);
        }

        if ($isReviewDecision && $from !== $to) {
            $this->notifier->reviewed($task, $user);
        }

        return $this->detail($task->fresh(), $request);
    }

    /** Mentor/admin memutuskan Selesai (`approve`/`done`) atau Revisi. Revisi wajib disertai catatan. */
    public function review(Request $request, Task $task): JsonResponse
    {
        $this->authorize('review', $task);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', TaskStatus::Done->value, TaskStatus::Revision->value])],
            'feedback' => ['required_if:decision,' . TaskStatus::Revision->value, 'nullable', 'string', 'max:5000'],
            'score' => ['nullable', 'integer', 'between:0,100'],
        ]);

        if ($task->status !== TaskStatus::Review) {
            throw ValidationException::withMessages(['decision' => 'Hanya tugas berstatus Review yang bisa dinilai.']);
        }

        $decision = $data['decision'] === TaskStatus::Revision->value ? TaskStatus::Revision : TaskStatus::Done;

        DB::transaction(function () use ($request, $task, $decision, $data) {
            $isDone = $decision === TaskStatus::Done;

            $task->feedback = $data['feedback'] ?? null;
            $task->score = $isDone ? ($data['score'] ?? null) : null;
            $task->completed_at = $isDone ? now() : null;

            $this->tasks->move($task, $decision, null, $request->user(), [
                'score' => $task->score,
                'feedback' => $task->feedback,
                'decision' => $isDone ? 'approve' : 'revision',
            ]);
        });

        $this->notifier->reviewed($task, $request->user());

        return $this->detail($task->fresh(), $request);
    }

    /**
     * Backlog: pindahkan tugas ke sprint/backlog dan simpan urutannya.
     * `rank_order` = urutan lengkap bagian tujuan setelah dipindah.
     */
    public function plan(Request $request, Task $task): JsonResponse
    {
        $workspace = $task->workspace;
        $this->authorize('manageSprints', $workspace);

        $data = $request->validate([
            'sprint_id' => ['present', 'nullable', 'integer'],
            'rank_order' => ['nullable', 'array', 'max:500'],
            'rank_order.*' => ['integer'],
        ]);

        $target = $this->resolveSprint($workspace, $data['sprint_id']);

        DB::transaction(function () use ($task, $workspace, $target, $data) {
            $this->sprints->lock($workspace);

            if ($task->sprint_id !== $target) {
                $task->forceFill(['sprint_id' => $target, 'rank' => $this->sprints->nextRank($workspace->id, $target)])->save();
            }

            $this->sprints->applyOrder($workspace, $target, $data['rank_order'] ?? [$task->id]);
        });

        return response()->json(['data' => $this->card($task->fresh(), $request)]);
    }

    /**
     * Bagi tugas ke anggota (boleh beberapa, boleh kosong): mentor/admin atau ketua sprint tugas ini.
     * Perubahan dicatat sebagai `assigned` / `unassigned` per intern.
     */
    public function assignees(Request $request, Task $task): JsonResponse
    {
        $this->authorize('assign', $task);

        $data = $request->validate([
            'assignee_ids' => ['present', 'array'],
            'assignee_ids.*' => ['integer', 'distinct'],
        ]);

        $requested = array_map('intval', $data['assignee_ids']);
        $allowed = $requested ? $this->tasks->allowedAssigneeIds($task->workspace, $requested) : [];

        if (count($allowed) !== count($requested)) {
            throw ValidationException::withMessages(['assignee_ids' => 'Ada intern yang bukan anggota workspace ini.']);
        }

        $diff = $task->assignees()->sync($allowed);
        $names = \App\Models\Intern::whereIn('id', array_merge($diff['attached'], $diff['detached']))->pluck('name', 'id');

        foreach ($diff['attached'] as $id) {
            $this->tasks->record($task, $request->user(), 'assigned', ['assignee' => $names[$id] ?? null, 'assignee_id' => $id]);
        }
        foreach ($diff['detached'] as $id) {
            $this->tasks->record($task, $request->user(), 'unassigned', ['assignee' => $names[$id] ?? null, 'assignee_id' => $id]);
        }

        if ($diff['attached']) {
            $task->load(self::RELATIONS);
            $this->notifier->assigned($task, $diff['attached'], $request->user());
        }

        return $this->detail($task->fresh(), $request);
    }

    /** Perpanjang deadline: membuka kunci kirim hasil dan menghapus penanda terlambat. */
    public function extendDeadline(Request $request, Task $task): JsonResponse
    {
        $this->authorize('extendDeadline', $task);

        $data = $request->validate([
            'due_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        $from = $task->due_at;
        $to = Carbon::parse($data['due_at']);
        $reason = isset($data['reason']) && trim($data['reason']) !== '' ? trim($data['reason']) : null;

        if ($to->lte($from) || $to->lte(now())) {
            throw ValidationException::withMessages(['due_at' => 'Deadline baru harus setelah deadline lama dan setelah sekarang.']);
        }

        $task->forceFill(['due_at' => $to, 'overdue_at' => null, 'reminded_at' => null])->save();

        $this->tasks->record($task, $request->user(), 'deadline_extended', [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'reason' => $reason,
        ]);

        $this->notifier->deadlineExtended($task, $request->user(), $from, $reason);

        return $this->detail($task->fresh(), $request);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->tasks->record($task, $request->user(), 'deleted');
        $task->delete();

        return response()->json(null, 204);
    }

    /** Centang / batal centang sub-item checklist. Balas `{data: {done, total}}`. */
    public function toggleChecklistItem(Request $request, Task $task, TaskChecklistItem $item): JsonResponse
    {
        abort_unless($item->task_id === $task->id, 404);

        $this->authorize('updateChecklist', $task);

        $data = $request->validate(['is_done' => ['required', 'boolean']]);

        $item->update(['is_done' => $data['is_done']]);

        $items = $task->checklistItems()->get();

        return response()->json([
            'data' => ['done' => $items->where('is_done', true)->count(), 'total' => $items->count()],
            'id' => $item->id,
            'is_done' => $item->is_done,
            'checklist_progress' => $items->isEmpty() ? null : (int) round($items->where('is_done', true)->count() / $items->count() * 100),
        ]);
    }

    /**
     * Intern selalu menugaskan dirinya sendiri (to-do pribadi). Admin/mentor wajib memilih
     * minimal satu intern yang memang anggota workspace.
     *
     * @param  array<int, int>  $requested
     * @return array<int, int>
     */
    private function resolveAssignees(Request $request, Workspace $workspace, array $requested): array
    {
        $user = $request->user();

        if ($user->isIntern()) {
            return [$user->intern->id];
        }

        // Tugas di tim boleh belum dibagi (backlog, ketua sprint yang membagi); Space pribadi wajib punya assignee.
        if (! $requested) {
            if ($workspace->type === 'intern') {
                throw ValidationException::withMessages(['assignee_ids' => 'Pilih minimal satu intern.']);
            }

            return [];
        }

        $allowed = $this->tasks->allowedAssigneeIds($workspace, $requested);

        if (count($allowed) !== count($requested)) {
            throw ValidationException::withMessages(['assignee_ids' => 'Ada intern yang bukan anggota workspace ini.']);
        }

        return $allowed;
    }
}
