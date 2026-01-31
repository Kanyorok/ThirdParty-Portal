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
                'thirdPartyName' => $this->ThirdPartyName,
                'tradingName' => $this->TradingName,
                'businessType' => $this->businessType?->CodeDetailsName,
                'registrationNumber' => $this->RegistrationNumber,
                'taxPIN'             => $this->TaxPIN,
                'physicalAddress'    => $this->PhysicalAddress,
                'website'            => $this->Website,
                'email'              => $this->Email,
                'phone'              => $this->Phone,
                'countryId'          => (string) $this->CountryId,
            ],
            'isSupplier'     => $this->supplierMaster()->exists(),
            'isTenant'       => \App\Models\PropertyManagement\PropertyNewTenant::where('ThirdPartyId', $this->Id)->exists(),
            'isCustomer'     => \App\Models\Insurance\BancassuranceCustomer::where('ThirdPartyId', $this->Id)->exists(),
            'isPrequalified' => (bool) ($this->supplierMaster?->IsPrequalified ?? false),
            'supplierId' => $this->supplierMaster?->SupplierID,
            'approvalStatus' => $this->supplierMaster?->ApprovalStatus,
            'types' => $this->types->map(fn($t) => [
                // 'id'    => $t->TypeId,
                'code'  => $t->Code,
                'label' => $t->Description,
            ]),
            'createdOn' => $this->CreatedOn?->toDateTimeString(),
        ];
    }
}