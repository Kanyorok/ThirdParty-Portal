<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ThirdPartiesBankDetailsSeeder extends Seeder
{
    public function run(): void
    {
        // $this->call(CurrencySeeder::class);

        /*     $thirdParty = ThirdParties::first();

             if (!$thirdParty) {
                 $thirdParty = ThirdParties::firstOrCreate(
                     ['Email' => 'default.bank.thirdparty@example.com'],
                     [
                         'ThirdPartyName' => 'Default Bank Third Party',
                         'TradingName' => 'Default Bank Trading',
                         'BusinessType' => 'Financial',
                         'RegistrationNumber' => 'BNK' . Str::random(5),
                         'Phone' => '9876543210',
                         'ThirdPartyType' => ThirdPartyTypeEnum::Tenant,
                         'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Approved,
                         'Status' => ThirdPartyStatusEnum::Active,
                         'CreatedBy' => 1,
                     ]
                 );
             }

             ThirdPartiesBankDetails::firstOrCreate(
                 ['AccountNumber' => '1234567890', 'ThirdPartyId' => $thirdParty->Id],
                 [
                     'BankName' => 'National Bank',
                     'Branch' => 'Main Branch',
                     'CurrencyId' => 1,
                     'SwiftCode' => 'NBKKENA',
                     'CreatedBy' => 1,
                 ]
             );

             ThirdPartiesBankDetails::firstOrCreate(
                 ['AccountNumber' => '0987654321', 'ThirdPartyId' => $thirdParty->Id],
                 [
                     'BankName' => 'Commercial Bank',
                     'Branch' => 'City Branch',
                     'CurrencyId' => 2,
                     'SwiftCode' => 'CBKKENA',
                     'CreatedBy' => 1,
                 ]
             );*/
    }
}
