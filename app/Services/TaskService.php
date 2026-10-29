<?php

namespace App\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Intern;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskLabel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /** Membuat tugas beserta assignee, label, checklist, dan catatan aktivitas. */
    public function create(Workspace $workspace, User $creator, array $data, array $internIds, array $extraMeta = []): Task
    {
        return DB::transaction(function () use ($workspace, $creator, $data, $internIds, $extraMeta) {
            // Kunci baris workspace supaya nomor tugas (T-12) dan posisi tidak bentrok.
            Workspace::whereKey($workspace->id)->lockForUpdate()->first();

            $number = (int) Task::withTrashed()->where('workspace_id', $workspace->id)->max('number') + 1;
            $position = (int) Task::where('workspace_id', $workspace->id)
                ->where('status', TaskStatus::Todo->value)
                ->max('position') + 1;

            $sprintId = $data['sprint_id'] ?? null;
            $rank = (int) Task::where('workspace_id', $workspace->id)->where('sprint_id', $sprintId)->max('rank') + 1;

            $task = Task::create([
                'workspace_id' => $workspace->id,
                'sprint_id' => $sprintId,
                'rank' => $rank,
                'number' => $number,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => TaskStatus::Todo,
                'priority' => $data['priority'] ?? TaskPriority::Medium,
                'created_by' => $creator->id,
                'start_at' => $data['start_at'] ?? null,
                'due_at' => $data['due_at'],
                'position' => $position,
                'template_id' => $data['template_id'] ?? null,
                'result_links' => $data['result_links'] ?? null,
            ]);

            $task->assignees()->sync($internIds);
            $this->applyLabels($task, $data);
            $this->applyChecklist($task, $data);

            $this->record($task, $creator, 'created', $extraMeta + ['assignee_ids' => array_values($internIds)]);

            return $task;
        });
    }

    public function record(Task $task, ?User $user, string $action, array $meta = []): TaskActivity
    {
        return TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $user?->id,
            'action' => $action,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * Pindah status dan/atau posisi kartu di board, lalu rapatkan urutan kolom tujuan.
     * Perubahan status dicatat di task_activities.
     */
    public function move(Task $task, TaskStatus $to, ?int $position, User $user, array $extraMeta = []): void
    {
        DB::transaction(function () use ($task, $to, $position, $user, $extraMeta) {
            // Serialkan pemindahan kartu per workspace supaya urutan kolom tidak rusak saat dua drag bersamaan.
            Workspace::whereKey($task->workspace_id)->lockForUpdate()->first();

            $from = $task->status;

            $siblings = Task::where('workspace_id', $task->workspace_id)
                ->where('status', $to->value)
                ->where('id', '!=', $task->id)
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('position', 'id')
                ->all();

            $ids = array_keys($siblings);
            $index = $position === null ? count($ids) : max(0, min($position, count($ids)));
            array_splice($ids, $index, 0, [$task->id]);

            foreach ($ids as $i => $id) {
                if ($id === $task->id) {
                    continue;
                }
                if ((int) $siblings[$id] !== $i) {
                    Task::whereKey($id)->update(['position' => $i]);
                }
            }

            $task->status = $to;
            $task->position = $index;
            // Batang timeline dimulai saat tugas mulai dikerjakan, kecuali tanggal mulai sudah ditentukan.
            if ($to === TaskStatus::InProgress && ! $task->start_at) {
                $task->start_at = now();
            }
            if ($from === TaskStatus::Done && $to !== TaskStatus::Done) {
                $task->completed_at = null;
            } elseif ($to === TaskStatus::Done && ! $task->completed_at) {
                $task->completed_at = now();
            }
            $task->save();

            if ($from !== $to) {
                $this->record($task, $user, 'status_changed', $extraMeta + ['from' => $from->value, 'to' => $to->value]);
            }
        });
    }

    /**
     * Intern yang boleh di-assign di workspace ini:
     * Workspace Tim = anggota aktif berperan member, Space Intern = pemilik Space.
     *
     * @param  array<int, int>  $internIds
     * @return array<int, int>
     */
    public function allowedAssigneeIds(Workspace $workspace, array $internIds): array
    {
        if ($workspace->type === 'intern') {
            return in_array((int) $workspace->intern_id, array_map('intval', $internIds), true) ? [$workspace->intern_id] : [];
        }

        return Intern::whereIn('interns.id', $internIds)
            ->join('workspace_members', function ($join) use ($workspace) {
                $join->on('workspace_members.user_id', '=', 'interns.user_id')
                    ->where('workspace_members.workspace_id', $workspace->id)
                    ->whereNull('workspace_members.left_at')
                    ->where('workspace_members.role', 'member');
            })
            ->pluck('interns.id')
            ->all();
    }

    /** Label dari `label_ids` (id label tim) bila ada, kalau tidak dari `labels` (nama bebas). */
    public function applyLabels(Task $task, array $data): void
    {
        if (array_key_exists('label_ids', $data)) {
            $this->syncLabelIds($task, $data['label_ids'] ?? []);
        } elseif (array_key_exists('labels', $data)) {
            $this->syncLabels($task, $data['labels'] ?? []);
        }
    }

    /** Checklist dari `checklist_items` ([{id?, content, is_done}]) bila ada, kalau tidak dari `checklist` (teks). */
    public function applyChecklist(Task $task, array $data): void
    {
        if (array_key_exists('checklist_items', $data)) {
            $this->syncChecklistItems($task, $data['checklist_items'] ?? []);
        } elseif (array_key_exists('checklist', $data)) {
            $this->replaceChecklist($task, $data['checklist'] ?? []);
        }
    }

    /** Hanya label milik tim workspace ini yang diterima; id lain diabaikan. */
    public function syncLabelIds(Task $task, array $ids): void
    {
        $teamId = $this->labelTeamId($task->workspace);

        $valid = $teamId
            ? TaskLabel::where('team_id', $teamId)->whereIn('id', $ids)->pluck('id')->all()
            : [];

        $task->labels()->sync($valid);
    }

    /**
     * Item dengan `id` milik tugas ini diperbarui, item tanpa id dibuat, dan item lama yang
     * tidak dikirim dihapus. Urutan mengikuti urutan array.
     */
    public function syncChecklistItems(Task $task, array $items): void
    {
        $existing = $task->checklistItems()->pluck('id')->all();
        $keep = [];

        foreach (array_values($items) as $position => $item) {
            $attributes = [
                'content' => (string) ($item['content'] ?? ''),
                'is_done' => ! empty($item['is_done']),
                'position' => $position,
            ];

            if (! empty($item['id']) && in_array((int) $item['id'], $existing, true)) {
                $task->checklistItems()->whereKey($item['id'])->update($attributes);
                $keep[] = (int) $item['id'];
            } else {
                $keep[] = $task->checklistItems()->create($attributes)->id;
            }
        }

        $task->checklistItems()->whereNotIn('id', $keep)->delete();
    }

    /** Label bebas per tim; Space Intern memakai tim intern atau mentornya. */
    public function syncLabels(Task $task, array $names): void
    {
        $teamId = $this->labelTeamId($task->workspace);

        if (! $teamId) {
            $task->labels()->detach();

            return;
        }

        $ids = collect($names)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->map(fn ($name) => TaskLabel::firstOrCreate(['team_id' => $teamId, 'name' => $name])->id)
            ->all();

        $task->labels()->sync($ids);
    }

    /** @param  array<int, string|array{content: string, is_done?: bool}>  $items */
    public function replaceChecklist(Task $task, array $items): void
    {
        $task->checklistItems()->delete();

        foreach (array_values($items) as $i => $item) {
            $task->checklistItems()->create([
                'content' => is_array($item) ? ($item['content'] ?? '') : (string) $item,
                'is_done' => is_array($item) && ! empty($item['is_done']),
                'position' => $i,
            ]);
        }
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
