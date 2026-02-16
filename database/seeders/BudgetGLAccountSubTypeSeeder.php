<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetGLAccountSubTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            echo "❌ No users found in t_Users. Please seed users first.\n";

            return;
        }

        $subTypes = [
            // A - Assets
            ['A', 'Cash and Cash Equivalents'],
            ['A', 'Accounts Receivable'],
            ['A', 'Inventory'],
            ['A', 'Prepaid Expenses'],
            ['A', 'Fixed Assets'],
            ['A', 'Investments'],
            ['A', 'Accrued Revenue'],

            // L - Liabilities
            ['L', 'Accounts Payable'],
            ['L', 'Accrued Expenses'],
            ['L', 'Short-term Loans'],
            ['L', 'Long-term Debt'],
            ['L', 'Deferred Revenue'],
            ['L', 'Tax Liabilities'],
            ['L', 'Lease Obligations'],

            // I - Income
            ['I', 'Loan Interest Income'],
            ['I', 'Fee-Based Income'],
            ['I', 'Commission Income'],
            ['I', 'Investment Income'],
            ['I', 'Foreign Exchange Gains'],
            ['I', 'Service Charges'],
            ['I', 'Miscellaneous Income'],

            // E - Expenses
            ['E', 'Salaries and Wages'],
            ['E', 'Utilities'],
            ['E', 'IT Infrastructure'],
            ['E', 'Marketing and Advertising'],
            ['E', 'Depreciation'],
            ['E', 'Travel and Transport'],
            ['E', 'Regulatory and Compliance Costs'],
        ];

        foreach ($subTypes as [$type, $name]) {
            DB::table('t_GLAccountSubTypes')->insert([
                'GLAccountTypeValue' => $type,
                'GLAccountSubTypeName' => $name,
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => $now->copy()->subDays(rand(5, 30)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => $now->copy()->subDays(rand(1, 4)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }
    }
}
