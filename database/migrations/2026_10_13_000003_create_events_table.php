<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->foreignUuid('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('event_date');
            $table->unsignedSmallInteger('start_minutes'); // 0 - 1439 (misal: 09:00 = 540)
            $table->unsignedSmallInteger('end_minutes');   // 0 - 1439 (misal: 12:00 = 720)
            $table->string('work_unit', 100);
            $table->string('status', 20)->default('PENDING'); // PENDING, APPROVED, REJECTED, CLOSED
            $table->text('rejection_note')->nullable();
            $table->uuid('qr_token')->unique();
            $table->json('config_fields')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Collision detection composite index
            $table->index(['room_id', 'event_date', 'start_minutes', 'end_minutes'], 'idx_events_collision');
            $table->index(['event_date', 'status'], 'idx_events_date_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
