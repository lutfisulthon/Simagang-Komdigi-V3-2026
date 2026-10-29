<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name', 60);
            $table->text('goal')->nullable();
            $table->string('status', 20)->default('planned'); // planned | active | completed
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('leader_intern_id')->nullable()->constrained('interns')->nullOnDelete();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('divide_reminded_at')->nullable(); // pengingat "bagi tugas" H+1 ke ketua
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
        });

        // MySQL tidak punya indeks unik parsial: kolom virtual berisi workspace_id hanya saat
        // sprint aktif (NULL selain itu), jadi unik menjamin maksimal satu sprint aktif per workspace.
        DB::statement("ALTER TABLE sprints ADD active_workspace_id BIGINT UNSIGNED GENERATED ALWAYS AS (IF(status = 'active', workspace_id, NULL)) VIRTUAL");
        DB::statement('CREATE UNIQUE INDEX sprints_one_active_per_workspace ON sprints (active_workspace_id)');

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('sprint_id')->nullable()->after('workspace_id')->constrained('sprints')->nullOnDelete();
            $table->unsignedInteger('rank')->default(0)->after('position'); // urutan di sprint/backlog
            $table->index(['workspace_id', 'sprint_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['sprint_id']);
            $table->dropIndex(['workspace_id', 'sprint_id', 'rank']);
            $table->dropColumn(['sprint_id', 'rank']);
        });

        Schema::dropIfExists('sprints');
    }
};
