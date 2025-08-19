<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class LegalLoanSecuritiesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            [
                'SecurityType' => 'Personal Guarantee',
                'OwnerName' => 'John Doe',
                'OwnerIDNumber' => 'ID12345678',
                'LoanAccountNumber' => 'LN-001',
                'Value' => 0.00,
                'Institution' => 'ABC Bank',
                'RegistrationDetails' => 'Guarantee Form #001',
                'Locations' => 'Head Office',
                'SecurityStatus' => 'Active',
                'Remarks' => 'Director’s personal guarantee',
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'SecurityType' => 'Securities Pledge',
                'OwnerName' => 'Jane Smith',
                'OwnerIDNumber' => 'ID87654321',
                'LoanAccountNumber' => 'LN-002',
                'Value' => 500000.00,
                'Institution' => 'XYZ Bank',
                'RegistrationDetails' => 'Pledge Agreement #P-2025',
                'Locations' => 'Central Depository',
                'SecurityStatus' => 'Active',
                'Remarks' => 'Pledge of 5,000 company shares',
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ]
        ];

        DB::table('t_LegalLoanSecurities')->insert($data);
    }
}
