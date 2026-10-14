<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppConfig;
use App\Services\AppConfigService;
use Illuminate\Database\Seeder;

final class AppConfigSeeder extends Seeder
{
    public function run(): void
    {
        AppConfig::updateOrCreate(
            ['config_key' => AppConfigService::KEY_PIMPINAN],
            [
                'config_value' => [
                    'name' => 'dr. Resi Arisandi, M.H.',
                    'nip' => '197505122002121002',
                    'position' => 'Kepala Balai Besar Kekarantinaan Kesehatan Tanjung Priok',
                    'signature_url' => null,
                ],
            ]
        );
    }
}
