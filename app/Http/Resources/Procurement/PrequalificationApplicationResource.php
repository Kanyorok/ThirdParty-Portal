<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ApplicationID' => $this->ApplicationID,
            'applicationId' => $this->ApplicationID,
            'SupplierID' => $this->SupplierID,
            'RoundID' => $this->RoundID,
            'roundId' => $this->RoundID,
            'CategoryID' => $this->CategoryID,
            'Status' => $this->Status,
            'status' => $this->when($this->Status, fn() => [
                'value' => $this->Status->value ?? (string)$this->Status,
                'label' => method_exists($this->Status, 'label') ? $this->Status->label() : (string)$this->Status,
            ]),
            'hasApplied' => true,
            'SubmittedOn' => $this->SubmittedOn?->format('Y-m-d H:i:s'),
            'Evaluations' => PrequalificationEvaluationResource::collection($this->whenLoaded('evaluations')),
        ];
    }
}
