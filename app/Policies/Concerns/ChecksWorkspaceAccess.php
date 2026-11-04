<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

/**
 * Pengecekan akses bersama untuk WorkspacePolicy, TaskPolicy, ChannelPolicy,
 * dan AttachmentPolicy. Akses ditentukan keanggotaan workspace (workspace_members),
 * bukan prefix route per role.
 */
trait ChecksWorkspaceAccess
{
    /** @var array<string, string|null> */
    private array $roleCache = [];

    /** Admin modul Workspace: super_admin dan admin_full. */
    protected function isWorkspaceAdmin(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminFull();
    }

    /** Role Institusi dan Industri belum mendapat akses di fase ini. */
    protected function canUseWorkspace(User $user): bool
    {
        return $this->isWorkspaceAdmin($user) || $user->isMentor() || $user->isIntern();
    }

    /** Role user di workspace (owner/mentor/member/viewer); null jika bukan anggota aktif. */
    protected function memberRole(User $user, Workspace $workspace): ?string
    {
        $key = $user->id . ':' . $workspace->id;

        if (! array_key_exists($key, $this->roleCache)) {
            $this->roleCache[$key] = DB::table('workspace_members')
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->value('role');
        }

        return $this->roleCache[$key];
    }

    protected function isMember(User $user, Workspace $workspace): bool
    {
        return $this->memberRole($user, $workspace) !== null;
    }

    /** Mentor yang menjadi anggota aktif workspace (tim sendiri atau Space intern bimbingan). */
    protected function isWorkspaceMentor(User $user, Workspace $workspace): bool
    {
        return $user->isMentor() && $this->memberRole($user, $workspace) === 'mentor';
    }

    /** Anggota yang boleh menulis: bukan viewer dan workspace belum diarsipkan. */
    protected function canWrite(User $user, Workspace $workspace): bool
    {
        if ($workspace->is_archived) {
            return false;
        }

        $role = $this->memberRole($user, $workspace);

        return $role !== null && $role !== 'viewer';
    }

    /** Space Intern milik user ini sendiri. */
    protected function isOwnSpace(User $user, Workspace $workspace): bool
    {
        return $workspace->type === 'intern'
            && $user->isIntern()
            && $workspace->intern_id !== null
            && optional($user->intern)->id === $workspace->intern_id;
    }
}
