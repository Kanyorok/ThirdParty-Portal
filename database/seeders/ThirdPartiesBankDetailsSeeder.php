<?php

namespace Database\Seeders;

use App\Models\ThirdPartiesBankDetails;
use App\Models\ThirdParty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdPartyStatusEnum;

class ThirdPartiesBankDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $thirdParty = ThirdParty::first();

        if (!$thirdParty) {
            $thirdParty = ThirdParty::firstOrCreate(
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
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ]
            );
        }

        ThirdPartiesBankDetails::firstOrCreate(
            ['AccountNumber' => '1234567890'],
            [
                'ThirdPartyID' => $thirdParty->Id,
                'BankName' => 'National Bank',
                'Branch' => 'Main Branch',
                'Currency' => 'KES',
                'SwiftCode' => 'NBKKENA',
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]
        );

        ThirdPartiesBankDetails::firstOrCreate(
            ['AccountNumber' => '0987654321'],
            [
                'ThirdPartyID' => $thirdParty->Id,
                'BankName' => 'Commercial Bank',
                'Branch' => 'City Branch',
                'Currency' => 'USD',
                'SwiftCode' => 'CBKKENA',
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]
        );
    }
}
