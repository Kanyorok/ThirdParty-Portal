<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\JsonResource;

class FloorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->Id,
            'floor_label' => $this->FloorLabel,
            'floor_notes' => $this->FloorNotes,
            'units' => UnitResource::collection($this->units),
        ];
    }
}
