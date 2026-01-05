<?php

namespace App\Http\Resources\ThirdParty\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Id,
            'userId' => $this->UserID,
            'firstName' => $this->FirstName,
            'lastName' => $this->LastName,
            'fullName' => "{$this->FirstName} {$this->LastName}",
            'email' => $this->Email,
            'phone' => $this->Phone,
            'imageId' => $this->ImageId,
            'thirdPartyId' => $this->ThirdPartyId,
            'isActive' => (bool)$this->IsActive,
            'isSupplier' => $this->isSupplier(),
            'isTenant' => $this->isTenant(),
            'isCustomer' => $this->isCustomer(),
            'emailVerifiedOn' => $this->EmailVerifiedOn?->toDateTimeString(),
            'createdOn' => $this->CreatedOn?->toDateTimeString(),
            'modifiedOn' => $this->ModifiedOn?->toDateTimeString(),
            'thirdParty' => new ThirdPartyResource($this->whenLoaded('thirdParty')),
        ];
    }
}
