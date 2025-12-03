<?php

namespace Database\Seeders;

use App\Enums\BusinessTypeEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyStatusEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Database\Seeder;

class ThirdPartyTenantSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $userId = User::query()->value('Id');
        $tpUserId = ThirdPartyUser::query()->value('Id');
        $countryId = Country::query()->value('Id');
        $tenantTypeId = CodeDetail::where('CodeID', 'TenantType')->value('ID');

        $parties = [
            ['ThirdPartyName' => 'Acme Developers Ltd', 'TradingName' => 'Acme Dev', 'Email' => 'acme@example.com', 'Phone' => '+254700000001'],
            ['ThirdPartyName' => 'Blue Ocean Properties', 'TradingName' => 'Blue Ocean', 'Email' => 'blueocean@example.com', 'Phone' => '+254700000002'],
            ['ThirdPartyName' => 'Cedar Homes Limited', 'TradingName' => 'Cedar Homes', 'Email' => 'cedar@example.com', 'Phone' => '+254700000003'],
            ['ThirdPartyName' => 'Delta Estates PLC', 'TradingName' => 'Delta Estates', 'Email' => 'delta@example.com', 'Phone' => '+254700000004'],
        ];

        foreach ($parties as $p) {
            $tp = ThirdParties::create([
                'ThirdPartyName' => $p['ThirdPartyName'],
                'TradingName' => $p['TradingName'],
                'BusinessType' => BusinessTypeEnum::Corporation->value,
                'RegistrationNumber' => (string)fake()->numerify('REG#######'),
                'TaxPIN' => (string)fake()->bothify('P#########'),
                'VATNumber' => (string)fake()->numerify('VAT#######'),
                'CountryId' => $countryId,
                'PhysicalAddress' => fake()->streetAddress(),
                'Email' => $p['Email'],
                'Phone' => $p['Phone'],
                'Website' => fake()->domainName(),
                'ApprovalStatus' => 'A',
                'Status' => 'A',
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'CreatedBy' => $tpUserId,
                'ModifiedBy' => $tpUserId,
            ]);

            // Ensure approved and active (in case model casts override on creating)
            $tp->ApprovalStatus = ThirdPartyApprovalStatusEnum::Approved;
            $tp->Status = ThirdPartyStatusEnum::Active;
            $tp->save();

            // Register as Property Tenant (link ThirdParty to Tenant Maintenance)
            PropertyNewTenant::create([
                'ThirdPartyId' => $tp->Id,
                'TenantType' => $tenantTypeId,
                'Remarks' => 'Seeded tenant for demo',
                'IsActive' => 1,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
                'CreatedBy' => $userId ?? 1,
                'ModifiedBy' => $userId ?? 1,
            ]);
        }
    }
}


