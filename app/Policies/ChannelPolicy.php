<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use App\Policies\Concerns\ChecksWorkspaceAccess;
use Illuminate\Support\Facades\DB;

class ChannelPolicy
{
    use ChecksWorkspaceAccess;

    /**
     * Admin melihat semua channel. Pengguna lain harus anggota channel dan anggota aktif
     * workspace-nya, sehingga channel tim lain dan DM intern lain tertutup (403).
     * #pengumuman (tanpa workspace) terbuka untuk admin, mentor, dan intern.
     */
    public function view(User $user, Channel $channel): bool
    {
        if ($this->isWorkspaceAdmin($user)) {
            return true;
        }

        if (! $this->canUseWorkspace($user)) {
            return false;
        }

        if ($channel->workspace_id === null) {
            return true;
        }

        return $this->isChannelMember($user, $channel)
            && $this->isMember($user, $channel->workspace);
    }

    /** Kirim pesan, balas thread, dan mention. #pengumuman hanya untuk admin dan mentor. */
    public function post(User $user, Channel $channel): bool
    {
        if ($channel->is_archived || ! $this->view($user, $channel)) {
            return false;
        }

        if ($channel->workspace_id === null) {
            return $this->isWorkspaceAdmin($user) || $user->isMentor();
        }

        $workspace = $channel->workspace;

        if ($this->isWorkspaceAdmin($user)) {
            return ! $workspace->is_archived;
        }

        return $this->canWrite($user, $workspace);
    }

    /** Membuat channel di workspace: admin atau mentor workspace itu. Pemakaian: can('create', [Channel::class, $workspace]) */
    public function create(User $user, Workspace $workspace): bool
    {
        if ($workspace->is_archived) {
            return false;
        }

        return $this->isWorkspaceAdmin($user) || $this->isWorkspaceMentor($user, $workspace);
    }

    /** Menghapus pesan orang lain dan pin pesan: admin, atau mentor di channel timnya. */
    public function moderate(User $user, Channel $channel): bool
    {
        if ($this->isWorkspaceAdmin($user)) {
            return true;
        }

        return $channel->workspace_id !== null
            && $this->isWorkspaceMentor($user, $channel->workspace);
    }

    /** Mengarsipkan channel: admin, atau mentor di workspace channel itu. */
    public function archive(User $user, Channel $channel): bool
    {
        return $this->moderate($user, $channel);
    }

    /** Mention @channel: khusus admin dan mentor. */
    public function mentionAll(User $user, Channel $channel): bool
    {
        return $this->post($user, $channel)
            && ($this->isWorkspaceAdmin($user) || $user->isMentor());
    }

    private function isChannelMember(User $user, Channel $channel): bool
    {
        return DB::table('channel_members')
            ->where('channel_id', $channel->id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
