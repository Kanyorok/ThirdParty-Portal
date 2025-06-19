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
        $now = Carbon::now();

        // Fetch required foreign keys
        $userIds       = User::pluck('Id')->toArray();
        $categoryIds   = DB::table('t_BudgetLineCategories')->pluck('Id')->toArray();
        $departmentIds = DB::table('t_Departments')->pluck('Id')->toArray();
        $glTypeIds     = DB::table('t_CodeDetails')->pluck('Id')->toArray(); // assuming GL types are stored here
        $glSubTypeIds  = DB::table('t_GLAccountSubTypes')->pluck('Id')->toArray();
        $glAccountIds  = DB::table('t_BudgetGLAccounts')->pluck('Id')->toArray();
        $productTypeIds = DB::table('t_BudgetProductTypes')->pluck('Id')->toArray();

        // Validate required data
        if (empty($userIds)) throw new \Exception('No users found. Please seed t_Users.');
        if (empty($categoryIds)) throw new \Exception('No budget line categories found. Please seed t_BudgetLineCategories.');
        if (empty($departmentIds)) throw new \Exception('No departments found. Please seed t_Departments.');
        if (empty($glTypeIds)) throw new \Exception('No GL account types found in t_CodeDetails.');
        if (empty($glSubTypeIds)) throw new \Exception('No GL account subtypes found. Please seed t_GLAccountSubTypes.');
        if (empty($glAccountIds)) throw new \Exception('No GL accounts found. Please seed t_BudgetGLAccounts.');
        if (empty($productTypeIds)) throw new \Exception('No product types found. Please seed t_BudgetProductTypes.');

        $faker = \Faker\Factory::create();
        $names = [
            'Loan Interest Income', 'Fee Income', 'Operational Expenses', 'Expected Loan Losses', 'Funding Costs',
            'IT Infrastructure Costs', 'Marketing Expenses', 'Wealth Management Fees', 'Regulatory Compliance Costs',
            'Branch Expansion Costs', 'Customer Service Costs', 'Training & Development', 'Legal Fees',
            'Consultancy Fees', 'Utilities', 'Insurance Premiums', 'Depreciation', 'Amortization',
            'Maintenance Costs', 'Security Services', 'Travel Expenses', 'Office Supplies', 'Telecom Expenses',
            'Software Licenses', 'Hardware Purchases', 'Employee Welfare', 'CSR Activities', 'Research & Development',
            'Tax Expenses', 'Interest Expense', 'Miscellaneous Income', 'Other Operating Income'
        ];
        for ($i = 0; $i < 30; $i++) {
            $isProductDriven = (bool)rand(0, 1);
            $budgetLineId = DB::table('t_BudgetLines')->insertGetId([
                'LineName'             => $names[$i],
                'Description'          => $faker->sentence(10),
                'IsDefault'            => $i === 0, // Only first is default
                'IsProductDriven'      => $isProductDriven,
                'BudgetLineCategoryID' => $categoryIds[array_rand($categoryIds)],
                'DepartmentID'         => $departmentIds[array_rand($departmentIds)],
                'GLAccountTypeID'      => $glTypeIds[array_rand($glTypeIds)],
                'GLAccountSubTypeID'   => $glSubTypeIds[array_rand($glSubTypeIds)],
                'CreatedBy'            => $userIds[array_rand($userIds)],
                'CreatedOn'            => $now->copy()->subDays(rand(5, 30)),
                'ModifiedBy'           => $userIds[array_rand($userIds)],
                'ModifiedOn'           => $now->copy()->subDays(rand(1, 4)),
                'DeletedBy'            => null,
                'DeletedOn'            => null,
            ]);

            // Attach 1-3 random GL accounts
            $glCount = rand(1, 3);
            $selectedGLs = $faker->randomElements($glAccountIds, $glCount);
            foreach ($selectedGLs as $glId) {
                DB::table('t_BudgetLinesGLAccounts')->insert([
                    'BudgetLineID' => $budgetLineId,
                    'BudgetGLAccountID' => $glId,
                    'CreatedBy' => $userIds[array_rand($userIds)],
                    'CreatedOn' => $now->copy()->subDays(rand(1, 30)),
                    'ModifiedBy' => $userIds[array_rand($userIds)],
                    'ModifiedOn' => $now->copy()->subDays(rand(0, 10)),
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                ]);
            }

            // Attach 1-3 random product types if product driven
            if ($isProductDriven) {
                $prodCount = rand(1, 3);
                $selectedProducts = $faker->randomElements($productTypeIds, $prodCount);
                foreach ($selectedProducts as $prodId) {
                    DB::table('t_BudgetLineProductTypes')->insert([
                        'BudgetLineId' => $budgetLineId,
                        'ProductTypeId' => $prodId,
                        'CreatedBy' => $userIds[array_rand($userIds)],
                        'CreatedOn' => $now->copy()->subDays(rand(1, 30)),
                        'ModifiedBy' => $userIds[array_rand($userIds)],
                        'ModifiedOn' => $now->copy()->subDays(rand(0, 10)),
                        'DeletedBy' => null,
                        'DeletedOn' => null,
                    ]);
                }
            }
        }
    }
}
