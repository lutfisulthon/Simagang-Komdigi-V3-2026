<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daftar tautan hasil kerja [{url, label}]; kolom result_link lama tetap dibaca sebagai tautan pertama.
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('result_links')->nullable()->after('result_link');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('result_links');
        });
    }
};
