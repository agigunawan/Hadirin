<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

final class ResetAdminCommand extends Command
{
    protected $signature = 'admin:reset 
                            {--username=superadmin : Username akun super admin} 
                            {--password=AdminPriok2026! : Password baru} 
                            {--name=Super Admin BBKK : Nama lengkap admin}';

    protected $description = 'Rescue CLI: Me-reset kredensial Super Admin dan membersihkan cache kegagalan login (pengganti perbaikiLoginAdmin GAS)';

    public function handle(AuditLogService $auditLogService): int
    {
        $username = (string) $this->option('username');
        $password = (string) $this->option('password');
        $name = (string) $this->option('name');

        $this->info("Memproses reset akun pengelola sistem: {$username}...");

        $user = User::where('username', $username)->first();

        if ($user) {
            $user->password = Hash::make($password);
            $user->name = $name;
            $user->role = UserRole::SUPER_ADMIN;
            $user->save();
            $this->info("✓ Kata sandi akun {$username} berhasil diperbarui.");
        } else {
            $user = User::create([
                'username' => $username,
                'name' => $name,
                'email' => 'admin@bbkkpriok.go.id',
                'password' => Hash::make($password),
                'role' => UserRole::SUPER_ADMIN,
                'work_unit' => 'Tata Usaha / Sistem Informasi',
            ]);
            $this->info("✓ Akun Super Admin baru berhasil dibuat.");
        }

        // Bersihkan cache kegagalan login rate limiter
        RateLimiter::clear(strtolower($username) . '|127.0.0.1');

        $auditLogService->log(
            action: 'RESCUE_ADMIN_RESET',
            detail: "Akun pengelola sistem '{$username}' direset melalui Artisan CLI command",
            username: 'CLI_SYSTEM'
        );

        $this->newLine();
        $this->table(
            ['Username', 'Password', 'Peran', 'Status'],
            [[$username, $password, 'SUPER_ADMIN', 'Aktif & Terbuka']]
        );

        return self::SUCCESS;
    }
}
