<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class TenderInvitationResource extends JsonResource
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
            'InvitationDate' => $this->InvitationDate ?? null,
            'tender' => [
                'Id' => $this->TenderId ?? null,
                'TenderNo' => $this->TenderNo ?? null,
                'Title' => $this->Title ?? null,
                'TenderType' => $this->TenderType ?? null,
                'TenderCategory' => $this->TenderCategory ?? null,
                'SubmissionDeadline' => $this->SubmissionDeadline ?? null,
                'OpeningDate' => $this->OpeningDate ?? null,
                'Status' => $this->TenderStatus ?? null,
                'CurrencyId' => $this->CurrencyId ?? null,
                'currency_code' => $this->CurrencyCode ?? null,
            ],
        ];
    }
}
