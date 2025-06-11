<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetDriverRatesSeeder extends Seeder
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

        $periodTypeIds = DB::table('t_BudgetPeriodTypes')->pluck('Id')->toArray();
        if (empty($periodTypeIds)) {
            throw new \Exception('No period types found in t_BudgetPeriodTypes table. Please seed t_BudgetPeriodTypes first.');
        }

        $productTypeIds = DB::table('t_BudgetProductTypes')->pluck('Id', 'ProductCode')->toArray();
        if (empty($productTypeIds)) {
            throw new \Exception('No product types found in t_BudgetProductTypes table. Please seed t_BudgetProductTypes first.');
        }

        $rateTypeIds = DB::table('t_BudgetRates')->pluck('Id')->toArray();
        if (empty($rateTypeIds)) {
            throw new \Exception('No rate types found in t_BudgetRates table. Please seed t_BudgetRates first.');
        }

        $rates = [
            ['PeriodTypeID' => $periodTypeIds[0], 'ProductTypeID' => $productTypeIds['LN001'], 'RateTypeID' => $rateTypeIds[0], 'RateValue' => 5.25, 'EffectiveDate' => '2025-01-01 00:00:00', 'Source' => 'Market Analysis'],
            ['PeriodTypeID' => $periodTypeIds[1], 'ProductTypeID' => $productTypeIds['MTG01'], 'RateTypeID' => $rateTypeIds[1], 'RateValue' => 3.75, 'EffectiveDate' => '2025-02-01 00:00:00', 'Source' => 'Central Bank'],
            ['PeriodTypeID' => $periodTypeIds[2], 'ProductTypeID' => $productTypeIds['CC001'], 'RateTypeID' => $rateTypeIds[1], 'RateValue' => 18.50, 'EffectiveDate' => '2025-03-01 00:00:00', 'Source' => 'Internal Policy'],
            ['PeriodTypeID' => $periodTypeIds[3], 'ProductTypeID' => $productTypeIds['SV001'], 'RateTypeID' => $rateTypeIds[2], 'RateValue' => 1.20, 'EffectiveDate' => '2025-04-01 00:00:00', 'Source' => 'Market Analysis'],
            ['PeriodTypeID' => $periodTypeIds[4], 'ProductTypeID' => $productTypeIds['CK001'], 'RateTypeID' => $rateTypeIds[2], 'RateValue' => 10.50, 'EffectiveDate' => '2025-05-01 00:00:00', 'Source' => 'Internal Policy'],
            ['PeriodTypeID' => $periodTypeIds[5], 'ProductTypeID' => $productTypeIds['CL001'], 'RateTypeID' => $rateTypeIds[3], 'RateValue' => 6.80, 'EffectiveDate' => '2025-06-01 00:00:00', 'Source' => 'Market Analysis'],
            ['PeriodTypeID' => $periodTypeIds[0], 'ProductTypeID' => $productTypeIds['WM001'], 'RateTypeID' => $rateTypeIds[4], 'RateValue' => 2.00, 'EffectiveDate' => '2025-07-01 00:00:00', 'Source' => 'Internal Policy'],
            ['PeriodTypeID' => $periodTypeIds[0], 'ProductTypeID' => $productTypeIds['OD001'], 'RateTypeID' => $rateTypeIds[0], 'RateValue' => 12.00, 'EffectiveDate' => '2025-08-01 00:00:00', 'Source' => 'Central Bank'],
            ['PeriodTypeID' => $periodTypeIds[6], 'ProductTypeID' => $productTypeIds['FD001'], 'RateTypeID' => $rateTypeIds[0], 'RateValue' => 2.50, 'EffectiveDate' => '2025-09-01 00:00:00', 'Source' => 'Market Analysis'],
            ['PeriodTypeID' => $periodTypeIds[4], 'ProductTypeID' => $productTypeIds['IL001'], 'RateTypeID' => $rateTypeIds[1], 'RateValue' => 7.25, 'EffectiveDate' => '2025-10-01 00:00:00', 'Source' => 'Internal Policy'],
        ];

        foreach ($rates as $rate) {
            DB::table('t_BudgetDriverRates')->insert([
                'PeriodTypeID' => $rate['PeriodTypeID'],
                'ProductTypeID' => $rate['ProductTypeID'],
                'RateTypeID' => $rate['RateTypeID'],
                'RateValue' => $rate['RateValue'],
                'EffectiveDate' => $rate['EffectiveDate'],
                'Source' => $rate['Source'],
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
