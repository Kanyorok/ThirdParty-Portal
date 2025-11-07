<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actor = SystemHelper::user();
        $date = now();

        DB::table('t_Branches')->insert([
            [
                'Name' => 'Head Office',
                'BranchID' => '000',
                'CreatedOn' => $date,
                'IsHQ' => true,
                'CreatedBy' => $actor->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $actor->Id,
            ],
            [
            'Name' => 'Moshi',
            'BranchID' => '001',
            'CreatedOn' => $date,
            'IsHQ' => false,
            'CreatedBy' => $actor->Id,
            'ModifiedOn' => $date,
            'ModifiedBy' => $actor->Id,
            ],
            [
                'Name' => 'TANDAHIMBA ',
                'BranchID' => '002',
                'CreatedOn' => $date,
                'IsHQ' => false,
                'CreatedBy' => $actor->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $actor->Id,
            ],
            [
                'Name' => 'DODOMA ',
                'BranchID' => '003',
                'CreatedOn' => $date,
                'IsHQ' => false,
                'CreatedBy' => $actor->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $actor->Id,
            ],
            [
                'Name' => 'TABORA ',
                'BranchID' => '004',
                'CreatedOn' => $date,
                'IsHQ' => false,
                'CreatedBy' => $actor->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $actor->Id,
            ],
        ]);
        //
    }
}
