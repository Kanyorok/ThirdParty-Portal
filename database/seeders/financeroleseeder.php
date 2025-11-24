<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class FinanceRoleSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        DB::table('t_FinanceRoles')->insert([
            [
                'RoleName' => 'Debtor',
                'Description' => 'Owes us money (Clients, Tenants)',
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'DeletedBy' => 1,
            ],
            [
                'RoleName' => 'Creditor',
                'Description' => 'We owe them money (Suppliers, Landlords)',
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'DeletedBy' => 1,
            ],
            [
                'RoleName' => 'Both',
                'Description' => 'Can be both Debtor and Creditor',
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'DeletedBy' => 1,
            ],
            [
                'RoleName' => 'Neutral',
                'Description' => 'Non-financial party',
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'DeletedBy' => 1,
            ],
        ]);
    }
}
