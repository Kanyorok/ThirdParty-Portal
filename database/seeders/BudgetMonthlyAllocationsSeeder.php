<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetMonthlyAllocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            echo "❌ No users found in t_Users table.\n";

            return;
        }

        $activityMap = [
            'Loan Origination' => [
                'BudgetLineID' => 1,
                'BranchID' => 1,
                'Description' => 'Processing and disbursement of new loan applications',
                'AllocationType' => 'monthly',
                'FullAllocation' => 560000,
                'Allocations' => [100000.00, 50000.00, 0.00, 150000.00, 50000.00, 0.00, 100000.00, 0.00, 50000.00, 0.00, 0.00, 60000.00],
            ],
            'Financial Forecasting' => [
                'BudgetLineID' => 5,
                'BranchID' => 5,
                'Description' => 'Projection of income and expenses',
                'AllocationType' => 'monthly',
                'FullAllocation' => 1300000.01,
                'Allocations' => array_fill(0, 12, round(1300000.01 / 12, 2)),
            ],
            'Digital Product Launch' => [
                'BudgetLineID' => 7,
                'BranchID' => 3,
                'Description' => 'Launch of mobile banking and app features',
                'AllocationType' => 'monthly',
                'FullAllocation' => 900899.09,
                'Allocations' => [200000.00, 0.00, 150000.00, 100000.00, 0.00, 200000.00, 0.00, 150000.00, 0.00, 50000.00, 0.00, 50899.09],
            ],
        ];

        foreach ($activityMap as $activityName => $config) {
            // Get the ActivityID from the master table
            $activityMaster = DB::table('t_BudgetActivityMaster')->where('ActivityName', $activityName)->first();
            if (! $activityMaster) {
                echo "⚠️ Skipping: '{$activityName}' not found in t_BudgetActivityMaster.\n";

                continue;
            }

            // Insert into t_BudgetActivities
            $budgetActivityID = DB::table('t_BudgetActivities')->insertGetId([
                'BudgetLineID' => $config['BudgetLineID'],
                'BranchID' => $config['BranchID'],
                'ActivityID' => $activityMaster->Id,
                'Description' => $config['Description'],
                'AllocationType' => $config['AllocationType'],
                'FullAllocation' => $config['FullAllocation'],
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => $now->copy()->subDays(rand(5, 20)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => $now->copy()->subDays(rand(1, 4)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);

            // Insert into t_BudgetMonthlyAllocations
            foreach ($config['Allocations'] as $month => $amount) {
                DB::table('t_BudgetMonthlyAllocations')->insert([
                    'BudgetActivityID' => $budgetActivityID,
                    'Month' => $month + 1,
                    'Amount' => $amount,
                    'CreatedBy' => $userIds[array_rand($userIds)],
                    'CreatedOn' => $now->copy()->subDays(rand(1, 10)),
                    'ModifiedBy' => $userIds[array_rand($userIds)],
                    'ModifiedOn' => $now->copy()->subDays(rand(0, 5)),
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                ]);
            }

            echo "✅ Seeded monthly allocations for activity: {$activityName}\n";
        }
    }
}
