<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyInterestCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($interest) {
                return [
                    'id' => $interest->Id,
                    'property_id' => $interest->PropertyId,
                    'unit_id' => $interest->UnitId,
                    'tenant_id' => $interest->TenantId,
                    'interested_start_date' => $interest->InterestedStartDate,
                    'interested_end_date' => $interest->InterestedEndDate,
                    'payment_frequency' => $interest->code?->Description,
                    'tenant_name' => $interest->tenant?->ThirdParty->ThirdPartyName,
                    'property_name' => $interest->property?->PropertyName,
                    'unit_name' => $interest->unit?->UnitCode,
                    'unit_price' => (float) $interest->price?->Amount,
                    'created_on' => $interest->CreatedOn,
                ];
            }),
        ];
    }
}
