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
            'thirdPartyName' => $this->ThirdPartyName,
            'tradingName' => $this->TradingName,
            'businessType' => $this->BusinessType->label(),
            'registrationNumber' => $this->RegistrationNumber,
            'taxPIN' => $this->TaxPIN,
            'vatNumber' => $this->VATNumber,
            'country' => $this->Country,
            'physicalAddress' => $this->PhysicalAddress,
            'email' => $this->Email,
            'phone' => $this->Phone,
            'website' => $this->Website,
            'approvalStatus' => $this->ApprovalStatus->label(),
            'status' => $this->Status?->value,
            'thirdPartyType' => $this->ThirdPartyType?->value,
            'isPrequalified' => (bool) $this->IsPrequalified,
            'createdOn' => optional($this->CreatedOn)->format('Y-m-d H:i:s'),
            'modifiedOn' => optional($this->ModifiedOn)->format('Y-m-d H:i:s'),
            'createdBy' => $this->CreatedBy,
            'isActive' => (bool) $this->IsActive,
        ];
    }
}
