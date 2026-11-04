<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ChatUnreadNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WorkspaceNotifyUnreadChatCommand extends Command
{
    protected $signature = 'workspace:notify-unread-chat {--count=5 : Ambang jumlah pesan} {--hours=5 : Ambang usia pesan tertua}';

    protected $description = 'Email ringkasan chat yang belum dibaca: 5 pesan atau lebih, atau pesan tertua sudah 5 jam';

    public function handle(): int
    {
        $minCount = (int) $this->option('count');
        $cutoff = now()->subHours((int) $this->option('hours'));

        // Pesan orang lain yang belum dibaca dan belum pernah diingatkan, per anggota channel aktif.
        $rows = DB::table('channel_members as cm')
            ->join('channels as c', 'c.id', '=', 'cm.channel_id')
            ->join('messages as m', function ($join) {
                $join->on('m.channel_id', '=', 'cm.channel_id')
                    ->whereNull('m.deleted_at')
                    ->whereNull('m.parent_id')
                    ->whereRaw('m.id > greatest(coalesce(cm.last_read_message_id, 0), coalesce(cm.unread_emailed_until_id, 0))')
                    ->whereRaw('(m.user_id is null or m.user_id <> cm.user_id)');
            })
            ->where('cm.is_muted', false)
            ->where('c.is_archived', false)
            ->where('c.type', '!=', 'announcement')
            ->groupBy('cm.id', 'cm.user_id', 'cm.channel_id', 'c.name', 'c.workspace_id')
            ->selectRaw('cm.id as member_id, cm.user_id, cm.channel_id, c.name as channel, c.workspace_id,
                count(*) as total, min(m.created_at) as oldest, max(m.id) as last_id')
            ->get()
            ->filter(fn ($r) => $r->total >= $minCount || $r->oldest <= $cutoff->toDateTimeString());

        $sent = 0;

        foreach ($rows->groupBy('user_id') as $userId => $items) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            try {
                $user->notify(new ChatUnreadNotification($items->map(fn ($r) => [
                    'channel' => $r->channel,
                    'workspace_id' => (int) $r->workspace_id,
                    'count' => (int) $r->total,
                ])->all()));

                foreach ($items as $r) {
                    DB::table('channel_members')->where('id', $r->member_id)->update(['unread_emailed_until_id' => $r->last_id]);
                }
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->info("Pengingat chat dikirim ke {$sent} user.");

        return self::SUCCESS;
    }
}
