<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Id,
            'profileCompletion' => $this->profile_completion,
            'thirdPartyDetails' => [
                'thirdPartyName'     => $this->ThirdPartyName,
                'tradingName'        => $this->TradingName,
                'businessType'       => $this->businessType?->CodeDetailsName,
                'registrationNumber' => $this->RegistrationNumber,
                'taxPIN'             => $this->TaxPIN,
                'physicalAddress'    => $this->PhysicalAddress,
                'website'            => $this->Website,
                'countryId'          => (string) $this->CountryId,
            ],
            'isPrequalified' => (bool) ($this->supplierMaster?->IsPrequalified ?? false),
            'supplierId'     => $this->supplierMaster?->SupplierID,
            'approvalStatus' => $this->supplierMaster?->ApprovalStatus,
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
