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
            'Name' => 'Nairobi',
            'BranchID' => '005',
            'CreatedOn' => $date,
            'IsHQ' => true,
            'CreatedBy' => $actor->Id,
            'ModifiedOn' => $date,
            'ModifiedBy' => $actor->Id,
        ],
        [
            'Name' => 'Mombasa',
            'BranchID' => '006',
            'CreatedOn' => $date,
            'IsHQ' => false,
            'CreatedBy' => $actor->Id,
            'ModifiedOn' => $date,
            'ModifiedBy' => $actor->Id,
        ],
        [
            'Name' => 'Kisumu',
            'BranchID' => '007',
            'CreatedOn' => $date,
            'IsHQ' => false,
            'CreatedBy' => $actor->Id,
            'ModifiedOn' => $date,
            'ModifiedBy' => $actor->Id,
        ],
        [
            'Name' => 'Eldoret',
            'BranchID' => '008',
            'CreatedOn' => $date,
            'IsHQ' => false,
            'CreatedBy' => $actor->Id,
            'ModifiedOn' => $date,
            'ModifiedBy' => $actor->Id,
        ],
        [
            'Name' => 'Nyeri',
            'BranchID' => '009',
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
