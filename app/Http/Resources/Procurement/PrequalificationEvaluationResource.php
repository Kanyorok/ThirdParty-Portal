<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'EvaluationID' => $this->EvaluationID,
            'ApplicationID' => $this->ApplicationID,
            'EvaluatorID' => $this->EvaluatorID,
            'SectionID' => $this->SectionID,
            'CriteriaID' => $this->CriteriaID,
            'Score' => $this->Score,
            'MaxScore' => $this->MaxScore,
            'Remarks' => $this->Remarks,
            'EvaluatedOn' => $this->EvaluatedOn?->format('Y-m-d H:i:s'),
        ];
    }
}
