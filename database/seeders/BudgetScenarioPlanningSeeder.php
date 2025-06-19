<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetScenarioPlanningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ensure dependencies exist
        $userId = DB::table('t_Users')->value('Id');
        //$budgetPeriods = DB::table('t_BudgetPeriods')->pluck('Id')->toArray();
        $planningMethods = DB::table('t_BudgetPlanningMethods')->pluck('Id')->toArray();

        // if (!$userId || empty($budgetPeriods) || empty($planningMethods)) {
        //     echo "❌ Missing required data: Users, BudgetPeriods, or PlanningMethods.\n";
        //     return;
        // }

        $scenarios = [
            'Base Case',
            'Worst Case',
            'Best Case',
        ];

        foreach ($scenarios as $index => $name) {
            DB::table('t_BudgetScenarioPlanning')->insert([
                'scenarioName' => $name,
                'description' => "Scenario planning for {$name}.",
                //'budgetPeriod' => $budgetPeriods[array_rand($budgetPeriods)],
                'planningMethod' => $planningMethods[array_rand($planningMethods)],
                'isDefault' => $index === 0, // First scenario isDefault
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }

        echo "✅ t_BudgetScenarioPlanning seeded with 10 scenarios.\n";
    }
}
