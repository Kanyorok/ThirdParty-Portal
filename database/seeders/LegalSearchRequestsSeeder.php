<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalSearchRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $requests = [
            [
                'RequestType' => 'Company Search',
                'EntityName' => 'Acme Corporation',
                // 'EntityType' => 'Company',
                // 'RegistrationNumber' => 'REG123456',
                // 'Country' => 'Kenya',
                'Status' => 'Pending',
                // 'IsActive' => 1,
                'Remarks' => 'Urgent search required for due diligence.',
                'Findings' => null,
                'ApprovalReason' => null,
                'RequestedBy' => 1,
                'RequestDate' => '2025-08-05 10:30:00',
            ],
            [
                'RequestType' => 'Property Search',
                'EntityName' => 'Green Acres Farm',
                // 'EntityType' => 'Property',
                // 'RegistrationNumber' => 'PROP98765',
                // 'Country' => 'Kenya',
                'Status' => 'Pending',
                // 'IsActive' => 1,
                'Remarks' => 'Search completed successfully.',
                'Findings' => null,
                'ApprovalReason' => null,
                'RequestedBy' => 2,
                'RequestDate' => '2025-08-12 15:00:00',
            ],
            [
                'RequestType' => 'Court Search',
                'EntityName' => 'High Court of Kenya',
                // 'EntityType' => 'Court Case',
                // 'RegistrationNumber' => 'CASE2025/789',
                // 'Country' => 'Kenya',
                'Status' => 'Pending',
                // 'IsActive' => 1,
                'Remarks' => 'Awaiting court records verification.',
                'Findings' => null,
                'ApprovalReason' => null,
                'RequestedBy' => 1,
                'RequestDate' => '2025-08-15 09:15:00',
            ],
        ];

        foreach ($requests as $request) {
            $exists = DB::table('t_LegalSearchRequests')
                ->where('RequestType', $request['RequestType'])
                ->where('EntityName', $request['EntityName'])
                // ->where('RegistrationNumber', $request['RegistrationNumber'])
                ->exists();

            if (!$exists) {
                DB::table('t_LegalSearchRequests')->insert([
                    'RequestType' => $request['RequestType'],
                    'EntityName' => $request['EntityName'],
                    // 'EntityType' => $request['EntityType'],
                    // 'RegistrationNumber' => $request['RegistrationNumber'],
                    // 'Country' => $request['Country'],
                    'Status' => $request['Status'],
                    // 'IsActive' => $request['IsActive'],
                    'Remarks' => $request['Remarks'],
                    'Findings' => $request['Findings'],
                    'ApprovalReason' => $request['ApprovalReason'],
                    'RequestedBy' => $request['RequestedBy'],
                    'RequestDate' => $request['RequestDate'],
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                ]);
            }
        }
    }
}
