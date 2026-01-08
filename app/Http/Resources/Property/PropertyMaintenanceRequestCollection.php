<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyMaintenanceRequestCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($request) {
                return (new PropertyMaintenanceRequestResource($request));
            }),
        ];
    }
}
