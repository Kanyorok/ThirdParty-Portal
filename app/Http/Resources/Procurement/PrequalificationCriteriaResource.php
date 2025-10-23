<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrequalificationCriteriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Id' => $this->Id,
            'id' => $this->Id,
            'CriteriaID' => $this->CriteriaID ?? $this->CriteriaId,
            'criteriaId' => $this->CriteriaID ?? $this->CriteriaId,
            'Included' => $this->Included,
            'included' => $this->Included,
            'MaxScore' => $this->MaxScore,
            'maxScore' => $this->MaxScore,
        ];
    }
}
