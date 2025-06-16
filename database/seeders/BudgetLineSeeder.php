<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetLineSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            throw new \Exception('No users found in t_Users table. Please seed t_Users first.');
        }

        // Get available BudgetLineCategories
        $categoryIds = DB::table('t_BudgetLineCategories')->pluck('Id')->toArray();
        if (empty($categoryIds)) {
            throw new \Exception('No budget line categories found. Please seed t_BudgetLineCategories first.');
        }

        $budgetLines = [
            ['Loan Interest Income', 'Money earned by the bank from charging interest on loans.', true],
            ['Fee Income', 'Income from service fees like ATM use, maintenance, etc.', false],
            ['Operational Expenses', 'Costs to keep branches running (rent, salaries).', false],
            ['Expected Loan Losses', 'Provisions for unpaid loans.', false],
            ['Funding Costs', 'Costs paid on deposits or borrowed funds.', false],
            ['IT Infrastructure Costs', 'Running digital systems, software, and security.', false],
            ['Marketing Expenses', 'Promotions and outreach activities.', false],
            ['Wealth Management Fees', 'Fees from managing customer investments.', false],
            ['Regulatory Compliance Costs', 'Audit and reporting expenses.', false],
            ['Branch Expansion Costs', 'Opening new branches or installing ATMs.', false],
        ];

        foreach ($budgetLines as [$name, $desc, $isDefault]) {
            DB::table('t_BudgetLines')->insert([
                'LineName'              => $name,
                'Description'           => $desc,
                'IsDefault'             => $isDefault,
                'BudgetLineCategoryID'  => $categoryIds[array_rand($categoryIds)], // Random category
                'CreatedBy'             => $userIds[array_rand($userIds)],
                'CreatedOn'             => Carbon::now()->subDays(rand(1, 30)),
                'ModifiedBy'            => $userIds[array_rand($userIds)],
                'ModifiedOn'            => Carbon::now()->subDays(rand(0, 10)),
                'DeletedBy'             => null,
                'DeletedOn'             => null,
            ]);
        }
    }
}
