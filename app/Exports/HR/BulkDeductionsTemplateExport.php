<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkDeductionsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'EmployeeNo',
            'DeductionCode',
            'DeductionName',
            'Amount',
            'Month',
            'Year',
            'IsRecurring',
            'Status',
        ];
    }

    public function array(): array
    {
        return [[
            'EMP-0001',
            'SACCO',
            'Sacco Deduction',
            '5000',
            '1',
            '2026',
            '1',
            'Pending',
        ]];
    }
}
