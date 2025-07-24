<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceSegmentOrderSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_FinanceSegmentOrder')->insert([
            [
                'SegmentType' => 'GLAccountTypeValue',
                'Description' => 'Store Value for GLType',
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'SegmentType' => 'GLTypeGroupIDValue',
                'Description' => 'Store value for TypeGroup',
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'SegmentType' => 'GLSubAccountTypeIDValue',
                'Description' => 'Store the SubAccount Value',
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'SegmentType' => 'BranchID',
                'Description' => 'Store the BranchId code like 00, 005 etc',
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'SegmentType' => 'GLDigits',
                'Description' => '1',
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
