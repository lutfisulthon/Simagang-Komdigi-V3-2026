<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Workspace;
use App\Services\ChannelUnreadService;
use App\Services\WorkspacePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChannelController extends Controller
{
    public function __construct(private ChannelUnreadService $unread, private WorkspacePresenter $presenter)
    {
    }

    /**
     * Channel milik user beserta jumlah pesan belum dibaca (untuk badge dan polling).
     * Admin yang memberi ?workspace_id= melihat semua channel workspace itu.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Workspace::class);

        $p = $request->validate([
            'workspace_id' => ['nullable', 'integer'],
            'include_archived' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $seesAll = ($user->isSuperAdmin() || $user->isAdminFull()) && ! empty($p['workspace_id']);

        $channels = Channel::query()
            ->with('workspace:id,type,name,is_archived')
            ->when(! $seesAll, fn ($q) => $q->whereHas('members', fn ($m) => $m->where('users.id', $user->id)))
            ->when($p['workspace_id'] ?? null, fn ($q, $id) => $q->where('workspace_id', $id))
            ->when(empty($p['include_archived']), fn ($q) => $q->where('is_archived', false))
            ->orderBy('workspace_id')
            ->orderBy('name')
            ->get();

        // Setara ChannelPolicy::view, tapi keanggotaan workspace dicek sekali (bukan per channel).
        if (! $user->isSuperAdmin() && ! $user->isAdminFull()) {
            $workspaceIds = DB::table('workspace_members')
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->pluck('workspace_id')
                ->flip();

            $channels = $channels->filter(fn (Channel $c) => $c->workspace_id === null || $workspaceIds->has($c->workspace_id));
        }

        $channels = $channels->values();

        $data = collect($this->presenter->channelList($channels, $user))
            ->map(fn (array $c) => $c + ['workspace_name' => $channels->firstWhere('id', $c['id'])?->workspace?->name]);

        return response()->json([
            'data' => $data,
            // Channel yang dibisukan tidak ikut dihitung ke total.
            'meta' => ['total_unread' => $data->where('is_muted', false)->sum('unread_count')],
        ]);
    }

    /** Buat channel topik. Anggota dipilih pembuat; kosong = semua anggota aktif workspace. */
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('create', [Channel::class, $workspace]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:40', 'regex:/^[a-z0-9][a-z0-9\-]*$/',
                // unik di workspace ini, termasuk channel global
                Rule::unique('channels', 'name')->where(fn ($q) => $q->where('workspace_id', $workspace->id)->orWhereNull('workspace_id'))],
            'description' => ['nullable', 'string', 'max:255'],
            'is_private' => ['nullable', 'boolean'],
            'member_ids' => ['nullable', 'array', 'max:200'],
            'member_ids.*' => ['integer', 'distinct'],
        ]);

        $workspaceUserIds = DB::table('workspace_members')
            ->where('workspace_id', $workspace->id)
            ->whereNull('left_at')
            ->pluck('user_id')
            ->all();

        $memberIds = $data['member_ids'] ?? $workspaceUserIds;

        if (! empty($data['is_private']) && ! array_diff($memberIds, [$request->user()->id])) {
            throw ValidationException::withMessages(['member_ids' => 'Channel privat butuh minimal satu anggota selain pembuat.']);
        }

        if (array_diff($memberIds, $workspaceUserIds)) {
            throw ValidationException::withMessages(['member_ids' => 'Ada user yang bukan anggota workspace ini.']);
        }

        $channel = DB::transaction(function () use ($request, $workspace, $data, $memberIds) {
            $channel = Channel::create([
                'workspace_id' => $workspace->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'type' => 'topic',
                'is_private' => $data['is_private'] ?? false,
                'created_by' => $request->user()->id,
            ]);

            $channel->members()->syncWithoutDetaching(array_unique([...$memberIds, $request->user()->id]));

            return $channel;
        });

        return response()->json(['data' => $this->presenter->channelList(collect([$channel]), $request->user())[0]], 201);
    }

    /** Ubah info channel: nama (khusus topik), deskripsi, dan arsipkan/pulihkan (khusus topik). */
    public function update(Request $request, Channel $channel): JsonResponse
    {
        $this->authorize('archive', $channel);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:40', 'regex:/^[a-z0-9][a-z0-9\-]*$/',
                Rule::unique('channels', 'name')->ignore($channel->id)->where(fn ($q) => $q->where('workspace_id', $channel->workspace_id)->orWhereNull('workspace_id'))],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_archived' => ['sometimes', 'boolean'],
        ]);

        if ((isset($data['name']) || isset($data['is_archived'])) && $channel->type !== 'topic') {
            throw ValidationException::withMessages(['channel' => 'Hanya channel topik yang bisa diganti nama atau diarsipkan.']);
        }

        $channel->update($data);

        return response()->json(['data' => $this->presenter->channelList(collect([$channel->fresh()]), $request->user())[0]]);
    }

    /** Bisukan atau aktifkan kembali channel untuk user ini (hanya anggota channel). */
    public function mute(Request $request, Channel $channel): JsonResponse
    {
        $this->authorize('view', $channel);

        $data = $request->validate(['is_muted' => ['required', 'boolean']]);

        $updated = DB::table('channel_members')
            ->where('channel_id', $channel->id)
            ->where('user_id', $request->user()->id)
            ->update(['is_muted' => $data['is_muted'], 'updated_at' => now()]);

        abort_if($updated === 0, 403, 'Hanya anggota channel yang bisa membisukannya.');

        return response()->json(['data' => $this->presenter->channelList(collect([$channel]), $request->user())[0]]);
    }

    /** Arsipkan channel topik (read-only, pesan tetap tersimpan). */
    public function archive(Channel $channel): JsonResponse
    {
        $this->authorize('archive', $channel);

        if ($channel->type !== 'topic') {
            throw ValidationException::withMessages(['channel' => 'Hanya channel topik yang bisa diarsipkan.']);
        }

        $channel->update(['is_archived' => true]);

        return response()->json(['data' => ['id' => $channel->id, 'is_archived' => true]]);
    }

    /** Tandai channel sudah dibaca sampai pesan terbaru (atau sampai message_id). */
    public function markRead(Request $request, Channel $channel): JsonResponse
    {
        $this->authorize('view', $channel);

        $data = $request->validate([
            'message_id' => ['nullable', 'integer', Rule::exists('messages', 'id')->where('channel_id', $channel->id)],
        ]);

        $user = $request->user();
        $latest = Message::withTrashed()->where('channel_id', $channel->id)->max('id');
        $target = $latest ? min($data['message_id'] ?? $latest, $latest) : null;

        if ($target) {
            // Hanya maju, tidak pernah mundur. Non-anggota (admin yang melihat saja) tidak punya baris, jadi tidak berubah.
            DB::table('channel_members')
                ->where('channel_id', $channel->id)
                ->where('user_id', $user->id)
                ->where(fn ($q) => $q->whereNull('last_read_message_id')->orWhere('last_read_message_id', '<', $target))
                ->update(['last_read_message_id' => $target, 'updated_at' => now()]);
        }

        return response()->json([
            'unread_count' => (int) ($this->unread->counts($user, [$channel->id])[$channel->id] ?? 0),
        ]);
    }
}
