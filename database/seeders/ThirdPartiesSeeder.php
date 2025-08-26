<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThirdParty\ThirdParties; // Import the ThirdParties model
use App\Models\ThirdParty\ThirdPartyUser; // Import the ThirdPartyUser model to get a creator ID
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;

class ThirdPartiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creatorUser = ThirdPartyUser::first();

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

        $this->command->info('Third Parties seeded successfully!');
    }
}
