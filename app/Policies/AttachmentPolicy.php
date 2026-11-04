<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Message;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Unduhan lampiran mengikuti hak lihat induknya: tugas, komentar tugas, atau pesan.
 */
class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        // Lampiran yang belum ditautkan hanya terlihat oleh pengunggahnya.
        if ($attachment->attachable_id === null) {
            return $attachment->user_id === $user->id;
        }

        $parent = $attachment->attachable;

        return match (true) {
            $parent instanceof Task => Gate::forUser($user)->allows('view', $parent),
            $parent instanceof TaskComment => Gate::forUser($user)->allows('view', $parent->task),
            $parent instanceof Message => Gate::forUser($user)->allows('view', $parent->channel),
            default => false,
        };
    }
}
