<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Use actual primary key RoundID; keep both camel/lower if downstream expects lowercase keys
            'Id' => $this->RoundID,            // backward compatibility (existing consumers using 'Id')
            'id' => $this->RoundID,            // preferred lowercase key
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

            'Status' => $this->when($this->Status, fn() => [
                'Value' => $this->Status->value ?? null,
                'Label' => method_exists($this->Status, 'label') ? $this->Status->label() : (string) $this->Status,
            ]),
            'status' => $this->when($this->Status, fn() => [
                'value' => $this->Status->value ?? null,
                'label' => method_exists($this->Status, 'label') ? $this->Status->label() : (string) $this->Status,
            ]),

            'CreatedOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'createdOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'ModifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),
            'modifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),

            'Sections' => PrequalificationSectionResource::collection($this->whenLoaded('sections')),
            'sections' => PrequalificationSectionResource::collection($this->whenLoaded('sections')),

            'Criteria' => PrequalificationCriteriaResource::collection($this->whenLoaded('criteria')),
            'criteria' => PrequalificationCriteriaResource::collection($this->whenLoaded('criteria')),

            'Applications' => PrequalificationApplicationResource::collection($this->whenLoaded('applications')),
            'applications' => PrequalificationApplicationResource::collection($this->whenLoaded('applications')),
            // Application state for current supplier (present only when joined in apiIndex)
            'applicationId' => $this->when(isset($this->applicationId), fn() => $this->applicationId),
            // hasApplied now means the current authenticated user has an application (user-scoped)
            'hasApplied' => $this->when(isset($this->applicationId), fn() => (bool) $this->applicationId),
            'createdByOwner' => $this->when(isset($this->createdByOwner), fn() => (bool)$this->createdByOwner),
            // canApply combines: not yet applied AND not owner of related categories (currently only hasApplied available)
            'canApply' => $this->when(true, fn() => !(isset($this->applicationId) && $this->applicationId)),
        ];
    }
}
