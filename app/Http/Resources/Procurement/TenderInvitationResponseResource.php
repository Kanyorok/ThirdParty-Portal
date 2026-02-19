<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class TenderInvitationResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'InvitationID' => $this->InvitationID ?? null,
            'TenderId' => $this->TenderId ?? null,
            'SupplierId' => $this->SupplierId ?? null,
            'ResponseStatus' => $this->ResponseStatus ?? null,
            'ResponseDate' => $this->ResponseDate ?? null,
            'DeclineReason' => $this->DeclineReason ?? null,
        ];
    }
}
