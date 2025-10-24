<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanksSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_Banks')->insert([
            [
                'BankName' => 'Kenya Commercial Bank',
                'ShortName' => 'KCB',
                'BankCode' => '01',
                'SwiftCode' => 'KCBLKENX',
                'ClearingCode' => '0001',
                'CountryID' => 110,
                'Phone' => '+254711000000',
                'Website' => 'https://www.kcbgroup.com',
                'IsActive' => 1,
                'CreatedOn' => now(),
            ],
            [
                'BankName' => 'Equity Bank',
                'ShortName' => 'EQTY',
                'BankCode' => '68',
                'SwiftCode' => 'EQBLKENX',
                'ClearingCode' => '0068',
                'CountryID' => 110,
                'Phone' => '+254763000000',
                'Website' => 'https://www.equitygroupholdings.com',
                'IsActive' => 1,
                'CreatedOn' => now(),
            ],
        ]);
    }
}
