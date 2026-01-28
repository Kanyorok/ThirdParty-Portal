<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetDriversMasterSeeder extends Seeder
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

        $driverTypeIds = DB::table('t_BudgetDrivers')->pluck('Id', 'DriverName')->toArray();
        if (empty($driverTypeIds)) {
            throw new \Exception('No drivers found in t_BudgetDrivers table. Please seed t_BudgetDrivers first.');
        }

        $driversMaster = [
            ['DriverName' => 'Personal Loan Volume', 'DriverTypeID' => $driverTypeIds['Loan Volume'], 'IsActive' => true, 'Frequency' => 'Monthly'],
            ['DriverName' => 'Mortgage Interest Rate', 'DriverTypeID' => $driverTypeIds['Interest Rate'], 'IsActive' => true, 'Frequency' => 'Quarterly'],
            ['DriverName' => 'Transaction Count', 'DriverTypeID' => $driverTypeIds['Transaction Volume'], 'IsActive' => true, 'Frequency' => 'Monthly'],
            ['DriverName' => 'Savings Deposit Balance', 'DriverTypeID' => $driverTypeIds['Deposit Balance'], 'IsActive' => true, 'Frequency' => 'Monthly'],
            ['DriverName' => 'Loan Default Rate', 'DriverTypeID' => $driverTypeIds['Default Rate'], 'IsActive' => true, 'Frequency' => 'Quarterly'],
            ['DriverName' => 'Funding Cost Rate', 'DriverTypeID' => $driverTypeIds['Cost of Funding'], 'IsActive' => true, 'Frequency' => 'Monthly'],
            ['DriverName' => 'Customer Acquisition', 'DriverTypeID' => $driverTypeIds['Customer Acquisition Cost'], 'IsActive' => false, 'Frequency' => 'Annually'],
            ['DriverName' => 'Branch Operations Cost', 'DriverTypeID' => $driverTypeIds['Branch Operating Cost'], 'IsActive' => true, 'Frequency' => 'Monthly'],
            ['DriverName' => 'Net Interest Margin Rate', 'DriverTypeID' => $driverTypeIds['Net Interest Margin'], 'IsActive' => true, 'Frequency' => 'Quarterly'],
            ['DriverName' => 'Compliance Cost', 'DriverTypeID' => $driverTypeIds['Regulatory Compliance Cost'], 'IsActive' => false, 'Frequency' => 'Annually'],
        ];

        foreach ($driversMaster as $driver) {
            DB::table('t_BudgetDriversMaster')->insert([
                'DriverName' => $driver['DriverName'],
                'DriverTypeID' => $driver['DriverTypeID'],
                'IsActive' => $driver['IsActive'],
                'Frequency' => $driver['Frequency'],
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
