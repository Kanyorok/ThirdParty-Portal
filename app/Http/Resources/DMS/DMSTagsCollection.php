<?php

namespace App\Http\Resources\DMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DMSTagsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->transform(function ($tag) {
                return [
                    'id' => $tag->TagID,
                    'Name' => $tag->Name,
                    'visibility' => [
                        'value' => $this->resource->Visibility->value,
                        'name' => $this->resource->Visibility->name,
                        'icon' => $this->resource->Visibility->icon(),
                    ],
                ];
            }),
        ];
    }
}
