<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->Id,
            'unit_code' => $this->UnitCode,
            'unit_size' => $this->UnitSize,
            'is_rentable' => (bool) $this->IsRentable,
            'current_status' => (bool) $this->CurrentStatus,
            'availability_label' => $this->CurrentStatus ? 'Vacant' : 'Occupied',
        ];
    }
}
