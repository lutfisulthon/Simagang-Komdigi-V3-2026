<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Pengaturan notifikasi per user: in-app untuk semua jenis, email untuk jenis di EMAIL_TYPES (lainnya `email` null). */
class NotificationPreferences
{
    /**
     * Katalog jenis notifikasi sesuai tabel event PRD. `roles` menentukan siapa yang melihat
     * dan bisa mengatur jenis itu.
     *
     * @var array<int, array{type: string, group: string, label: string, description: string, roles: array<int, string>}>
     */
    public const CATALOG = [
        ['type' => 'task_assigned', 'group' => 'Tugas', 'label' => 'Tugas baru', 'description' => 'Saat mentor menugaskan tugas kepada Anda.', 'roles' => ['intern']],
        ['type' => 'task_in_review', 'group' => 'Tugas', 'label' => 'Tugas menunggu review', 'description' => 'Saat intern mengirim tugas untuk direview.', 'roles' => ['mentor', 'admin']],
        ['type' => 'task_approved', 'group' => 'Tugas', 'label' => 'Tugas dinyatakan Selesai', 'description' => 'Saat mentor menyetujui tugas Anda.', 'roles' => ['intern']],
        ['type' => 'task_revision', 'group' => 'Tugas', 'label' => 'Tugas perlu revisi', 'description' => 'Saat mentor meminta revisi tugas Anda.', 'roles' => ['intern']],
        ['type' => 'task_due_soon', 'group' => 'Tugas', 'label' => 'Deadline H-1', 'description' => 'Pengingat 24 jam sebelum deadline tugas.', 'roles' => ['intern']],
        ['type' => 'task_overdue', 'group' => 'Tugas', 'label' => 'Tugas terlambat', 'description' => 'Saat tugas melewati deadline.', 'roles' => ['intern', 'mentor']],
        ['type' => 'task_commented', 'group' => 'Tugas', 'label' => 'Komentar di tugas', 'description' => 'Saat ada komentar baru di tugas Anda.', 'roles' => ['intern', 'mentor', 'admin']],
        ['type' => 'deadline_extended', 'group' => 'Tugas', 'label' => 'Deadline diperpanjang', 'description' => 'Saat mentor memperpanjang deadline tugas Anda.', 'roles' => ['intern']],
        ['type' => 'sprint_leader', 'group' => 'Sprint', 'label' => 'Ditunjuk jadi ketua sprint', 'description' => 'Saat Anda ditunjuk menjadi ketua tim sebuah sprint.', 'roles' => ['intern']],
        ['type' => 'sprint_to_divide', 'group' => 'Sprint', 'label' => 'Tugas menunggu dibagi', 'description' => 'Pengingat bagi ketua sprint bila masih ada tugas belum dibagi.', 'roles' => ['intern']],
        ['type' => 'group_joined', 'group' => 'Kelompok', 'label' => 'Dimasukkan ke kelompok', 'description' => 'Saat mentor memasukkan Anda ke sebuah kelompok.', 'roles' => ['intern']],
        ['type' => 'mentioned', 'group' => 'Percakapan', 'label' => 'Disebut (mention)', 'description' => 'Saat seseorang menyebut Anda di chat atau komentar.', 'roles' => ['intern', 'mentor', 'admin']],
        ['type' => 'chat_unread', 'group' => 'Percakapan', 'label' => 'Chat belum dibaca', 'description' => 'Email ringkasan bila ada 5 pesan atau lebih belum dibaca, atau pesan belum dibaca selama 5 jam.', 'roles' => ['intern', 'mentor', 'admin']],
        ['type' => 'announcement', 'group' => 'Pengumuman', 'label' => 'Pengumuman baru', 'description' => 'Saat ada pengumuman di #pengumuman.', 'roles' => ['intern', 'mentor', 'admin']],
        ['type' => 'intern_joined', 'group' => 'Anak bimbingan', 'label' => 'Intern baru bergabung', 'description' => 'Saat anak bimbingan baru masuk tim.', 'roles' => ['mentor', 'admin']],
        ['type' => 'intern_without_mentor', 'group' => 'Anak bimbingan', 'label' => 'Intern tanpa mentor', 'description' => 'Saat ada intern aktif yang belum punya mentor.', 'roles' => ['admin']],
    ];

    /** Jenis yang juga dikirim lewat email (selain in-app); lainnya hanya in-app. */
    public const EMAIL_TYPES = [
        'task_assigned', 'task_in_review', 'task_approved', 'task_revision', 'task_due_soon', 'task_overdue',
        'task_commented', 'deadline_extended', 'sprint_leader', 'sprint_to_divide', 'group_joined',
        'mentioned', 'chat_unread',
    ];

    public function roleOf(User $user): string
    {
        return match (true) {
            $user->isSuperAdmin() || $user->isAdminFull() => 'admin',
            $user->isMentor() => 'mentor',
            $user->isIntern() => 'intern',
            default => 'admin',
        };
    }

    /** @return array<int, array> katalog yang berlaku untuk role user ini */
    private function catalogFor(User $user): array
    {
        $role = $this->roleOf($user);

        return array_values(array_filter(self::CATALOG, fn ($c) => in_array($role, $c['roles'], true)));
    }

    /** Apakah notifikasi in-app jenis ini boleh dikirim ke user? Tanpa pilihan tersimpan = ya. */
    public function allows(object $notifiable, string $type): bool
    {
        if (! $notifiable instanceof User) {
            return true;
        }

        return ! DB::table('notification_preferences')
            ->where('user_id', $notifiable->id)
            ->where('type', $type)
            ->where('in_app', false)
            ->exists();
    }

    /** Apakah email jenis ini boleh dikirim ke user? Hanya jenis EMAIL_TYPES; tanpa pilihan tersimpan = ya. */
    public function allowsEmail(object $notifiable, string $type): bool
    {
        if (! $notifiable instanceof User || ! in_array($type, self::EMAIL_TYPES, true)) {
            return false;
        }

        return ! DB::table('notification_preferences')
            ->where('user_id', $notifiable->id)
            ->where('type', $type)
            ->where('email', false)
            ->exists();
    }

    /** @return array<int, array{type: string, group: string, label: string, description: string, in_app: bool, email: ?bool}> */
    public function typesFor(User $user): array
    {
        $stored = DB::table('notification_preferences')->where('user_id', $user->id)->pluck('in_app', 'type');
        $storedEmail = DB::table('notification_preferences')->where('user_id', $user->id)->pluck('email', 'type');

        return array_map(fn ($c) => [
            'type' => $c['type'],
            'group' => $c['group'],
            'label' => $c['label'],
            'description' => $c['description'],
            'in_app' => (bool) ($stored[$c['type']] ?? true),
            'email' => in_array($c['type'], self::EMAIL_TYPES, true) ? (bool) ($storedEmail[$c['type']] ?? true) : null,
        ], $this->catalogFor($user));
    }

    /**
     * Simpan pilihan. Jenis yang bukan milik role ini ditolak, dan `email: true` ditolak
     * untuk jenis yang tidak dikirim lewat email.
     *
     * @param  array<string, array{in_app?: bool, email?: bool|null}>  $types
     */
    public function save(User $user, array $types): void
    {
        $allowed = array_column($this->catalogFor($user), 'type');
        $errors = [];

        foreach ($types as $type => $choice) {
            if (! in_array($type, $allowed, true)) {
                $errors["types.{$type}"] = 'Jenis notifikasi tidak dikenal.';
            } elseif (! empty($choice['email']) && ! in_array($type, self::EMAIL_TYPES, true)) {
                $errors["types.{$type}.email"] = 'Jenis ini tidak dikirim lewat email.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($types as $type => $choice) {
            $values = [];

            if (array_key_exists('in_app', $choice)) {
                $values['in_app'] = (bool) $choice['in_app'];
            }
            if (array_key_exists('email', $choice) && $choice['email'] !== null && in_array($type, self::EMAIL_TYPES, true)) {
                $values['email'] = (bool) $choice['email'];
            }

            if ($values) {
                DB::table('notification_preferences')->updateOrInsert(
                    ['user_id' => $user->id, 'type' => $type],
                    $values + ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    /** @return array<int, array{id: int, name: string, workspace: ?array{id: int, name: string, url: string}}> */
    public function mutedChannels(User $user): array
    {
        return DB::table('channel_members')
            ->join('channels', 'channels.id', '=', 'channel_members.channel_id')
            ->leftJoin('workspaces', 'workspaces.id', '=', 'channels.workspace_id')
            ->where('channel_members.user_id', $user->id)
            ->where('channel_members.is_muted', true)
            ->orderBy('channels.name')
            ->get(['channels.id', 'channels.name', 'workspaces.id as workspace_id', 'workspaces.name as workspace_name'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'workspace' => $c->workspace_id
                    ? ['id' => $c->workspace_id, 'name' => $c->workspace_name, 'url' => route('workspace.show', $c->workspace_id)]
                    : null,
            ])
            ->all();
    }
}
