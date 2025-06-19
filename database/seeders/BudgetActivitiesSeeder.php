<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch foreign key data
        $userIds = DB::table('t_Users')->pluck('Id')->toArray();
        if (empty($userIds)) throw new \Exception('❌ No users found in t_Users table.');

        $budgetLines = DB::table('t_BudgetLines')->select('Id', 'LineName')->get()->keyBy('LineName');
        if ($budgetLines->isEmpty()) throw new \Exception('❌ No budget lines found in t_BudgetLines.');

        $branchIds = DB::table('t_Branches')->pluck('Id')->toArray();
        if (empty($branchIds)) throw new \Exception('❌ No branches found in t_Branches.');

        $budgets = DB::table('t_Budgets')->pluck('Id')->toArray();
        if (empty($budgets)) throw new \Exception('❌ No budgets found in t_Budgets.');

        $activityMasters = DB::table('t_BudgetActivityMaster')->select('Id', 'ActivityName')->get()->keyBy('ActivityName');
        if ($activityMasters->isEmpty()) throw new \Exception('❌ No activities found in t_BudgetActivityMaster.');

        if (DB::table('t_BudgetActivities')->exists()) {
            echo "✅ t_BudgetActivities already seeded. Skipping...\n";
            return;
        }

        // Sample activities
        $activities = [
            [
                'ActivityName' => 'Micro Loan Origination',
                'LineName'     => 'Micro Loan Interest Income',
                'Description'  => 'Marketing campaign for micro loans',
                'AllocationType' => 'monthly',
                'FullAllocation' => null,
            ],
            [
                'ActivityName' => 'Payroll Processing',
                'LineName'     => 'Salaries and Wages',
                'Description'  => 'Payroll processing for staff',
                'AllocationType' => 'monthly',
                'FullAllocation' => null,
            ],
            [
                'ActivityName' => 'Digital Ad Campaigns',
                'LineName'     => 'Marketing Expenses',
                'Description'  => 'Running monthly marketing campaigns',
                'AllocationType' => 'full',
                'FullAllocation' => 30000.00,
            ],
            [
                'ActivityName' => 'Server Maintenance',
                'LineName'     => 'IT Infrastructure Costs',
                'Description'  => 'Weekly server and network maintenance',
                'AllocationType' => 'monthly',
                'FullAllocation' => null,
            ],
            [
                'ActivityName' => 'Wealth Client Advisory',
                'LineName'     => 'Wealth Management Fees',
                'Description'  => 'Client sessions for wealth portfolio advice',
                'AllocationType' => 'full',
                'FullAllocation' => 15000.00,
            ],
        ];

        foreach ($activities as $activity) {
            $line = $budgetLines[$activity['LineName']] ?? null;
            $activityMaster = $activityMasters[$activity['ActivityName']] ?? null;

            if (!$line || !$activityMaster) {
                echo "⚠️ Skipping: '{$activity['ActivityName']}' (Budget Line/Activity Master not found)\n";
                continue;
            }

            DB::table('t_BudgetActivities')->insert([
                'BudgetID'        => $budgets[array_rand($budgets)],
                'BudgetLineID'    => $line->Id,
                'BranchID'        => $branchIds[array_rand($branchIds)],
                'ActivityID'      => $activityMaster->Id,
                'Description'     => $activity['Description'],
                'AllocationType'  => $activity['AllocationType'],
                'FullAllocation'  => $activity['FullAllocation'],
                'CreatedBy'       => $userIds[array_rand($userIds)],
                'CreatedOn'       => $now->copy()->subDays(rand(10, 30)),
                'ModifiedBy'      => $userIds[array_rand($userIds)],
                'ModifiedOn'      => $now->copy()->subDays(rand(1, 9)),
                'DeletedBy'       => null,
                'DeletedOn'       => null,
            ]);
        }

        echo "✅ t_BudgetActivities seeded successfully with BudgetID.\n";
    }
}
