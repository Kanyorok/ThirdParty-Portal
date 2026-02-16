<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetLinesGLAccountsSeeder extends Seeder
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

        $budgetLineIds = DB::table('t_BudgetLines')->pluck('Id')->toArray();
        if (empty($budgetLineIds)) {
            throw new \Exception('No budget lines found in t_BudgetLines table. Please seed t_BudgetLines first.');
        }

        $budgetGLAccountIds = DB::table('t_BudgetGLAccounts')->pluck('Id')->toArray();
        if (empty($budgetGLAccountIds)) {
            throw new \Exception('No GL accounts found in t_BudgetGLAccounts table. Please seed t_BudgetGLAccounts first.');
        }

        $mappings = [
            ['BudgetLineID' => $budgetLineIds[0], 'BudgetGLAccountID' => $budgetGLAccountIds[0]], // Loan Interest Income -> Travel budget
            ['BudgetLineID' => $budgetLineIds[1], 'BudgetGLAccountID' => $budgetGLAccountIds[1]], // Fee Income -> Office supplies
            ['BudgetLineID' => $budgetLineIds[2], 'BudgetGLAccountID' => $budgetGLAccountIds[2]], // Operational Expenses -> Project X
            ['BudgetLineID' => $budgetLineIds[3], 'BudgetGLAccountID' => $budgetGLAccountIds[3]], // Expected Loan Losses -> Annual maintenance
            ['BudgetLineID' => $budgetLineIds[4], 'BudgetGLAccountID' => $budgetGLAccountIds[4]], // Funding Costs -> IT upgrades
            ['BudgetLineID' => $budgetLineIds[5], 'BudgetGLAccountID' => $budgetGLAccountIds[5]], // IT Infrastructure Costs -> Marketing
            ['BudgetLineID' => $budgetLineIds[6], 'BudgetGLAccountID' => $budgetGLAccountIds[6]], // Marketing Expenses -> Training
            ['BudgetLineID' => $budgetLineIds[7], 'BudgetGLAccountID' => $budgetGLAccountIds[7]], // Wealth Management Fees -> Employee welfare
            ['BudgetLineID' => $budgetLineIds[8], 'BudgetGLAccountID' => $budgetGLAccountIds[8]], // Regulatory Compliance Costs -> Consultancy
            ['BudgetLineID' => $budgetLineIds[9], 'BudgetGLAccountID' => $budgetGLAccountIds[9]], // Branch Expansion Costs -> Legal fees
        ];

        foreach ($mappings as $mapping) {
            DB::table('t_BudgetLinesGLAccounts')->insert([
                'BudgetLineID' => $mapping['BudgetLineID'],
                'BudgetGLAccountID' => $mapping['BudgetGLAccountID'],
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
