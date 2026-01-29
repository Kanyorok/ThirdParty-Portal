<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidSubmissionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now(); // Current timestamp: 2025-05-16 09:06:00 AM EAT

        DB::table('t_BidSubmissions')->insert([
            [
                'TenderRef' => 'TND/PROC/2025/001',
                'SupplierName' => 'Tech Supplies Ltd',
                'SubmissionMode' => 1, // Assumes mode ID 1 (e.g., 'Online') exists in t_CodeDetails
                'ReceivedAt' => Carbon::parse('2025-05-10 14:30:00'),
                'Remarks' => 'Submitted with all required documents.',
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'TenderRef' => 'TND/PROC/2025/002',
                'SupplierName' => 'Global Construction Co.',
                'SubmissionMode' => 2, // Assumes mode ID 2 (e.g., 'Physical') exists in t_CodeDetails
                'ReceivedAt' => Carbon::parse('2025-05-12 09:15:00'),
                'Remarks' => null,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'TenderRef' => 'TND/PROC/2025/003',
                'SupplierName' => 'IT Solutions Inc.',
                'SubmissionMode' => 1, // Assumes mode ID 1 (e.g., 'Online') exists in t_CodeDetails
                'ReceivedAt' => Carbon::parse('2025-05-15 11:45:00'),
                'Remarks' => 'Additional clarification documents attached.',
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ]);
    }
}
