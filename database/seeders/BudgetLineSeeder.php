<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetLineSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $faker = \Faker\Factory::create();

        // Fetch required foreign keys
        $userIds = User::pluck('Id')->toArray();
        $categoryIds = DB::table('t_BudgetLineCategories')->pluck('Id', 'CategoryName')->toArray();
        $departmentIds = DB::table('t_Departments')->pluck('Id')->toArray();
        $glTypeIds = DB::table('t_CodeDetails')->where('CodeID', 'GLAccountType')->pluck('Id', 'Value')->toArray();
        $glSubTypeIds = DB::table('t_GLAccountSubTypes')->pluck('Id', 'GLAccountSubTypeName')->toArray();
        $glAccounts = DB::table('t_BudgetGLAccounts')->select('Id', 'GTType', 'Description')->get()->groupBy('GTType')->toArray();
        $productTypeIds = DB::table('t_BudgetProductTypes')->pluck('Id')->toArray();

        // Validate required data
        if (empty($userIds)) {
            throw new \Exception('No users found. Please seed t_Users.');
        }
        if (empty($categoryIds)) {
            throw new \Exception('No budget line categories found. Please seed t_BudgetLineCategories.');
        }
        if (empty($departmentIds)) {
            throw new \Exception('No departments found. Please seed t_Departments.');
        }
        if (empty($glTypeIds)) {
            throw new \Exception('No GL account types found in t_CodeDetails.');
        }
        if (empty($glSubTypeIds)) {
            throw new \Exception('No GL account subtypes found. Please seed t_GLAccountSubTypes.');
        }
        if (empty($glAccounts)) {
            throw new \Exception('No GL accounts found. Please seed t_BudgetGLAccounts.');
        }
        if (empty($productTypeIds)) {
            throw new \Exception('No product types found. Please seed t_BudgetProductTypes.');
        }

        // Define 30 meaningful budget lines
        $budgetLines = [
            // Income (I) - Revenue Category
            [
                'LineName' => 'Micro Loan Interest Income',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Loan Interest Income',
                'Category' => 'Revenue',
                'Description' => 'Interest earned from micro loans.',
                'IsProductDriven' => true,
                'Department' => 'Finance',
                'GLAccountDescriptions' => ['MICRO LOAN INTEREST RECEIVABLE'],
            ],
            [
                'LineName' => 'Salary Based Loan Interest Income',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Loan Interest Income',
                'Category' => 'Revenue',
                'Description' => 'Interest earned from salary-based loans.',
                'IsProductDriven' => true,
                'Department' => 'Finance',
                'GLAccountDescriptions' => ['SALARY BASED LOAN INTEREST RECEIVABLE'],
            ],
            [
                'LineName' => 'SME Loan Interest Income',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Loan Interest Income',
                'Category' => 'Revenue',
                'Description' => 'Interest earned from SME loans.',
                'IsProductDriven' => true,
                'Department' => 'Finance',
                'GLAccountDescriptions' => ['SME LOAN INTEREST RECEIVABLE'],
            ],
            [
                'LineName' => 'Transaction Fee Income',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Fee-Based Income',
                'Category' => 'Revenue',
                'Description' => 'Fees earned from account transactions.',
                'IsProductDriven' => true,
                'Department' => 'Operations',
                'GLAccountDescriptions' => ['SERVICE CHARGES'],
            ],
            [
                'LineName' => 'Wealth Management Fees',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Fee-Based Income',
                'Category' => 'Revenue',
                'Description' => 'Fees from wealth management services.',
                'IsProductDriven' => true,
                'Department' => 'Wealth Management',
                'GLAccountDescriptions' => ['COMMISSION INCOME'],
            ],
            [
                'LineName' => 'Foreign Exchange Gains',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Foreign Exchange Gains',
                'Category' => 'Revenue',
                'Description' => 'Gains from forex trading activities.',
                'IsProductDriven' => false,
                'Department' => 'Treasury',
                'GLAccountDescriptions' => ['FOREX TRADING'],
            ],
            [
                'LineName' => 'Investment Income',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Investment Income',
                'Category' => 'Revenue',
                'Description' => 'Income from financial investments.',
                'IsProductDriven' => false,
                'Department' => 'Treasury',
                'GLAccountDescriptions' => ['FINANCIAL ASSETS'],
            ],
            [
                'LineName' => 'Service Charges',
                'GLAccountType' => 'I',
                'GLAccountSubType' => 'Service Charges',
                'Category' => 'Revenue',
                'Description' => 'Charges for banking services.',
                'IsProductDriven' => true,
                'Department' => 'Operations',
                'GLAccountDescriptions' => ['SERVICE CHARGES'],
            ],

            // Expenses (E) - Expenses Category
            [
                'LineName' => 'Salaries and Wages',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Salaries and Wages',
                'Category' => 'Expenses',
                'Description' => 'Employee compensation costs.',
                'IsProductDriven' => false,
                'Department' => 'Human Resources',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],
            [
                'LineName' => 'IT Infrastructure Costs',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'IT Infrastructure',
                'Category' => 'Expenses',
                'Description' => 'Costs for IT systems and maintenance.',
                'IsProductDriven' => false,
                'Department' => 'IT',
                'GLAccountDescriptions' => ['COMPUTER SOFTWARE', 'COMPUTER EQUIPMENT'],
            ],
            [
                'LineName' => 'Marketing Expenses',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Marketing and Advertising',
                'Category' => 'Expenses',
                'Description' => 'Costs for advertising and promotions.',
                'IsProductDriven' => false,
                'Department' => 'Marketing',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],
            [
                'LineName' => 'Utilities',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Utilities',
                'Category' => 'Expenses',
                'Description' => 'Costs for electricity, water, and telecom.',
                'IsProductDriven' => false,
                'Department' => 'Facilities',
                'GLAccountDescriptions' => ['PREPAYMENTS'],
            ],
            [
                'LineName' => 'Regulatory Compliance Costs',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Regulatory and Compliance Costs',
                'Category' => 'Compliance & Risk',
                'Description' => 'Expenses for regulatory adherence.',
                'IsProductDriven' => false,
                'Department' => 'Compliance',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],
            [
                'LineName' => 'Travel Expenses',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Travel and Transport',
                'Category' => 'Expenses',
                'Description' => 'Costs for business travel and transport.',
                'IsProductDriven' => false,
                'Department' => 'Administration',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],
            [
                'LineName' => 'Depreciation Expense',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Depreciation',
                'Category' => 'Expenses',
                'Description' => 'Depreciation of fixed assets.',
                'IsProductDriven' => false,
                'Department' => 'Finance',
                'GLAccountDescriptions' => ['ACC. DEPN. OFFICE EQUIPMENT', 'ACC. DEPN. FURNITURE AND FITTINGS'],
            ],
            [
                'LineName' => 'Training and Development',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Salaries and Wages',
                'Category' => 'Expenses',
                'Description' => 'Costs for employee training programs.',
                'IsProductDriven' => false,
                'Department' => 'Human Resources',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],
            [
                'LineName' => 'Legal Fees',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Regulatory and Compliance Costs',
                'Category' => 'Compliance & Risk',
                'Description' => 'Fees for legal services.',
                'IsProductDriven' => false,
                'Department' => 'Legal',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],
            [
                'LineName' => 'Consultancy Fees',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Regulatory and Compliance Costs',
                'Category' => 'Expenses',
                'Description' => 'Fees for external consultants.',
                'IsProductDriven' => false,
                'Department' => 'Administration',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],
            [
                'LineName' => 'Office Supplies',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Utilities',
                'Category' => 'Expenses',
                'Description' => 'Costs for office supplies and stationery.',
                'IsProductDriven' => false,
                'Department' => 'Administration',
                'GLAccountDescriptions' => ['PREPAYMENTS'],
            ],
            [
                'LineName' => 'Security Services',
                'GLAccountType' => 'E',
                'GLAccountSubType' => 'Utilities',
                'Category' => 'Expenses',
                'Description' => 'Costs for branch and office security.',
                'IsProductDriven' => false,
                'Department' => 'Facilities',
                'GLAccountDescriptions' => ['ACCRUED EXPENSES'],
            ],

            // Assets (A) - Capital Expenditures Category
            [
                'LineName' => 'Office Equipment Purchases',
                'GLAccountType' => 'A',
                'GLAccountSubType' => 'Fixed Assets',
                'Category' => 'Capital Expenditures',
                'Description' => 'Acquisition of office equipment.',
                'IsProductDriven' => false,
                'Department' => 'Facilities',
                'GLAccountDescriptions' => ['OFFICE EQUIPMENT'],
            ],
            [
                'LineName' => 'Branch Expansion Costs',
                'GLAccountType' => 'A',
                'GLAccountSubType' => 'Fixed Assets',
                'Category' => 'Capital Expenditures',
                'Description' => 'Costs for new branch setups.',
                'isProductDriven' => false,
                'Department' => 'Operations',
                'GLAccountDescriptions' => ['LAND AND BUILDINGS'],
            ],
            [
                'LineName' => 'Loan Disbursements',
                'GLAccountType' => 'A',
                'GLAccountSubType' => 'Accounts Receivable',
                'Category' => 'Revenue',
                'Description' => 'Funds disbursed for loans.',
                'IsProductDriven' => true,
                'Department' => 'Finance',
                'GLAccountDescriptions' => ['MICRO LOAN CONTROL ACCOUNT', 'SALARY BASED LOAN CONTROL ACCOUNT'],
            ],
            [
                'LineName' => 'Computer Software Acquisition',
                'GLAccountType' => 'A',
                'GLAccountSubType' => 'Fixed Assets',
                'Category' => 'Capital Expenditures',
                'Description' => 'Purchase of software licenses.',
                'IsProductDriven' => false,
                'Department' => 'IT',
                'GLAccountDescriptions' => ['COMPUTER SOFTWARE'],
            ],
            [
                'LineName' => 'Furniture and Fittings',
                'GLAccountType' => 'A',
                'GLAccountSubType' => 'Fixed Assets',
                'Category' => 'Capital Expenditures',
                'Description' => 'Purchase of office furniture.',
                'IsProductDriven' => false,
                'Department' => 'Facilities',
                'GLAccountDescriptions' => ['FURNITURE AND FITTINGS'],
            ],
            [
                'LineName' => 'Cash and Cash Equivalents',
                'GLAccountType' => 'A',
                'GLAccountSubType' => 'Cash and Cash Equivalents',
                'Category' => 'Revenue',
                'Description' => 'Cash held in vaults and bank accounts.',
                'IsProductDriven' => false,
                'Department' => 'Treasury',
                'GLAccountDescriptions' => ['TELLER VAULT ACCOUNT', 'CBZ BANK ACCOUNT'],
            ],

            // Liabilities (L) - Provisions Category
            [
                'LineName' => 'Expected Loan Losses',
                'GLAccountType' => 'L',
                'GLAccountSubType' => 'Accounts Payable',
                'Category' => 'Provisions',
                'Description' => 'Provisions for potential loan defaults.',
                'IsProductDriven' => true,
                'Department' => 'Risk Management',
                'GLAccountDescriptions' => ['MICRO LOAN GENERAL LOAN LOSS PROVISION'],
            ],
            [
                'LineName' => 'Funding Costs',
                'GLAccountType' => 'L',
                'GLAccountSubType' => 'Long-term Debt',
                'Category' => 'Provisions',
                'Description' => 'Costs associated with borrowed funds.',
                'IsProductDriven' => false,
                'Department' => 'Treasury',
                'GLAccountDescriptions' => ['MONEY MARKET BORROWING'],
            ],
            [
                'LineName' => 'Tax Liabilities',
                'GLAccountType' => 'L',
                'GLAccountSubType' => 'Tax Liabilities',
                'Category' => 'Provisions',
                'Description' => 'Provisions for tax obligations.',
                'IsProductDriven' => false,
                'Department' => 'Finance',
                'GLAccountDescriptions' => ['WITHHOLDING TAX'],
            ],
            [
                'LineName' => 'Lease Obligations',
                'GLAccountType' => 'L',
                'GLAccountSubType' => 'Lease Obligations',
                'Category' => 'Provisions',
                'Description' => 'Liabilities for leased assets.',
                'IsProductDriven' => false,
                'Department' => 'Facilities',
                'GLAccountDescriptions' => ['LEASE LIABILITY'],
            ],
            [
                'LineName' => 'Deposit Liabilities',
                'GLAccountType' => 'L',
                'GLAccountSubType' => 'Accounts Payable',
                'Category' => 'Provisions',
                'Description' => 'Customer deposit obligations.',
                'IsProductDriven' => true,
                'Department' => 'Operations',
                'GLAccountDescriptions' => ['MOMBE FIXED DEPOSIT CONTROL'],
            ],
        ];

        foreach ($budgetLines as $index => $line) {
            // Insert budget line
            $budgetLineId = DB::table('t_BudgetLines')->insertGetId([
                'LineName' => $line['LineName'],
                'Description' => $line['Description'],
                'IsDefault' => $index === 0, // First line is default
                'IsProductDriven' => $line['IsProductDriven'] ?? false,
                'BudgetLineCategoryID' => $categoryIds[$line['Category']] ?? reset($categoryIds),
                'DepartmentID' => $departmentIds[array_rand($departmentIds)], // Random department for simplicity
                'GLAccountTypeID' => $line['GLAccountType'],
                'GLAccountSubTypeID' => $glSubTypeIds[$line['GLAccountSubType']] ?? reset($glSubTypeIds),
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => $now->copy()->subDays(rand(5, 30)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => $now->copy()->subDays(rand(1, 4)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);

            $glAccountType = ($line['GLAccountType'] === 'I' || $line['GLAccountType'] === 'E')
                ? null
                : ($line['GLAccountType'] === 'A' ? 'ASSET' : 'LIABILITY');

            // Ensure $availableGLs is an array
            $availableGLs = isset($glAccounts[$glAccountType]) ? $glAccounts[$glAccountType] : [];

            // Filter matching GL accounts
            $matchedGLs = array_filter($availableGLs, function ($gl) use ($line) {
                foreach ($line['GLAccountDescriptions'] as $desc) {
                    if (stripos($gl->Description, $desc) !== false) {
                        return true;
                    }
                }

                return false;
            });

            // Select GL accounts
            $selectedGLs = array_column($matchedGLs, 'Id');
            $selectedGLs = $faker->randomElements($selectedGLs, min(count($selectedGLs), rand(1, 3)));

            // Fallback if none matched
            if (empty($selectedGLs)) {
                $allGLIds = [];
                foreach ($glAccounts as $glGroup) {
                    $allGLIds = array_merge($allGLIds, array_column($glGroup, 'Id'));
                }
                $selectedGLs = $faker->randomElements($allGLIds, rand(1, 3));
            }

            // Attach selected GL accounts
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

            // Attach product types if product-driven
            if (! empty($line['IsProductDriven'])) {
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
