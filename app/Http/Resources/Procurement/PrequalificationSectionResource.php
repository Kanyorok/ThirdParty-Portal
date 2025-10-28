<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Id' => $this->Id,
            'id' => $this->Id,
            'SectionID' => $this->SectionID ?? $this->SectionId,
            // Normalized lowercase alias used by portal frontend
            'sectionId' => $this->SectionID ?? $this->SectionId,
            // Provide human-readable section name from master section relation when available
            'SectionName' => $this->when($this->relationLoaded('masterSection'), fn () => optional($this->masterSection)->SectionName),
            'name' => $this->when($this->relationLoaded('masterSection'), fn () => optional($this->masterSection)->SectionName),
            'Weight' => $this->Weight,
            'weight' => $this->Weight,
            'Criteria' => PrequalificationCriteriaResource::collection($this->whenLoaded('criteria')),
            'criteria' => PrequalificationCriteriaResource::collection($this->whenLoaded('criteria')),
        ];
    }
}
