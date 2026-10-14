<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AuditLogService;
use App\Services\ExcelExportService;
use App\Services\PdfExportService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ExcelExportService $excelExportService,
        private readonly PdfExportService $pdfExportService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function exportSpj(Event $event): BinaryFileResponse
    {
        $this->auditLogService->log(
            action: 'EXPORT_SPJ_EXCEL',
            detail: "Mengunduh rekapitulasi daftar hadir SPJ untuk kegiatan '{$event->title}'"
        );

        return $this->excelExportService->downloadSpjExcel($event);
    }

    public function exportPdf(Event $event): Response
    {
        $this->auditLogService->log(
            action: 'EXPORT_SPJ_PDF',
            detail: "Mengunduh rekapitulasi daftar hadir SPJ format PDF untuk kegiatan '{$event->title}'"
        );

        return $this->pdfExportService->downloadSpjPdf($event);
    }
}
