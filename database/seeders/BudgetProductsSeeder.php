<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        $productTypeIds = DB::table('t_BudgetProductTypes')->pluck('Id', 'ProductCode')->toArray();
        if (empty($productTypeIds)) {
            throw new \Exception('No product types found in t_BudgetProductTypes table. Please seed t_BudgetProductTypes first.');
        }

        $currencyIds = DB::table('t_Currencies')->pluck('Id')->toArray();
        if (empty($currencyIds)) {
            throw new \Exception('No currencies found in t_Currencies table. Please seed t_Currencies first.');
        }

        $glAccountIds = DB::table('t_BudgetGLAccounts')->pluck('Id')->toArray();
        if (empty($glAccountIds)) {
            throw new \Exception('No GL accounts found in t_BudgetGLAccounts table. Please seed t_BudgetGLAccounts first.');
        }

        $products = [
            ['CBSProductID' => $productTypeIds['LN001'], 'Description' => 'Standard Personal Loan', 'ProductTypeID' => 'LN001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[0]],
            ['CBSProductID' => $productTypeIds['MTG01'], 'Description' => '30-Year Fixed Mortgage', 'ProductTypeID' => 'MTG01', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[1]],
            ['CBSProductID' => $productTypeIds['CC001'], 'Description' => 'Visa Credit Card', 'ProductTypeID' => 'CC001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[2]],
            ['CBSProductID' => $productTypeIds['SV001'], 'Description' => 'High-Yield Savings', 'ProductTypeID' => 'SV001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[3]],
            ['CBSProductID' => $productTypeIds['CK001'], 'Description' => null, 'ProductTypeID' => 'CK001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[4]],
            ['CBSProductID' => $productTypeIds['CL001'], 'Description' => 'Small Business Loan', 'ProductTypeID' => 'CL001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[5]],
            ['CBSProductID' => $productTypeIds['WM001'], 'Description' => 'Premium Wealth Advisory', 'ProductTypeID' => 'WM001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[6]],
            ['CBSProductID' => $productTypeIds['OD001'], 'Description' => 'Overdraft Line of Credit', 'ProductTypeID' => 'OD001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[7]],
            ['CBSProductID' => $productTypeIds['FD001'], 'Description' => '12-Month Fixed Deposit', 'ProductTypeID' => 'FD001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[8]],
            ['CBSProductID' => $productTypeIds['IL001'], 'Description' => 'Real Estate Investment Loan', 'ProductTypeID' => 'IL001', 'CurrencyID' => $currencyIds[0], 'GLAccountID' => $glAccountIds[9]],
        ];

        foreach ($products as $product) {
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
