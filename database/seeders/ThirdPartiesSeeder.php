<?php

namespace Database\Seeders;

use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyStatusEnum;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Database\Seeder;

// Import the ThirdParties model

// Import the ThirdPartyUser model to get a creator ID

class ThirdPartiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*$creatorUser = ThirdPartyUser::first();

        if (!$creatorUser) {
            $this->command->error('No ThirdPartyUser found. Please run ThirdPartyUserSeeder first.');
            return;
        }

        $creatorId = $creatorUser->Id;

        $commonData = [
            'Status' => ThirdPartyStatusEnum::Active,
            'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Pending,
            'CreatedBy' => $creatorId,
            'ModifiedBy' => $creatorId,
        ];

        ThirdParties::create(array_merge($commonData, [
            'ThirdPartyName' => 'Customer Solutions LLC',
            'TradingName' => 'CustomerSol',
            'BusinessType' => BusinessTypeEnum::SoleProprietorship,
            'RegistrationNumber' => 'CUST-001',
            'TaxPIN' => 'PIN123456789X',
            'VATNumber' => null,
            'Country' => 'US',
            'PhysicalAddress' => '100 Main Street, Anytown, USA',
            'Email' => 'contact@customersol.com',
            'Phone' => '+12125550100',
            'Website' => 'https://www.customersol.com',
            'ThirdPartyType' => ThirdPartyTypeEnum::Supplier,
            'IsPrequalified' => false,
        ]));

        ThirdParties::create(array_merge($commonData, [
            'ThirdPartyName' => 'Retail Innovations Ltd.',
            'TradingName' => 'RetailInnov',
            'BusinessType' => BusinessTypeEnum::Corporation,
            'RegistrationNumber' => 'CUST-002',
            'TaxPIN' => 'PIN987654321Y',
            'VATNumber' => 'VAT1122334455',
            'Country' => 'GB',
            'PhysicalAddress' => 'Retail Park, London, UK',
            'Email' => 'info@retailinnov.co.uk',
            'Phone' => '+442071234567',
            'Website' => 'https://www.retailinnov.co.uk',
            'ThirdPartyType' => ThirdPartyTypeEnum::Supplier,
            'IsPrequalified' => false,
        ]));

        $this->command->info('Third Parties seeded successfully!');*/
    }
}
