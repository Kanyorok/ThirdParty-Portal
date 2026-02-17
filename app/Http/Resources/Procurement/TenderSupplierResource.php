<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class TenderSupplierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'Id' => $this->id ?? $this->Id ?? null,
            'TenderID' => $this->TenderID ?? null,
            'SupplierID' => $this->SupplierID ?? null,
            'CreatedBy' => $this->CreatedBy ?? null,
            'CreatedOn' => $this->CreatedOn ?? null,
            'ModifiedBy' => $this->ModifiedBy ?? null,
            'ModifiedOn' => $this->ModifiedOn ?? null,
        ];
    }
}
