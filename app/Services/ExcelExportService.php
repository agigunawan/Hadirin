<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\SpjAttendanceExport;
use App\Models\Event;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExcelExportService
{
    /**
     * Menghasilkan file Excel unduhan SPJ resmi BBKK Tanjung Priok.
     */
    public function downloadSpjExcel(Event $event): BinaryFileResponse
    {
        $sanitizedTitle = Str::slug($event->title);
        $dateStr = $event->event_date->format('Y-m-d');
        $fileName = "SPJ_Hadirin_{$dateStr}_{$sanitizedTitle}.xlsx";

        return Excel::download(new SpjAttendanceExport($event), $fileName);
    }
}
