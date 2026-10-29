<?php

namespace App\Http\Controllers\Workspace;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\SprintService;
use App\Services\TaskNotifier;
use App\Services\TaskService;
use App\Services\WorkspacePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Sprint per workspace (kontrak FE #19): buat, ubah, mulai, selesaikan, hapus. Hanya mentor/admin. */
class SprintController extends Controller
{
    public function __construct(
        private SprintService $sprints,
        private TaskService $tasks,
        private TaskNotifier $notifier,
        private WorkspacePresenter $presenter,
    ) {
    }

    private function body(Sprint $sprint, array $extra = [], int $status = 200): JsonResponse
    {
        $sprint->load('leader');

        return response()->json(['data' => $this->presenter->sprint($sprint)] + $extra, $status);
    }

    /** Ketua harus intern anggota workspace; id lain ditolak dengan 422. */
    private function leaderId(Workspace $workspace, mixed $id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (! $this->tasks->allowedAssigneeIds($workspace, [(int) $id])) {
            throw ValidationException::withMessages(['leader_id' => 'Ketua harus anggota kelompok ini.']);
        }

        return (int) $id;
    }

    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('manageSprints', $workspace);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:60'],
            'goal' => ['nullable', 'string', 'max:1000'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'leader_id' => ['nullable', 'integer'],
            'tasks' => ['nullable', 'array', 'max:50'],
            'tasks.*.title' => ['required', 'string', 'max:150'],
            'tasks.*.priority' => ['nullable', Rule::enum(TaskPriority::class)],
        ]);

        $leaderId = $this->leaderId($workspace, $data['leader_id'] ?? null);
        $user = $request->user();
        $created = collect();

        $sprint = DB::transaction(function () use ($workspace, $data, $leaderId, $user, &$created) {
            $this->sprints->lock($workspace);

            $sprint = Sprint::create([
                'workspace_id' => $workspace->id,
                'name' => trim((string) ($data['name'] ?? '')) ?: $this->sprints->nextName($workspace),
                'goal' => $data['goal'] ?? null,
                'status' => Sprint::PLANNED,
                'end_date' => $data['end_date'],
                'leader_intern_id' => $leaderId,
            ]);

            // Tugas cepat: tanpa assignee, deadline = akhir hari deadline sprint.
            $due = Carbon::parse($data['end_date'])->setTime(23, 59);
            foreach ($data['tasks'] ?? [] as $row) {
                $created->push($this->tasks->create($workspace, $user, [
                    'title' => $row['title'],
                    'priority' => $row['priority'] ?? TaskPriority::Medium,
                    'due_at' => $due,
                    'sprint_id' => $sprint->id,
                ], []));
            }

            return $sprint;
        });

        $sprint->setRelation('workspace', $workspace);
        $sprint->load('leader.user');
        $this->notifier->sprintLeader($sprint, $user);

        $cards = $created->map(function (Task $t) use ($user) {
            $t->load(['assignees', 'labels', 'checklistItems'])->loadCount(['comments', 'attachments']);

            return $this->presenter->taskCard($t, $user);
        })->values()->all();

        return $this->body($sprint, ['tasks' => $cards], 201);
    }

    public function update(Request $request, Sprint $sprint): JsonResponse
    {
        $workspace = $sprint->workspace;
        $this->authorize('manageSprints', $workspace);

        if ($sprint->status === Sprint::COMPLETED) {
            abort(403, 'Sprint yang sudah selesai tidak bisa diubah.');
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:60'],
            'goal' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'start_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'leader_id' => ['sometimes', 'nullable', 'integer'],
        ]);

        $start = array_key_exists('start_date', $data) ? $data['start_date'] : $sprint->start_date?->toDateString();
        $end = array_key_exists('end_date', $data) ? $data['end_date'] : $sprint->end_date?->toDateString();
        $errors = [];

        if ($sprint->status === Sprint::ACTIVE) {
            if (! $start) {
                $errors['start_date'] = 'Tanggal mulai wajib diisi.';
            }
            if (! $end) {
                $errors['end_date'] = 'Tanggal selesai wajib diisi.';
            }
        }
        if ($start && $end && $end <= $start) {
            $errors['end_date'] = 'Tanggal selesai harus setelah tanggal mulai.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $newLeader = $sprint->leader_intern_id;
        if (array_key_exists('leader_id', $data)) {
            $newLeader = $this->leaderId($workspace, $data['leader_id']);
            if ($newLeader === null && $sprint->status === Sprint::ACTIVE) {
                throw ValidationException::withMessages(['leader_id' => 'Sprint yang berjalan wajib punya ketua.']);
            }
        }

        $leaderChanged = $newLeader !== null && $newLeader !== $sprint->leader_intern_id;

        $sprint->fill(array_diff_key($data, ['leader_id' => 1]));
        $sprint->leader_intern_id = $newLeader;
        $sprint->save();

        if ($leaderChanged) {
            $sprint->load('leader.user', 'workspace');
            $this->notifier->sprintLeader($sprint, $request->user());
        }

        return $this->body($sprint);
    }

    public function start(Request $request, Sprint $sprint): JsonResponse
    {
        $workspace = $sprint->workspace;
        $this->authorize('manageSprints', $workspace);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'goal' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after:start_date'],
            'leader_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($workspace, $sprint, $data) {
            $this->sprints->lock($workspace);
            $sprint->refresh();

            if ($sprint->status !== Sprint::PLANNED) {
                throw ValidationException::withMessages(['sprint' => 'Sprint ini tidak bisa dimulai.']);
            }
            if ($workspace->sprints()->where('status', Sprint::ACTIVE)->exists()) {
                throw ValidationException::withMessages(['sprint' => 'Selesaikan sprint yang sedang berjalan terlebih dahulu.']);
            }

            $leaderId = array_key_exists('leader_id', $data) && $data['leader_id'] !== null
                ? $this->leaderId($workspace, $data['leader_id'])
                : $sprint->leader_intern_id;

            if (! $leaderId) {
                throw ValidationException::withMessages(['leader_id' => 'Pilih ketua tim sebelum memulai sprint.']);
            }

            $sprint->fill([
                'name' => trim($data['name']),
                'goal' => $data['goal'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'leader_intern_id' => $leaderId,
                'status' => Sprint::ACTIVE,
            ])->save();
        });

        $sprint->load('leader.user', 'workspace');

        // Ketua diminta membagi tugas yang belum punya assignee.
        $undivided = $this->sprints->undivided($sprint);
        if ($undivided->isNotEmpty()) {
            $this->notifier->sprintToDivide($sprint, $undivided->count(), $undivided->pluck('title')->take(5)->all());
            $sprint->forceFill(['divide_reminded_at' => now()])->save();
        }

        return $this->body($sprint);
    }

    public function complete(Request $request, Sprint $sprint): JsonResponse
    {
        $workspace = $sprint->workspace;
        $this->authorize('manageSprints', $workspace);

        $data = $request->validate(['move_to' => ['required']]);
        $moveTo = $data['move_to'];

        if (! in_array($moveTo, ['backlog', 'new'], true) && ! (is_numeric($moveTo) && (int) $moveTo > 0)) {
            throw ValidationException::withMessages(['move_to' => 'Tujuan tugas tidak valid.']);
        }

        $createdSprint = null;
        $movedIds = [];
        $targetId = null;

        DB::transaction(function () use ($workspace, $sprint, $moveTo, &$createdSprint, &$movedIds, &$targetId) {
            $this->sprints->lock($workspace);
            $sprint->refresh();

            if ($sprint->status !== Sprint::ACTIVE) {
                throw ValidationException::withMessages(['sprint' => 'Hanya sprint yang berjalan yang bisa diselesaikan.']);
            }

            if ($moveTo === 'new') {
                $createdSprint = Sprint::create([
                    'workspace_id' => $workspace->id,
                    'name' => $this->sprints->nextName($workspace),
                    'status' => Sprint::PLANNED,
                ]);
                $targetId = $createdSprint->id;
            } elseif ($moveTo !== 'backlog') {
                $target = $workspace->sprints()->whereKey((int) $moveTo)->first();
                if (! $target || $target->status !== Sprint::PLANNED) {
                    throw ValidationException::withMessages(['move_to' => 'Pilih sprint yang belum dimulai.']);
                }
                $targetId = $target->id;
            }

            // Tugas Selesai tetap tercatat di sprint ini (riwayat laporan); sisanya dipindah.
            $movedIds = $sprint->tasks()->where('status', '!=', TaskStatus::Done->value)
                ->orderBy('rank')->orderBy('id')->pluck('id')->all();

            $this->sprints->moveTasks($workspace, $movedIds, $targetId);

            $sprint->forceFill(['status' => Sprint::COMPLETED, 'completed_at' => now()])->save();
        });

        $extra = ['moved' => ['task_ids' => $movedIds, 'sprint_id' => $targetId], 'created_sprint' => null];

        if ($createdSprint) {
            $createdSprint->load('leader');
            $extra['created_sprint'] = $this->presenter->sprint($createdSprint);
        }

        return $this->body($sprint, $extra);
    }

    /** Hanya sprint yang belum dimulai; tugasnya kembali ke backlog. */
    public function destroy(Sprint $sprint): JsonResponse
    {
        $workspace = $sprint->workspace;
        $this->authorize('manageSprints', $workspace);

        if ($sprint->status !== Sprint::PLANNED) {
            abort(403, 'Hanya sprint yang belum dimulai yang bisa dihapus.');
        }

        DB::transaction(function () use ($workspace, $sprint) {
            $this->sprints->lock($workspace);
            $this->sprints->moveTasks($workspace, $sprint->tasks()->pluck('id')->all(), null);
            $sprint->delete();
        });

        return response()->json(null, 204);
    }
}
