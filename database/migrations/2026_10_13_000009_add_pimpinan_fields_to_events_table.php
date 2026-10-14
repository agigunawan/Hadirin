<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('pimpinan_name', 150)->nullable()->after('config_fields');
            $table->string('pimpinan_nip', 50)->nullable()->after('pimpinan_name');
            $table->string('pimpinan_position', 150)->nullable()->after('pimpinan_nip');
            $table->mediumText('pimpinan_signature')->nullable()->after('pimpinan_position');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'pimpinan_name',
                'pimpinan_nip',
                'pimpinan_position',
                'pimpinan_signature',
            ]);
        });
    }
};
