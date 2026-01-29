<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockItemsSeeder extends Seeder
{
    public function run()
    {
        DB::table('t_StockItems')->insert([
            [
                'SKUCode' => 'SKU-2001',
                'ItemType' => 'Stapler',
                'Batch' => true,
                'Serial' => false,
                'Perishable' => true,
                'Saleable' => true,
                'Purchasable' => true,
                'Store' => 'Main Warehouse',
                'Branch' => 'Central',
                'CurrentQty' => 120,
                'Min' => 50,
                'Reorder' => 75,
                'Max' => 200,
                'LastReceived' => Carbon::now()->subDays(10),
                'Status' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'DeletedBy' => null,
                'CreatedOn' => Carbon::now(),
                'ModifiedOn' => Carbon::now(),
            ],
            [
                'SKUCode' => 'SKU-2002',
                'ItemType' => 'Printer Paper',
                'Batch' => false,
                'Serial' => true,
                'Perishable' => false,
                'Saleable' => true,
                'Purchasable' => false,
                'Store' => 'Branch Store A',
                'Branch' => 'North',
                'CurrentQty' => 15,
                'Min' => 10,
                'Reorder' => 20,
                'Max' => 50,
                'LastReceived' => Carbon::now()->subDays(30),
                'Status' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'DeletedBy' => null,
                'CreatedOn' => Carbon::now(),
                'ModifiedOn' => Carbon::now(),
            ],
            [
                'SKUCode' => 'SKU-2003',
                'ItemType' => 'Glue Stick',
                'Batch' => false,
                'Serial' => false,
                'Perishable' => false,
                'Saleable' => false,
                'Purchasable' => true,
                'Store' => 'Backup Warehouse',
                'Branch' => 'East',
                'CurrentQty' => 0,
                'Min' => 5,
                'Reorder' => 10,
                'Max' => 30,
                'LastReceived' => Carbon::now()->subDays(60),
                'Status' => false,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'DeletedBy' => null,
                'CreatedOn' => Carbon::now(),
                'ModifiedOn' => Carbon::now(),
            ],
        ]);
    }
}
