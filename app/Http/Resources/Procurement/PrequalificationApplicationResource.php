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
            'SupplierID' => $this->SupplierID,
            'RoundID' => $this->RoundID,
            'CategoryID' => $this->CategoryID,
            'Status' => $this->Status,
            'SubmittedOn' => $this->SubmittedOn?->format('Y-m-d H:i:s'),
            'Evaluations' => PrequalificationEvaluationResource::collection($this->whenLoaded('evaluations')),
        ];
    }
}
