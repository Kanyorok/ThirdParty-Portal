<?php

namespace Database\Seeders;

use App\Models\ThirdParty\ThirdParties;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyStatusEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ThirdPartySeeder extends Seeder
{
    public function run(): void
    {
        ThirdParties::create([
            'ThirdPartyName' => 'ShakTech Ltd',
            'TradingName' => 'ShakTech',
            'BusinessType' => 'Private Company',
            'RegistrationNumber' => 'PVT-KEN-123456',
            'TaxPIN' => 'P012345678X',
            'VATNumber' => 'VAT0123456',
            'Country' => 'Kenya',
            'PhysicalAddress' => 'Alpha Towers, 3rd Floor, Nairobi',
            'Email' => 'info@shaktech.co.ke',
            'Phone' => '+254712345678',
            'Website' => 'https://shaktech.co.ke',
            'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Approved,
            'Status' => ThirdPartyStatusEnum::Active,
            'ThirdPartyType' => ThirdPartyTypeEnum::Supplier,
            'CreatedBy' => 1,
            'CreatedOn' => Carbon::now(),
        ]);
    }
}
