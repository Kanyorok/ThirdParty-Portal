<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequisitionLinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('t_RequisitionLines')->insert([
            [
                'RequisitionID' => '1', // Make sure this ID exists in t_Requisitions
                'Type' => 'Goods',
                'Item' => 'CHAIR001',
                'Description' => 'Ergonomic Office Chair',
                'UOM' => 'Each',
                'Quantity' => 10,
                'Urgency' => 2, // Assuming 1 = Low, 2 = Medium, 3 = High
                'Status' => 'p',
                'CreatedBy' => 1,
                'CreatedOn' => Carbon::now(),
                'ModifiedBy' => 1,
                'ModifiedOn' => Carbon::now(),
                'CategoryId' => 1, // Ensure this exists in t_ItemCategories
                'ExpectedPrice' => 1500.00,
            ],
            [
                'RequisitionID' => '1',
                'Type' => 'Goods',
                'Item' => 'LAPTOP001',
                'Description' => 'Dell Latitude 7420',
                'UOM' => 'Each',
                'Quantity' => 5,
                'Urgency' => 3,
                'Status' => 'p',
                'CreatedBy' => 1,
                'CreatedOn' => Carbon::now(),
                'ModifiedBy' => 1,
                'ModifiedOn' => Carbon::now(),
                'CategoryId' => 2,
                'ExpectedPrice' => 120000.00,
            ],
        ]);
    }
}
