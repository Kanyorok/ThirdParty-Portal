<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class ThirdPartyUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $thirdParty = $this->whenLoaded('thirdParty');
        $hasThirdParty = $thirdParty !== null && !($thirdParty instanceof MissingValue);
        $supplierMaster = $hasThirdParty ? $thirdParty->supplierMaster : null;

        return [
            'id' => $this->Id,
            'userId' => $this->UserID,
            'firstName' => $this->FirstName,
            'lastName' => $this->LastName,
            'fullName' => "{$this->FirstName} {$this->LastName}",
            'email' => $this->Email,
            'phone' => $this->Phone,
            'imageId' => $this->ImageId,
            'gender' => $this->Gender,
            'thirdPartyId' => (string) $this->ThirdPartyId,
            'isActive' => (bool) $this->IsActive,
            'isPrequalified' => (bool) ($supplierMaster->IsPrequalified ?? false),
            'approvalStatus' => $this->resolveStatus($hasThirdParty, $supplierMaster),
            'isSupplier' => $this->hasProfile() && $hasThirdParty && $thirdParty->relationLoaded('types') ? $this->checkIsSupplier($thirdParty) : false,
            'isTenant' => $this->hasProfile() && $hasThirdParty && $thirdParty->relationLoaded('types') ? $this->checkIsTenant($thirdParty) : false,
            'isCustomer' => $this->hasProfile() && $hasThirdParty && $thirdParty->relationLoaded('types') ? $this->checkIsCustomer($thirdParty) : false,
            'hasProfile' => $this->hasProfile(),
            'emailVerified' => $this->hasVerifiedEmail(),
            'emailVerifiedOn' => $this->EmailVerifiedOn?->format('Y-m-d H:i:s'),
            'createdOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'modifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),
            'thirdParty' => $this->when($hasThirdParty, function () use ($thirdParty, $supplierMaster) {
                return [
                    'id' => $thirdParty->Id,
                    'profileCompletion' => $this->calculateProfileCompletion($thirdParty),
                    'thirdPartyDetails' => [
                        'thirdPartyName' => $thirdParty->ThirdPartyName,
                        'tradingName' => $thirdParty->TradingName,
                        'businessType' => $thirdParty->businessType?->CodeDetailsName,
                        'registrationNumber' => $thirdParty->RegistrationNumber,
                        'taxPIN' => $thirdParty->TaxPIN,
                        'physicalAddress' => $thirdParty->PhysicalAddress,
                        'website' => $thirdParty->Website,
                        'countryId' => (string) $thirdParty->CountryId,
                    ],
                    'isPrequalified' => (bool) ($supplierMaster->IsPrequalified ?? false),
                    'supplierId' => $supplierMaster?->SupplierID,
                    'approvalStatus' => $supplierMaster?->ApprovalStatus ?? 'Pending',
                    'types' => $this->when($thirdParty->relationLoaded('types'), function () use ($thirdParty) {
                        return $thirdParty->types->map(fn($type) => [
                            'id' => $type->Id,
                            'code' => $type->Code,
                            'label' => $type->Name,
                        ]);
                    }),
                    'createdOn' => $thirdParty->CreatedOn?->format('Y-m-d H:i:s'),
                ];
            }),
        ];
    }

    private function resolveStatus($hasThirdParty, $supplierMaster)
    {
        if (!$hasThirdParty || !$supplierMaster) {
            return 'Pending';
        }
        return $supplierMaster->ApprovalStatus ?? 'Pending';
    }

    private function checkIsSupplier($thirdParty): bool
    {
        // Check if ThirdParty has supplier type using the model's method
        return $thirdParty->isSupplier();
    }

    private function checkIsTenant($thirdParty): bool
    {
        // Check if ThirdParty has tenant type using the model's method
        return $thirdParty->isTenant();
    }

    private function checkIsCustomer($thirdParty): bool
    {
        // Check if ThirdParty has customer type using the model's method
        return $thirdParty->isCustomer();
    }

    private function calculateProfileCompletion($thirdParty): int
    {
        $fields = [
            'ThirdPartyName' => $thirdParty->ThirdPartyName,
            'RegistrationNumber' => $thirdParty->RegistrationNumber,
            'TaxPIN' => $thirdParty->TaxPIN,
            'BusinessType' => $thirdParty->BusinessType,
            'CountryId' => $thirdParty->CountryId,
            'PhysicalAddress' => $thirdParty->PhysicalAddress,
        ];
        $totalFields = count($fields);
        $filledFields = collect($fields)->filter(fn($value) => !empty($value))->count();
        return (int) (($filledFields / $totalFields) * 100);
    }
}
