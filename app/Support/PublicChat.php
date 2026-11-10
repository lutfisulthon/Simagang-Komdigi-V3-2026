<?php

namespace App\Support;

use App\Events\PublicChatEvent;
use App\Models\PublicChatMessage;
use App\Models\User;

/** Aturan bersama Chat Publik: peran tampilan, akses, dan pembersihan teks. */
class PublicChat
{
    /** Peran tampilan: admin, mentor, intern, institusi, industri. */
    public static function roleOf(?User $user): string
    {
        return match (true) {
            ! $user => 'intern',
            $user->isAdmin() || $user->isSuperAdmin() => 'admin',
            $user->isMentor() => 'mentor',
            $user->isInstitusi() => 'institusi',
            $user->isIndustri() => 'industri',
            default => 'intern',
        };
    }

    /**
     * User nonaktif tidak boleh membaca maupun menulis: peserta magang / mentor / institusi / industri
     * yang profilnya ditandai is_active = false. Admin selalu boleh.
     */
    public static function isBlocked(User $user): bool
    {
        $profile = match (self::roleOf($user)) {
            'admin' => null,
            'mentor' => $user->mentor,
            'institusi' => $user->institusi,
            'industri' => $user->industri,
            default => $user->intern,
        };

        return $profile !== null && $profile->is_active !== null && ! (bool) $profile->is_active;
    }

    /**
     * Bersihkan teks: buang karakter kontrol dan penimpa arah teks, satukan baris baru,
     * ringkas baris kosong beruntun, lalu trim. Teks tetap polos (HTML tidak diproses).
     */
    public static function clean(?string $text): string
    {
        $text = (string) $text;
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\x{0000}-\x{0008}\x{000B}\x{000C}\x{000E}-\x{001F}\x{007F}-\x{009F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $text) ?? '';
        $text = preg_replace("/[ \t]+\n/u", "\n", $text) ?? '';
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? '';

        return trim($text);
    }

    public static function containsLink(string $text): bool
    {
        return (bool) preg_match(
            '~(https?://|www\.|\b[a-z0-9-]+(\.[a-z0-9-]+)*\.(com|net|org|id|co|io|me|ly|xyz|info|biz|site|online|link|app|dev|top|click|ru|cn|tk)(/|\b))~iu',
            $text,
        );
    }

    /**
     * Kirim event realtime. Kegagalan (mis. server Reverb mati) hanya dicatat di log:
     * pesan tetap tersimpan dan klien tetap menerimanya lewat polling.
     */
    public static function broadcast(string $type, array $payload = []): void
    {
        // Tanpa return: PendingBroadcast dikirim saat objeknya dibuang, jadi harus terjadi di dalam rescue.
        rescue(function () use ($type, $payload) {
            broadcast(new PublicChatEvent($type, $payload));
        });
    }

    /** Seluruh pengumuman aktif, terbaru disematkan lebih dulu. */
    public static function pins(): array
    {
        return PublicChatMessage::activePins()
            ->with('user.roles')
            ->orderByDesc('pinned_at')
            ->orderByDesc('id')
            ->get()
            ->map->toChatArray()
            ->values()
            ->all();
    }
}
