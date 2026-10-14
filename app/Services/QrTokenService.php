<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class QrTokenService
{
    /**
     * Menerbitkan token QR unik baru berbasis UUIDv4.
     */
    public function generateToken(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Mendapatkan URL lengkap rute presensi peserta dari token.
     */
    public function getPresensiUrl(string $token): string
    {
        return route('presensi.show', ['token' => $token]);
    }

    /**
     * Menghasilkan string gambar QR Code SVG beresolusi tajam.
     */
    public function generateSvg(string $token, int $size = 350): string
    {
        $url = $this->getPresensiUrl($token);

        return (string) QrCode::size($size)
            ->format('svg')
            ->errorCorrection('H')
            ->margin(2)
            ->generate($url);
    }
}
