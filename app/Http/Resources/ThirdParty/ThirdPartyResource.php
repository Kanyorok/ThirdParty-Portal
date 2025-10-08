<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Load types relation if not already loaded to avoid N+1 when explicitly requested
        if (!$this->relationLoaded('types')) {
            $this->resource->loadMissing('types');
        }

        $types = $this->types->map(fn($t) => [
            'id' => $t->TypeId,
            'code' => $t->Code,
            'categoryId' => $t->Type,
        ]);

        return [
            'id' => $this->Id,
            'thirdPartyName' => $this->ThirdPartyName,
            'tradingName' => $this->TradingName,
            'businessType' => $this->BusinessType?->label(),
            'registrationNumber' => $this->RegistrationNumber,
            'taxPIN' => $this->TaxPIN,
            'vatNumber' => $this->VATNumber,
            'country' => $this->Country,
            'countryId' => $this->CountryId,
            'countryInfo' => $this->whenLoaded('country', function () {
                return [
                    'id' => $this->country->Id,
                    'name' => $this->country->Name,
                    'code' => $this->country->CountryCode,
                    'phoneCode' => $this->country->PhoneCode,
                    'flag' => $this->country->Flag,
                ];
            }),
            'physicalAddress' => $this->PhysicalAddress,
            'email' => $this->Email,
            'phone' => $this->Phone,
            'website' => $this->Website,
            'approvalStatus' => $this->ApprovalStatus?->label(),
            'status' => $this->Status?->value,
            // Legacy single type value (enum) kept for backward compatibility during transition
            'thirdPartyType' => $this->ThirdPartyType?->value,
            // New array of assigned types via pivot
            'types' => $types,
            'isPrequalified' => (bool)$this->IsPrequalified,
            'createdOn' => optional($this->CreatedOn)->format('Y-m-d H:i:s'),
            'modifiedOn' => optional($this->ModifiedOn)->format('Y-m-d H:i:s'),
            'createdBy' => $this->CreatedBy,
            'isActive' => (bool)$this->IsActive,
            'categories' => SupplierCategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
