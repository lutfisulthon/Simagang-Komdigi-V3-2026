<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template membawa prioritas, label, dan tenggat bawaan (hari penugasan + N hari).
        Schema::table('task_templates', function (Blueprint $table) {
            $table->string('priority', 10)->default('medium')->after('description');
            $table->json('label_ids')->nullable()->after('priority');
            $table->unsignedSmallInteger('due_in_days')->default(7)->after('label_ids');
        });

        // Pilihan intern: sertakan rekap tugas di laporan akhir.
        Schema::table('final_reports', function (Blueprint $table) {
            $table->boolean('include_task_recap')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('final_reports', function (Blueprint $table) {
            $table->dropColumn('include_task_recap');
        });

        Schema::table('task_templates', function (Blueprint $table) {
            $table->dropColumn(['priority', 'label_ids', 'due_in_days']);
        });
    }
};
