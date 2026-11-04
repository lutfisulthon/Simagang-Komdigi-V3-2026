<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use App\Notifications\MessageMentionNotification;
use Illuminate\Support\Collection;

class MessageService
{
    /** @channel dikenali sebagai kata utuh, bukan bagian dari alamat email. */
    private const CHANNEL_MENTION = '/(?<![\w@])@channel(?!\w)/iu';

    private const TASK_REF = '/(?<![\w])#T-(\d+)/i';

    public function mentionsChannel(string $body): bool
    {
        return (bool) preg_match(self::CHANNEL_MENTION, $body);
    }

    /**
     * User yang disebut: @channel (semua anggota), @nama anggota channel, atau id eksplisit
     * dari autocomplete. Hanya anggota channel, penulis tidak ikut.
     *
     * @param  array<int, int>  $explicitIds
     * @return array<int, int>
     */
    public function resolveMentionIds(Channel $channel, User $author, string $body, array $explicitIds = []): array
    {
        $members = $channel->members()->get(['users.id', 'users.name']);

        if ($this->mentionsChannel($body)) {
            return $members->pluck('id')->reject(fn ($id) => $id === $author->id)->values()->all();
        }

        return $this->matchMentions($members, $author, $body, $explicitIds);
    }

    /**
     * User yang disebut @nama di komentar tugas: anggota aktif workspace tugas itu.
     *
     * @return array<int, int>
     */
    public function resolveTaskMentionIds(Task $task, User $author, string $body): array
    {
        $members = User::query()
            ->select('users.id', 'users.name')
            ->join('workspace_members', 'workspace_members.user_id', '=', 'users.id')
            ->where('workspace_members.workspace_id', $task->workspace_id)
            ->whereNull('workspace_members.left_at')
            ->get();

        return $this->matchMentions($members, $author, $body, []);
    }

    /**
     * @param  Collection<int, User>  $members
     * @param  array<int, int>  $explicitIds
     * @return array<int, int>
     */
    private function matchMentions(Collection $members, User $author, string $body, array $explicitIds): array
    {
        $ids = [];
        $remaining = $body;

        // Nama terpanjang dulu, supaya "@Ahmad Fauzi" tidak ikut menyebut anggota bernama "Ahmad".
        foreach ($members->sortByDesc(fn ($m) => mb_strlen($m->name)) as $member) {
            $pattern = '/(?<![\w@])@' . preg_quote($member->name, '/') . '(?!\w)/iu';

            if (preg_match($pattern, $remaining)) {
                $ids[] = $member->id;
                $remaining = preg_replace($pattern, '', $remaining);
            }
        }

        $ids = array_merge($ids, $members->whereIn('id', $explicitIds)->pluck('id')->all());

        return array_values(array_unique(array_filter($ids, fn ($id) => $id !== $author->id)));
    }

    /** Simpan mention dan beri notifikasi hanya ke user yang baru disebut (aman untuk edit pesan). */
    public function syncMentions(Message $message, User $author, array $explicitIds = []): void
    {
        $message->loadMissing('channel');
        $message->setRelation('user', $author);

        $ids = $this->resolveMentionIds($message->channel, $author, $message->body, $explicitIds);
        $changes = $message->mentions()->sync($ids);

        if (! $changes['attached']) {
            return;
        }

        foreach (User::whereIn('id', $changes['attached'])->get() as $user) {
            try {
                $user->notify(new MessageMentionNotification($message));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Rujukan #T-12 di pesan, dicocokkan dengan tugas di workspace channel.
     *
     * @param  Collection<int, Message>  $messages
     * @return array<int, array<int, array{code: string, task_id: int}>>  id pesan => daftar rujukan
     */
    public function taskRefs(Channel $channel, Collection $messages): array
    {
        if (! $channel->workspace_id) {
            return [];
        }

        $numbersByMessage = [];
        foreach ($messages as $message) {
            if ($message->trashed() || ! preg_match_all(self::TASK_REF, $message->body, $m)) {
                continue;
            }
            $numbersByMessage[$message->id] = array_values(array_unique(array_map('intval', $m[1])));
        }

        if (! $numbersByMessage) {
            return [];
        }

        $tasks = Task::where('workspace_id', $channel->workspace_id)
            ->whereIn('number', array_unique(array_merge(...array_values($numbersByMessage))))
            ->pluck('id', 'number');

        $refs = [];
        foreach ($numbersByMessage as $messageId => $numbers) {
            foreach ($numbers as $number) {
                if ($tasks->has($number)) {
                    $refs[$messageId][] = ['code' => 'T-' . $number, 'task_id' => $tasks[$number]];
                }
            }
        }

        return $refs;
    }
}
