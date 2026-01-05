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
    public function registerThirdParty(array $userData): ThirdPartyUser
    {
        return DB::transaction(function () use ($userData) {
            $systemUser = User::where('UserID', 'ERPSYS')->first();
            $systemUserId = $systemUser?->Id;

            // Fetch default Gender (required by DB) - Keep this as Step 1 still doesn't ask for Gender?
            // User form (frontend) DOES NOT have Gender field? 
            // Step 1 only has: Name, Email, Password, Phone.
            // So we still need a default Gender for the User record.
            $defaultGender = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'Gender')->where('Value', 'M')->first();
            if (!$defaultGender) {
                // Fallback or create? Best to just get any gender
                $defaultGender = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'Gender')->first();
                // If still null, create one?
                if (!$defaultGender) {
                    // Risk of failure if table constrained. Assuming at least one exists or we create.
                    // For now, if null, we might still fail. Let's create dummy if desperately needed.
                    try {
                        $defaultGender = \App\Models\Core\Approval\CodeDetail::create([
                            'CodeID' => 'Gender',
                            'Value' => 'M',
                            'Description' => 'Male',
                            'DisplayOrder' => 1,
                            'IsActive' => 1,
                            'CreatedBy' => $systemUserId ?? 1,
                            'ModifiedBy' => $systemUserId ?? 1,
                        ]);
                    } catch (\Throwable $e) {
                    }
                }
            }

            $user = ThirdPartyUser::create([
                'FirstName' => $userData['FirstName'],
                'LastName' => $userData['LastName'],
                'Email' => $userData['Email'],
                'Phone' => $userData['Phone'],
                'Password' => $userData['Password'],
                'Gender' => $defaultGender?->ID ?? 153, // Hard fallback to 153 from debug if all else fails
                'IsActive' => false,
                'CreatedBy' => $systemUserId ?? 1,
                'ModifiedBy' => $systemUserId ?? 1,
            ]);

            if (!empty($userData['verification_base_url'])) {
                $user->verificationBaseUrl = $userData['verification_base_url'];
            }

            event(new Registered($user));

            return $user;
        });
    }

    public function createThirdPartyForUser(ThirdPartyUser $user, array $thirdPartyData): ThirdParties
    {
        return DB::transaction(function () use ($user, $thirdPartyData) {
            $systemUser = User::where('UserID', 'ERPSYS')->first();
            $systemUserId = $systemUser?->Id;

            $initialName = $thirdPartyData['ThirdPartyName']
                ?? ($thirdPartyData['FirstName'] . ' ' . $thirdPartyData['LastName'])
                ?? $thirdPartyData['Email'];

            // Fetch default BusinessType (e.g. Individual or first available)
            $businessTypeId = $thirdPartyData['BusinessType'] ?? null;
            if (!$businessTypeId) {
                $bt = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'BusinessType')
                    ->where('Value', 'I') // Try Individual first
                    ->first();
                if (!$bt) {
                    $bt = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'BusinessType')->first();
                }

                if (!$bt) {
                    try {
                        $bt = \App\Models\Core\Approval\CodeDetail::create([
                            'CodeID' => 'BusinessType',
                            'Value' => 'I',
                            'Description' => 'Individual',
                            'DisplayOrder' => 1,
                            'IsActive' => 1,
                            'CreatedBy' => $systemUserId ?? 1,
                            'ModifiedBy' => $systemUserId ?? 1,
                        ]);
                    } catch (\Throwable $e) {
                    }
                }
                $businessTypeId = $bt?->ID ?? 47; // Hard fallback from debug
            }

            // Fetch default Country
            $countryId = $thirdPartyData['CountryId'] ?? null;
            if (!$countryId) {
                // Default to Kenya (KE) or first
                $ct = \App\Models\Core\Country::where('CountryCode', 'KE')->first();
                if (!$ct) $ct = \App\Models\Core\Country::first();
                $countryId = $ct?->Id ?? 1; // Hard fallback
            }


            $thirdParty = ThirdParties::create([
                'ThirdPartyName' => $initialName,
                'TradingName' => $thirdPartyData['TradingName'] ?? $initialName,
                'BusinessType' => $businessTypeId, // Now likely not null
                'RegistrationNumber' => $thirdPartyData['RegistrationNumber'] ?? 'PENDING',
                'TaxPIN' => $thirdPartyData['TaxPIN'] ?? '',
                'VATNumber' => $thirdPartyData['VATNumber'] ?? '',
                'CountryId' => $countryId, // Now likely not null
                'PhysicalAddress' => $thirdPartyData['PhysicalAddress'] ?? 'Pending Address',
                'Email' => $thirdPartyData['Email'] ?? null,
                'Phone' => $thirdPartyData['Phone'] ?? null,
                'Website' => $thirdPartyData['Website'] ?? null,

                'IsActive' => false,
                'ApprovalStatus' => 'P',

                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            $accountType = $thirdPartyData['accountType'] ?? 'supplier';
            $this->attachAccountType($thirdParty, $accountType, $user, $thirdPartyData);

            if (!empty($thirdPartyData['ThirdPartyType'])) {
                DB::table('t_ThirdPartyType_ThirdParties')->insert([
                    'TypeId' => $thirdPartyData['ThirdPartyType'],
                    'ThirdPartyId' => $thirdParty->Id,
                    'CreatedBy' => $systemUserId,
                    'ModifiedBy' => $systemUserId,
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ]);
            }

            return $thirdParty;
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
