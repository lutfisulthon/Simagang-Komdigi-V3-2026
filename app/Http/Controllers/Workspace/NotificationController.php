<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Notifications\GroupJoinedNotification;
use App\Notifications\InternJoinedTeamNotification;
use App\Notifications\InternWithoutMentorNotification;
use App\Notifications\MessageMentionNotification;
use App\Notifications\DeadlineExtendedNotification;
use App\Notifications\SprintLeaderNotification;
use App\Notifications\SprintToDivideNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentedNotification;
use App\Notifications\TaskCommentMentionNotification;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TaskOverdueNotification;
use App\Notifications\TaskReviewedNotification;
use App\Notifications\TaskReviewRequestedNotification;
use App\Services\ChannelUnreadService;
use App\Services\NotificationPreferences;
use App\Services\WorkspacePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Lonceng dan halaman notifikasi in-app, plus pengaturan notifikasi per user. */
class NotificationController extends Controller
{
    private const BELL_LIMIT = 10;
    private const PAGE_SIZE = 20;
    private const MAX_PER_PAGE = 50;

    /** Kelas notifikasi per kategori filter halaman notifikasi. */
    private const CATEGORIES = [
        'task' => [
            TaskAssignedNotification::class,
            TaskReviewRequestedNotification::class,
            TaskReviewedNotification::class,
            TaskCommentedNotification::class,
            TaskDueSoonNotification::class,
            TaskOverdueNotification::class,
            DeadlineExtendedNotification::class,
        ],
        'sprint' => [SprintLeaderNotification::class, SprintToDivideNotification::class],
        'group' => [GroupJoinedNotification::class],
        'mention' => [MessageMentionNotification::class, TaskCommentMentionNotification::class],
        'announcement' => [AnnouncementNotification::class],
        'intern' => [InternJoinedTeamNotification::class, InternWithoutMentorNotification::class],
    ];

    /** Jenis untuk baris lama yang belum menyimpan `kind` di datanya. */
    private const KIND_BY_CLASS = [
        InternJoinedTeamNotification::class => 'intern_joined',
        InternWithoutMentorNotification::class => 'intern_without_mentor',
        MessageMentionNotification::class => 'mentioned',
        TaskCommentMentionNotification::class => 'mentioned',
        TaskAssignedNotification::class => 'task_assigned',
        TaskReviewRequestedNotification::class => 'task_in_review',
        TaskCommentedNotification::class => 'task_commented',
        TaskDueSoonNotification::class => 'task_due_soon',
        TaskOverdueNotification::class => 'task_overdue',
        AnnouncementNotification::class => 'announcement',
        SprintLeaderNotification::class => 'sprint_leader',
        SprintToDivideNotification::class => 'sprint_to_divide',
        DeadlineExtendedNotification::class => 'deadline_extended',
        GroupJoinedNotification::class => 'group_joined',
    ];

    private ?string $fallbackWorkspaceUrl = null;

    public function __construct(
        private ChannelUnreadService $unread,
        private WorkspacePresenter $presenter,
        private NotificationPreferences $preferences,
    ) {
    }

    /**
     * Satu URL, dua wajah: HTML (halaman semua notifikasi) atau JSON (lonceng dan "muat lebih banyak").
     * JSON: ?limit= atau ?per_page= (maks. 50), ?page=, ?filter=unread (atau ?unread=1), ?category=.
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->expectsJson()) {
            return $this->page($request);
        }

        $p = $request->validate([
            'limit' => ['nullable', 'integer', 'between:1,' . self::MAX_PER_PAGE],
            'per_page' => ['nullable', 'integer', 'between:1,' . self::MAX_PER_PAGE],
            'page' => ['nullable', 'integer', 'min:1'],
            'unread' => ['nullable', 'boolean'],
            'filter' => ['nullable', Rule::in(['all', 'unread'])],
            'category' => ['nullable', Rule::in(array_keys(self::CATEGORIES))],
        ]);

        $user = $request->user();
        $paginator = $this->query($user, $p['filter'] ?? (! empty($p['unread']) ? 'unread' : 'all'), $p['category'] ?? null)
            ->paginate($p['per_page'] ?? $p['limit'] ?? self::BELL_LIMIT);

        $unreadCount = $user->unreadNotifications()->count();

        $body = [
            'unread_count' => $unreadCount,
            'data' => $this->present($paginator->getCollection()),
            'meta' => [
                'unread_count' => $unreadCount,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ];

        // Lonceng sekaligus membawa angka belum dibaca per channel (satu polling ringkas).
        if (! $request->has('page')) {
            $body['unread_messages'] = $this->unreadMessages($user);
        }

        return response()->json($body);
    }

    /** Halaman HTML: halaman pertama sudah dibawa supaya tidak ada request tambahan saat dibuka. */
    private function page(Request $request): View
    {
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';
        $category = array_key_exists((string) $request->query('category'), self::CATEGORIES) ? $request->query('category') : null;

        $paginator = $this->query($request->user(), $filter, $category)->paginate(self::PAGE_SIZE, ['*'], 'page', 1);

        return view('notifications.index', [
            'notifications' => [
                'data' => $this->present($paginator->getCollection()),
                'meta' => ['current_page' => 1, 'has_more' => $paginator->hasMorePages()],
            ],
            'filter' => $filter,
            'category' => $category,
        ]);
    }

    private function query(User $user, string $filter, ?string $category)
    {
        return $user->notifications()
            ->when($filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($category, fn ($q, $c) => $q->whereIn('type', self::CATEGORIES[$c]));
    }

    /** Klik satu notifikasi menandainya dibaca. Balasan membawa jumlah terbaru (kompatibel dengan 204). */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $user->notifications()->whereKey($id)->firstOrFail()->markAsRead();

        return response()->json(['unread_count' => $user->unreadNotifications()->count()]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0]);
    }

    public function settings(): View
    {
        return view('notifications.settings', ['preferences' => null]);
    }

    public function preferences(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->preferencePayload($request->user())]);
    }

    public function savePreferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'types' => ['required', 'array'],
            'types.*' => ['array'],
            'types.*.in_app' => ['nullable', 'boolean'],
            'types.*.email' => ['nullable', 'boolean'],
        ]);

        $this->preferences->save($request->user(), $data['types']);

        return response()->json(['data' => $this->preferencePayload($request->user())]);
    }

    private function preferencePayload(User $user): array
    {
        return [
            'types' => $this->preferences->typesFor($user),
            'muted_channels' => $this->preferences->mutedChannels($user),
        ];
    }

    /**
     * Belum dibaca per channel yang user ikuti. Channel dibisukan tetap muncul di `by_channel`
     * (badge abu-abu) tetapi tidak dihitung ke `total`.
     */
    private function unreadMessages(User $user): array
    {
        $memberships = DB::table('channel_members')
            ->join('channels', 'channels.id', '=', 'channel_members.channel_id')
            ->where('channel_members.user_id', $user->id)
            ->where('channels.is_archived', false)
            ->pluck('channel_members.is_muted', 'channels.id');

        $counts = $this->unread->counts($user, $memberships->keys()->all());

        return [
            'total' => (int) collect($counts)->filter(fn ($n, $id) => ! $memberships[$id])->sum(),
            'by_channel' => (object) collect($counts)->filter(fn ($n) => $n > 0)->mapWithKeys(fn ($n, $id) => [(string) $id => (int) $n])->all(),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, DatabaseNotification>  $items
     * @return array<int, array>
     */
    private function present($items): array
    {
        $actorIds = $items->map(fn (DatabaseNotification $n) => $n->data['actor_id'] ?? null)->filter()->unique();
        $actors = User::with(['mentor', 'intern'])->whereIn('id', $actorIds)->get()->keyBy('id');

        return $items->map(fn (DatabaseNotification $n) => $this->presentOne($n, $actors))->values()->all();
    }

    private function presentOne(DatabaseNotification $n, $actors): array
    {
        $data = $n->data;
        $kind = $this->kindOf($n);
        [$title, $body] = $this->text($kind, $data);
        $actor = isset($data['actor_id']) ? ($actors[$data['actor_id']] ?? null) : null;
        $actorName = $actor?->name ?? ($data['actor_name'] ?? null);

        return [
            'id' => $n->id,
            'type' => $kind,
            'title' => $title ?: ($data['message'] ?? ''),
            'body' => $body,
            'url' => $this->url($n, $kind, $data),
            'actor' => $actorName
                ? ['name' => $actorName, 'avatar_url' => $actor ? $this->presenter->user($actor)['avatar_url'] : null]
                : null,
            'read_at' => $this->presenter->iso($n->read_at),
            'created_at' => $this->presenter->iso($n->created_at),
            // Kunci lama dipertahankan untuk klien yang belum pindah ke title/body.
            'message' => $data['message'] ?? '',
            'data' => collect($data)->except('message')->all(),
        ];
    }

    private function kindOf(DatabaseNotification $n): string
    {
        if (! empty($n->data['kind'])) {
            return $n->data['kind'];
        }

        if ($n->type === TaskReviewedNotification::class) {
            return ($n->data['status'] ?? null) === 'done' ? 'task_approved' : 'task_revision';
        }

        return self::KIND_BY_CLASS[$n->type] ?? Str::snake(Str::beforeLast(class_basename($n->type), 'Notification'));
    }

    /** @return array{0: string, 1: ?string} [judul, isi] */
    private function text(string $kind, array $d): array
    {
        $code = $d['task_code'] ?? null;
        $title = $d['task_title'] ?? null;
        $task = $code && $title ? "{$code} {$title}" : null;
        $actor = $d['actor_name'] ?? null;

        return match ($kind) {
            'task_assigned' => $task ? ["Tugas baru: {$task}", trim(($actor ? "Dari {$actor}" : '') . (isset($d['due_at']) ? ' · deadline ' . $this->shortDate($d['due_at']) : ''), ' ·') ?: null] : ['', null],
            'task_in_review' => $task ? ["{$code} menunggu review", ($actor ? "{$actor} mengirim " : '') . "\"{$title}\""] : ['', null],
            'task_approved' => $task ? ["{$code} dinyatakan Selesai", isset($d['score']) ? "Nilai {$d['score']} · {$title}" : $title] : ['', null],
            'task_revision' => $task ? ["{$code} perlu direvisi", Str::limit((string) ($d['feedback'] ?? $title), 100)] : ['', null],
            'task_due_soon' => $task ? ["Deadline {$code} kurang dari 24 jam", $title] : ['', null],
            'task_overdue' => $task ? ["{$code} terlambat", $title] : ['', null],
            'task_commented' => $task ? ["Komentar baru di {$code}", ($actor ? "{$actor}: " : '') . ($d['excerpt'] ?? $title)] : ['', null],
            'mentioned' => $actor ? [
                "{$actor} menyebut kamu",
                isset($d['channel_name']) ? "di #{$d['channel_name']}" . (isset($d['excerpt']) ? ": {$d['excerpt']}" : '') : ($code ? "di komentar {$code}" : null),
            ] : ['', null],
            'announcement' => ['Pengumuman baru', ($actor ? "{$actor}: " : '') . ($d['excerpt'] ?? '')],
            'intern_joined' => isset($d['intern_name']) ? ["Intern baru: {$d['intern_name']}", isset($d['team_name']) ? "Masuk tim {$d['team_name']}" : null] : ['', null],
            'intern_without_mentor' => isset($d['intern_name']) ? ['Intern belum punya mentor', $d['intern_name']] : ['', null],
            'deadline_extended' => $task ? ["Deadline {$code} diperpanjang", trim((isset($d['due_at']) ? 'Sampai ' . $this->shortDate($d['due_at']) : '') . (! empty($d['reason']) ? ' · ' . Str::limit($d['reason'], 80) : ''), ' ·') ?: $title] : ['', null],
            'sprint_leader' => isset($d['sprint_name']) ? [
                "Anda ketua {$d['sprint_name']}",
                trim(($d['workspace_name'] ?? '') . (isset($d['end_date']) ? ' · deadline ' . $this->shortDate($d['end_date']) : '') . (isset($d['task_count']) ? " · {$d['task_count']} tugas" : ''), ' ·') ?: null,
            ] : ['', null],
            'group_joined' => isset($d['workspace_name']) ? ["Dimasukkan ke {$d['workspace_name']}", $actor ? "Oleh {$actor}" : null] : ['', null],
            'sprint_to_divide' => isset($d['task_count']) ? ["{$d['task_count']} tugas menunggu Anda bagi", implode(', ', $d['task_titles'] ?? []) ?: null] : ['', null],
            default => ['', null],
        };
    }

    private function shortDate(string $iso): string
    {
        return Carbon::parse($iso)->locale('id')->translatedFormat('j M');
    }

    /** Tautan paling spesifik: tugas membuka slide-over, mention dan pengumuman membuka channel. */
    private function url(DatabaseNotification $n, string $kind, array $d): string
    {
        if (isset($d['task_id'])) {
            if (isset($d['workspace_id'], $d['task_code'])) {
                return route('workspace.show', $d['workspace_id']) . '?task=' . rawurlencode($d['task_code']);
            }

            return route('workspace.tasks.show', $d['task_id']);
        }

        if (($d['kind'] ?? null) === 'group_joined' && isset($d['workspace_id'])) {
            return route('workspace.show', $d['workspace_id']);
        }

        if (isset($d['sprint_id'], $d['workspace_id'])) {
            return route('workspace.show', $d['workspace_id']) . '?tab=board';
        }

        if (isset($d['channel_id'])) {
            $workspaceUrl = isset($d['workspace_id'])
                ? route('workspace.show', $d['workspace_id'])
                : $this->fallbackWorkspaceUrl($n->notifiable_id);

            return $workspaceUrl . '?tab=channel&channel=' . $d['channel_id'] . (isset($d['message_id']) ? '&message=' . $d['message_id'] : '');
        }

        return route('workspace.index');
    }

    /** Channel global (#pengumuman) tidak punya workspace: pakai workspace milik penerima. */
    private function fallbackWorkspaceUrl(int $userId): string
    {
        if ($this->fallbackWorkspaceUrl !== null) {
            return $this->fallbackWorkspaceUrl;
        }

        $id = DB::table('workspace_members')
            ->join('workspaces', 'workspaces.id', '=', 'workspace_members.workspace_id')
            ->where('workspace_members.user_id', $userId)
            ->whereNull('workspace_members.left_at')
            ->orderByRaw("workspaces.type = 'intern' desc")
            ->orderBy('workspaces.id')
            ->value('workspaces.id');

        return $this->fallbackWorkspaceUrl = $id ? route('workspace.show', $id) : route('workspace.index');
    }
}
