<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\Workspace;

class SprintService
{
    /** Nama default "Sprint N": N = angka terbesar pada nama sprint yang ada + 1. */
    public function nextName(Workspace $workspace): string
    {
        $max = $workspace->sprints()->pluck('name')
            ->map(fn ($name) => (int) preg_replace('/\D/', '', (string) $name))
            ->max() ?? 0;

        return 'Sprint ' . ($max + 1);
    }

    /** Serialkan perubahan sprint/backlog per workspace (nomor sprint, rank, sprint aktif). */
    public function lock(Workspace $workspace): void
    {
        Workspace::whereKey($workspace->id)->lockForUpdate()->first();
    }

    /** Rank berikutnya di satu bagian (sprint, atau backlog bila $sprintId null). */
    public function nextRank(int $workspaceId, ?int $sprintId): int
    {
        return (int) Task::where('workspace_id', $workspaceId)->where('sprint_id', $sprintId)->max('rank') + 1;
    }

    /**
     * Pindahkan tugas ke sprint/backlog, ditaruh di akhir bagian tujuan.
     *
     * @param  array<int, int>  $taskIds
     */
    public function moveTasks(Workspace $workspace, array $taskIds, ?int $sprintId): void
    {
        $rank = $this->nextRank($workspace->id, $sprintId);

        $tasks = Task::where('workspace_id', $workspace->id)->whereIn('id', $taskIds)->orderBy('rank')->orderBy('id')->get();

        foreach ($tasks as $task) {
            $task->forceFill(['sprint_id' => $sprintId, 'rank' => $rank++])->save();
        }
    }

    /** Tugas sprint yang belum dibagi (tanpa assignee dan belum Selesai). */
    public function undivided(Sprint $sprint)
    {
        return $sprint->tasks()
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereDoesntHave('assignees')
            ->orderBy('rank')
            ->orderBy('id')
            ->get(['id', 'title']);
    }

    /**
     * Tulis ulang rank 1..n sesuai urutan `$order` untuk bagian tujuan.
     *
     * @param  array<int, int>  $order
     */
    public function applyOrder(Workspace $workspace, ?int $sprintId, array $order): void
    {
        $valid = Task::where('workspace_id', $workspace->id)->where('sprint_id', $sprintId)->pluck('id')->all();

        // Id di luar bagian tujuan diabaikan; tugas yang tidak disebut ditaruh di belakang.
        $ordered = array_values(array_unique(array_merge(
            array_values(array_intersect(array_map('intval', $order), $valid)),
            array_values(array_diff($valid, array_map('intval', $order)))
        )));

        foreach ($ordered as $i => $id) {
            Task::whereKey($id)->update(['rank' => $i + 1]);
        }
    }
}
