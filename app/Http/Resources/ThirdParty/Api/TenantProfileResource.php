<?php

namespace App\Http\Resources\ThirdParty\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'tenantType' => $this->TenantType,
            'typeName' => $this->type->Name ?? null,
            'remarks' => $this->Remarks,
            'isActive' => (bool)$this->IsActive,
            'createdOn' => $this->CreatedOn ? $this->CreatedOn->toIso8601String() : null,
        ];
    }
}
