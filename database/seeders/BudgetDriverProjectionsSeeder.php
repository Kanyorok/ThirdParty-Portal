<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetDriverProjectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            throw new \Exception('No users found in t_Users table. Please seed t_Users first.');
        }

        $scenarioIds = DB::table('t_BudgetScenarioPlanning')->pluck('Id')->toArray();
        if (empty($scenarioIds)) {
            throw new \Exception('No scenarios found in t_BudgetScenarioPlanning table. Please seed t_BudgetScenarioPlanning first.');
        }

        $currencyIds = DB::table('t_Currencies')->pluck('Id')->toArray();
        if (empty($currencyIds)) {
            throw new \Exception('No currencies found in t_Currencies table. Please seed t_Currencies first.');
        }

        $periodIds = DB::table('t_BudgetPeriods')->pluck('Id')->toArray();
        if (empty($periodIds)) {
            throw new \Exception('No periods found in t_BudgetPeriods table. Please seed t_BudgetPeriods first.');
        }

        $projections = [
            ['ScenarioID' => $scenarioIds[0 % count($scenarioIds)], 'CurrencyID' => $currencyIds[0 % count($currencyIds)], 'PeriodID' => $periodIds[0 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[1 % count($scenarioIds)], 'CurrencyID' => $currencyIds[1 % count($currencyIds)], 'PeriodID' => $periodIds[1 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[2 % count($scenarioIds)], 'CurrencyID' => $currencyIds[0 % count($currencyIds)], 'PeriodID' => $periodIds[2 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[0 % count($scenarioIds)], 'CurrencyID' => $currencyIds[2 % count($currencyIds)], 'PeriodID' => $periodIds[3 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[1 % count($scenarioIds)], 'CurrencyID' => $currencyIds[1 % count($currencyIds)], 'PeriodID' => $periodIds[0 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[2 % count($scenarioIds)], 'CurrencyID' => $currencyIds[0 % count($currencyIds)], 'PeriodID' => $periodIds[1 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[0 % count($scenarioIds)], 'CurrencyID' => $currencyIds[2 % count($currencyIds)], 'PeriodID' => $periodIds[2 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[1 % count($scenarioIds)], 'CurrencyID' => $currencyIds[1 % count($currencyIds)], 'PeriodID' => $periodIds[3 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[2 % count($scenarioIds)], 'CurrencyID' => $currencyIds[0 % count($currencyIds)], 'PeriodID' => $periodIds[0 % count($periodIds)]],
            ['ScenarioID' => $scenarioIds[0 % count($scenarioIds)], 'CurrencyID' => $currencyIds[2 % count($currencyIds)], 'PeriodID' => $periodIds[1 % count($periodIds)]],
        ];

        foreach ($projections as $projection) {
            DB::table('t_BudgetDriverProjections')->insert([
                'ScenarioID' => $projection['ScenarioID'],
                'CurrencyID' => $projection['CurrencyID'],
                'PeriodID' => $projection['PeriodID'],
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => Carbon::now()->subDays(rand(1, 30)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => Carbon::now()->subDays(rand(0, 10)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }
    }
}
