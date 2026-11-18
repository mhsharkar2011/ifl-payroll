<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Header row — exactly two columns matching the MB460 export.
     */
    public function headings(): array
    {
        return [
            'Ac-No',
            'sTime',
        ];
    }

    /**
     * Sample rows so the admin sees the expected format.
     */
    public function array(): array
    {
        return [
            ['150026', '2026-10-07 09:15:42'],
            ['150026', '2026-10-07 17:30:11'],
            ['150027', '2026-10-07 09:20:05'],
        ];
    }

    /**
     * Header styling.
     *
     * Signature MUST match the interface exactly:
     *     public function styles(Worksheet $sheet): ?array
     */
    public function styles(Worksheet $sheet): ?array
    {
        return [
            // Header row (row 1)
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => '6366F1'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                ],
            ],
        ];
    }
}
