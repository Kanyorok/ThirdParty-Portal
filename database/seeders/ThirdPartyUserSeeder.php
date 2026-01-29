<?php

namespace Database\Seeders;

use App\Enums\BusinessTypeEnum;
use App\Enums\Employee\GenderEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ThirdPartyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $thirdParty = ThirdParties::firstOrCreate(
            [
                'ThirdPartyName' => 'Seeder Company Inc.',
                'RegistrationNumber' => 'SEED-COMP-001',
            ],
            [
                'TradingName' => 'SeederCo',
                'BusinessType' => BusinessTypeEnum::SoleProprietorship,
                'TaxPIN' => 'P000000000S',
                'VATNumber' => null,
                'Country' => 'US',
                'PhysicalAddress' => '123 Seeder Lane, Seed City',
                'Email' => 'company@seeder.com',
                'Phone' => '+15551234567',
                'Website' => 'https://www.seedercompany.com',
                'Status' => \App\Enums\ThirdParty\ThirdPartyStatusEnum::Active,
                'ThirdPartyType' => ThirdPartyTypeEnum::Supplier,
                'IsPrequalified' => false,
                'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Approved,
                'CreatedBy' => null,
                'ModifiedBy' => null,
            ]
        );

        if (! $thirdParty) {
            $this->command->error('Failed to create or retrieve ThirdParty for ThirdPartyUser seeder.');

            return;
        }

        ThirdPartyUser::firstOrCreate(
            ['Email' => 'testuser@example.com'],
            [
                'FirstName' => 'John',
                'LastName' => 'Doe',
                'Phone' => '+1234567890',
                'ImageId' => null,
                'Gender' => GenderEnum::Male,
                'ThirdPartyId' => $thirdParty->Id,
                'IsActive' => true,
                'CreatedBy' => $thirdParty->CreatedBy,
                'ModifiedBy' => $thirdParty->ModifiedBy,
                'Password' => Hash::make('password'),
                'EmailVerifiedOn' => Carbon::now(),
            ]
        );

        ThirdPartyUser::firstOrCreate(
            ['Email' => 'jane.smith@example.com'],
            [
                'FirstName' => 'Jane',
                'LastName' => 'Smith',
                'Phone' => '+1987654321',
                'ImageId' => null,
                'Gender' => GenderEnum::Female,
                'ThirdPartyId' => $thirdParty->Id,
                'IsActive' => true,
                'CreatedBy' => $thirdParty->CreatedBy,
                'ModifiedBy' => $thirdParty->ModifiedBy,
                'Password' => Hash::make('password'),
                'EmailVerifiedOn' => Carbon::now(),
            ]
        );

        $this->command->info('ThirdPartyUsers seeded successfully!');
    }
}
