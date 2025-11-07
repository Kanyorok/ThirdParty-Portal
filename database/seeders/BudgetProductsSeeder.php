<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetProductsSeeder extends Seeder
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

        // Fetch product type IDs and ProductCodes
        $productTypeIds = DB::table('t_BudgetProductTypes')
            ->pluck('Id', 'ProductCode')
            ->toArray();

        // Fetch GL account IDs based on GLName for mapping
        $glAccountIds = DB::table('t_BudgetGLAccounts')
            ->pluck('Id', 'GLName')
            ->toArray();

        $products = [
            [
                'ProductCode' => 'BD',
                'CBSProductID' => $productTypeIds['BD'] ?? 1,
                'Description' => 'Bill discounting in ZIG',
                'ProductTypeID' => 'BD',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-102204'] ?? 1, // CP01 INTEREST RECEIVABLE ACCOUNT
            ],
            [
                'ProductCode' => 'BD',
                'CBSProductID' => $productTypeIds['BD'] ?? 1,
                'Description' => 'Bill discounting in USD',
                'ProductTypeID' => 'BD',
                'CurrencyID' => '10',
                'GLAccountID' => $glAccountIds['GL-102204'] ?? 1, // CP01 INTEREST RECEIVABLE ACCOUNT
            ],
            [
                'ProductCode' => 'BI',
                'CBSProductID' => $productTypeIds['BI'] ?? 2,
                'Description' => 'Treasury bills in ZIG',
                'ProductTypeID' => 'BI',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-104104'] ?? 1, // TB01 CONTROL ACCOUNT
            ],
            [
                'ProductCode' => 'CA',
                'CBSProductID' => $productTypeIds['CA'] ?? 4,
                'Description' => 'Current account in ZIG',
                'ProductTypeID' => 'CA',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-105003'] ?? 1, // CBZ BANK ACCOUNT
            ],
            [
                'ProductCode' => 'CA',
                'CBSProductID' => $productTypeIds['CA'] ?? 4,
                'Description' => 'Current account in USD',
                'ProductTypeID' => 'CA',
                'CurrencyID' => '10',
                'GLAccountID' => $glAccountIds['GL-115003'] ?? 1, // CBZ USD
            ],
            [
                'ProductCode' => 'FD',
                'CBSProductID' => $productTypeIds['FD'] ?? 8,
                'Description' => 'Fixed deposit in ZIG',
                'ProductTypeID' => 'FD',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-307111'] ?? 1, // MFD Fixed Deposit Control Account
            ],
            [
                'ProductCode' => 'LN',
                'CBSProductID' => $productTypeIds['LN'] ?? 12,
                'Description' => 'Micro loan in ZIG',
                'ProductTypeID' => 'LN',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-101001'] ?? 1, // MICRO LOAN CONTROL ACCOUNT
            ],
            [
                'ProductCode' => 'LN',
                'CBSProductID' => $productTypeIds['LN'] ?? 12,
                'Description' => 'Micro loan in USD',
                'ProductTypeID' => 'LN',
                'CurrencyID' => '10',
                'GLAccountID' => $glAccountIds['GL-111001'] ?? 1, // MICRO LOAN CONTROL ACCOUNT USD
            ],
            [
                'ProductCode' => 'LN',
                'CBSProductID' => $productTypeIds['LN'] ?? 12,
                'Description' => 'Salary-based loan in ZIG',
                'ProductTypeID' => 'LN',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-101002'] ?? 1, // SALARY BASED LOAN CONTROL ACCOUNT
            ],
            [
                'ProductCode' => 'MB',
                'CBSProductID' => $productTypeIds['MB'] ?? 13,
                'Description' => 'Money market borrowing in ZIG',
                'ProductTypeID' => 'MB',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-303011'] ?? 1, // MB01 CONTROL ACCOUNT
            ],
            [
                'ProductCode' => 'ML',
                'CBSProductID' => $productTypeIds['ML'] ?? 14,
                'Description' => 'Money market lending in ZIG',
                'ProductTypeID' => 'ML',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-104009'] ?? 1, // MONEY MARKET LENDING
            ],
            [
                'ProductCode' => 'SB',
                'CBSProductID' => $productTypeIds['SB'] ?? 21,
                'Description' => 'Savings bank account in ZIG',
                'ProductTypeID' => 'SB',
                'CurrencyID' => '1',
                'GLAccountID' => $glAccountIds['GL-307101'] ?? 1, // BUSINESS SAVINGS CONTROL ACCOUNT
            ],
            [
                'ProductCode' => 'SB',
                'CBSProductID' => $productTypeIds['SB'] ?? 21,
                'Description' => 'Savings bank account in USD',
                'ProductTypeID' => 'SB',
                'CurrencyID' => '10',
                'GLAccountID' => $glAccountIds['GL-333101'] ?? 1, // CREDIT SUSPENSE ACCOUNT USD (as placeholder)
            ],
        ];

        foreach ($products as $product) {
            if (isset($product['CBSProductID']) && isset($product['GLAccountID'])) {
                DB::table('t_BudgetProducts')->insert([
                    'CBSProductID' => $product['CBSProductID'],
                    'Description' => $product['Description'],
                    'ProductTypeID' => $product['ProductTypeID'],
                    'CurrencyID' => $product['CurrencyID'],
                    'GLAccountID' => $product['GLAccountID'],
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
