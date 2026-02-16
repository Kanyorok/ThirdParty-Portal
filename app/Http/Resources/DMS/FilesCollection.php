<?php

namespace App\Http\Resources\DMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FilesCollection extends ResourceCollection
{
    protected bool $minified = false;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->transform(function ($document) {
                return (new FileResource($document))->setMinified($this->minified);
            }),
        ];
    }

    public function setMinified(bool $minified = false): static
    {
        $this->minified = $minified;

        return $this;
    }
}
