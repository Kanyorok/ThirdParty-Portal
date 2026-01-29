<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
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
            ['ProductCode' => 'BD', 'Name' => 'Bill Discounting', 'Description' => null, 'CBSCode' => 'BD'],
            ['ProductCode' => 'BI', 'Name' => 'Treasury Bills', 'Description' => null, 'CBSCode' => 'BI'],
            ['ProductCode' => 'BO', 'Name' => 'Treasury Bonds', 'Description' => null, 'CBSCode' => 'BO'],
            ['ProductCode' => 'CA', 'Name' => 'Current Account', 'Description' => null, 'CBSCode' => 'CA'],
            ['ProductCode' => 'CP', 'Name' => 'Commercial Papers', 'Description' => null, 'CBSCode' => 'CP'],
            ['ProductCode' => 'CS', 'Name' => 'Compulsory Savings / Loan Insurance Fund', 'Description' => null, 'CBSCode' => 'CS'],
            ['ProductCode' => 'FA', 'Name' => 'Fixed Assets', 'Description' => null, 'CBSCode' => 'FA'],
            ['ProductCode' => 'FD', 'Name' => 'Fixed Deposit', 'Description' => null, 'CBSCode' => 'FD'],
            ['ProductCode' => 'LC', 'Name' => 'Letters Of Credit', 'Description' => null, 'CBSCode' => 'LC'],
            ['ProductCode' => 'LG', 'Name' => 'Letters of Guarantee', 'Description' => null, 'CBSCode' => 'LG'],
            ['ProductCode' => 'LK', 'Name' => 'Lockers', 'Description' => null, 'CBSCode' => 'LK'],
            ['ProductCode' => 'LN', 'Name' => 'Loans', 'Description' => null, 'CBSCode' => 'LN'],
            ['ProductCode' => 'MB', 'Name' => 'Money Market Borrowing', 'Description' => null, 'CBSCode' => 'MB'],
            ['ProductCode' => 'ML', 'Name' => 'Money Market Lending', 'Description' => null, 'CBSCode' => 'ML'],
            ['ProductCode' => 'NONE', 'Name' => 'NONE', 'Description' => null, 'CBSCode' => 'NONE'],
            ['ProductCode' => 'OB', 'Name' => 'Offsheet Balance', 'Description' => null, 'CBSCode' => 'OB'],
            ['ProductCode' => 'OD', 'Name' => 'Overdraft', 'Description' => null, 'CBSCode' => 'OD'],
            ['ProductCode' => 'RD', 'Name' => 'Recurring Deposits', 'Description' => null, 'CBSCode' => 'RD'],
            ['ProductCode' => 'RE', 'Name' => 'Repos', 'Description' => null, 'CBSCode' => 'RE'],
            ['ProductCode' => 'RR', 'Name' => 'Reverse Repos', 'Description' => null, 'CBSCode' => 'RR'],
            ['ProductCode' => 'SB', 'Name' => 'Savings Bank', 'Description' => null, 'CBSCode' => 'SB'],
            ['ProductCode' => 'SC', 'Name' => 'Savings Certificates', 'Description' => null, 'CBSCode' => 'SC'],
            ['ProductCode' => 'SH', 'Name' => 'Shares', 'Description' => null, 'CBSCode' => 'SH'],
            ['ProductCode' => 'ZC', 'Name' => 'Zero Coupon Bonds', 'Description' => null, 'CBSCode' => 'ZC'],
        ];

        foreach ($productTypes as $productType) {
            // Check if ProductCode already exists
            if (! DB::table('t_BudgetProductTypes')->where('ProductCode', $productType['ProductCode'])->exists()) {
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
}
