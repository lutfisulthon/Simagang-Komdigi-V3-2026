<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pilihan email per jenis notifikasi; jenis tanpa baris dianggap aktif (seperti in_app).
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->boolean('email')->default(true)->after('in_app');
        });
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
