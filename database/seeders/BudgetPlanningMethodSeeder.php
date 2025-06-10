<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetPlanningMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ensure at least one user exists
        $userId = DB::table('t_Users')->value('Id');
        if (!$userId) {
            echo "❌ No users found in t_Users. Please seed users first.\n";
            return;
        }

        $methods = [
            ['MethodName' => 'Zero-Based Budgeting', 'Description' => 'Every expense must be justified from scratch.', 'IsActive' => true],
            ['MethodName' => 'Incremental Budgeting', 'Description' => 'Budget is based on last year’s figures plus adjustments.', 'IsActive' => true],
            ['MethodName' => 'Activity-Based Budgeting', 'Description' => 'Budgeting based on activity costs driving resource needs.', 'IsActive' => false],
            ['MethodName' => 'Value Proposition Budgeting', 'Description' => 'Focuses on spending that aligns with value delivery.', 'IsActive' => true],
            ['MethodName' => 'Rolling Forecast', 'Description' => 'Continuously updated forecast with real-time data.', 'IsActive' => false],
        ];

        foreach ($methods as $method) {
            DB::table('t_BudgetPlanningMethods')->insert([
                'MethodName' => $method['MethodName'],
                'Description' => $method['Description'],
                'IsActive' => $method['IsActive'],
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }

        echo "✅ t_BudgetPlanningMethods seeded with 5 rows.\n";
    }
}
