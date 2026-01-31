<?php

namespace App\Http\Resources\ThirdParty\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TenantProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'tenantType' => $this->TenantType,
            'typeName'   => $this->type->Name ?? null,
            'remarks'    => $this->Remarks,
            'isActive'   => (bool)$this->IsActive,
            'createdOn'  => $this->CreatedOn instanceof Carbon
                ? $this->CreatedOn->toIso8601String()
                : ($this->CreatedOn ? Carbon::parse($this->CreatedOn)->toIso8601String() : null),
        ];
    }
}
