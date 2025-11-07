<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_Orders')->insert([
            [
                'OrderNo' => 'PO-0001',
                'AccountID' => 1,
                'Description' => 'Order for office supplies',
                'OrdTotExcl' => 15200.00,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
            ],
            [
                'OrderNo' => 'PO-0002',
                'AccountID' => 2,
                'Description' => 'Order for IT equipment',
                'OrdTotExcl' => 89000.00,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
            ],
            [
                'OrderNo' => 'PO-0003',
                'AccountID' => 3,
                'Description' => 'Order for field tools',
                'OrdTotExcl' => 45250.00,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
            ],
        ]);
    }
}
