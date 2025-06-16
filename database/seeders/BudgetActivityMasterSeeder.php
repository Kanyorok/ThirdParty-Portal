<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Auth\User;

class BudgetActivityMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            echo "❌ No users found in t_Users. Please seed users first.\n";
            return;
        }

        $budgetLineIDs = DB::table('t_BudgetLines')->pluck('Id')->toArray();
        if (empty($budgetLineIDs)) {
            echo "❌ No budget lines found in t_BudgetLines. Please seed budget lines first.\n";
            return;
        }

        $activities = [
            ['ACT-001', 'Loan Origination', 'Processing and disbursement of new loan applications, including approval and documentation.', true],
            ['ACT-002', 'Customer Onboarding', 'Registration, verification, and activation of new customers in the core banking system.', true],
            ['ACT-003', 'Branch Operations Audit', 'Routine review of processes, controls, and compliance across all physical branches.', false],
            ['ACT-004', 'Deposit Mobilization Campaign', 'Promotional campaigns to increase customer savings and deposit accounts.', true],
            ['ACT-005', 'Compliance Training', 'Annual training programs to ensure staff adhere to banking regulations and policies.', false],
            ['ACT-006', 'ATM Maintenance', 'Scheduled servicing and troubleshooting of ATMs across all regions.', true],
            ['ACT-007', 'Credit Scoring Enhancement', 'Improving algorithms used to assess customer creditworthiness.', true],
            ['ACT-008', 'Digital Product Launch', 'Introduction of new digital services like mobile banking upgrades or app features.', true],
            ['ACT-009', 'Staff Performance Appraisal', 'Quarterly review of employee KPIs to support HR and strategic decisions.', false],
            ['ACT-010', 'Financial Forecasting', 'Projection of income, expenses, and profitability based on key budget drivers.', true],
        ];

        foreach ($activities as [$code, $name, $desc, $isActive]) {
            DB::table('t_BudgetActivityMaster')->insert([
                'ActivityCode'   => $code,
                'ActivityName'   => $name,
                'Description'    => $desc,
                'IsActive'       => $isActive,
                'BudgetLineID'   => $budgetLineIDs[array_rand($budgetLineIDs)],
                'CreatedBy'      => $userIds[array_rand($userIds)],
                'CreatedOn'      => $now->copy()->subDays(rand(5, 30)),
                'ModifiedBy'     => $userIds[array_rand($userIds)],
                'ModifiedOn'     => $now->copy()->subDays(rand(1, 4)),
                'DeletedBy'      => null,
                'DeletedOn'      => null,
            ]);
        }
    }
}
