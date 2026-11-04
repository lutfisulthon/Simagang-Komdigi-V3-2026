<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Ringkasan deadline per tugas untuk kartu kelompok, urutan "deadline terdekat", dan filter
 * "perlu perhatian" (kontrak FE #20). Sumber tunggal tetap tasks.due_at.
 *
 * Yang dihitung: tugas berstatus todo / in_progress / revision di workspace yang belum diarsipkan
 * (tugas review dan done sudah dikirim). Semua tugas ikut, termasuk backlog dan sprint terencana.
 */
class WorkspaceDeadlineService
{
    /** Jatuh tempo "segera" = dalam 3 hari ke depan. */
    public const SOON_DAYS = 3;

    /** @var array<int, array> */
    private array $cache = [];

    /**
     * Subquery agregat per workspace aktif: overdue_count, due_soon_count, next_due.
     * Dipakai untuk urutan dan filter di daftar, dan untuk mengisi Workspace.deadline.
     *
     * @param  array<int, int>|null  $workspaceIds  null = semua workspace aktif
     */
    public function aggregate(?array $workspaceIds = null): Builder
    {
        $now = now();
        $soon = now()->addDays(self::SOON_DAYS);

        return DB::table('tasks')
            ->join('workspaces', 'workspaces.id', '=', 'tasks.workspace_id')
            ->whereNull('tasks.deleted_at')
            ->where('workspaces.is_archived', false)
            ->whereIn('tasks.status', $this->pendingStatuses())
            ->when($workspaceIds !== null, fn ($q) => $q->whereIn('tasks.workspace_id', $workspaceIds))
            ->groupBy('tasks.workspace_id')
            ->selectRaw(
                'tasks.workspace_id as workspace_id, '
                . 'sum(case when tasks.overdue_at is not null or tasks.due_at < ? then 1 else 0 end) as overdue_count, '
                . 'sum(case when tasks.overdue_at is null and tasks.due_at >= ? and tasks.due_at <= ? then 1 else 0 end) as due_soon_count, '
                . 'min(case when tasks.overdue_at is null and tasks.due_at >= ? then tasks.due_at end) as next_due',
                [$now, $now, $soon, $now]
            );
    }

    /**
     * Isi Workspace.deadline untuk banyak workspace sekaligus (satu query agregat + satu query tugas terdekat).
     *
     * @param  array<int, int>  $workspaceIds
     */
    public function preload(array $workspaceIds): void
    {
        $workspaceIds = array_values(array_diff(array_unique($workspaceIds), array_keys($this->cache)));

        if (! $workspaceIds) {
            return;
        }

        $rows = $this->aggregate($workspaceIds)->get()->keyBy('workspace_id');

        // Tugas terdekat: yang due_at-nya sama dengan next_due; bila kembar, id terkecil.
        $nextTasks = collect();
        $pairs = $rows->filter(fn ($r) => $r->next_due !== null);
        if ($pairs->isNotEmpty()) {
            $nextTasks = Task::query()
                ->whereIn('status', $this->pendingStatuses())
                ->whereNull('overdue_at')
                ->where(function ($q) use ($pairs) {
                    foreach ($pairs as $row) {
                        $q->orWhere(fn ($w) => $w->where('workspace_id', $row->workspace_id)->where('due_at', $row->next_due));
                    }
                })
                ->orderBy('id')
                ->get(['id', 'workspace_id', 'number', 'title', 'due_at'])
                ->unique('workspace_id')
                ->keyBy('workspace_id');
        }

        foreach ($workspaceIds as $id) {
            $row = $rows[$id] ?? null;
            $next = $nextTasks[$id] ?? null;

            $this->cache[$id] = [
                'next' => $next ? [
                    'id' => $next->id,
                    'code' => $next->code,
                    'title' => $next->title,
                    'due_at' => $next->due_at->toIso8601String(),
                    'url' => route('workspace.tasks.show', $next->id),
                ] : null,
                'overdue_count' => (int) ($row->overdue_count ?? 0),
                'due_soon_count' => (int) ($row->due_soon_count ?? 0),
            ];
        }
    }

    /** Workspace.deadline; null untuk workspace yang sudah selesai (diarsipkan). */
    public function forWorkspace(int $workspaceId, bool $archived): ?array
    {
        if ($archived) {
            return null;
        }

        $this->preload([$workspaceId]);

        return $this->cache[$workspaceId];
    }

    /** @return array<int, string> */
    private function pendingStatuses(): array
    {
        return [TaskStatus::Todo->value, TaskStatus::InProgress->value, TaskStatus::Revision->value];
    }
}
