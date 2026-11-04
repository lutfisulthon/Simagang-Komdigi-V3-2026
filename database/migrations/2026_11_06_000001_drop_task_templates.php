<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Fitur template tugas dihapus: lepas relasi dari tasks lalu buang tabelnya. */
    public function up(): void
    {
        if (Schema::hasColumn('tasks', 'template_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('template_id');
            });
        }

        Schema::dropIfExists('task_templates');
    }

    public function down(): void
    {
        // Tidak dapat dipulihkan: data template ikut terhapus.
    }
};
