<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankBranchesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_BankBranches')->insert([
            [
                'BankID'     => 1, // KCB
                'BranchName' => 'KCB Nairobi Branch',
                'BranchCode' => '0001',
                'Address1'   => 'Kenyatta Avenue',
                'CityID'     => 1,
                'CountryID'  => 110,
                'Phone'      => '+254711111111',
                'EmailID'    => 'nairobi@kcb.com',
                'IsActive'   => 1,
                'CreatedOn'  => now(),
            ],
            [
                'BankID'     => 2, // Equity
                'BranchName' => 'Equity Westlands Branch',
                'BranchCode' => '0068-01',
                'Address1'   => 'Westlands, Nairobi',
                'CityID'     => 1,
                'CountryID'  => 110,
                'Phone'      => '+254763111111',
                'EmailID'    => 'westlands@equity.com',
                'IsActive'   => 1,
                'CreatedOn'  => now(),
            ],
        ]);
    }
}