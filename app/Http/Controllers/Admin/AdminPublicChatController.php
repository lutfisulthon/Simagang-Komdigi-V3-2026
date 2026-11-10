<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicChatMember;
use App\Models\PublicChatMessage;
use App\Models\User;
use App\Support\PublicChat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Moderasi Chat Publik oleh admin: sembunyikan/pulihkan pesan, cabut sematan, bungkam user. */
class AdminPublicChatController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['visible', 'hidden', 'pinned'], true) ? $request->query('status') : 'all';
        $q = trim((string) $request->query('q', ''));

        $messages = PublicChatMessage::query()
            ->with(['user.roles', 'hiddenBy'])
            ->when($status === 'visible', fn ($query) => $query->visible())
            ->when($status === 'hidden', fn ($query) => $query->whereNotNull('hidden_at'))
            ->when($status === 'pinned', fn ($query) => $query->activePins())
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn ($w) => $w->where('text', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)));
            })
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $mutedIds = PublicChatMember::whereNotNull('muted_at')
            ->where(fn ($w) => $w->whereNull('muted_until')->orWhere('muted_until', '>', now()))
            ->pluck('user_id')
            ->all();

        $muted = PublicChatMember::with(['user', 'mutedBy'])->whereIn('user_id', $mutedIds)->orderByDesc('muted_at')->get();

        return view('admin.public-chat.index', [
            'messages' => $messages,
            'muted' => $muted,
            'mutedIds' => $mutedIds,
            'status' => $status,
            'q' => $q,
            'counts' => [
                'all' => PublicChatMessage::count(),
                'visible' => PublicChatMessage::visible()->count(),
                'hidden' => PublicChatMessage::whereNotNull('hidden_at')->count(),
                'pinned' => PublicChatMessage::activePins()->count(),
            ],
        ]);
    }

    public function hide(Request $request, PublicChatMessage $message): RedirectResponse
    {
        $wasPinned = $message->isPinned();
        $message->forceFill(['hidden_at' => now(), 'hidden_by' => $request->user()->id])->save();

        PublicChat::broadcast('message.hidden', ['id' => (string) $message->id]);
        if ($wasPinned) {
            PublicChat::broadcast('pins.changed');
        }

        return back()->with('success', 'Pesan disembunyikan dari Chat Publik.');
    }

    public function restore(PublicChatMessage $message): RedirectResponse
    {
        $message->forceFill(['hidden_at' => null, 'hidden_by' => null])->save();
        if ($message->isPinned()) {
            PublicChat::broadcast('pins.changed');
        }

        return back()->with('success', 'Pesan ditampilkan kembali.');
    }

    public function unpin(PublicChatMessage $message): RedirectResponse
    {
        $message->forceFill(['pinned_at' => null, 'pinned_until' => null])->save();
        PublicChat::broadcast('pins.changed');

        return back()->with('success', 'Sematan pengumuman dicabut.');
    }

    public function mute(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'days' => ['required', 'integer', Rule::in([0, 1, 7, 30])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->is($request->user()) || $user->isAdmin() || $user->isSuperAdmin()) {
            return back()->with('error', 'Admin tidak dapat dibungkam.');
        }

        $member = PublicChatMember::for($user);
        $member->forceFill([
            'muted_at' => now(),
            'muted_until' => $data['days'] ? now()->addDays((int) $data['days']) : null,
            'mute_reason' => $data['reason'] ?? null,
            'muted_by' => $request->user()->id,
        ])->save();

        return back()->with('success', "{$user->name} tidak dapat mengirim pesan di Chat Publik.");
    }

    public function unmute(User $user): RedirectResponse
    {
        PublicChatMember::where('user_id', $user->id)->update([
            'muted_at' => null,
            'muted_until' => null,
            'mute_reason' => null,
            'muted_by' => null,
        ]);

        return back()->with('success', "{$user->name} dapat mengirim pesan lagi.");
    }
}
