<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are users in t_Users table
        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            throw new \Exception('No users found in t_Users table. Please seed t_Users first.');
        }

        // Sample budget lines for a banking system
        $budgetLines = [
            [
                'LineName' => 'Loan Interest Income',
                'Description' => 'Revenue from interest on loans issued to customers',
                'IsDefault' => true
            ],
            [
                'LineName' => 'Fee Income',
                'Description' => 'Income from account maintenance and transaction fees',
                'IsDefault' => false
            ],
            [
                'LineName' => 'Operational Expenses',
                'Description' => 'Costs for branch operations, utilities, and staffing',
                'IsDefault' => false
            ],
            [
                'LineName' => 'Expected Loan Losses',
                'Description' => 'Provisions for potential loan defaults based on risk models',
                'IsDefault' => false
            ],
            [
                'LineName' => 'Funding Costs',
                'Description' => 'Interest expenses on deposits and borrowed funds',
                'IsDefault' => false
            ],
            [
                'LineName' => 'IT Infrastructure Costs',
                'Description' => 'Expenses for maintaining banking systems and cybersecurity',
                'IsDefault' => false
            ],
            [
                'LineName' => 'Marketing Expenses',
                'Description' => 'Costs for customer acquisition and promotional campaigns',
                'IsDefault' => false
            ],
            [
                'LineName' => 'Wealth Management Fees',
                'Description' => 'Revenue from wealth management and advisory services',
                'IsDefault' => false
            ],
            [
                'LineName' => 'Regulatory Compliance Costs',
                'Description' => 'Expenses for meeting regulatory requirements and audits',
                'IsDefault' => false
            ],
            [
                'LineName' => 'Branch Expansion Costs',
                'Description' => 'Capital expenditure for opening new branches or ATMs',
                'IsDefault' => false
            ],
        ];

        foreach ($budgetLines as $line) {
            DB::table('t_BudgetLines')->insert([
                'LineName' => $line['LineName'],
                'Description' => $line['Description'],
                'IsDefault' => $line['IsDefault'],
                'CreatedBy' => $userIds[array_rand($userIds)], // Random user ID
                'CreatedOn' => Carbon::now()->subDays(rand(1, 30)), // Random date within last 30 days
                'ModifiedBy' => $userIds[array_rand($userIds)], // Random user ID
                'ModifiedOn' => Carbon::now()->subDays(rand(0, 10)), // Recent modification
                'DeletedBy' => null, // No soft deletes
                'DeletedOn' => null, // No soft deletes
            ]);
        }
    }
}
