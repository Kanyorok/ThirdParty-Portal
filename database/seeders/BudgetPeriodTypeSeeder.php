<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetPeriodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch the first available user Id
        $userId = DB::table('t_Users')->value('Id');

        // Exit if no user exists
        if (! $userId) {
            echo "❌ No users found in t_Users. Seed users first.\n";

            return;
        }

        $periodTypes = [
            ['PeriodType' => 'Annually', 'Code' => 'ANL', 'IsActive' => true],
            ['PeriodType' => 'Bi-Annually', 'Code' => 'BAN', 'IsActive' => true],
            ['PeriodType' => 'Quarterly', 'Code' => 'QTR', 'IsActive' => true],
            ['PeriodType' => 'Monthly', 'Code' => 'MTH', 'IsActive' => true],
            ['PeriodType' => 'Weekly', 'Code' => 'WKY', 'IsActive' => false],
            ['PeriodType' => 'Daily', 'Code' => 'DAY', 'IsActive' => false],
            ['PeriodType' => 'Biennially', 'Code' => 'BIN', 'IsActive' => true],
            ['PeriodType' => 'Semi-Annually', 'Code' => 'SMA', 'IsActive' => true],
            ['PeriodType' => 'Custom', 'Code' => 'CST', 'IsActive' => false],
            ['PeriodType' => 'One-time', 'Code' => 'ONE', 'IsActive' => true],
        ];

        foreach ($periodTypes as $type) {
            DB::table('t_BudgetPeriodTypes')->insert([
                'PeriodType' => $type['PeriodType'],
                'Code' => $type['Code'],
                'IsActive' => $type['IsActive'],
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }

        echo "✅ t_BudgetPeriodTypes seeded successfully.\n";
    }
}
