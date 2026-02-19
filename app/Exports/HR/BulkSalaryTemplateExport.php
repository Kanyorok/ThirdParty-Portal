<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkSalaryTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'EmployeeNo',
            'BasicSalary',
            'EffectiveFrom',
            'Notes',
        ];
    }

    public function array(): array
    {
        return [[
            'EMP-0001',
            '75000',
            '2026-02-01',
            'Annual increment',
        ]];
    }
}
