<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemBankSettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_SystemBankSetting')->insert([
            'BankName'      => 'TaskBridge Bank',
            'ShortName'     => 'TBB',
            'BankCode'      => '123',
            'SwiftCode'     => 'TBBLKENX',
            'ClearingCode'  => '001',
            'Address1'      => 'Head Office, Nairobi',
            'CityID'        => 1,
            'CountryID'     => 110, // Kenya
            'Phone1'        => '+254700000000',
            'EmailID'       => 'info@taskbridgebank.com',
            'Website'       => 'https://www.taskbridgebank.com',
            'BankRegNumber' => 'C123456',
            'IsActive'      => 1,
            'CreatedOn'     => now(),
        ]);
    }
}