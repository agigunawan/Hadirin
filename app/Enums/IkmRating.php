<?php

declare(strict_types=1);

namespace App\Enums;

enum IkmRating: string
{
    case SANGAT_BAIK = 'SANGAT_BAIK';
    case BAIK = 'BAIK';
    case CUKUP = 'CUKUP';
    case KURANG = 'KURANG';

    public function label(): string
    {
        return match ($this) {
            self::SANGAT_BAIK => 'Sangat Baik (Bintang 4)',
            self::BAIK => 'Baik (Bintang 3)',
            self::CUKUP => 'Cukup (Bintang 2)',
            self::KURANG => 'Kurang (Bintang 1)',
        };
    }

    public function numericScore(): int
    {
        return match ($this) {
            self::SANGAT_BAIK => 4,
            self::BAIK => 3,
            self::CUKUP => 2,
            self::KURANG => 1,
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::SANGAT_BAIK => '🌟',
            self::BAIK => '👍',
            self::CUKUP => '😐',
            self::KURANG => '👎',
        };
    }
}
