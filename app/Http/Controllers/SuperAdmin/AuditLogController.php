<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $actionFilter = $request->query('action');

        $logs = AuditLog::query()
            ->when($search, function ($query, $search) {
                $query->where('username', 'like', "%{$search}%")
                    ->orWhere('detail', 'like', "%{$search}%");
            })
            ->when($actionFilter, fn ($q) => $q->where('action', $actionFilter))
            ->orderBy('timestamp', 'desc')
            ->paginate(25);

        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('super-admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'search' => $search,
            'currentAction' => $actionFilter,
        ]);
    }
}
