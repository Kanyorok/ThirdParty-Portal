<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThirdParies\Supplier;

// Use the Supplier model
use App\Models\ThirdParty\ThirdPartyUser;

// Import the ThirdPartyUser model
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creatorUser = ThirdPartyUser::first();

        if (!$creatorUser) {
            $this->command->error('No ThirdPartyUser found. Run ThirdPartyUserSeeder first.');
            return;
        }

        $creatorId = $creatorUser->Id;

        $commonSupplierData = [
            'ThirdPartyType' => ThirdPartyTypeEnum::Supplier,
            'IsPrequalified' => true,
            'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Approved,
            'CreatedBy' => $creatorId,
            'ModifiedBy' => $creatorId,
            'Status' => \App\Enums\ThirdPartyStatusEnum::Active,
        ];

        Supplier::create(array_merge($commonSupplierData, [
            'ThirdPartyName' => 'Advanced Tech Solutions',
            'TradingName' => 'ATS',
            'BusinessType' => BusinessTypeEnum::SoleProprietorship,
            'RegistrationNumber' => 'SUP-ATS-001',
            'TaxPIN' => 'PIN123456789S',
            'VATNumber' => 'VAT0011223344',
            'Country' => 'KE',
            'PhysicalAddress' => 'Tech Park, Nairobi, Kenya',
            'Email' => 'contact@ats.com',
            'Phone' => '+254701234567',
            'Website' => 'https://www.ats.com',
        ]));

        Supplier::create(array_merge($commonSupplierData, [
            'ThirdPartyName' => 'Green Energy Providers',
            'TradingName' => 'GEP',
            'BusinessType' => BusinessTypeEnum::Corporation,
            'RegistrationNumber' => 'SUP-GEP-002',
            'TaxPIN' => 'PIN987654321E',
            'VATNumber' => 'VAT5566778899',
            'Country' => 'TZ',
            'PhysicalAddress' => 'Solar Farm Road, Arusha, Tanzania',
            'Email' => 'info@greenenergy.co.tz',
            'Phone' => '+255765432109',
            'Website' => 'https://www.greenenergy.co.tz',
        ]));

        Supplier::create(array_merge($commonSupplierData, [
            'ThirdPartyName' => 'Logistics Master Ltd.',
            'TradingName' => 'LogiMaster',
            'BusinessType' => BusinessTypeEnum::LimitedLiabilityCompany,
            'RegistrationNumber' => 'SUP-LML-003',
            'TaxPIN' => 'PIN112233445L',
            'VATNumber' => 'VAT1020304050',
            'Country' => 'UG',
            'PhysicalAddress' => 'Warehouse District, Kampala, Uganda',
            'Email' => 'operations@logimaster.ug',
            'Phone' => '+256778901234',
            'Website' => 'https://www.logimaster.ug',
        ]));

        $this->command->info('Prequalified and approved Suppliers seeded successfully!');
    }
}
