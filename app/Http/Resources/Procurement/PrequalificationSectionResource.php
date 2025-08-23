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
            'SectionID' => $this->SectionID,
            'Weight' => $this->Weight,
            'Criteria' => PrequalificationCriteriaResource::collection($this->whenLoaded('criteria')),
        ];
    }
}
