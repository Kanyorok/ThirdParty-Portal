<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyInterestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Id,
            'property_id' => $this->PropertyId,
            'block_id' => $this->BlockId,
            'floor_id' => $this->FloorId,
            'unit_id' => $this->UnitId,
            'tenant_id' => $this->TenantId,
            'interested_start_date' => $this->InterestedStartDate,
            'interested_end_date' => $this->InterestedEndDate,
            'payment_frequency' => [
                'id' => $this->code?->ID,
                'code' => $this->code?->Code,
                'description' => $this->code?->Description,
            ],
            'additional_information' => $this->AdditionalInformation,
            'tenant' => [
                'id' => $this->tenant?->Id,
                'name' => $this->tenant?->name,
                'email' => $this->tenant?->email,
            ],
            'property' => [
                'id' => $this->property?->Id,
                'name' => $this->property?->PropertyName,
            ],
            'unit' => [
                'id' => $this->unit?->Id,
                'name' => $this->unit?->UnitName,
            ],
            'unit_price' => $this->price?->Amount,
            'currency' => $this->price?->CurrencyId,
            'created_on' => $this->CreatedOn,
            'modified_on' => $this->ModifiedOn,
            'created_by' => $this->createdBy?->name,
        ];
    }
}
