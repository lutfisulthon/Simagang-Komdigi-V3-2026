<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // diisi scheduler: penanda Terlambat dan pengingat H-1, masing-masing sekali per tugas
            $table->dateTime('overdue_at')->nullable()->after('completed_at');
            $table->dateTime('reminded_at')->nullable()->after('overdue_at');

            $table->index(['due_at', 'overdue_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['due_at', 'overdue_at']);
            $table->dropColumn(['overdue_at', 'reminded_at']);
        });
    }
};
