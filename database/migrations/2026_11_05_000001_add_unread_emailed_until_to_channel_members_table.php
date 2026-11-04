<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_members', function (Blueprint $table) {
            // Pesan sampai id ini sudah pernah diingatkan lewat email; pengingat berikutnya hanya menghitung pesan di atasnya.
            $table->unsignedBigInteger('unread_emailed_until_id')->nullable()->after('last_read_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('channel_members', function (Blueprint $table) {
            $table->dropColumn('unread_emailed_until_id');
        });
    }
};
