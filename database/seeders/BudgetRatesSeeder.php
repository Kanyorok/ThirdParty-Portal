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
            ['RATE-001', 'Interest Rate', 'Used for loan calculations', true],
            ['RATE-002', 'Exchange Rate', 'Used for currency conversion', false],
            ['RATE-003', 'Inflation Rate', 'Adjusts for economic inflation', false],
            ['RATE-004', 'Discount Rate', 'Used for present value calculations', false],
            ['RATE-005', 'Tax Rate', 'Applicable for tax projections', false],
            ['RATE-006', 'Growth Rate', 'Used in forecasting future values', false],
            ['RATE-007', 'Depreciation Rate', 'Used for asset depreciation', false],
            ['RATE-008', 'Contribution Rate', 'Staff pension or benefits contribution', false],
            ['RATE-009', 'Penalty Rate', 'Late payment penalties', false],
            ['RATE-010', 'Escalation Rate', 'Annual increase projections', false]
        ];

        foreach ($rateTypes as [$code, $name, $desc, $isDefault]) {
            DB::table('t_BudgetRates')->insert([
                'RateTypeCode' => $code,
                'RateTypeName' => $name,
                'Description'   => $desc,
                'IsDefault'     => $isDefault,
                'CreatedBy'     => $userId,
                'CreatedOn'     => $now,
                'ModifiedBy'    => $userId,
                'ModifiedOn'    => $now,
                'DeletedBy'     => null,
                'DeletedOn'     => null,
            ]);
        }

        echo "✅ Seeded t_BudgetRates with 10 entries.\n";
    }
}
