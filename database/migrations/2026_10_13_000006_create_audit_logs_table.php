<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp')->useCurrent();
            $table->string('username', 50)->default('Guest');
            $table->string('action', 50); // LOGIN, ADD_KEGIATAN, SUBMIT_KEHADIRAN, APPROVE_KEGIATAN, etc.
            $table->text('detail');
            $table->string('ip_address', 45)->nullable();

            $table->index(['timestamp', 'action'], 'idx_audit_logs_time_action');
            $table->index('username', 'idx_audit_logs_username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
