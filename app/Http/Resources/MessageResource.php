<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Isi pesan dikirim apa adanya (teks mentah). Klien wajib meng-escape HTML saat
 * merender markdown. Pesan yang dihapus tampil sebagai placeholder tanpa isi.
 *
 * @mixin \App\Models\Message
 */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $deleted = $this->trashed();
        $viewerId = $request->user()?->id;
        $own = $viewerId !== null && $this->user_id === $viewerId;
        $canPost = (bool) ($this->resource->can_post ?? false);
        $canModerate = (bool) ($this->resource->can_moderate ?? false);

        return [
            'id' => $this->id,
            'channel_id' => $this->channel_id,
            'parent_id' => $this->parent_id,
            'body' => $deleted ? null : $this->body,
            'body_html' => $deleted ? '' : $this->html(),
            'is_deleted' => $deleted,
            'is_pinned' => ! $deleted && $this->is_pinned,
            'edited_at' => $deleted ? null : $this->edited_at,
            'created_at' => $this->created_at,
            // whenLoaded() mengembalikan null mentah jika relasinya kosong, jadi dicek eksplisit agar penulis terhapus tetap punya nama.
            'user' => $this->when($this->resource->relationLoaded('user'), fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name ?? 'Pengguna dihapus',
                'role' => $this->roleOf($this->user),
                'avatar_url' => null,
            ]),
            // Daftar users.id yang disebut (frontend menyorot pesan yang menyebut user login).
            'mentions' => $deleted ? [] : $this->whenLoaded('mentions', fn () => $this->mentions->pluck('id')->values()),
            'mentions_channel' => ! $deleted && $this->mentionsAll(),
            'attachments' => $deleted ? [] : $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->original_name,
                'original_name' => $a->original_name,
                'mime' => $a->mime,
                'size' => (int) $a->size,
                'is_image' => str_starts_with((string) $a->mime, 'image/'),
                'thumbnail_url' => str_starts_with((string) $a->mime, 'image/') ? route('workspace.attachments.download', $a) : null,
                'download_url' => route('workspace.attachments.download', $a),
                'url' => route('workspace.attachments.download', $a),
            ])),
            'replies_count' => $this->whenCounted('replies'),
            'reply_count' => $this->whenCounted('replies'),
            'last_reply_at' => $this->resource->replies_max_created_at
                ? \Illuminate\Support\Carbon::parse($this->resource->replies_max_created_at)->toIso8601String()
                : null,
            'reactions' => $deleted ? [] : $this->whenLoaded('reactions', fn () => self::reactionsFor($this->resource, $viewerId)),
            'can' => [
                'edit' => ! $deleted && $own && $canPost,
                'delete' => ! $deleted && ($canModerate || ($own && $canPost)),
                'pin' => ! $deleted && ! $this->parent_id && $canModerate,
            ],
            'task_refs' => $this->resource->task_refs ?? [],
        ];
    }

    /**
     * Reaksi dikelompokkan per emoji (urutan reaksi cepat): jumlah dan apakah user ini ikut bereaksi.
     *
     * @return array<int, array{emoji: string, count: int, me: bool}>
     */
    public static function reactionsFor($message, ?int $viewerId): array
    {
        $grouped = $message->reactions->groupBy('emoji');

        return collect(\App\Models\MessageReaction::ALLOWED)
            ->filter(fn ($emoji) => $grouped->has($emoji))
            ->map(fn ($emoji) => [
                'emoji' => $emoji,
                'count' => $grouped[$emoji]->count(),
                'me' => $viewerId !== null && $grouped[$emoji]->contains('user_id', $viewerId),
            ])
            ->values()
            ->all();
    }

    private function roleOf($user): ?string
    {
        return match (true) {
            ! $user => null,
            $user->isSuperAdmin() || $user->isAdminFull() => 'admin',
            $user->isMentor() => 'mentor',
            $user->isIntern() => 'intern',
            default => 'admin',
        };
    }

    /** @channel hanya berlaku bila penulisnya mentor atau admin (sama dengan aturan di MessageController). */
    private function mentionsAll(): bool
    {
        return in_array($this->roleOf($this->user), ['mentor', 'admin'], true)
            && (bool) preg_match('/(^|\s)@channel\b/u', (string) $this->body);
    }

    /** Markdown aman (HTML di-escape) lalu rujukan #T-12 dan @mention diberi penanda. */
    private function html(): string
    {
        $html = app(\App\Services\WorkspacePresenter::class)->markdown($this->body);

        foreach ($this->resource->task_refs ?? [] as $ref) {
            $url = e(route('workspace.tasks.show', $ref['task_id']));
            $html = preg_replace(
                '/#' . preg_quote($ref['code'], '/') . '(?![\w-])/',
                '<a href="' . $url . '" class="ws-task-ref" data-task-id="' . (int) $ref['task_id'] . '">#' . e($ref['code']) . '</a>',
                $html
            );
        }

        if ($this->resource->relationLoaded('mentions')) {
            $names = $this->mentions->sortByDesc(fn ($u) => mb_strlen($u->name));

            foreach ($names as $u) {
                $html = preg_replace(
                    '/@' . preg_quote(e($u->name), '/') . '(?![\w])/u',
                    '<span class="ws-mention" data-user-id="' . (int) $u->id . '">@' . e($u->name) . '</span>',
                    $html
                );
            }
        }

        if ($this->mentionsAll()) {
            $html = preg_replace('/@channel\b/u', '<span class="ws-mention ws-mention--all">@channel</span>', $html);
        }

        return $html;
    }
}
