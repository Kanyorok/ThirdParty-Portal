<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkAttendanceTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'EmployeeNo',
            'LogTime',
            'LogType',
            'Channel',
            'DeviceID',
            'Latitude',
            'Longitude',
            'Remarks',
        ];
    }

    public function array(): array
    {
        return [[
            'EMP-0001',
            '2026-01-05 08:00:00',
            'IN',
            'Device',
            'BIO-01',
            '-1.2921',
            '36.8219',
            'Clock in',
        ]];
    }
}
