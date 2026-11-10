<?php

namespace App\Models;

use App\Support\PublicChat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicChatMessage extends Model
{
    protected $fillable = ['user_id', 'text', 'pinned_at', 'pinned_until'];

    protected $casts = [
        'pinned_at' => 'datetime',
        'pinned_until' => 'datetime',
        'hidden_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }

    public function scopeActivePins(Builder $query): Builder
    {
        return $query->visible()->whereNotNull('pinned_at')->where('pinned_until', '>', now());
    }

    public function isPinned(): bool
    {
        return $this->pinned_at !== null && $this->pinned_until?->isFuture();
    }

    /** Bentuk JSON sesuai kontrak frontend (docs/public-chat-backend.md). */
    public function toChatArray(): array
    {
        $data = [
            'id' => (string) $this->id,
            'uid' => (string) $this->user_id,
            'name' => (string) $this->user?->name,
            'role' => PublicChat::roleOf($this->user),
            'text' => $this->text,
            'ts' => $this->created_at->getTimestampMs(),
        ];

        if ($this->isPinned()) {
            $data['pinned_at'] = $this->pinned_at->getTimestampMs();
            $data['pinned_until'] = $this->pinned_until->getTimestampMs();
        }

        return $data;
    }
}
