<?php

namespace App\Services;

use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\ThirdParty\SupplierMaster;
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
                'Status'             => $data['Status'] ?? 1,
                'CreatedBy'          => $user->Id,
            ]);

            $typeMap = ['tenant' => 4, 'supplier' => 5, 'customer' => 6];
            $accountType = strtolower($data['accountType'] ?? 'supplier');
            $typeId = $typeMap[$accountType] ?? null;

            if ($accountType === 'supplier') {
                $supplier = SupplierMaster::create([
                    'ThirdPartyId'   => $thirdParty->Id,
                    'SupplierID'     => $this->generateSupplierCode(),
                    'ApprovalStatus' => 1,
                    'IsPrequalified' => 0,
                    'CreatedBy'      => $user->Id,
                ]);

                if ($typeId) {
                    $thirdParty->types()->attach($typeId, [
                        'PartyType' => 'SupplierMasterId',
                        'PartyID'   => $supplier->Id,
                        'CreatedBy' => $user->Id,
                        'CreatedOn' => now()
                    ]);
                }

                if (!empty($data['supplierCategories'])) {
                    $thirdParty->categories()->sync($data['supplierCategories']);
                }
            } elseif ($typeId) {
                $thirdParty->types()->attach($typeId, [
                    'PartyType' => 'ThirdPartyId',
                    'PartyID'   => $thirdParty->Id,
                    'CreatedBy' => $user->Id,
                    'CreatedOn' => now()
                ]);
            }

            $user->update([
                'ThirdPartyId' => $thirdParty->Id,
                'ModifiedBy'   => $user->Id
            ]);

            return $thirdParty;
        });
    }

    private function generateSupplierCode(): string
    {
        $year = date('Y');
        $latest = SupplierMaster::where('SupplierID', 'like', "SUP-$year-%")
            ->orderBy('Id', 'desc')
            ->first();

        $sequence = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->SupplierID, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return "SUP-$year-" . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
