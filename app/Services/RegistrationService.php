<?php

namespace App\Services;

use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\ThirdParties\SupplierService;
use App\Services\ThirdParties\TenantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    public function createInitialAccount(array $data): ThirdPartyUser
    {
        return ThirdPartyUser::create([
            'FirstName' => $data['FirstName'],
            'LastName'  => $data['LastName'],
            'Email'     => $data['Email'],
            'Phone'     => $data['Phone'],
            'Password'  => Hash::make($data['Password']),
            'Status'    => 1,
        ]);
    }

    public function registerThirdPartyDetails(ThirdPartyUser $user, array $data): ThirdParties
    {
        return DB::transaction(function () use ($user, $data) {
            $thirdParty = ThirdParties::create([
                'ThirdPartyName'     => $data['ThirdPartyName'],
                'TradingName'        => $data['TradingName'] ?? $data['ThirdPartyName'],
                'RegistrationNumber' => $data['RegistrationNumber'],
                'TaxPIN'             => $data['TaxPIN'],
                'BusinessType'       => $data['BusinessType'],
                'CountryId'          => $data['CountryId'],
                'LocationId'         => $data['LocationId'] ?? 1,
                'PhysicalAddress'    => $data['PhysicalAddress'],
                'Website'            => $data['Website'] ?? null,
                'Email'              => $user->Email,
                'Phone'              => $user->Phone,
                'Status'             => 1,
                // Use lowercase ->id if that is your DB standard, or check your model
                'CreatedBy'          => $user->id ?? $user->Id,
            ]);

            $accountType = $data['accountType'] ?? 'supplier';
            $this->attachAccountType($thirdParty, $accountType, $user, $data);

            $user->update([
                'ThirdPartyId' => $thirdParty->id ?? $thirdParty->Id,
                'ModifiedBy'   => $user->id ?? $user->Id
            ]);

            return $thirdParty->fresh(['types', 'businessType']);
        });
    }

    protected function attachAccountType(ThirdParties $thirdParty, string $accountType, ThirdPartyUser $user, array $data): void
    {
        switch ($accountType) {
            case 'supplier':
                SupplierService::createFromParty($thirdParty, $user);

                if (!empty($data['supplierCategories'])) {
                    $thirdParty->categories()->sync($data['supplierCategories']);
                }
                break;

            case 'tenant':
                TenantService::createFromParty($thirdParty, $user);

                if (!empty($data['tenantRemarks'])) {
                    $thirdParty->tenantProfile()->updateOrCreate(
                        ['ThirdPartyId' => $thirdParty->Id],
                        [
                            'Remarks' => $data['tenantRemarks'],
                            'ModifiedBy' => $user->Id,
                        ]
                    );
                }
                break;

            case 'customer':
                $this->attachCustomerType($thirdParty, $user, $data);
                break;
        }
    }

    protected function attachCustomerType(ThirdParties $thirdParty, ThirdPartyUser $user, array $data): void
    {
        $thirdParty->types()->syncWithoutDetaching([6 => [
            'PartyType' => 'ThirdPartyId',
            'PartyID'   => $thirdParty->Id,
            'CreatedBy' => $user->Id,
            'CreatedOn' => now()
        ]]);

        $thirdParty->customerProfile()->updateOrCreate(
            ['ThirdPartyId' => $thirdParty->Id],
            [
                'Gender' => $data['Gender'] ?? null,
                'MaritalStatus' => $data['MaritalStatus'] ?? null,
                'Occupation' => $data['Occupation'] ?? null,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]
        );
    }
}
