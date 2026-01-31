<?php

namespace App\Services\ThirdParties;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ThirdPartyRegistrationService
{
    public function registerThirdParty(array $data): ThirdPartyUser
    {
        return DB::transaction(function () use ($data) {
            $user = ThirdPartyUser::create([
                'FirstName' => $data['user_FirstName'],
                'LastName' => $data['user_LastName'],
                'Email' => strtolower($data['user_Email']),
                'Phone' => $data['user_Phone'],
                'Gender' => $data['user_Gender'] ?? null,
                'Password' => Hash::make($data['user_Password']),
                'IsActive' => true,
                'EmailVerifiedOn' => now(),
            ]);

            $this->completeProfile($user, $data);

            return $user;
        });
    }

    public function completeProfile(ThirdPartyUser $user, array $data): ThirdParties
    {
        if ($user->ThirdPartyId) {
            throw new \InvalidArgumentException('Profile already exists for this account.');
        }

        return DB::transaction(function () use ($user, $data) {
            $location = Locality::findOrFail($data['Location'] ?? $data['LocationId'] ?? $data['location_id']);
            $businessType = CodeDetail::findOrFail($data['BusinessType'] ?? $data['business_type_id']);

            $supplierService = SupplierService::create(
                name: $data['Name'] ?? $data['ThirdPartyName'] ?? $data['company_name'],
                tradingName: $data['TradingName'] ?? $data['trading_name'] ?? null,
                businessType: $businessType,
                registrationNumber: $data['RegistrationNumber'] ?? $data['registration_number'],
                taxPIN: $data['TaxPIN'] ?? $data['tax_pin'],
                vatNumber: $data['VATNumber'] ?? $data['vat_number'] ?? null,
                locationID: $location,
                physicalAddress: $data['PhysicalAddress'] ?? $data['physical_address'] ?? null,
                email: $data['Email'] ?? $data['company_email'] ?? $user->Email,
                phone: $data['Phone'] ?? $data['company_phone'] ?? $user->Phone,
                website: $data['Website'] ?? $data['website'] ?? null,
                status: null,
                extra: $data['extra'] ?? null,
                actor: $user
            );

            $user->update([
                'ThirdPartyId' => $supplierService->party->Id,
                'ModifiedBy' => $user->Id,
            ]);

            if (! empty($data['category_ids'])) {
                $supplierService->party->categories()->sync($data['category_ids']);
            } elseif (! empty($data['supplier_category_id'])) {
                $supplierService->party->categories()->sync([$data['supplier_category_id']]);
            }

            return $supplierService->party->fresh([
                'categories',
                'types',
                'supplierMaster',
            ]);
        });
    }

    public function getSuccessMessage(string $accountType): string
    {
        return strtolower($accountType) === 'supplier'
            ? 'Your profile has been submitted for approval. You will be notified once approved.'
            : 'Your profile has been created successfully!';
    }
}
