<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kelompok buatan mentor (workspaces.type = 'group'); kolom kosong untuk workspace tim dan Space intern.
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('color', 20)->nullable()->after('name');
            $table->text('description')->nullable()->after('color');
            $table->foreignId('mentor_user_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('finished_at')->nullable()->after('is_archived');
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropForeign(['mentor_user_id']);
            $table->dropColumn(['color', 'description', 'mentor_user_id', 'finished_at']);
        });
    }
};
