<?php

namespace App\Policies;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Policies\Concerns\ChecksWorkspaceAccess;
use Illuminate\Support\Facades\DB;

class TaskPolicy
{
    use ChecksWorkspaceAccess;

    /** @var array<string, bool> */
    private array $leaderCache = [];

    public function view(User $user, Task $task): bool
    {
        return $this->canViewWorkspace($user, $task->workspace);
    }

    /**
     * Admin dan mentor workspace boleh membuat tugas. Intern hanya boleh membuat
     * to-do pribadi di Space-nya sendiri.
     *
     * Pemakaian: $user->can('create', [Task::class, $workspace])
     */
    public function create(User $user, Workspace $workspace): bool
    {
        if ($workspace->is_archived) {
            return false;
        }

        if ($this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace)) {
            return true;
        }

        return $this->isOwnSpace($user, $workspace) && $this->canWrite($user, $workspace);
    }

    public function update(User $user, Task $task): bool
    {
        $workspace = $task->workspace;

        if ($workspace->is_archived) {
            return false;
        }

        if ($this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace)) {
            return true;
        }

        // Intern hanya mengubah to-do pribadi yang ia buat sendiri.
        return $this->isOwnSpace($user, $workspace)
            && $task->created_by === $user->id
            && $this->canWrite($user, $workspace);
    }

    /** Soft delete: admin, atau mentor untuk tugas buatannya. Intern tidak boleh. */
    public function delete(User $user, Task $task): bool
    {
        if ($this->isWorkspaceAdmin($user)) {
            return true;
        }

        return $this->isWorkspaceMentor($user, $task->workspace)
            && $task->created_by === $user->id;
    }

    /**
     * Pindah status / posisi di board.
     * Admin dan mentor bebas. Intern hanya pada tugas yang di-assign ke dia, dan hanya
     * sampai Review: Selesai dan Revisi hanya lewat review mentor.
     *
     * Pemakaian: $user->can('updateStatus', [$task, TaskStatus::Review])
     */
    public function updateStatus(User $user, Task $task, TaskStatus $to): bool
    {
        $workspace = $task->workspace;

        if ($workspace->is_archived) {
            return false;
        }

        if ($this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace)) {
            return true;
        }

        if (! $user->isIntern() || ! $this->canWrite($user, $workspace) || ! $this->isAssignee($user, $task)) {
            return false;
        }

        // Dari Review dan Selesai hanya mentor yang memutuskan.
        if (! in_array($task->status, [TaskStatus::Todo, TaskStatus::InProgress, TaskStatus::Revision], true)) {
            return false;
        }

        $allowedTargets = [TaskStatus::Todo, TaskStatus::InProgress, TaskStatus::Review];
        if (! in_array($to, $allowedTargets, true)) {
            return false;
        }

        // Revisi kembali ke In Progress, bukan loncat ke Review atau To Do.
        if ($task->status === TaskStatus::Revision && $to !== TaskStatus::InProgress) {
            return false;
        }

        return true;
    }

    /** Menandai Selesai atau Revisi: hanya admin dan mentor workspace. Status Review dicek di controller. */
    public function review(User $user, Task $task): bool
    {
        $workspace = $task->workspace;

        if ($workspace->is_archived) {
            return false;
        }

        return $this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace);
    }

    /**
     * Membagi tugas ke anggota: mentor/admin, atau intern yang menjadi ketua sprint tugas itu
     * (selama sprint belum selesai). Tugas di Space pribadi tidak dibagi.
     */
    public function assign(User $user, Task $task): bool
    {
        $workspace = $task->workspace;

        if ($workspace->is_archived || $workspace->type === 'intern') {
            return false;
        }

        if ($this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace)) {
            return true;
        }

        return $this->isSprintLeader($user, $task) && $this->canWrite($user, $workspace);
    }

    /** Perpanjang deadline: mentor/admin, selama tugas belum Selesai. */
    public function extendDeadline(User $user, Task $task): bool
    {
        $workspace = $task->workspace;

        if ($workspace->is_archived || $task->status === TaskStatus::Done) {
            return false;
        }

        return $this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace);
    }

    /** Mencentang checklist: admin, mentor workspace, atau intern yang di-assign. */
    public function updateChecklist(User $user, Task $task): bool
    {
        $workspace = $task->workspace;

        if ($workspace->is_archived) {
            return false;
        }

        if ($this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace)) {
            return true;
        }

        return $user->isIntern() && $this->canWrite($user, $workspace) && $this->isAssignee($user, $task);
    }

    /** Komentar tugas: semua anggota yang boleh menulis di workspace. */
    public function comment(User $user, Task $task): bool
    {
        $workspace = $task->workspace;

        if ($this->isWorkspaceAdmin($user)) {
            return ! $workspace->is_archived;
        }

        return $this->view($user, $task) && $this->canWrite($user, $workspace);
    }

    private function canViewWorkspace(User $user, Workspace $workspace): bool
    {
        if ($this->isWorkspaceAdmin($user)) {
            return true;
        }

        return $this->canUseWorkspace($user) && $this->isMember($user, $workspace);
    }

    private function isSprintLeader(User $user, Task $task): bool
    {
        $internId = optional($user->intern)->id;

        if (! $internId || $task->sprint_id === null) {
            return false;
        }

        $key = $internId . ':' . $task->sprint_id;

        return $this->leaderCache[$key] ??= DB::table('sprints')
            ->where('id', $task->sprint_id)
            ->where('leader_intern_id', $internId)
            ->whereIn('status', ['planned', 'active'])
            ->exists();
    }

    private function isAssignee(User $user, Task $task): bool
    {
        $internId = optional($user->intern)->id;

        return $internId
            && DB::table('task_assignees')
                ->where('task_id', $task->id)
                ->where('intern_id', $internId)
                ->exists();
    }
}
