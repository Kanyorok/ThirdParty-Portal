<?php

namespace App\Http\Resources\ThirdParty\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'dateOfBirth' => $this->DateOfBirth,
            'gender' => [
                'id' => $this->Gender,
                'label' => $this->genders->Name ?? null
            ],
            'maritalStatus' => [
                'id' => $this->MaritalStatus,
                'label' => $this->maritalstatus->Name ?? null
            ],
            'occupation' => [
                'id' => $this->Occupation,
                'label' => $this->occupations->Name ?? null
            ],
            'createdOn' => $this->CreatedOn ? $this->CreatedOn->toIso8601String() : null,
        ];
    }
}
