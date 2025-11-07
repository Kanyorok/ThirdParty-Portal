<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetDriverTypes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are users in t_Users table
        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            throw new \Exception('❌ No users found in t_Users table. Please seed t_Users first.');
        }

        // Budget drivers for a banking system with clear descriptions
        $drivers = [
            [
                'DriverName' => 'Financial',
                'Description' => 'Drivers based on financial metrics such as revenue, expenses, and profitability.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Operational',
                'Description' => 'Drivers tied to business operations such as staffing, system uptime, or process efficiency.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Loan Volume',
                'Description' => 'Tracks the total value or number of loans issued in a given period. Helps in forecasting interest income.',
                'IsActive' => false
            ],
            [
                'DriverName' => 'Interest Rate',
                'Description' => 'Reflects applicable interest rates affecting borrowing costs and investment returns.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Transaction Volume',
                'Description' => 'Measures the number of customer transactions processed over time, used to estimate operational load.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Deposit Balance',
                'Description' => 'Represents total customer deposits, often used in liquidity, reserve, and funding analysis.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Default Rate',
                'Description' => 'Indicates the proportion of loans that are not repaid, used in assessing credit risk.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Cost of Funding',
                'Description' => 'Reflects the average interest or cost incurred by the institution in acquiring funds.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Customer Acquisition Cost',
                'Description' => 'Represents the average marketing and onboarding cost incurred to bring in a new customer.',
                'IsActive' => false
            ],
            [
                'DriverName' => 'Branch Operating Cost',
                'Description' => 'Covers recurring expenses of physical branches like rent, utilities, and staffing.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Net Interest Margin',
                'Description' => 'Measures the spread between interest earned and paid, a key indicator of profitability.',
                'IsActive' => true
            ],
            [
                'DriverName' => 'Regulatory Compliance Cost',
                'Description' => 'Includes all expenditures related to meeting local, regional, or international financial regulations.',
                'IsActive' => false
            ],
        ];

        // Insert all driver types
        foreach ($drivers as $driver) {
            DB::table('t_BudgetDrivers')->insert([
                'DriverName' => $driver['DriverName'],
                'Description' => $driver['Description'],
                'IsActive' => $driver['IsActive'],
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => Carbon::now()->subDays(rand(1, 30)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => Carbon::now()->subDays(rand(0, 10)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }

        echo "✅ Seeded t_BudgetDrivers with 12 entries.\n";
    }
}
