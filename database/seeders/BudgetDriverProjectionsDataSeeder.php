<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetDriverProjectionsDataSeeder extends Seeder
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

        $projectionIds = DB::table('t_BudgetDriverProjections')->pluck('Id')->toArray();
        if (empty($projectionIds)) {
            throw new \Exception('No projections found in t_BudgetDriverProjections table. Please seed t_BudgetDriverProjections first.');
        }

        $productIds = DB::table('t_BudgetProductTypes')->pluck('Id')->toArray();
        if (empty($productIds)) {
            throw new \Exception('No product types found in t_BudgetProductTypes table. Please seed t_BudgetProductTypes first.');
        }

        $projectionData = [
            ['BudgetDriverProjectionsID' => $projectionIds[0 % count($projectionIds)], 'ProductID' => $productIds[0 % count($productIds)], 'Volume' => 1000, 'Value' => 500000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[1 % count($projectionIds)], 'ProductID' => $productIds[1 % count($productIds)], 'Volume' => 500, 'Value' => 250000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[2 % count($projectionIds)], 'ProductID' => $productIds[2 % count($productIds)], 'Volume' => 2000, 'Value' => 1000000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[3 % count($projectionIds)], 'ProductID' => $productIds[3 % count($productIds)], 'Volume' => 750, 'Value' => 375000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[4 % count($projectionIds)], 'ProductID' => $productIds[4 % count($productIds)], 'Volume' => 300, 'Value' => 150000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[5 % count($projectionIds)], 'ProductID' => $productIds[0 % count($productIds)], 'Volume' => 1200, 'Value' => 600000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[6 % count($projectionIds)], 'ProductID' => $productIds[1 % count($productIds)], 'Volume' => 400, 'Value' => 200000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[7 % count($projectionIds)], 'ProductID' => $productIds[2 % count($productIds)], 'Volume' => 1500, 'Value' => 750000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[8 % count($projectionIds)], 'ProductID' => $productIds[3 % count($productIds)], 'Volume' => 600, 'Value' => 300000.00],
            ['BudgetDriverProjectionsID' => $projectionIds[9 % count($projectionIds)], 'ProductID' => $productIds[4 % count($productIds)], 'Volume' => 800, 'Value' => 400000.00],
        ];

        foreach ($projectionData as $data) {
            DB::table('t_BudgetDriverProjectionsData')->insert([
                'BudgetDriverProjectionsID' => $data['BudgetDriverProjectionsID'],
                'ProductID' => $data['ProductID'],
                'Volume' => $data['Volume'],
                'Value' => $data['Value'],
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
