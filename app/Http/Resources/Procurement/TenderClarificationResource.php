<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class TenderClarificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'clarificationId' => $this->ClarificationID ?? $this->ClarificationId ?? null,
            'tenderId' => $this->TenderID ?? null,
            'vendorId' => $this->VendorID ?? null,
            'question' => $this->Question ?? null,
            'questionDate' => $this->QuestionDate ?? null,
            'answer' => $this->Answer ?? null,
            'answerDate' => $this->AnswerDate ?? null,
            'isPublic' => (bool)($this->ISPUBLISHEDTOALL ?? false),
            'isOwnQuestion' => $this->getAttribute('is_own_question'),
            'status' => $this->getAttribute('status'),
            'createdBy' => $this->CreatedBy ?? null,
            'createdOn' => $this->CreatedOn ?? null,
            'tender' => $this->whenLoaded('tenderID'),
            'vendor' => $this->whenLoaded('vendorID'),
        ];
    }
}
