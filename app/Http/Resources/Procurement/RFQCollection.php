<?php

namespace App\Http\Resources\Procurement;

use app\Http\Resources\Procurement\RFQResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RFQCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
        'data' => RFQResource::collection($this->collection),
        ];
    }
}
