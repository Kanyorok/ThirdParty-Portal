<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkEmployeesTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'EmployeeNo',
            'FirstName',
            'LastName',
            'OtherNames',
            'Email',
            'Phone',
            'Gender',
            'DateOfBirth',
            'Branch',
            'Department',
            'Grade',
            'Role',
            'Supervisor',
            'EmploymentDate',
            'EmploymentType',
            'ContractType',
            'Address',
            'NSSFNo',
            'NHIFNo',
            'KRAPIN',
            'BasicSalary',
            'PaymentMode',
            'Bank',
            'BankBranch',
            'BankAccount',
            'Status',
            'IsActive',
            'SalaryEffectiveFrom',
        ];
    }

    public function array(): array
    {
        return [[
            'EMP-0001',
            'John',
            'Doe',
            '',
            'john.doe@example.com',
            '254700000000',
            'Male',
            '1990-01-15',
            'Head Office',
            'ICT Support',
            'Grade 1',
            'Support Level 1',
            '',
            '2026-01-01',
            'Permanent',
            'Permanent',
            'Nairobi',
            '',
            '',
            '',
            '60000',
            'Bank',
            'Equity Bank',
            'Kiandao',
            '1234567890',
            'Active',
            '1',
            '2026-01-01',
        ]];
    }
}
