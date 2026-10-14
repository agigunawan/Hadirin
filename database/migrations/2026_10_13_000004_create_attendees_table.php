<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('agency', 150);
            $table->boolean('is_internal')->default(true);
            $table->string('gender', 20); // LAKI_LAKI, PEREMPUAN
            $table->string('nip_or_id', 20);
            $table->string('position', 100);
            $table->string('phone', 25);
            $table->string('address', 255);
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_no', 30)->nullable();
            $table->string('bank_account_name', 100)->nullable();
            $table->string('coordinates', 100)->nullable();
            $table->mediumText('signature_data'); // Base64 PNG data URL
            $table->timestamp('created_at')->useCurrent();

            // Mencegah duplikasi presensi peserta pada kegiatan yang sama (Zero Duplicates)
            $table->unique(['event_id', 'nip_or_id'], 'uq_attendee_event_nip');
            $table->index(['event_id', 'created_at'], 'idx_attendees_event_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
