<?php
 
namespace Database\Seeders;
 
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
 
        $budgetIds = DB::table('t_Budgets')->pluck('Id')->toArray();
        if (empty($budgetIds)) {
            throw new \Exception('No budgets found in t_Budgets table. Please seed t_Budgets first.');
        }
 
        $currencyIds = DB::table('t_Currencies')->pluck('Id')->toArray();
        if (empty($currencyIds)) {
            throw new \Exception('No currencies found in t_Currencies table. Please seed t_Currencies first.');
        }
 
        // $periodIds = DB::table('t_BudgetPeriods')->pluck('Id')->toArray();
        // if (empty($periodIds)) {
        //     throw new \Exception('No periods found in t_BudgetPeriods table. Please seed t_BudgetPeriods first.');
        // }
 
        $productTypeIds = DB::table('t_BudgetProductTypes')->pluck('Id')->toArray();
        if (empty($productTypeIds)) {
            throw new \Exception('No product types found in t_BudgetProductTypes table. Please seed t_BudgetProductTypes first.');
        }
 
        $faker = \Faker\Factory::create();
        $projectionCount = 10;
        for ($i = 0; $i < $projectionCount; $i++) {
            $budgetId = $budgetIds[$i % count($budgetIds)];
            $currencyId = $currencyIds[$i % count($currencyIds)];
            // $periodId = $periodIds[$i % count($periodIds)];
 
            $projId = DB::table('t_BudgetDriverProjections')->insertGetId([
                'BudgetID' => $budgetId,
                'CurrencyID' => $currencyId,
                // 'PeriodID' => $periodId,
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => Carbon::now()->subDays(rand(1, 30)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => Carbon::now()->subDays(rand(0, 10)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
 
            // Attach 1-3 random products for each projection
            $productCount = rand(1, 3);
            $selectedProducts = $faker->randomElements($productTypeIds, $productCount);
            foreach ($selectedProducts as $prodId) {
                DB::table('t_BudgetDriverProjectionsData')->insert([
                    'BudgetDriverProjectionsID' => $projId,
                    'ProductID' => $prodId,
                    'Volume' => $faker->numberBetween(100, 10000),
                    'Value' => $faker->randomFloat(2, 10000, 1000000),
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
}
 