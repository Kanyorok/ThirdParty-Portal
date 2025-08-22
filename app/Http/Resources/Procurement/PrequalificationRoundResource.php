<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Id' => $this->Id,
            'Title' => $this->Title,
            'Description' => $this->Description,
            'StartDate' => $this->StartDate?->format('Y-m-d'),
            'EndDate' => $this->EndDate?->format('Y-m-d'),
            'MaxVendors' => $this->MaxVendors,

            'Status' => $this->when($this->Status, fn() => [
                'Value' => $this->Status->value ?? null,
                'Label' => method_exists($this->Status, 'label') ? $this->Status->label() : (string) $this->Status,
            ]),

            'CreatedOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'ModifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),

            'Sections' => PrequalificationSectionResource::collection(
                $this->whenLoaded('sections')
            ),

            'Criteria' => PrequalificationCriteriaResource::collection(
                $this->whenLoaded('criteria')
            ),

            'Applications' => PrequalificationApplicationResource::collection(
                $this->whenLoaded('applications')
            ),
        ];
    }
}
