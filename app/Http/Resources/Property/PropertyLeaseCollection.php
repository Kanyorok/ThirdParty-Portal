<?php
namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyLeaseCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => PropertyLeaseResource::collection($this->collection),
        ];
    }
}
