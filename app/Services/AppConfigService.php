<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Storage;

final class AppConfigService
{
    public const string KEY_PIMPINAN = 'INFO_PIMPINAN';
    public const string KEY_LOGO = 'APP_LOGO';

    public function getPimpinanProfile(): array
    {
        return AppConfig::getValue(self::KEY_PIMPINAN, [
            'name' => 'dr. Resi Arisandi, M.H.',
            'nip' => '197505122002121002',
            'position' => 'Kepala Balai Besar Kekarantinaan Kesehatan Tanjung Priok',
            'signature_url' => null,
        ]);
    }

    public function updatePimpinanProfile(array $data): void
    {
        AppConfig::setValue(self::KEY_PIMPINAN, [
            'name' => $data['name'] ?? '',
            'nip' => $data['nip'] ?? '',
            'position' => $data['position'] ?? '',
            'signature_url' => $data['signature_url'] ?? null,
        ]);
    }

    public function getLogoUrl(): ?string
    {
        $path = AppConfig::getValue(self::KEY_LOGO);
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        return null;
    }
}
