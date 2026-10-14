<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminTimKerja;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AuditLogService;
use App\Services\ExcelExportService;
use App\Services\PdfExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AttendeeMonitorController extends Controller
{
    public function __construct(
        private readonly ExcelExportService $excelExportService,
        private readonly PdfExportService $pdfExportService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function show(Event $event, Request $request): View
    {
        $user = $request->user();
        if ($event->created_by !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak berhak melihat rincian kegiatan ini.');
        }

        $attendees = $event->attendees()
            ->with('evaluation')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin-tim-kerja.attendees.show', [
            'event' => $event,
            'attendees' => $attendees,
        ]);
    }

    public function export(Event $event, Request $request): BinaryFileResponse
    {
        $user = $request->user();
        if ($event->created_by !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak berhak mengunduh rekap agenda ini.');
        }

        $this->auditLogService->log(
            action: 'EXPORT_SPJ_EXCEL',
            detail: "Admin Tim Kerja {$user->name} mengunduh berkas SPJ untuk '{$event->title}'"
        );

        return $this->excelExportService->downloadSpjExcel($event);
    }

    public function exportPdf(Event $event, Request $request): Response
    {
        $user = $request->user();
        if ($event->created_by !== $user->id && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak berhak mengunduh rekap agenda ini.');
        }

        $this->auditLogService->log(
            action: 'EXPORT_SPJ_PDF',
            detail: "Admin Tim Kerja {$user->name} mengunduh berkas SPJ format PDF untuk '{$event->title}'"
        );

        return $this->pdfExportService->downloadSpjPdf($event);
    }
}
