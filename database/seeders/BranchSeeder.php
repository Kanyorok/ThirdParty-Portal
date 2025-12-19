<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $actor = SystemHelper::user();
        $date = now();

        $branches = [
            [
                'BranchID' => '000',
                'Name' => 'Head Office',
                'IsHQ' => true,
            ],
            [
                'BranchID' => '001',
                'Name' => 'Moshi',
                'IsHQ' => false,
            ],
            [
                'BranchID' => '002',
                'Name' => 'TANDAHIMBA',
                'IsHQ' => false,
            ],
            [
                'BranchID' => '003',
                'Name' => 'DODOMA',
                'IsHQ' => false,
            ],
            [
                'BranchID' => '004',
                'Name' => 'TABORA',
                'IsHQ' => false,
            ],
        ];

        foreach ($branches as $branch) {
            DB::table('t_Branches')->updateOrInsert(
                ['BranchID' => $branch['BranchID']], // condition
                [
                    'Name' => $branch['Name'],
                    'IsHQ' => $branch['IsHQ'],
                    'CreatedOn' => $date,
                    'CreatedBy' => $actor->Id,
                    'ModifiedOn' => $date,
                    'ModifiedBy' => $actor->Id,
                ]
            );
        }
    }
}
