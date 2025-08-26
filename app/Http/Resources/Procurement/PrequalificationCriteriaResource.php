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
            'CriteriaID' => $this->CriteriaID,
            'Included' => $this->Included,
            'MaxScore' => $this->MaxScore,
        ];
    }
}
