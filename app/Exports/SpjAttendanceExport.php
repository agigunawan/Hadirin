<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SpjAttendanceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    /**
     * @var array<int, array{id: string, heading: string, value: \Closure, include: bool}>|null
     */
    private ?array $activeColumns = null;

    public function __construct(
        private readonly Event $event
    ) {}

    public function startCell(): string
    {
        return 'A6';
    }

    public function title(): string
    {
        return 'Daftar Hadir';
    }

    /**
     * Menentukan kolom-kolom yang aktif dan memiliki data untuk dimunculkan pada Excel.
     * Kolom yang tidak dikonfigurasi / tidak diisi sama sekali tidak akan dimunculkan.
     *
     * @return array<int, array{id: string, heading: string, value: \Closure, include: bool}>
     */
    private function getActiveColumns(): array
    {
        if ($this->activeColumns !== null) {
            return $this->activeColumns;
        }

        $attendees = $this->event->attendees()->orderBy('created_at', 'asc')->get();
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
                'include' => $hasAttendees ? $hasAnyValue('full_name') : $this->event->isFieldActive('full_name'),
            ],
            [
                'id' => 'agency',
                'heading' => 'INSTANSI / BAGIAN',
                'value' => fn ($attendee) => $attendee->agency ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('agency') : $this->event->isFieldActive('agency'),
            ],
            [
                'id' => 'nip_or_id',
                'heading' => 'NIP / NIK',
                'value' => fn ($attendee) => $attendee->nip_or_id ? "'" . $attendee->nip_or_id : '-',
                'include' => $hasAttendees ? $hasAnyValue('nip_or_id') : $this->event->isFieldActive('nip_or_id'),
            ],
            [
                'id' => 'position',
                'heading' => 'JABATAN KEDINASAN',
                'value' => fn ($attendee) => $attendee->position ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('position') : $this->event->isFieldActive('position'),
            ],
            [
                'id' => 'gender',
                'heading' => 'JENIS KELAMIN',
                'value' => fn ($attendee) => $attendee->gender?->label() ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('gender') : $this->event->isFieldActive('gender'),
            ],
            [
                'id' => 'phone',
                'heading' => 'NO HP / WHATSAPP',
                'value' => fn ($attendee) => $attendee->phone ? "'" . $attendee->phone : '-',
                'include' => $hasAttendees ? $hasAnyValue('phone') : $this->event->isFieldActive('phone'),
            ],
            [
                'id' => 'address',
                'heading' => 'ALAMAT ASAL',
                'value' => fn ($attendee) => $attendee->address ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('address') : $this->event->isFieldActive('address'),
            ],
            [
                'id' => 'coordinates',
                'heading' => 'KOORDINAT LOKASI GPS',
                'value' => fn ($attendee) => $attendee->coordinates ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('coordinates') : $this->event->isFieldActive('coordinates'),
            ],
            [
                'id' => 'bank_name',
                'heading' => 'BANK',
                'value' => fn ($attendee) => $attendee->bank_name ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('bank_name') : $this->event->isFieldActive('transport'),
            ],
            [
                'id' => 'bank_account_no',
                'heading' => 'NO. REKENING',
                'value' => fn ($attendee) => $attendee->bank_account_no ? "'" . $attendee->bank_account_no : '-',
                'include' => $hasAttendees ? $hasAnyValue('bank_account_no') : $this->event->isFieldActive('transport'),
            ],
            [
                'id' => 'bank_account_name',
                'heading' => 'ATAS NAMA REKENING',
                'value' => fn ($attendee) => $attendee->bank_account_name ?? '-',
                'include' => $hasAttendees ? $hasAnyValue('bank_account_name') : $this->event->isFieldActive('transport'),
            ],
            [
                'id' => 'signature',
                'heading' => 'TANDA TANGAN',
                'value' => fn ($attendee) => '',
                'include' => true,
            ],
            [
                'id' => 'waktu',
                'heading' => 'WAKTU PRESENSI (WIB)',
                'value' => fn ($attendee) => $attendee->created_at ? $attendee->created_at->format('d/m/Y H:i:s') : '-',
                'include' => true,
            ],
        ];

        $this->activeColumns = array_values(array_filter($candidates, fn ($col) => (bool) $col['include']));

        return $this->activeColumns;
    }

    public function headings(): array
    {
        return array_map(fn ($col) => $col['heading'], $this->getActiveColumns());
    }

    public function collection()
    {
        $columns = $this->getActiveColumns();

        return $this->event->attendees()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($attendee, $index) use ($columns) {
                $row = [];
                foreach ($columns as $col) {
                    $row[$col['id']] = ($col['value'])($attendee, $index);
                }
                return $row;
            });
    }

    public function styles(Worksheet $sheet): array
    {
        $columns = $this->getActiveColumns();
        $colCount = max(count($columns), 1);
        $lastColumn = Coordinate::stringFromColumnIndex($colCount);

        // Header Judul Kedinasan
        $sheet->setCellValue('A1', 'BALAI BESAR KEKARANTINAAN KESEHATAN TANJUNG PRIOK');
        $sheet->setCellValue('A2', 'REKAPITULASI DAFTAR HADIR PERTEMUAN');
        $sheet->setCellValue('A3', 'Kegiatan: ' . $this->event->title);
        $sheet->setCellValue('A4', 'Tanggal: ' . $this->event->event_date->translatedFormat('d F Y') . ' | Waktu: ' . $this->event->formatted_time . ' | Ruang: ' . ($this->event->room->name ?? '-'));

        if ($colCount > 1) {
            $sheet->mergeCells("A1:{$lastColumn}1");
            $sheet->mergeCells("A2:{$lastColumn}2");
            $sheet->mergeCells("A3:{$lastColumn}3");
            $sheet->mergeCells("A4:{$lastColumn}4");
        }

        $sheet->getStyle("A1:{$lastColumn}2")->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle("A3:{$lastColumn}4")->getFont()->setSize(10);
        $sheet->getStyle("A1:{$lastColumn}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Styling tabel header A6:{$lastColumn}6
        $sheet->getStyle("A6:{$lastColumn}6")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '047857'], // Emerald 700
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $highestRow = max($sheet->getHighestRow(), 6);

        // Border seluruh tabel data & perataan vertikal
        $sheet->getStyle("A6:{$lastColumn}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Sisipkan Gambar Tanda Tangan Setiap Peserta
        $sigColIndex = null;
        foreach ($columns as $idx => $col) {
            if ($col['id'] === 'signature') {
                $sigColIndex = $idx + 1;
                break;
            }
        }

        if ($sigColIndex !== null) {
            $sigColLetter = Coordinate::stringFromColumnIndex($sigColIndex);
            $sheet->getColumnDimension($sigColLetter)->setWidth(20);

            $attendees = $this->event->attendees()->orderBy('created_at', 'asc')->get();
            foreach ($attendees as $idx => $attendee) {
                $rowNumber = 7 + $idx;
                $sheet->getRowDimension($rowNumber)->setRowHeight(42);

                if ($attendee->signature_data && str_starts_with($attendee->signature_data, 'data:image')) {
                    try {
                        $parts = explode(',', $attendee->signature_data);
                        $base64 = count($parts) > 1 ? $parts[1] : $parts[0];
                        $imageBinary = base64_decode($base64);

                        if ($imageBinary !== false) {
                            $tempPath = tempnam(sys_get_temp_dir(), 'hadirin_att_sig_') . '.png';
                            file_put_contents($tempPath, $imageBinary);

                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $drawing->setName('TTD ' . $attendee->full_name);
                            $drawing->setDescription('Tanda Tangan ' . $attendee->full_name);
                            $drawing->setPath($tempPath);
                            $drawing->setHeight(36);
                            $drawing->setCoordinates($sigColLetter . $rowNumber);
                            $drawing->setOffsetX(20);
                            $drawing->setOffsetY(4);
                            $drawing->setWorksheet($sheet);
                        }
                    } catch (\Throwable) {
                        // Fail silently if attendee signature cannot be parsed
                    }
                }
            }

            if ($highestRow >= 7) {
                $sheet->getStyle("{$sigColLetter}7:{$sigColLetter}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
        }

        // Bukti Pengesahan Pimpinan Kedinasan
        $pimpinanName = $this->event->pimpinan_name;
        $pimpinanNip = $this->event->pimpinan_nip;
        $pimpinanPosition = $this->event->pimpinan_position;
        $pimpinanSignature = $this->event->pimpinan_signature;

        // Fallback ke profil pimpinan default jika pimpinan kegiatan belum diset khusus
        if (!$pimpinanName && class_exists(\App\Services\AppConfigService::class)) {
            try {
                $defaultPimpinan = app(\App\Services\AppConfigService::class)->getPimpinanProfile();
                $pimpinanName = $defaultPimpinan['name'] ?? null;
                $pimpinanNip = $defaultPimpinan['nip'] ?? null;
                $pimpinanPosition = $defaultPimpinan['position'] ?? ('Ketua ' . $this->event->work_unit);
            } catch (\Throwable) {
                // Abaikan jika service tidak tersedia
            }
        }

        if ($pimpinanName || $pimpinanPosition || $pimpinanSignature) {
            $signStartColIndex = max(1, $colCount >= 4 ? $colCount - 2 : ($colCount >= 2 ? $colCount - 1 : 1));
            $signEndColIndex = $colCount;
            $signCol = Coordinate::stringFromColumnIndex($signStartColIndex);
            $signEndCol = Coordinate::stringFromColumnIndex($signEndColIndex);

            $signRow = $highestRow + 2;

            // Baris Tempat & Tanggal
            $dateText = $this->event->pimpinan_date_text ?: ('Jakarta, ' . $this->event->event_date->translatedFormat('d F Y'));
            $sheet->setCellValue("{$signCol}{$signRow}", $dateText);

            // Baris Jabatan Penandatangan
            $posText = $pimpinanPosition ?: ('Ketua ' . $this->event->work_unit);
            $sheet->setCellValue("{$signCol}" . ($signRow + 1), $posText);

            if ($colCount > 1 && $signStartColIndex !== $signEndColIndex) {
                $sheet->mergeCells("{$signCol}{$signRow}:{$signEndCol}{$signRow}");
                $sheet->mergeCells("{$signCol}" . ($signRow + 1) . ":{$signEndCol}" . ($signRow + 1));
            }

            // Sisipkan Gambar Tanda Tangan jika tersedia
            if ($pimpinanSignature && str_starts_with($pimpinanSignature, 'data:image')) {
                try {
                    $parts = explode(',', $pimpinanSignature);
                    $base64 = count($parts) > 1 ? $parts[1] : $parts[0];
                    $imageBinary = base64_decode($base64);

                    if ($imageBinary !== false) {
                        $tempPath = tempnam(sys_get_temp_dir(), 'hadirin_sig_') . '.png';
                        file_put_contents($tempPath, $imageBinary);

                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setName('Bukti Pengesahan Pimpinan');
                        $drawing->setDescription('Tanda Tangan Pimpinan');
                        $drawing->setPath($tempPath);
                        $drawing->setHeight(65);
                        $drawing->setCoordinates($signCol . ($signRow + 2));
                        $drawing->setOffsetX(35);
                        $drawing->setOffsetY(4);
                        $drawing->setWorksheet($sheet);
                    }
                } catch (\Throwable) {
                    // Fail gracefully jika format gambar tidak valid
                }
            }

            // Nama & NIP Pimpinan
            $nameRow = $signRow + 5;
            $nipRow = $signRow + 6;

            $sheet->setCellValue("{$signCol}{$nameRow}", $pimpinanName ?: '-');
            $sheet->setCellValue("{$signCol}{$nipRow}", $pimpinanNip ? 'NIP. ' . $pimpinanNip : '');

            if ($colCount > 1 && $signStartColIndex !== $signEndColIndex) {
                $sheet->mergeCells("{$signCol}{$nameRow}:{$signEndCol}{$nameRow}");
                $sheet->mergeCells("{$signCol}{$nipRow}:{$signEndCol}{$nipRow}");
            }

            // Styling Blok TTD Pimpinan
            $sheet->getStyle("{$signCol}{$signRow}:{$signEndCol}{$nipRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$signCol}{$signRow}:{$signEndCol}" . ($signRow + 1))->getFont()->setSize(10);
            $sheet->getStyle("{$signCol}{$nameRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(11);
            $sheet->getStyle("{$signCol}{$nipRow}")->getFont()->setSize(10);
        }

        return [];
    }
}
