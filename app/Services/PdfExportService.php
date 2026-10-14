<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

final class PdfExportService
{
    public function __construct(
        private readonly AppConfigService $appConfigService,
    ) {}

    /**
     * Menghasilkan berkas PDF unduhan SPJ resmi BBKK Tanjung Priok.
     */
    public function downloadSpjPdf(Event $event): Response
    {
        $attendees = $event->attendees()->orderBy('created_at', 'asc')->get();
        $hasAttendees = $attendees->isNotEmpty();

        $hasAnyValue = function (string $key) use ($attendees): bool {
            return $attendees->contains(function ($attendee) use ($key) {
                $val = $attendee->{$key};
                if ($val === null) {
                    return false;
                }
                if ($val instanceof \BackedEnum) {
                    $val = $val->value;
                } elseif ($val instanceof \UnitEnum) {
                    $val = $val->name;
                }
                $str = trim((string) $val);
                return $str !== '' && $str !== '-';
            });
        };

        $candidates = [
            [
                'id' => 'no',
                'heading' => 'NO',
                'value' => fn ($attendee, $index) => $index + 1,
                'include' => true,
            ],
            [
                'id' => 'full_name',
                'heading' => 'NAMA LENGKAP & GELAR',
                'value' => fn ($attendee) => $attendee->full_name ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('full_name') : $event->isFieldActive('full_name'),
            ],
            [
                'id' => 'agency',
                'heading' => 'INSTANSI / BAGIAN',
                'value' => fn ($attendee) => $attendee->agency ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('agency') : $event->isFieldActive('agency'),
            ],
            [
                'id' => 'nip_or_id',
                'heading' => 'NIP / NIK',
                'value' => fn ($attendee) => $attendee->nip_or_id ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('nip_or_id') : $event->isFieldActive('nip_or_id'),
            ],
            [
                'id' => 'position',
                'heading' => 'JABATAN KEDINASAN',
                'value' => fn ($attendee) => $attendee->position ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('position') : $event->isFieldActive('position'),
            ],
            [
                'id' => 'gender',
                'heading' => 'JENIS KELAMIN',
                'value' => fn ($attendee) => $attendee->gender?->label() ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('gender') : $event->isFieldActive('gender'),
            ],
            [
                'id' => 'phone',
                'heading' => 'NO HP / WHATSAPP',
                'value' => fn ($attendee) => $attendee->phone ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('phone') : $event->isFieldActive('phone'),
            ],
            [
                'id' => 'address',
                'heading' => 'ALAMAT ASAL',
                'value' => fn ($attendee) => $attendee->address ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('address') : $event->isFieldActive('address'),
            ],
            [
                'id' => 'coordinates',
                'heading' => 'KOORDINAT LOKASI GPS',
                'value' => fn ($attendee) => $attendee->coordinates ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('coordinates') : $event->isFieldActive('coordinates'),
            ],
            [
                'id' => 'bank_name',
                'heading' => 'BANK',
                'value' => fn ($attendee) => $attendee->bank_name ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('bank_name') : $event->isFieldActive('transport'),
            ],
            [
                'id' => 'bank_account_no',
                'heading' => 'NO. REKENING',
                'value' => fn ($attendee) => $attendee->bank_account_no ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('bank_account_no') : $event->isFieldActive('transport'),
            ],
            [
                'id' => 'bank_account_name',
                'heading' => 'ATAS NAMA REKENING',
                'value' => fn ($attendee) => $attendee->bank_account_name ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('bank_account_name') : $event->isFieldActive('transport'),
            ],
            [
                'id' => 'signature',
                'heading' => 'TANDA TANGAN',
                'value' => fn ($attendee) => $attendee->signature_data,
                'include' => true,
            ],
            [
                'id' => 'waktu',
                'heading' => 'WAKTU PRESENSI (WIB)',
                'value' => fn ($attendee) => $attendee->created_at ? $attendee->created_at->format('d/m/Y H:i:s') : '-',
                'include' => true,
            ],
        ];

        $columns = array_values(array_filter($candidates, fn ($col) => (bool) $col['include']));

        // Pimpinan pengesahan
        $pimpinanName = $event->pimpinan_name;
        $pimpinanNip = $event->pimpinan_nip;
        $pimpinanPosition = $event->pimpinan_position;
        $pimpinanSignature = $event->pimpinan_signature;

        if (! $pimpinanName) {
            try {
                $defaultPimpinan = $this->appConfigService->getPimpinanProfile();
                $pimpinanName = $defaultPimpinan['name'] ?? null;
                $pimpinanNip = $defaultPimpinan['nip'] ?? null;
                $pimpinanPosition = $defaultPimpinan['position'] ?? ('Ketua ' . $event->work_unit);
                $pimpinanSignature = $defaultPimpinan['signature_data'] ?? ($defaultPimpinan['signature_url'] ?? null);
            } catch (\Throwable) {
                // Ignore fallback error
            }
        }

        $pimpinan = [
            'name' => $pimpinanName,
            'nip' => $pimpinanNip,
            'position' => $pimpinanPosition ?: ('Ketua ' . $event->work_unit),
            'date_text' => $event->pimpinan_date_text ?: ('Jakarta, ' . $event->event_date->translatedFormat('d F Y')),
            'signature' => $pimpinanSignature,
        ];

        // Format data rows
        $rows = $attendees->map(function ($attendee, $index) use ($columns) {
            $row = [];
            foreach ($columns as $col) {
                $row[$col['id']] = ($col['value'])($attendee, $index);
            }
            return $row;
        });

        $pdf = Pdf::loadView('exports.spj-pdf', [
            'event' => $event,
            'columns' => $columns,
            'rows' => $rows,
            'pimpinan' => $pimpinan,
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isRemoteEnabled', true)
        ->setOption('isHtml5ParserEnabled', true);

        $sanitizedTitle = Str::slug($event->title);
        $dateStr = $event->event_date->format('Y-m-d');
        $fileName = "SPJ_Hadirin_{$dateStr}_{$sanitizedTitle}.pdf";

        return $pdf->download($fileName);
    }
}
