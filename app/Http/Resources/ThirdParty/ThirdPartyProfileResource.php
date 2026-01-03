<?php

namespace App\Http\Resources\ThirdParty;

use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ThirdParties $this */

        // Safely check types to avoid circular reference issues
        $types = $this->relationLoaded('types') ? $this->types : collect();

        return [
            'third_party_id' => $this->Id,
            'third_party_name' => $this->ThirdPartyName,
            'trading_name' => $this->TradingName,
            'registration_number' => $this->RegistrationNumber,
            'tax_pin' => $this->TaxPIN,
            'email' => $this->Email,
            'phone' => $this->Phone,
            'physical_address' => $this->PhysicalAddress,
            'website' => $this->Website,
            'profile_types' => ProfileTypeResource::collection($this->whenLoaded('types')),
            'is_supplier' => $types->contains(fn($t) => str_starts_with($t->Code, 'SU-')),
            'is_tenant' => $types->contains(fn($t) => str_starts_with($t->Code, 'TE-')),
            'is_customer' => $types->contains(fn($t) => str_starts_with($t->Code, 'CU-')),
            'supplier_approval_status' => $this->supplierMaster?->ApprovalStatus?->value,
            'supplier_is_prequalified' => $this->supplierMaster?->IsPrequalified ?? false,
            'created_at' => $this->CreatedOn?->toISOString(),
            'updated_at' => $this->ModifiedOn?->toISOString(),
        ];
    }
}
