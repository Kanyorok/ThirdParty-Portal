<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CodeDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->transform(function ($detail) {
                return [
                    'id' => $detail->ID,
                    'Name' => ($detail->Description) ?? $detail->Name,
                    'DisplayOrder' => ($detail->DisplayOrder) ?? '',
                ];
            }),
        ];
    }
}
