<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Hitung pesan belum dibaca dari channel_members.last_read_message_id. */
class ChannelUnreadService
{
    /**
     * Pesan orang lain yang belum dibaca per channel.
     *
     * @param  array<int, int>  $channelIds
     * @return array<int, int>  channel_id => jumlah
     */
    public function counts(User $user, array $channelIds): array
    {
        if (! $channelIds) {
            return [];
        }

        return DB::table('messages')
            ->selectRaw('channel_id, count(*) as total')
            ->whereIn('channel_id', $channelIds)
            ->whereNull('deleted_at')
            ->whereNull('parent_id')
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', '!=', $user->id))
            ->whereRaw(
                'id > coalesce((select cm.last_read_message_id from channel_members cm where cm.channel_id = messages.channel_id and cm.user_id = ?), 0)',
                [$user->id]
            )
            ->groupBy('channel_id')
            ->pluck('total', 'channel_id')
            ->all();
    }

    /** Total belum dibaca di semua channel aktif yang user ikuti. */
    public function total(User $user): int
    {
        $channelIds = DB::table('channel_members')
            ->join('channels', 'channels.id', '=', 'channel_members.channel_id')
            ->where('channel_members.user_id', $user->id)
            ->where('channel_members.is_muted', false)
            ->where('channels.is_archived', false)
            ->pluck('channels.id')
            ->all();

        return (int) array_sum($this->counts($user, $channelIds));
    }
}
