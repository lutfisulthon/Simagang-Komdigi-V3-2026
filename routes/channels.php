<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Workspace: aturan sama dengan WorkspacePolicy@view (anggota workspace atau admin).
Broadcast::channel('workspace.{workspaceId}', function ($user, $workspaceId) {
    $workspace = \App\Models\Workspace::find($workspaceId);

    return $workspace !== null && $user->can('view', $workspace);
});

// #pengumuman dan channel global lain (tanpa workspace): aturan sama dengan ChannelPolicy@view.
Broadcast::channel('announcements', function ($user) {
    $channel = \App\Models\Channel::whereNull('workspace_id')->first();

    return $channel !== null && $user->can('view', $channel);
});

// Chat Publik: semua user login yang aktif (sama dengan middleware public-chat).
Broadcast::channel('public-chat', function ($user) {
    return ! \App\Support\PublicChat::isBlocked($user);
});
