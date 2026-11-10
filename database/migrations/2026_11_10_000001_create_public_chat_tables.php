<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Chat Publik: pesan (termasuk sematan pengumuman dan status disembunyikan moderator)
| dan status per user (penanda terakhir dibaca, waktu aktif untuk hitungan online, bungkam).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->timestamp('pinned_at')->nullable();
            $table->timestamp('pinned_until')->nullable()->index();
            $table->timestamp('hidden_at')->nullable()->index();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('public_chat_members', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('last_read_id')->default(0);
            $table->timestamp('last_active_at')->nullable()->index();
            // muted_at terisi = dibungkam; muted_until kosong = tanpa batas waktu.
            $table->timestamp('muted_at')->nullable();
            $table->timestamp('muted_until')->nullable();
            $table->string('mute_reason', 255)->nullable();
            $table->foreignId('muted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_chat_members');
        Schema::dropIfExists('public_chat_messages');
    }
};
