<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'RoundID' => $this->RoundID,
            'id' => $this->RoundID,

            'Title' => $this->Title,
            'title' => $this->Title,
            'Description' => $this->Description,
            'description' => $this->Description,
            'StartDate' => $this->StartDate?->format('Y-m-d'),
            'startDate' => $this->StartDate?->format('Y-m-d'),
            'EndDate' => $this->EndDate?->format('Y-m-d'),
            'endDate' => $this->EndDate?->format('Y-m-d'),
            'MaxVendors' => $this->MaxVendors,
            'maxVendors' => $this->MaxVendors,

            'status' => [
                'value' => $this->Status?->value,
                'label' => $this->Status?->label(),
                'badgeClass' => $this->Status?->getBadgeClass(),
            ],

            'CreatedOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'ModifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),

            'sections' => PrequalificationSectionResource::collection($this->whenLoaded('sections')),
            'criteria' => PrequalificationCriteriaResource::collection($this->whenLoaded('criteria')),
            'applications' => PrequalificationApplicationResource::collection($this->whenLoaded('applications')),

            'applicationId' => $this->when(isset($this->applicationId), fn () => $this->applicationId),
            'hasApplied' => $this->when(isset($this->applicationId), fn () => (bool)$this->applicationId),
        ];
    }
}
