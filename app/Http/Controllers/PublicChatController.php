<?php

namespace App\Http\Controllers;

use App\Models\PublicChatMember;
use App\Models\PublicChatMessage;
use App\Support\PublicChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Chat Publik untuk semua pengguna yang login. Kontrak JSON: docs/public-chat-backend.md.
 * Identitas penulis selalu diambil dari sesi login, bukan dari isi permintaan.
 */
class PublicChatController extends Controller
{
    public function page(Request $request)
    {
        $this->markRead(PublicChatMember::for($request->user()));

        return view('chat-publik.index');
    }

    /** GET /public-chat/messages?after=<id>[&viewing=1] */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'after' => ['nullable', 'integer', 'min:0'],
            'viewing' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $member = PublicChatMember::for($user);
        $this->touch($member);

        $query = PublicChatMessage::visible()->with('user.roles');
        if (isset($data['after'])) {
            $messages = $query->where('id', '>', (int) $data['after'])
                ->orderBy('id')
                ->limit(config('public_chat.poll_limit'))
                ->get();
        } else {
            $messages = $query->orderByDesc('id')->limit(config('public_chat.history'))->get()->reverse()->values();
        }

        if ($request->boolean('viewing')) {
            $this->markRead($member);
        }

        $unread = PublicChatMessage::visible()
            ->where('id', '>', $member->last_read_id)
            ->where('user_id', '!=', $user->id)
            ->count();

        return response()->json([
            'messages' => $messages->map->toChatArray()->all(),
            'online' => PublicChatMember::where('last_active_at', '>=', now()->subSeconds(config('public_chat.online_window')))->count(),
            'pins' => PublicChat::pins(),
            'unread' => min($unread, 99),
        ]);
    }

    /** POST /public-chat/messages  { text, pin_days? } */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = PublicChat::roleOf($user);
        $member = PublicChatMember::for($user);

        if ($member->isMuted()) {
            return response()->json([
                'message' => $member->muted_until
                    ? 'Anda tidak dapat mengirim pesan sampai ' . $member->muted_until->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') . '.'
                    : 'Anda tidak dapat mengirim pesan di Chat Publik.',
            ], 403);
        }

        if ($request->filled('pin_days') && $role !== 'mentor') {
            return response()->json(['message' => 'Hanya mentor yang dapat mengirim pengumuman.'], 403);
        }

        $text = PublicChat::clean($request->input('text'));
        $request->merge(['text' => $text]);
        $request->validate([
            'text' => ['required', 'string', 'max:' . config('public_chat.max_length')],
            'pin_days' => ['nullable', 'integer', Rule::in(config('public_chat.pin_days'))],
        ], [
            'text.required' => 'Tulis pesan terlebih dahulu.',
            'text.max' => 'Pesan maksimal ' . config('public_chat.max_length') . ' karakter.',
            'pin_days.in' => 'Lama sematan hanya 7 atau 30 hari.',
        ]);

        if (! in_array($role, config('public_chat.links_allowed_roles'), true) && PublicChat::containsLink($text)) {
            throw ValidationException::withMessages(['text' => 'Pesan tidak boleh berisi tautan.']);
        }

        // Jeda minimum antar-pesan per user (batas per menit ditangani middleware throttle).
        $key = 'public-chat:last-send:' . $user->id;
        $nowMs = now()->getTimestampMs();
        $last = (int) Cache::get($key, 0);
        if ($last && $nowMs - $last < config('public_chat.min_interval_ms')) {
            return response()->json(['message' => 'Terlalu cepat. Coba lagi sebentar.'], 429);
        }
        Cache::put($key, $nowMs, 60);

        $days = $request->integer('pin_days');
        $message = $days
            ? $this->withPinLock(function () use ($user, $text, $days) {
                $this->ensurePinSlot();

                return PublicChatMessage::create([
                    'user_id' => $user->id,
                    'text' => $text,
                    'pinned_at' => now(),
                    'pinned_until' => now()->addDays($days),
                ]);
            })
            : PublicChatMessage::create(['user_id' => $user->id, 'text' => $text]);

        $message->load('user.roles');
        $data = $message->toChatArray();

        PublicChat::broadcast('message.created', ['message' => $data]);
        if ($days) {
            PublicChat::broadcast('pins.changed');
        }

        return response()->json(['message' => $data], 201);
    }

    /** POST /public-chat/messages/{id}/pin  { days: 7|30 } */
    public function pin(Request $request, string $id): JsonResponse
    {
        $message = $this->findVisible($id);
        $user = $request->user();

        if (PublicChat::roleOf($user) !== 'mentor' || (int) $message->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Hanya mentor penulis yang dapat menyematkan pesannya.'], 403);
        }

        $days = (int) $request->validate([
            'days' => ['required', 'integer', Rule::in(config('public_chat.pin_days'))],
        ], ['days.in' => 'Lama sematan hanya 7 atau 30 hari.'])['days'];

        $this->withPinLock(function () use ($message, $days) {
            $this->ensurePinSlot($message->id);
            $message->forceFill(['pinned_at' => now(), 'pinned_until' => now()->addDays($days)])->save();
        });
        PublicChat::broadcast('pins.changed');

        return response()->json(['pins' => PublicChat::pins()]);
    }

    /** DELETE /public-chat/messages/{id}/pin */
    public function unpin(Request $request, string $id): JsonResponse
    {
        $message = $this->findVisible($id);

        if ((int) $message->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Hanya penulis yang dapat menghapus pengumuman ini.'], 403);
        }

        $message->forceFill(['pinned_at' => null, 'pinned_until' => null])->save();
        PublicChat::broadcast('pins.changed');

        return response()->json(['pins' => PublicChat::pins()]);
    }

    private function findVisible(string $id): PublicChatMessage
    {
        abort_unless(ctype_digit($id), 404);

        return PublicChatMessage::visible()->findOrFail((int) $id);
    }

    /** Kunci agar dua permintaan bersamaan tidak melewati batas jumlah pengumuman aktif. */
    private function withPinLock(callable $callback): mixed
    {
        return Cache::lock('public-chat:pin', 10)->block(5, fn () => DB::transaction($callback));
    }

    private function ensurePinSlot(?int $exceptId = null): void
    {
        $max = (int) config('public_chat.max_pins');
        $active = PublicChatMessage::activePins()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->count();

        if ($active >= $max) {
            throw ValidationException::withMessages([
                'days' => "Maksimal {$max} pengumuman aktif. Hapus salah satu dulu.",
            ]);
        }
    }

    private function markRead(PublicChatMember $member): void
    {
        $latest = (int) PublicChatMessage::max('id');
        if ($latest > $member->last_read_id) {
            $member->forceFill(['last_read_id' => $latest])->save();
        }
    }

    /** Catat waktu aktif untuk hitungan online, paling sering tiap 10 detik per user. */
    private function touch(PublicChatMember $member): void
    {
        if (! $member->last_active_at || $member->last_active_at->lt(now()->subSeconds(10))) {
            $member->forceFill(['last_active_at' => now()])->save();
        }
    }
}
