<?php

namespace App\Services;

use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use App\Models\Auth\User;

class RegistrationService
{
    public function registerThirdParty(array $userData): ThirdParties
    {
        return DB::transaction(function () use ($userData) {
            $systemUser = User::where('UserID', 'ERPSYS')->first();
            $systemUserId = $systemUser?->Id;

            $user = ThirdPartyUser::create([
                'FirstName' => $userData['FirstName'],
                'LastName' => $userData['LastName'],
                'Email' => $userData['Email'],
                'Phone' => $userData['Phone'],
                'Password' => $userData['Password'],
                'IsActive' => false,
                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            $initialName = $userData['ThirdPartyName']
                ?? ($userData['FirstName'] . ' ' . $userData['LastName'])
                ?? $userData['Email'];

            $thirdParty = ThirdParties::create([
                'ThirdPartyName' => $initialName,
                'TradingName' => $userData['TradingName'] ?? $initialName,
                'BusinessType' => $userData['BusinessType'] ?? null,
                'RegistrationNumber' => $userData['RegistrationNumber'] ?? null,
                'TaxPIN' => $userData['TaxPIN'] ?? null,
                'VATNumber' => $userData['VATNumber'] ?? null,
                'CountryId' => $userData['CountryId'] ?? null,
                'PhysicalAddress' => $userData['PhysicalAddress'] ?? null,
                'Email' => $userData['Email'] ?? null,
                'Phone' => $userData['Phone'] ?? null,
                'Website' => $userData['Website'] ?? null,

                'IsActive' => false,
                'ApprovalStatus' => 'P',

                'CreatedBy' => $systemUserId,
                'ModifiedBy' => $systemUserId,
            ]);

            $user->ThirdPartyId = $thirdParty->Id;
            $user->save();

            if (!empty($userData['ThirdPartyType'])) {
                DB::table('t_ThirdPartyType_ThirdParties')->insert([
                    'TypeId' => $userData['ThirdPartyType'],
                    'ThirdPartyId' => $thirdParty->Id,
                    'CreatedBy' => $systemUserId,
                    'ModifiedBy' => $systemUserId,
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ]);
            }

            event(new Registered($user));

            return $thirdParty;
        });
    }
}
