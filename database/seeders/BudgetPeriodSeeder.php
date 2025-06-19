<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get a valid user ID
        $userId = DB::table('t_Users')->value('Id');
        if (!$userId) {
            echo "❌ No users found in t_Users. Please seed users first.\n";
            return;
        }

        // Get all available BudgetPeriodType IDs
        $periodTypeIds = DB::table('t_BudgetPeriodTypes')->pluck('Id')->all();
        if (empty($periodTypeIds)) {
            echo "❌ No period types found in t_BudgetPeriodTypes. Seed them first.\n";
            return;
        }

        // Define 10 budget periods
        $budgetPeriods = [
            ['fiscalYear' => '2023/2024', 'notes' => 'Full-year budget'],
            ['fiscalYear' => '2023/2024', 'notes' => 'Quarter 1 focus'],
            ['fiscalYear' => '2023/2024', 'notes' => 'Quarter 2 review'],
            ['fiscalYear' => '2023/2024', 'notes' => 'Special project allocation'],
            ['fiscalYear' => '2023/2024', 'notes' => 'Department reforecast'],
            ['fiscalYear' => '2024/2025', 'notes' => 'Initial planning'],
            ['fiscalYear' => '2024/2025', 'notes' => 'Q1 budget'],
            ['fiscalYear' => '2024/2025', 'notes' => 'Q2 projection'],
            ['fiscalYear' => '2024/2025', 'notes' => 'Emergency reserve'],
            ['fiscalYear' => '2024/2025', 'notes' => 'End-year summary'],
        ];

        foreach ($budgetPeriods as $period) {
            DB::table('t_BudgetPeriods')->insert([
                'fiscalYear' => $period['fiscalYear'],
                'periodType' => collect($periodTypeIds)->random(), // pick random valid type
                'notes' => $period['notes'],
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }

        echo "✅ t_BudgetPeriods seeded successfully with 10 rows.\n";
    }
}
