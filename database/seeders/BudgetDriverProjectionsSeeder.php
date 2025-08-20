<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Auth\User;

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

        $budgetIds = DB::table('t_Budgets')->pluck('Id')->toArray();
        if (empty($budgetIds)) {
            throw new \Exception('No budgets found in t_Budgets table. Please seed t_Budgets first.');
        }

        $currencyIds = DB::table('t_Currencies')->pluck('Id')->toArray();
        if (empty($currencyIds)) {
            throw new \Exception('No currencies found in t_Currencies table. Please seed t_Currencies first.');
        }

        $productTypeIds = DB::table('t_BudgetProductTypes')->pluck('Id')->toArray();
        if (empty($productTypeIds)) {
            throw new \Exception('No product types found in t_BudgetProductTypes table. Please seed t_BudgetProductTypes first.');
        }

        $projectionCount = 10;

        for ($i = 0; $i < $projectionCount; $i++) {
            $budgetId = $budgetIds[$i % count($budgetIds)];
            $currencyId = $currencyIds[$i % count($currencyIds)];

            $createdOn = Carbon::now()->subDays(rand(1, 30));
            $modifiedOn = Carbon::now()->subDays(rand(0, 10));

            $projId = DB::table('t_BudgetDriverProjections')->insertGetId([
                'BudgetID' => $budgetId,
                'CurrencyID' => $currencyId,
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => $createdOn,
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => $modifiedOn,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);

            // Attach 1-3 random products for each projection
            $productCount = rand(1, 3);
            shuffle($productTypeIds);
            $selectedProducts = array_slice($productTypeIds, 0, $productCount);

            foreach ($selectedProducts as $prodId) {
                DB::table('t_BudgetDriverProjectionsData')->insert([
                    'BudgetDriverProjectionsID' => $projId,
                    'ProductID' => $prodId,
                    'Volume' => rand(100, 10000),
                    'Value' => round(rand(1000000, 100000000) / 100, 2), // Between 10,000 and 1,000,000 with 2 decimals
                    'CreatedBy' => $userIds[array_rand($userIds)],
                    'CreatedOn' => $createdOn,
                    'ModifiedBy' => $userIds[array_rand($userIds)],
                    'ModifiedOn' => $modifiedOn,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                ]);
            }
        }
    }
}
