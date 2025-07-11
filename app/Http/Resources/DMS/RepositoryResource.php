<?php

namespace App\Http\Resources\DMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepositoryResource extends JsonResource
{
    protected bool $minified = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(($this->minified) ? [] :
            [
                'files' => [
                    'count' => (int)$this->documents_count,
                    'string' => number_format((int)$this->documents_count)
                ]
            ],
            [
                'id' => $this->RepositoryId,
                'name' => $this->Name,
                'description' => $this->Description,
                'visibility' => [
                    'value' => $this->resource->Visibility->value,
                    'name' => $this->resource->Visibility->name,
                    'icon' => $this->resource->Visibility->icon(),
                ],
                'dated' => [
                    'datetime' => $this->ModifiedOn->format('d M Y H:i'),
                    'string' => $this->ModifiedOn->diffForHumans(),
                ],
                'links' => [
                    'route' => route('repo.show', $this->RepositoryId),
                    'summary' => route('repo.edit', $this->RepositoryId)
                ]
            ]);
    }

    public function setMinified(bool $minified = false): static
    {
        $this->minified = $minified;
        return $this;
    }
}
