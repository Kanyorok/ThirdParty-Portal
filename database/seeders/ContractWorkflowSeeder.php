<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = ['rfq_award', 'tender_award'];

        $statuses = [
            [
                'Value' => 'pe',
                'Description' => 'Pending',
                'DisplayOrder' => 1,
            ],
            [
                'Value' => 'rv',
                'Description' => 'Under Review',
                'DisplayOrder' => 2,
            ],
            [
                'Value' => 'Ap',
                'Description' => 'Approved',
                'DisplayOrder' => 3,
            ],
            [
                'Value' => 'Re',
                'Description' => 'Rejected',
                'DisplayOrder' => 4,
            ],
             [
                'Value' => 'Dr',
                'Description' => 'Draft Created',
                'DisplayOrder' => 0,
            ],
        ];

        foreach ($modules as $codeId) {
            foreach ($statuses as $status) {
                // updates or inserts
                DB::table('t_CodeDetails')->updateOrInsert(
                    [
                        'CodeID' => $codeId,
                        'Value' => $status['Value'],
                    ],
                    [
                        'Description' => $status['Description'],
                        'DisplayOrder' => $status['DisplayOrder'],
                        'IsActive' => 1,
                        'CreatedBy' => 1, // System or Admin
                        'CreatedOn' => now(),
                        'ModifiedBy' => 1,
                        'ModifiedOn' => now(),
                        'DeletedBy' => null,
                        'DeletedOn' => null,
                    ]
                );
            }
        }

        $this->command->info('Contract workflow code details seeded successfully.');
    }
}
