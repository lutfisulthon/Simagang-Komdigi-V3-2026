<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;
use App\Policies\Concerns\ChecksWorkspaceAccess;

class WorkspacePolicy
{
    use ChecksWorkspaceAccess;

    public function viewAny(User $user): bool
    {
        return $this->canUseWorkspace($user);
    }

    /** Admin: semua tim. Mentor dan intern: workspace tempat mereka menjadi anggota aktif. */
    public function view(User $user, Workspace $workspace): bool
    {
        if ($this->isWorkspaceAdmin($user)) {
            return true;
        }

        return $this->canUseWorkspace($user) && $this->isMember($user, $workspace);
    }

    public function create(User $user): bool
    {
        return $this->isWorkspaceAdmin($user);
    }

    /**
     * Atur sprint, backlog, dan otomasi: admin atau mentor workspace tim. Space pribadi intern
     * tidak memakai sprint (satu orang, tanpa pembagian tugas).
     */
    public function manageSprints(User $user, Workspace $workspace): bool
    {
        if ($workspace->is_archived || $workspace->type === 'intern') {
            return false;
        }

        return $this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace);
    }

    /** Membuat kelompok: mentor (admin tidak membuatkan kelompok untuk mentor). */
    public function createGroup(User $user): bool
    {
        return $user->isMentor() && $user->mentor !== null;
    }

    /** Ganti nama, warna, dan tandai selesai: mentor pemilik atau admin; hanya kelompok yang masih aktif. */
    public function manageGroup(User $user, Workspace $workspace): bool
    {
        if ($workspace->type !== 'group' || $workspace->is_archived) {
            return false;
        }

        return $this->isWorkspaceAdmin($user) || $workspace->mentor_user_id === $user->id;
    }

    /** Tambah anggota kelompok: mentor pemilik saja. */
    public function addGroupMembers(User $user, Workspace $workspace): bool
    {
        return $workspace->type === 'group' && ! $workspace->is_archived && $workspace->mentor_user_id === $user->id;
    }

    public function archive(User $user, Workspace $workspace): bool
    {
        return $this->isWorkspaceAdmin($user);
    }
}
