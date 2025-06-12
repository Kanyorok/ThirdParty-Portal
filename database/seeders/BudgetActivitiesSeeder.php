<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetActivitiesSeeder extends Seeder
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

        $budgetLineIds = DB::table('t_BudgetLines')->pluck('Id')->toArray();
        if (empty($budgetLineIds)) {
            throw new \Exception('No budget lines found in t_BudgetLines table. Please seed t_BudgetLines first.');
        }

        $branchIds = DB::table('t_Branches')->pluck('Id')->toArray();
        if (empty($branchIds)) {
            throw new \Exception('No branches found in t_Branches table. Please seed t_Branches first.');
        }

        // Check if table is empty to avoid duplicate insertions
        if (DB::table('t_BudgetActivities')->count() > 0) {
            return; // Skip seeding if data exists
        }

        $activities = [
            ['BudgetLineID' => $budgetLineIds[0], 'BranchID' => $branchIds[0 % count($branchIds)], 'ActivityName' => 'Loan Campaign Q1 2025', 'Description' => 'Marketing campaign for personal loans', 'AllocationType' => 'monthly', 'FullAllocation' => null],
            ['BudgetLineID' => $budgetLineIds[1], 'BranchID' => $branchIds[1 % count($branchIds)], 'ActivityName' => 'Fee Optimization 2025', 'Description' => 'Analysis of transaction fee structures', 'AllocationType' => 'full', 'FullAllocation' => 50000.00],
            ['BudgetLineID' => $budgetLineIds[2], 'BranchID' => $branchIds[2 % count($branchIds)], 'ActivityName' => 'Branch Renovation 2025', 'Description' => 'Renovation of main branch facilities', 'AllocationType' => 'full', 'FullAllocation' => 150000.00],
            ['BudgetLineID' => $budgetLineIds[3], 'BranchID' => $branchIds[3 % count($branchIds)], 'ActivityName' => 'Risk Assessment Q2 2025', 'Description' => null, 'AllocationType' => 'monthly', 'FullAllocation' => null],
            ['BudgetLineID' => $budgetLineIds[4], 'BranchID' => $branchIds[4 % count($branchIds)], 'ActivityName' => 'Funding Strategy 2025', 'Description' => 'Planning for deposit funding sources', 'AllocationType' => 'monthly', 'FullAllocation' => null],
            ['BudgetLineID' => $budgetLineIds[5], 'BranchID' => $branchIds[1 % count($branchIds)], 'ActivityName' => 'IT System Upgrade 2025', 'Description' => 'Upgrade of core banking systems', 'AllocationType' => 'full', 'FullAllocation' => 200000.00],
            ['BudgetLineID' => $budgetLineIds[6], 'BranchID' => $branchIds[2 % count($branchIds)], 'ActivityName' => 'Digital Marketing 2025', 'Description' => 'Online advertising for new accounts', 'AllocationType' => 'monthly', 'FullAllocation' => null],
            ['BudgetLineID' => $budgetLineIds[7], 'BranchID' => $branchIds[1 % count($branchIds)], 'ActivityName' => 'Wealth Seminar 2025', 'Description' => 'Client seminar for wealth management', 'AllocationType' => 'full', 'FullAllocation' => 25000.00],
            ['BudgetLineID' => $budgetLineIds[8], 'BranchID' => $branchIds[2 % count($branchIds)], 'ActivityName' => 'Compliance Training 2025', 'Description' => null, 'AllocationType' => 'monthly', 'FullAllocation' => null],
            ['BudgetLineID' => $budgetLineIds[9], 'BranchID' => $branchIds[0 % count($branchIds)], 'ActivityName' => 'New Branch Setup 2025', 'Description' => 'Setup costs for new branch opening', 'AllocationType' => 'full', 'FullAllocation' => 300000.00],
        ];

        foreach ($activities as $activity) {
            DB::table('t_BudgetActivities')->insert([
                'BudgetLineID' => $activity['BudgetLineID'],
                'BranchID' => $activity['BranchID'],
                'ActivityName' => $activity['ActivityName'],
                'Description' => $activity['Description'],
                'AllocationType' => $activity['AllocationType'],
                'FullAllocation' => $activity['FullAllocation'],
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
