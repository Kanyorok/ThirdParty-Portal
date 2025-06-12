<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetMonthlyAllocationsSeeder extends Seeder
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

        $activities = [
            ['BudgetLineID' => 1, 'BranchID' => 1, 'ActivityName' => 'Loan Campaign Q1', 'Description' => 'Marketing campaign for personal loans', 'AllocationType' => 'monthly', 'FullAllocation' => 560000],
            ['BudgetLineID' => 2, 'BranchID' => 2, 'ActivityName' => 'Fee Optimization', 'Description' => 'Analysis of transaction fee structures', 'AllocationType' => 'full', 'FullAllocation' => 50000.00],
            ['BudgetLineID' => 5, 'BranchID' => 5, 'ActivityName' => 'Funding Strategy', 'Description' => 'Planning for deposit funding sources', 'AllocationType' => 'monthly', 'FullAllocation' => 1300000.01],
            ['BudgetLineID' => 6, 'BranchID' => 2, 'ActivityName' => 'IT System Upgrade', 'Description' => 'Upgrade of core banking systems', 'AllocationType' => 'full', 'FullAllocation' => 200000.00],
            ['BudgetLineID' => 7, 'BranchID' => 3, 'ActivityName' => 'Digital Marketing', 'Description' => 'Online advertising for new accounts', 'AllocationType' => 'monthly', 'FullAllocation' => 900899.09],
            ['BudgetLineID' => 8, 'BranchID' => 2, 'ActivityName' => 'Wealth Seminar', 'Description' => 'Client seminar for wealth management', 'AllocationType' => 'full', 'FullAllocation' => 25000000.00],
        ];

        // Insert activities into t_BudgetActivities to get their IDs
        $activityIds = [];
        foreach ($activities as $index => $activity) {
            $activityId = DB::table('t_BudgetActivities')->insertGetId([
                'BudgetLineID' => $activity['BudgetLineID'],
                'BranchID' => $activity['BranchID'],
                'ActivityName' => $activity['ActivityName'],
                'Description' => $activity['Description'],
                'AllocationType' => $activity['AllocationType'],
                'FullAllocation' => is_numeric($activity['FullAllocation']) ? $activity['FullAllocation'] : null,
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => Carbon::now()->subDays(rand(1, 30)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => Carbon::now()->subDays(rand(0, 10)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
            $activityIds[$activity['ActivityName']] = $activityId;
        }

        // Monthly allocations for 'monthly' allocation activities with numeric FullAllocation
        $monthlyAllocations = [
            // Loan Campaign Q1: Uneven distribution, some months 0.00
            ['ActivityName' => 'Loan Campaign Q1', 'Allocations' => [100000.00, 50000.00, 0.00, 150000.00, 50000.00, 0.00, 100000.00, 0.00, 50000.00, 0.00, 0.00, 60000.00]],
            // Funding Strategy: Equal distribution
            ['ActivityName' => 'Funding Strategy', 'Allocations' => array_fill(0, 12, 1300000.01 / 12)],
            // Digital Marketing: Uneven distribution, some months 0.00
            ['ActivityName' => 'Digital Marketing', 'Allocations' => [200000.00, 0.00, 150000.00, 100000.00, 0.00, 200000.00, 0.00, 150000.00, 0.00, 50000.00, 0.00, 50899.09]],
        ];

        foreach ($monthlyAllocations as $allocation) {
            $activityName = $allocation['ActivityName'];
            if (!isset($activityIds[$activityName])) {
                continue;
            }
            $budgetActivityID = $activityIds[$activityName];
            foreach ($allocation['Allocations'] as $month => $amount) {
                DB::table('t_BudgetMonthlyAllocations')->insert([
                    'BudgetActivityID' => $budgetActivityID,
                    'Month' => $month + 1,
                    'Amount' => $amount,
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
