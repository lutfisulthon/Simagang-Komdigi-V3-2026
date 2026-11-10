<?php

namespace App\Console\Commands;

use App\Models\PublicChatMessage;
use Illuminate\Console\Command;

class PublicChatPruneCommand extends Command
{
    protected $signature = 'public-chat:prune {--days= : Umur pesan (hari); bawaan config public_chat.retention_days}';

    protected $description = 'Hapus pesan Chat Publik yang lebih tua dari batas retensi (pengumuman yang masih aktif tidak ikut dihapus)';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('public_chat.retention_days'));
        if ($days < 1) {
            $this->error('Nilai --days minimal 1.');

            return self::FAILURE;
        }

        $deleted = PublicChatMessage::query()
            ->where('created_at', '<', now()->subDays($days))
            ->where(fn ($q) => $q->whereNull('pinned_until')->orWhere('pinned_until', '<=', now()))
            ->delete();

        $this->info("{$deleted} pesan Chat Publik lebih dari {$days} hari dihapus.");

        return self::SUCCESS;
    }
}
