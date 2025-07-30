<?php

namespace App\Services;

use App\Enums\ThirdPartyApprovalStatusEnum;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Enums\ThirdPartyStatusEnum;

class RegistrationService
{
    public function registerUser(array $userData): ThirdPartyUser
    {
        return ThirdPartyUser::create([
            'FirstName' => $userData['FirstName'],
            'LastName' => $userData['LastName'],
            'Email' => $userData['Email'],
            'Phone' => $userData['Phone'],
            'Password' => Hash::make($userData['Password']),
            'IsActive' => true, // TODO: change to false in prod
        ]);
    }

    public function registerThirdPartyDetails(string $userId, array $thirdPartyData): ThirdParties
    {
        return DB::transaction(function () use ($userId, $thirdPartyData) {
            $user = ThirdPartyUser::where('UserID', $userId)->firstOrFail();

            if ($user->ThirdPartyId !== null) {
                throw new \Exception('User is already associated with a third party.');
            }

            $thirdParty = ThirdParties::create([
                'ThirdPartyName' => $thirdPartyData['ThirdPartyName'],
                'TradingName' => $thirdPartyData['TradingName'] ?? null,
                'BusinessType' => $thirdPartyData['BusinessType'],
                'RegistrationNumber' => $thirdPartyData['RegistrationNumber'],
                'TaxPIN' => $thirdPartyData['TaxPIN'] ?? null,
                'VATNumber' => $thirdPartyData['VATNumber'] ?? null,
                'Country' => $thirdPartyData['Country'],
                'PhysicalAddress' => $thirdPartyData['PhysicalAddress'],
                'Email' => $thirdPartyData['Email'],
                'Phone' => $thirdPartyData['Phone'],
                'Website' => $thirdPartyData['Website'] ?? null,
                'ThirdPartyType' => $thirdPartyData['ThirdPartyType'],
                'Status' => ThirdPartyStatusEnum::Active, // TOODO: change to InActive in prod
                'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Approved, // TODO: default to pending in prod
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            $user->ThirdPartyId = $thirdParty->Id;
            $user->save();

            return $thirdParty;
        });
    }
}
