<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class MessagePolicy
{
    /** Edit hanya pesan sendiri, selama masih boleh posting di channel itu. */
    public function update(User $user, Message $message): bool
    {
        return $message->user_id === $user->id
            && ! $message->trashed()
            && Gate::forUser($user)->allows('post', $message->channel);
    }

    /** Hapus pesan sendiri, atau pesan orang lain oleh moderator (admin / mentor channel timnya). */
    public function delete(User $user, Message $message): bool
    {
        if (Gate::forUser($user)->allows('moderate', $message->channel)) {
            return true;
        }

        return $message->user_id === $user->id
            && Gate::forUser($user)->allows('post', $message->channel);
    }

    public function pin(User $user, Message $message): bool
    {
        return ! $message->trashed() && Gate::forUser($user)->allows('moderate', $message->channel);
    }
}
