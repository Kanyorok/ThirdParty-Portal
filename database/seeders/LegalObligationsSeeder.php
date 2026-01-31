<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalObligationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $obligations = [
            [
                'Title' => 'Submit Annual Compliance Report',
                'SourceType' => 'Contract',
                'DueDate' => '2025-09-30',
                'Status' => 'Pending',
                'Description' => 'Submit the required annual compliance report to the regulator.',
                'AssignedTo' => 1, // Assuming user ID 1 is assigned
                'ScheduledID' => null, // Assuming this is the ID of the related schedule
            ],
            [
                'Title' => 'Court Hearing for Case #456',
                'SourceType' => 'Case',
                'DueDate' => '2025-10-15',
                'Status' => 'Pending',
                'Description' => 'Attend scheduled court hearing for legal case #456.',
                'AssignedTo' => 2, // Assuming user ID 2 is assigned
                'ScheduledID' => null, // Assuming this is the ID of the related schedule
            ],
            [
                'Title' => 'Renew Business License',
                'SourceType' => 'Contract',
                'DueDate' => '2025-12-01',
                'Status' => 'Pending',
                'Description' => 'Renew the annual business operating license before expiration.',
                'AssignedTo' => 1, // Assuming user ID 3 is assigned
                'ScheduledID' => null, // Assuming this is the ID of the related schedule
            ],
        ];

        foreach ($obligations as $obligation) {
            $exists = DB::table('t_LegalObligations')
                ->where('Title', $obligation['Title'])
                ->where('SourceType', $obligation['SourceType'])
                ->exists();

            if (! $exists) {
                DB::table('t_LegalObligations')->insert([
                    'Title' => $obligation['Title'],
                    'SourceType' => $obligation['SourceType'],
                    'DueDate' => $obligation['DueDate'],
                    'Status' => $obligation['Status'],
                    'Description' => $obligation['Description'],
                    'AssignedTo' => $obligation['AssignedTo'],
                    'ScheduledID' => $obligation['ScheduledID'],
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                    'IsActive' => 1,
                ]);
            }
        }
    }
}
