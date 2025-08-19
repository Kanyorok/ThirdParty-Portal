<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class OrderLinesSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $now = Carbon::now();

            DB::table('t_OrderLines')->insert([
                // Order 1: Total = 15200
                [
                    'iOrderID' => 1,
                    'iStockCodeID'=>1,
                    'fQuantity' => 4,
                    'fUnitPriceExcl' => 2000.00,
                    'LineTotal' => 8000.00,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                ],
                [
                    'iOrderID' => 1,
                    'iStockCodeID'=>2,
                    'fQuantity' => 6,
                    'fUnitPriceExcl' => 1200.00,
                    'LineTotal' => 7200.00,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                ],

                // Order 2: Total = 89000
                [
                    'iOrderID' => 2,
                    'iStockCodeID'=>3,
                    'fQuantity' => 10,
                    'fUnitPriceExcl' => 5000.00,
                    'LineTotal' => 50000.00,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                ],
                [
                    'iOrderID' => 2,
                    'iStockCodeID'=>4,
                    'fQuantity' => 5,
                    'fUnitPriceExcl' => 7800.00,
                    'LineTotal' => 39000.00,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                ],

                // Order 3: Total = 45250
                [
                    'iOrderID' => 3,
                    'iStockCodeID'=>5,
                    'fQuantity' => 7,
                    'fUnitPriceExcl' => 2500.00,
                    'LineTotal' => 17500.00,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                ],
                [
                    'iOrderID' => 3,
                    'iStockCodeID'=>6,
                    'fQuantity' => 5,
                    'fUnitPriceExcl' => 5550.00,
                    'LineTotal' => 27750.00,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                ],
            ]);
            echo "✅ OrderLinesSeeder completed successfully.\n";
        } catch (Exception $e) {
            echo "❌ Seeder failed with error: " . $e->getMessage() . "\n";
        }
    }
}
