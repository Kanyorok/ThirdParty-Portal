<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $actor = 1;

        $types = [
            // Standard annual leave
            [
                'Code' => 'ANNUAL_STD',
                'Name' => 'Annual Leave (Standard)',
                'AnnualEntitlementDays' => 24,
                'AllowCarryForward' => 1,
                'MaxCarryForwardDays' => 5,
                'RequiresAttachment' => 0,
                'IsPaid' => 1,
                'Status' => 'Approved',
            ],
            // Management annual leave
            [
                'Code' => 'ANNUAL_MGMT',
                'Name' => 'Annual Leave (Management)',
                'AnnualEntitlementDays' => 30,
                'AllowCarryForward' => 1,
                'MaxCarryForwardDays' => 7,
                'RequiresAttachment' => 0,
                'IsPaid' => 1,
                'Status' => 'Approved',
            ],
            [
                'Code' => 'SICK',
                'Name' => 'Sick Leave',
                'AnnualEntitlementDays' => 14,
                'AllowCarryForward' => 0,
                'MaxCarryForwardDays' => null,
                'RequiresAttachment' => 1,
                'IsPaid' => 1,
                'Status' => 'Approved',
            ],
            [
                'Code' => 'MAT',
                'Name' => 'Maternity Leave',
                'AnnualEntitlementDays' => 90,
                'AllowCarryForward' => 0,
                'MaxCarryForwardDays' => null,
                'RequiresAttachment' => 1,
                'IsPaid' => 1,
                'Status' => 'Approved',
            ],
            [
                'Code' => 'PAT',
                'Name' => 'Paternity Leave',
                'AnnualEntitlementDays' => 14,
                'AllowCarryForward' => 0,
                'MaxCarryForwardDays' => null,
                'RequiresAttachment' => 1,
                'IsPaid' => 1,
                'Status' => 'Approved',
            ],
            [
                'Code' => 'COMP',
                'Name' => 'Compassionate Leave',
                'AnnualEntitlementDays' => 5,
                'AllowCarryForward' => 0,
                'MaxCarryForwardDays' => null,
                'RequiresAttachment' => 0,
                'IsPaid' => 1,
                'Status' => 'Approved',
            ],
            [
                'Code' => 'STUDY',
                'Name' => 'Study Leave',
                'AnnualEntitlementDays' => 10,
                'AllowCarryForward' => 0,
                'MaxCarryForwardDays' => null,
                'RequiresAttachment' => 1,
                'IsPaid' => 0,
                'Status' => 'Approved',
            ],
        ];

        foreach ($types as $type) {
            DB::table('t_HRLeaveTypes')->updateOrInsert(
                ['Code' => $type['Code']],
                array_merge($type, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actor,
                    'ModifiedOn' => $now,
                ])
            );
        }
    }
}
