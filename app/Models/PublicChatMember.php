<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Status Chat Publik per user: penanda terakhir dibaca, waktu aktif, dan bungkam oleh admin. */
class PublicChatMember extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = ['user_id', 'last_read_id', 'last_active_at'];

    protected $casts = [
        'last_read_id' => 'integer',
        'last_active_at' => 'datetime',
        'muted_at' => 'datetime',
        'muted_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mutedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muted_by');
    }

    public function isMuted(): bool
    {
        return $this->muted_at !== null && ($this->muted_until === null || $this->muted_until->isFuture());
    }

    /**
     * Ambil (atau buat) status milik user. User baru mulai dari pesan terbaru,
     * jadi riwayat lama tidak dihitung sebagai belum dibaca.
     */
    public static function for(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            ['last_read_id' => (int) PublicChatMessage::max('id')],
        );
    }
}
