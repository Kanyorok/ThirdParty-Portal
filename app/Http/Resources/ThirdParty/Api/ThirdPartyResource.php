<?php

namespace App\Http\Resources\ThirdParty\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Id,
            'profileCompletion' => $this->profile_completion ?? 0,
            'thirdPartyDetails' => [
                'thirdPartyName'     => $this->ThirdPartyName,
                'tradingName'        => $this->TradingName,
                'businessType'       => $this->businessType?->CodeDetailsName,
                'registrationNumber' => $this->RegistrationNumber,
                'taxPIN'             => $this->TaxPIN,
                'physicalAddress'    => $this->PhysicalAddress,
                'website'            => $this->Website,
                'email'              => $this->Email,
                'phone'              => $this->Phone,
                'countryId'          => (string) $this->CountryId,
            ],

            'profiles' => [
                'supplier' => $this->when($this->supplierMaster, [
                    'supplierId'     => $this->supplierMaster?->SupplierID,
                    'isPrequalified' => (bool) ($this->supplierMaster?->IsPrequalified ?? false),
                    'approvalStatus' => $this->supplierMaster?->ApprovalStatus,
                    'categoryId'     => $this->supplierMaster?->SupplierCategoryId,
                ]),

                'customer' => $this->whenLoaded('customerProfile', function () {
                    return new CustomerProfileResource($this->customerProfile);
                }),

                'tenant' => $this->whenLoaded('tenantProfile', function () {
                    return new TenantProfileResource($this->tenantProfile);
                }),
            ],

            'types' => $this->whenLoaded('types', function () {
                return $this->types->map(fn($t) => [
                    'id'    => $t->Id,
                    'code'  => $t->TypeCode,
                    'label' => $t->TypeName,
                ]);
            }, []),

            'createdOn' => $this->CreatedOn?->toDateTimeString(),
        ];
    }
}
