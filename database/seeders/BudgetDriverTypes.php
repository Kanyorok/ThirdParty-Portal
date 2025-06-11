<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            throw new \Exception('No users found in t_Users table. Please seed t_Users first.');
        }

        // Sample budget drivers for a banking system
        $drivers = [
            ['DriverName' => 'Financial', 'IsActive' => true],
            ['DriverName' => 'Operational', 'IsActive' => true],
            ['DriverName' => 'Loan Volume', 'IsActive' => false],
            ['DriverName' => 'Interest Rate', 'IsActive' => true],
            ['DriverName' => 'Transaction Volume', 'IsActive' => true],
            ['DriverName' => 'Deposit Balance', 'IsActive' => true],
            ['DriverName' => 'Default Rate', 'IsActive' => true],
            ['DriverName' => 'Cost of Funding', 'IsActive' => true],
            ['DriverName' => 'Customer Acquisition Cost', 'IsActive' => false],
            ['DriverName' => 'Branch Operating Cost', 'IsActive' => true],
            ['DriverName' => 'Net Interest Margin', 'IsActive' => true],
            ['DriverName' => 'Regulatory Compliance Cost', 'IsActive' => false],
        ];

        foreach ($drivers as $driver) {
            DB::table('t_BudgetDrivers')->insert([
                'DriverName' => $driver['DriverName'],
                'IsActive' => $driver['IsActive'],
                'CreatedBy' => $userIds[array_rand($userIds)], // Random user ID
                'CreatedOn' => Carbon::now()->subDays(rand(1, 30)), // Random date within last 30 days
                'ModifiedBy' => $userIds[array_rand($userIds)], // Random user ID
                'ModifiedOn' => Carbon::now()->subDays(rand(0, 10)), // Recent modification
                'DeletedBy' => null, // No soft deletes for now
                'DeletedOn' => null, // No soft deletes
            ]);
        }
    }
}
