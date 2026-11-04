<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Channel;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Notifications\AnnouncementNotification;
use App\Services\AttachmentService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    private const SEARCH_LIMIT = 20;
    private const PAGE_SIZE = 50;
    private const POLL_LIMIT = 200;

    public function __construct(private MessageService $messages, private AttachmentService $attachments)
    {
    }

    /** Query dasar pesan channel: penulis, mention, lampiran, reaksi, dan hitungan balasan. */
    private function base(Channel $channel)
    {
        return Message::withTrashed()
            ->where('channel_id', $channel->id)
            ->with(['user:id,name,role', 'mentions:id,name', 'attachments', 'reactions'])
            ->withCount('replies')
            ->withMax('replies', 'created_at');
    }

    /**
     * Daftar pesan channel.
     *  - default / ?before=ID : 50 pesan utama terbaru (urut lama ke baru), scroll ke atas dengan before
     *  - ?after=ID&since=T    : polling, pesan baru termasuk balasan thread di `data`, serta pesan lama
     *                           yang diedit/dihapus/disematkan/direaksi sejak T (dan induk balasan baru) di `updated`
     *  - ?parent_id=ID        : balasan satu thread
     *  - ?q=kata / ?pinned=1  : pencarian / pesan yang di-pin
     * `meta.server_time` dipakai klien sebagai `since` pada polling berikutnya.
     */
    public function index(Request $request, Channel $channel): JsonResponse
    {
        $this->authorize('view', $channel);

        $p = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
            'before' => ['nullable', 'integer', 'min:1'],
            'since' => ['nullable', 'date'],
            'parent_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
            'pinned' => ['nullable', 'boolean'],
        ]);

        $serverTime = now();
        $hasMore = false;
        $updated = collect();
        $total = null;
        $polling = isset($p['after']);

        if ($polling) {
            $messages = $this->base($channel)->where('id', '>', $p['after'])->orderBy('id')->limit(self::POLL_LIMIT)->get();

            if (! empty($p['since'])) {
                $since = Carbon::parse($p['since']);
                $parentIds = $messages->pluck('parent_id')->filter()->unique()->diff($messages->pluck('id'))->values()->all();

                $updated = $this->base($channel)
                    ->where(fn ($q) => $q
                        ->where(fn ($old) => $old->where('id', '<=', $p['after'])->where('updated_at', '>=', $since))
                        ->orWhereIn('id', $parentIds))
                    ->orderBy('id')
                    ->get();
            }
        } elseif (isset($p['parent_id'])) {
            $messages = $this->base($channel)->where('parent_id', $p['parent_id'])->orderBy('id')->limit(self::POLL_LIMIT)->get();
        } elseif (! empty($p['q'])) {
            $like = '%' . addcslashes($p['q'], '%_' . chr(92)) . '%';
            $match = fn () => $this->base($channel)->withoutTrashed()->where('body', 'like', $like);

            $total = $match()->count();
            $messages = $match()->orderByDesc('id')->limit(self::SEARCH_LIMIT)->get();
        } elseif (! empty($p['pinned'])) {
            $messages = $this->base($channel)->withoutTrashed()->where('is_pinned', true)->orderByDesc('id')->limit(self::PAGE_SIZE)->get();
        } else {
            $page = $this->base($channel)
                ->whereNull('parent_id')
                ->when($p['before'] ?? null, fn ($q, $before) => $q->where('id', '<', $before))
                ->orderByDesc('id')
                ->limit(self::PAGE_SIZE + 1)
                ->get();

            $hasMore = $page->count() > self::PAGE_SIZE;
            $messages = $page->take(self::PAGE_SIZE)->reverse()->values();
        }

        $this->attachTaskRefs($channel, $messages->concat($updated));

        $meta = [
            'has_more' => $hasMore,
            'has_more_before' => $hasMore,
            'latest_id' => $messages->max('id'),
            'server_time' => $serverTime->toIso8601String(),
        ];

        if ($total !== null) {
            $meta['total'] = $total;
        }

        $response = ['data' => MessageResource::collection($messages)->resolve($request)];

        if ($polling) {
            $response['updated'] = MessageResource::collection($updated)->resolve($request);
        }

        return response()->json($response + ['meta' => $meta]);
    }

    /** Balasan satu thread (lama ke baru) beserta pesan induknya. */
    public function replies(Request $request, Message $message): JsonResponse
    {
        abort_if($message->parent_id !== null, 404);

        $channel = $message->channel;
        $this->authorize('view', $channel);

        $replies = $this->base($channel)->where('parent_id', $message->id)->orderBy('id')->get();
        $parent = $this->base($channel)->findOrFail($message->id);

        $this->attachTaskRefs($channel, $replies->concat([$parent]));

        return response()->json([
            'data' => MessageResource::collection($replies)->resolve($request),
            'parent' => (new MessageResource($parent))->resolve($request),
        ]);
    }

    /** Reaksi emoji: menekan emoji yang sama lagi melepasnya. Anggota yang bisa melihat boleh bereaksi. */
    public function react(Request $request, Message $message): JsonResponse
    {
        $channel = $message->channel;
        $this->authorize('view', $channel);

        abort_if($channel->is_archived || $channel->workspace?->is_archived, 403, 'Channel sudah diarsipkan. Reaksi tidak bisa diubah.');

        $data = $request->validate(['emoji' => ['required', 'string', Rule::in(MessageReaction::ALLOWED)]]);
        $user = $request->user();

        $existing = MessageReaction::where(['message_id' => $message->id, 'user_id' => $user->id, 'emoji' => $data['emoji']])->first();

        if ($existing) {
            $existing->delete();
        } else {
            MessageReaction::create(['message_id' => $message->id, 'user_id' => $user->id, 'emoji' => $data['emoji']]);
        }

        // Menyentuh pesan supaya polling anggota lain membawanya di `updated`.
        $message->touch();

        return response()->json(['data' => MessageResource::reactionsFor($message->fresh('reactions'), $user->id)]);
    }

    /** Kirim pesan atau balas thread (parent_id). Teks, mention, dan lampiran. */
    public function store(Request $request, Channel $channel): JsonResponse
    {
        $this->authorize('post', $channel);

        $data = $request->validate([
            'body' => ['required_without_all:files,attachment_ids', 'nullable', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('messages', 'id')
                ->where('channel_id', $channel->id)
                ->whereNull('parent_id')
                ->whereNull('deleted_at')],
            'mention_ids' => ['nullable', 'array', 'max:50'],
            'mention_ids.*' => ['integer'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => AttachmentController::FILE_RULES,
            'attachment_ids' => ['nullable', 'array', 'max:10'],
            'attachment_ids.*' => ['integer'],
        ]);

        $body = trim($data['body'] ?? '');

        if ($this->messages->mentionsChannel($body)) {
            $this->authorize('mentionAll', $channel);
        }

        $user = $request->user();

        $message = DB::transaction(function () use ($request, $channel, $user, $data, $body) {
            $message = $channel->messages()->create([
                'user_id' => $user->id,
                'parent_id' => $data['parent_id'] ?? null,
                'body' => $body,
            ]);

            if ($request->hasFile('files')) {
                $this->attachments->store($message, $request->file('files'), $user, compressImages: true);
            }

            $this->attachments->claim($message, $data['attachment_ids'] ?? [], $user, $channel->workspace_id);

            $this->messages->syncMentions($message, $user, $data['mention_ids'] ?? []);

            return $message;
        });

        // Pengumuman baru: semua anggota channel kecuali penulis dan yang sudah di-mention.
        if ($channel->type === 'announcement' && ! $message->parent_id) {
            $mentioned = $message->mentions()->pluck('users.id');

            Notification::send(
                $channel->members()->where('users.id', '!=', $user->id)->whereNotIn('users.id', $mentioned)->get(),
                new AnnouncementNotification($message->setRelation('user', $user))
            );
        }

        return $this->respond($channel, $message, 201);
    }

    /** Edit pesan sendiri; diberi tanda "diedit". */
    public function update(Request $request, Message $message): JsonResponse
    {
        $this->authorize('update', $message);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'mention_ids' => ['nullable', 'array', 'max:50'],
            'mention_ids.*' => ['integer'],
        ]);

        $body = trim($data['body']);

        if ($this->messages->mentionsChannel($body)) {
            $this->authorize('mentionAll', $message->channel);
        }

        DB::transaction(function () use ($request, $message, $body, $data) {
            if ($body !== $message->body) {
                $message->update(['body' => $body, 'edited_at' => now()]);
            }

            $this->messages->syncMentions($message, $request->user(), $data['mention_ids'] ?? []);
        });

        return $this->respond($message->channel, $message);
    }

    /**
     * Soft delete: pesan tampil sebagai "pesan dihapus". Membalas `{data: Message}` dengan
     * `is_deleted: true`. Idempoten: menghapus pesan yang sudah terhapus membalas hal yang sama
     * (klik ganda tidak menghasilkan 404), asalkan user berhak menghapusnya.
     */
    public function destroy(Message $message): JsonResponse
    {
        $this->authorize('delete', $message);

        if (! $message->trashed()) {
            $message->delete();
        }

        return $this->respond($message->channel, $message);
    }

    /** Pin / lepas pin pesan: moderator saja. */
    public function pin(Request $request, Message $message): JsonResponse
    {
        $this->authorize('pin', $message);

        $data = $request->validate(['is_pinned' => ['required', 'boolean']]);

        if ($message->parent_id !== null) {
            throw ValidationException::withMessages(['is_pinned' => 'Hanya pesan utama yang bisa disematkan.']);
        }

        $message->update(['is_pinned' => $data['is_pinned']]);

        return $this->respond($message->channel, $message);
    }

    private function respond(Channel $channel, Message $message, int $status = 200): JsonResponse
    {
        $message = Message::withTrashed()
            ->with(['user:id,name,role', 'mentions:id,name', 'attachments'])
            ->withCount('replies')
            ->findOrFail($message->id);

        $this->attachTaskRefs($channel, collect([$message]));

        return (new MessageResource($message))->response()->setStatusCode($status);
    }

    /** @param  Collection<int, Message>  $messages */
    private function attachTaskRefs(Channel $channel, Collection $messages): void
    {
        $refs = $this->messages->taskRefs($channel, $messages);

        // Hak per channel dihitung sekali, bukan per pesan.
        $gate = \Illuminate\Support\Facades\Gate::forUser(request()->user());
        $canPost = $gate->allows('post', $channel);
        $canModerate = $gate->allows('moderate', $channel);

        foreach ($messages as $message) {
            $message->setAttribute('task_refs', $refs[$message->id] ?? []);
            $message->setAttribute('can_post', $canPost);
            $message->setAttribute('can_moderate', $canModerate);
        }
    }
}
