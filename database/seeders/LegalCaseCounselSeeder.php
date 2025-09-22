<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalCaseCounselSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $counselItems = [
            [
                'LegalCaseID' => 1,
                'CounselName' => 'John Doe',
                'FirmName' => 'Doe & Associates',
                'Email' => 'johndoe@example.com',
                'Phone' => '+254712345678',
                'Role' => 'Lead Counsel',
                // 'IsExternal'   => true,
                // 'AssignedOn'   => $now,
            ],
            [
                'LegalCaseID' => 1,
                'CounselName' => 'Jane Smith',
                'FirmName' => 'Smith Legal Partners',
                'Email' => 'jane@example.com',
                'Phone' => '+254798765432',
                'Role' => 'Assistant Counsel',
                // 'IsExternal'   => false,
                // 'AssignedOn'   => $now->copy()->subDays(2),
            ],
        ];

        foreach ($counselItems as $item) {
            $exists = DB::table('t_LegalCaseCounsels')
                ->where('LegalCaseID', $item['LegalCaseID'])
                ->where('CounselName', $item['CounselName'])
                ->exists();

            if (!$exists) {
                DB::table('t_LegalCaseCounsels')->insert([
                    'LegalCaseID' => $item['LegalCaseID'],
                    'CounselName' => $item['CounselName'],
                    'FirmName' => $item['FirmName'],
                    'Email' => $item['Email'],
                    'Phone' => $item['Phone'],
                    'Role' => $item['Role'],
                    // 'IsExternal'   => $item['IsExternal'],
                    // 'AssignedOn'   => $item['AssignedOn'],
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
