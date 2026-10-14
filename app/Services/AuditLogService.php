<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

final class AuditLogService
{
    /**
     * Mencatat peristiwa penting ke tabel audit_logs.
     */
    public function log(string $action, string $detail, ?string $username = null, ?string $ipAddress = null): AuditLog
    {
        $actorUsername = $username ?? auth()->user()?->username ?? 'Guest';
        $clientIp = $ipAddress ?? Request::ip() ?? '127.0.0.1';

        return AuditLog::create([
            'timestamp' => now(),
            'username' => $actorUsername,
            'action' => strtoupper($action),
            'detail' => $detail,
            'ip_address' => $clientIp,
        ]);
    }
}
