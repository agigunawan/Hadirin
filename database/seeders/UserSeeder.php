<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin Pengelola
        User::updateOrCreate(['username' => 'superadmin'], [
            'name' => 'Super Admin BBKK Priok',
            'email' => 'superadmin@bbkkpriok.go.id',
            'password' => Hash::make('AdminPriok2026!'),
            'role' => UserRole::SUPER_ADMIN,
            'work_unit' => 'Tata Usaha / Pengelola Sistem',
        ]);

        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator BBKK Priok',
            'email' => 'admin@bbkkpriok.go.id',
            'password' => Hash::make('AdminPriok2026!'),
            'role' => UserRole::SUPER_ADMIN,
            'work_unit' => 'Tata Usaha / Pengelola Sistem',
        ]);

        // 2. Admin Tim Kerja
        User::updateOrCreate(['username' => 'timker1'], [
            'name' => 'Admin Tim Kerja Karantina & Surveilans',
            'email' => 'timker1@bbkkpriok.go.id',
            'password' => Hash::make('TimkerPriok2026!'),
            'role' => UserRole::ADMIN_TIM_KERJA,
            'work_unit' => 'Tim Kerja Kekarantinaan & Surveilans Epidemiologi',
        ]);

        User::updateOrCreate(['username' => 'timker2'], [
            'name' => 'Admin Tim Kerja Pengendalian Risiko Lingkungan',
            'email' => 'timker2@bbkkpriok.go.id',
            'password' => Hash::make('TimkerPriok2026!'),
            'role' => UserRole::ADMIN_TIM_KERJA,
            'work_unit' => 'Tim Kerja Pengendalian Risiko Lingkungan',
        ]);
    }
}
