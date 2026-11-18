<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeeTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Sample data rows
     */
    public function array(): array
    {
        return [
            [
                '150016',
                'Monir',
                'Hossain',
                'monir@example.com',
                '01733172007',
                'IT',
                'Assistant Manager',
                '44000',
                '2023-12-02',
                '1990-03-02',
                'Mirpur, Dhaka',
                '150016',
                'active',
                'password123',
            ],
            [
                '150017',
                'John',
                'Doe',
                'john@example.com',
                '01811223344',
                'HR',
                'HR Officer',
                '35000',
                '2024-01-15',
                '1992-05-10',
                'Gulshan, Dhaka',
                '150017',
                'active',
                'password123',
            ],
        ];
    }

    /**
     * Excel headers
     */
    public function headings(): array
    {
        return [
            'employee_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'department',
            'designation',
            'salary',
            'joining_date',
            'date_of_birth',
            'address',
            'device_user_id',
            'status',
            'password',
        ];
    }

    /**
     * Column styling
     * ✅ Fixed: Return type ?array for Maatwebsite Excel v1.1+
     */
    public function styles(Worksheet $sheet): ?array
    {
        // Header styling
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        // Row height
        $sheet->getRowDimension(1)->setRowHeight(25);

        return null;
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // employee_id
            'B' => 15,  // first_name
            'C' => 15,  // last_name
            'D' => 25,  // email
            'E' => 15,  // phone
            'F' => 15,  // department
            'G' => 20,  // designation
            'H' => 12,  // salary
            'I' => 15,  // joining_date
            'J' => 15,  // date_of_birth
            'K' => 25,  // address
            'L' => 18,  // device_user_id
            'M' => 12,  // status
            'N' => 15,  // password
        ];
    }
}
