<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetRatesSeeder extends Seeder
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
            echo "❌ No users found in t_Users.\n";
            return;
        }

        $rateTypes = [
            ['RATE-001', 'Interest Rate', 'The percentage charged on loans or earned on savings over a period of time. Commonly used in financial projections and loan repayment schedules.', true],
            ['RATE-002', 'Exchange Rate', 'The rate at which one currency can be exchanged for another. Critical in budgeting for multi-currency transactions and conversions.', true],
            ['RATE-003', 'Inflation Rate', 'Represents the annual percentage increase in the price of goods and services. Used to adjust historical costs and forecast future pricing.', false],
            ['RATE-004', 'Discount Rate', 'Used to calculate the present value of future cash flows. It reflects the time value of money and investment risk.', false],
            ['RATE-005', 'Tax Rate', 'Represents the percentage of tax levied on income, goods, or services. Helps estimate tax liabilities in financial plans.', true],
            ];

        foreach ($rateTypes as [$code, $name, $desc, $isDefault]) {
            DB::table('t_BudgetRates')->insert([
                'RateTypeCode' => $code,
                'RateTypeName' => $name,
                'Description' => $desc,
                'IsDefault' => $isDefault,
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }

        echo "✅ Seeded t_BudgetRates with 10 entries (some marked as default).\n";
    }
}
