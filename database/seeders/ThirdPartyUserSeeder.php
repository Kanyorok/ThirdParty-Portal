<?php

namespace Database\Seeders;

use App\Enums\Employee\GenderEnum;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdPartyStatusEnum;

class ThirdPartyUserSeeder extends Seeder
{
    public function run(): void
    {
        $approvedThirdParty = ThirdParties::firstOrCreate(
            ['Email' => 'default.dcp@example.com'],
            [
                'ThirdPartyName' => 'DCP',
                'TradingName' => 'Sauti Ya Ground',
                'BusinessType' => 'Service',
                'RegistrationNumber' => 'REG' . Str::random(5),
                'Phone' => '1234567890',
                'ThirdPartyType' => ThirdPartyTypeEnum::Supplier,
                'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Approved,
                'Status' => ThirdPartyStatusEnum::Active,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]
        );

        $pendingThirdParty = ThirdParties::firstOrCreate(
            ['Email' => 'pending.thirdparty@example.com'],
            [
                'ThirdPartyName' => 'Pending Corp',
                'TradingName' => 'Pending Trading',
                'BusinessType' => 'Consulting',
                'RegistrationNumber' => 'PEND' . Str::random(5),
                'Phone' => '0987654321',
                'ThirdPartyType' => ThirdPartyTypeEnum::Supplier,
                'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Pending,
                'Status' => ThirdPartyStatusEnum::Active,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]
        );


        ThirdPartyUser::firstOrCreate(
            ['Email' => 'john.kim@example.com'],
            [
                'UserID' => Str::uuid(),
                'FirstName' => 'John',
                'LastName' => 'Kim',
                'Phone' => '0712345678',
                'ImageId' => null,
                'Gender' => GenderEnum::Male,
                'ThirdPartyId' => $approvedThirdParty->Id,
                'IsActive' => true,
                'Password' => Hash::make('password'),
                'EmailVerifiedOn' => now(),
                'CreatedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]
        );

        ThirdPartyUser::firstOrCreate(
            ['Email' => 'jane.smith@example.com'],
            [
                'UserID' => Str::uuid(),
                'FirstName' => 'Jane',
                'LastName' => 'Smith',
                'Phone' => '0787654321',
                'ImageId' => null,
                'Gender' => GenderEnum::Female,
                'ThirdPartyId' => $approvedThirdParty->Id,
                'IsActive' => true,
                'Password' => Hash::make('password'),
                'EmailVerifiedOn' => now(),
                'CreatedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]
        );

        ThirdPartyUser::firstOrCreate(
            ['Email' => 'pending.user@example.com'],
            [
                'UserID' => Str::uuid(),
                'FirstName' => 'Pending',
                'LastName' => 'User',
                'Phone' => '0777777777',
                'ImageId' => null,
                'Gender' => GenderEnum::Male,
                'ThirdPartyId' => $pendingThirdParty->Id,
                'IsActive' => true,
                'Password' => Hash::make('password'),
                'EmailVerifiedOn' => now(),
                'CreatedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedBy' => 1,
                'ModifiedOn' => now(),
            ]
        );
    }
}
