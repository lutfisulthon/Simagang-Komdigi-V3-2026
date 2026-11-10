<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Event realtime Chat Publik di channel privat "public-chat".
 *  - message.created  { message } (pesan lengkap; bila terlalu besar hanya { id } dan klien memuat lewat polling)
 *  - pins.changed     {}          (sinyal: klien memuat ulang daftar pengumuman)
 *  - message.hidden   { id }      (pesan disembunyikan moderator: klien menghapusnya dari layar)
 * Dikirim langsung (tanpa queue) lewat PublicChat::broadcast, yang menelan galat bila server Reverb mati.
 */
class PublicChatEvent implements ShouldBroadcastNow
{
    /** Batas aman ukuran payload websocket (byte). */
    private const MAX_PAYLOAD = 8000;

    public function __construct(
        public string $type,
        public array $payload = [],
    ) {
        if ($type === 'message.created' && strlen((string) json_encode($payload)) > self::MAX_PAYLOAD) {
            $this->payload = ['id' => $payload['message']['id'] ?? null];
        }
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('public-chat');
    }

    public function broadcastAs(): string
    {
        return $this->type;
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
