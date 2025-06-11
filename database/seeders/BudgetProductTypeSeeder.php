<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetProductTypeSeeder extends Seeder
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

        $productTypes = [
            ['ProductCode' => 'LN001', 'Name' => 'Personal Loan', 'Description' => 'Loans for individual customers', 'CBSCode' => 'PL001'],
            ['ProductCode' => 'MTG01', 'Name' => 'Mortgage Loan', 'Description' => 'Home financing products', 'CBSCode' => 'MT001'],
            ['ProductCode' => 'CC001', 'Name' => 'Credit Card', 'Description' => 'Consumer credit card products', 'CBSCode' => 'CC001'],
            ['ProductCode' => 'SV001', 'Name' => 'Savings Account', 'Description' => 'Standard savings accounts', 'CBSCode' => 'SV001'],
            ['ProductCode' => 'CK001', 'Name' => 'Checking Account', 'Description' => null, 'CBSCode' => 'CK001'],
            ['ProductCode' => 'CL001', 'Name' => 'Commercial Loan', 'Description' => 'Loans for businesses', 'CBSCode' => 'CL001'],
            ['ProductCode' => 'WM001', 'Name' => 'Wealth Management', 'Description' => 'Investment and advisory services', 'CBSCode' => 'WM001'],
            ['ProductCode' => 'OD001', 'Name' => 'Overdraft Protection', 'Description' => null, 'CBSCode' => 'OD001'],
            ['ProductCode' => 'FD001', 'Name' => 'Fixed Deposit', 'Description' => 'Fixed-term deposit accounts', 'CBSCode' => 'FD001'],
            ['ProductCode' => 'IL001', 'Name' => 'Investment Loan', 'Description' => 'Loans for investment purposes', 'CBSCode' => 'IL001'],
        ];

        foreach ($productTypes as $productType) {
            DB::table('t_BudgetProductTypes')->insert([
                'ProductCode' => $productType['ProductCode'],
                'Name' => $productType['Name'],
                'Description' => $productType['Description'],
                'CBSCode' => $productType['CBSCode'],
                'LastSyncDate' => null,
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
