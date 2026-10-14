<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->string('full_name', 150)->nullable()->change();
            $table->string('agency', 150)->nullable()->change();
            $table->string('gender', 20)->nullable()->change();
            $table->string('nip_or_id', 20)->nullable()->change();
            $table->string('position', 100)->nullable()->change();
            $table->string('phone', 25)->nullable()->change();
            $table->string('address', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->string('full_name', 150)->nullable(false)->change();
            $table->string('agency', 150)->nullable(false)->change();
            $table->string('gender', 20)->nullable(false)->change();
            $table->string('nip_or_id', 20)->nullable(false)->change();
            $table->string('position', 100)->nullable(false)->change();
            $table->string('phone', 25)->nullable(false)->change();
            $table->string('address', 255)->nullable(false)->change();
        });
    }
};
