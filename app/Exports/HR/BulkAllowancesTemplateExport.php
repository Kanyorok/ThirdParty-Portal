<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkAllowancesTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'EmployeeNo',
            'AllowanceCode',
            'AllowanceName',
            'Amount',
            'Month',
            'Year',
            'IsTaxable',
            'IsRecurring',
            'Status',
        ];
    }

    public function array(): array
    {
        return [[
            'EMP-0001',
            'AIRTIME',
            'Airtime Allowance',
            '2000',
            '1',
            '2026',
            '1',
            '1',
            'Pending',
        ]];
    }
}
