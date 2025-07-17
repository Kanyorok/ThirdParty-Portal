<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Id' => $this->Id,
            'ThirdPartyName' => $this->SupplierName,
            'TradingName' => $this->TradingName,
            'Label' => $this->label,
            'BusinessType' => $this->BusinessType,
            'RegistrationNumber' => $this->RegistrationNumber,
            'TaxPIN' => $this->TaxPIN,
            'VATNumber' => $this->VATNumber,
            'Country' => $this->Country,
            'PhysicalAddress' => $this->PhysicalAddress,
            'Email' => $this->Email,
            'Phone' => $this->Phone,
            'Website' => $this->Website,
            'ApprovalStatus' => $this->ApprovalStatus,
            'Status' => $this->Status->value,
            'ThirdPartyType' => $this->ThirdPartyType->value,
            'CreatedOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'ModifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),
            'CreatedBy' => $this->CreatedBy,
            'IsActive' => $this->IsActive,
            'UsersCount' => $this->whenLoaded('users', fn() => $this->users->count()),
        ];
    }
}
