<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkKpiTargetsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'EmployeeNo',
            'Period',
            'Year',
            'Segment',
            'KpiCode',
            'KpiName',
            'AnnualTarget',
            'PeriodTarget',
            'Weight',
            'Notes',
            'ItemNotes',
        ];
    }

    public function array(): array
    {
        return [[
            'EMP-0001',
            'Quarterly',
            '2026',
            'Q1',
            'KPI-001',
            'Revenue Growth',
            '2000000',
            '500000',
            '20',
            'Bulk goal import',
            '',
        ]];
    }
}
