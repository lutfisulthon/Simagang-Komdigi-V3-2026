<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Epic: pengelompokan tugas lintas sprint (per workspace), seperti epic di Jira.
        Schema::create('epics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('color', 7)->default('#6366f1');
            $table->timestamps();

            $table->index('workspace_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedSmallInteger('story_points')->nullable()->after('priority');
            $table->foreignId('epic_id')->nullable()->after('sprint_id')->constrained('epics')->nullOnDelete();
        });

        // Hitungan saat sprint ditutup (tugas dipindah setelahnya, jadi tidak bisa dihitung ulang).
        Schema::table('sprints', function (Blueprint $table) {
            $table->unsignedInteger('closed_total_tasks')->nullable();
            $table->unsignedInteger('closed_done_tasks')->nullable();
            $table->unsignedInteger('closed_total_points')->nullable();
            $table->unsignedInteger('closed_done_points')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            $table->dropColumn(['closed_total_tasks', 'closed_done_tasks', 'closed_total_points', 'closed_done_points']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('epic_id');
            $table->dropColumn('story_points');
        });

        Schema::dropIfExists('epics');
    }
};
