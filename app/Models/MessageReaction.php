<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageReaction extends Model
{
    /** Emoji yang boleh dipakai (reaksi cepat), dalam urutan tampil. */
    public const ALLOWED = ['👍', '❤️', '😂', '🎉', '🙏', '👀', '✅', '🔥'];

    protected $fillable = ['message_id', 'user_id', 'emoji'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
