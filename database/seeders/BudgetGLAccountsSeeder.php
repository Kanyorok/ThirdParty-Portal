<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetGLAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['CurrencyID' => 1, 'GLName' => 'TravelBudgetA', 'Description' => 'Travel budget for department A', 'GTType' => 'Type-A'],
            ['CurrencyID' => 1, 'GLName' => 'OfficeSupplies2025', 'Description' => 'Office supplies 2025', 'GTType' => 'Type-B'],
            ['CurrencyID' => 1, 'GLName' => 'ProjectXFunding', 'Description' => 'Project X funding', 'GTType' => 'Type-C'],
            ['CurrencyID' => 1, 'GLName' => 'AnnualMaintenance', 'Description' => 'Annual maintenance', 'GTType' => null],
            ['CurrencyID' => 1, 'GLName' => 'ITUpgrades', 'Description' => 'IT infrastructure upgrades', 'GTType' => 'Type-D'],
            ['CurrencyID' => 1, 'GLName' => 'MarketingExpenses', 'Description' => 'Marketing expenses', 'GTType' => 'Type-E'],
            ['CurrencyID' => 1, 'GLName' => 'TrainingPrograms', 'Description' => 'Training programs', 'GTType' => null],
            ['CurrencyID' => 1, 'GLName' => 'EmployeeWelfare', 'Description' => 'Employee welfare fund', 'GTType' => 'Type-F'],
            ['CurrencyID' => 1, 'GLName' => 'ConsultancyServices', 'Description' => 'Consultancy services', 'GTType' => 'Type-G'],
            ['CurrencyID' => 1, 'GLName' => 'LegalFees', 'Description' => 'Legal advisory fees', 'GTType' => null],
            ['CurrencyID' => 1, 'GLName' => 'VehicleMaintenance', 'Description' => 'Vehicle maintenance', 'GTType' => 'Type-H'],
            ['CurrencyID' => 1, 'GLName' => 'SecurityServices', 'Description' => 'Security services', 'GTType' => 'Type-I'],
            ['CurrencyID' => 1, 'GLName' => 'OfficeRent2025', 'Description' => 'Office rent 2025', 'GTType' => 'Type-J'],
            ['CurrencyID' => 1, 'GLName' => 'ConferenceHosting', 'Description' => 'Conference hosting', 'GTType' => null],
            ['CurrencyID' => 1, 'GLName' => 'SoftwareSubscriptions', 'Description' => 'Software subscriptions', 'GTType' => 'Type-K'],
        ];

        foreach ($accounts as $account) {
            DB::table('t_BudgetGLAccounts')->insert([
                'CurrencyID' => $account['CurrencyID'],
                'GLName' => $account['GLName'],
                'Description' => $account['Description'],
                'GTType' => $account['GTType'],
                'CreatedBy' => 1,
                'CreatedOn' => Carbon::now()->subDays(rand(1, 30)),
                'ModifiedBy' => 1,
                'ModifiedOn' => Carbon::now()->subDays(rand(0, 10)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }
    }
}
