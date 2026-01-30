<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->Id,
            'property_name' => $this->PropertyName,
            'blocks' => BlockResource::collection($this->getBlockByProperty),
        ];
    }
}
