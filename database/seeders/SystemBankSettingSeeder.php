<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemBankSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Use first country as default if present
        $countryId = DB::table('t_Countries')->min('Id');

        $exists = DB::table('t_SystemBankSetting')->count();
        if ($exists == 0) {
            DB::table('t_SystemBankSetting')->insert([
                'BankName' => 'Organization Default Bank',
                'ShortName' => 'ORG',
                'BankCode' => 'ORG-001',
                'SwiftCode' => 'ORGXXXX',
                'ClearingCode' => '0001',
                'Address1' => 'Head Office',
                'CityID' => null,
                'CountryID' => $countryId,
                'ZipCode' => '00000',
                'Phone1' => '+000-000-000',
                'EmailID' => 'info@example.com',
                'Website' => 'https://example.com',
                'BankRegNumber' => 'REG-0001',
                'AuditedDate' => now()->toDateString(),
                'BankTypeID' => null,
                'ImageID' => null,
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ]);
        }
    }
}

