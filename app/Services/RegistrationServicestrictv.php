<?php

namespace App\Services;

use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use App\Models\Auth\User;

class RegistrationService
{
    public function registerThirdParty(array $registrationData): ThirdParties
    {
        $systemUser = User::where('UserID', 'ERPSYS')->first();
        $systemUserId = $systemUser?->Id;

        $initialName = $registrationData['ThirdPartyName']
            ?? ($registrationData['FirstName'] . ' ' . $registrationData['LastName'])
            ?? $registrationData['Email'];

        return DB::transaction(function () use ($registrationData, $systemUserId, $initialName) {

            $thirdParty = ThirdParties::create([
                'Email' => $registrationData['Email'],
                'Password' => Hash::make($registrationData['Password']),
                'Phone' => $registrationData['Phone'] ?? null,

                // null coalessing for company/business details (defaults to null if not provided)
                'ThirdPartyName' => $initialName,
                'TradingName' => $registrationData['TradingName'] ?? null,
                'BusinessType' => $registrationData['BusinessType'] ?? null,
                'RegistrationNumber' => $registrationData['RegistrationNumber'] ?? null,
                'TaxPIN' => $registrationData['TaxPIN'] ?? null,
                'VATNumber' => $registrationData['VATNumber'] ?? null,
                'CountryId' => $registrationData['CountryId'] ?? null,
                'PhysicalAddress' => $registrationData['PhysicalAddress'] ?? null,
                'Website' => $registrationData['Website'] ?? null,

                'IsActive' => true,
                'ApprovalStatus' => 'P',

                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            if (!empty($registrationData['ThirdPartyType'])) {
                $typeId = $registrationData['ThirdPartyType'];
                $thirdParty->types()->attach($typeId, [
                    'CreatedBy' => $systemUserId,
                    'ModifiedBy' => $systemUserId,
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ]);
            } else {
                throw new \Exception('ThirdPartyType is required for initial registration.');
            }

            event(new Registered($thirdParty));

            return $thirdParty;
        });
    }
}
