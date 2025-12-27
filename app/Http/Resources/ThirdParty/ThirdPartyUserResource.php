<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $thirdParty = $this->thirdParty;

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
            'isPrequalified' => (bool) ($thirdParty?->IsPrequalified ?? false),
            'approvalStatus' => $this->whenLoaded('thirdParty', fn() => $thirdParty?->ApprovalStatus),

            'isSupplier' => $this->isSupplier(),
            'isTenant' => $this->isTenant(),
            'isCustomer' => $this->isCustomer(),
            'hasProfile' => $this->hasProfile(),

            'emailVerified' => $this->hasVerifiedEmail(),
            'emailVerifiedOn' => $this->EmailVerifiedOn?->format('Y-m-d H:i:s'),
            'createdOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'modifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),

            'thirdParty' => $this->when($thirdParty, [
                'id' => $thirdParty?->Id,
                'profileCompletion' => $thirdParty ? $this->calculateProfileCompletion($thirdParty) : 0,
                'thirdPartyDetails' => [
                    'thirdPartyName' => $thirdParty?->ThirdPartyName,
                    'tradingName' => $thirdParty?->TradingName,
                    'businessType' => $thirdParty?->businessType?->CodeDetailsName,
                    'registrationNumber' => $thirdParty?->RegistrationNumber,
                    'taxPIN' => $thirdParty?->TaxPIN,
                    'physicalAddress' => $thirdParty?->PhysicalAddress,
                    'website' => $thirdParty?->Website,
                    'countryId' => (string) $thirdParty?->CountryId,
                ],
                'isPrequalified' => (bool) ($thirdParty?->IsPrequalified ?? false),
                'supplierId' => $thirdParty?->supplierMaster?->SupplierID,
                'approvalStatus' => $thirdParty?->supplierMaster?->status?->CodeDetailsName ?? $thirdParty?->ApprovalStatus,
                'types' => $this->when($thirdParty?->relationLoaded('types'), function () use ($thirdParty) {
                    return $thirdParty->types->map(fn($type) => [
                        'id' => $type->Id,
                        'code' => $type->Code,
                        'label' => $type->Name,
                    ]);
                }),
                'createdOn' => $thirdParty?->CreatedOn?->format('Y-m-d H:i:s'),
            ]),
        ];
    }

    private function calculateProfileCompletion($thirdParty): int
    {
        if (!$thirdParty) return 0;

        $fields = [
            'ThirdPartyName' => $thirdParty->ThirdPartyName,
            'RegistrationNumber' => $thirdParty->RegistrationNumber,
            'TaxPIN' => $thirdParty->TaxPIN,
            'BusinessType' => $thirdParty->BusinessType,
            'CountryId' => $thirdParty->CountryId,
            'PhysicalAddress' => $thirdParty->PhysicalAddress,
            'Website' => $thirdParty->Website,
            'TradingName' => $thirdParty->TradingName
        ];

        $totalFields = count($fields);
        $filledFields = collect($fields)->filter(fn($value) => !empty($value))->count();

        return (int) (($filledFields / $totalFields) * 100);
    }
}
