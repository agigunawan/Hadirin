<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN_TIM_KERJA = 'ADMIN_TIM_KERJA';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin (Pengelola)',
            self::ADMIN_TIM_KERJA => 'Admin Tim Kerja',
        };
    }
}
