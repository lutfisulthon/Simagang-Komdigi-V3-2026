<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lampiran diunggah lebih dulu (dengan progress bar), baru ditautkan lewat attachment_ids.
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('attachable_type')->nullable()->change();
            $table->unsignedBigInteger('attachable_id')->nullable()->change();
            $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained('workspaces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workspace_id');
        });
    }
};
