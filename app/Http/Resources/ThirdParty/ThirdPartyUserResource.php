<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ThirdParty\ThirdPartyResource;

class ThirdPartyUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Id' => $this->Id,
            'UserId' => $this->UserID,
            'FirstName' => $this->FirstName,
            'LastName' => $this->LastName,
            'FullName' => $this->FullName,
            'Email' => $this->Email,
            'Phone' => $this->Phone,
            'ImageId' => $this->ImageId,
            'Gender' => $this->Gender?->value,
            'ThirdPartyId' => $this->ThirdPartyId,

            'CreatedOn' => optional($this->CreatedOn)->format('Y-m-d H:i:s'),
            'ModifiedOn' => optional($this->ModifiedOn)->format('Y-m-d H:i:s'),
            'EmailVerifiedOn' => optional($this->EmailVerifiedOn)->format('Y-m-d H:i:s'),

            'IsApproved' => $this->relationLoaded('thirdParty') ? $this->isApproved() : null,
            'IsSupplier' => $this->relationLoaded('thirdParty') ? $this->isSupplier() : null,
            'IsActive' => $this->relationLoaded('thirdParty') ? $this->isActive() : null,

            'ThirdParty' => new ThirdPartyResource($this->whenLoaded('thirdParty')),
        ];
    }
}
