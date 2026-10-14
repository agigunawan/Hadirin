<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendee_id')->unique()->constrained('attendees')->cascadeOnDelete();
            $table->string('rating', 20); // SANGAT_BAIK, BAIK, CUKUP, KURANG
            $table->text('feedback')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
