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
            // 'userId' => $this->Id,
            'firstName' => $this->FirstName,
            'lastName' => $this->LastName,
            'fullName' => trim($this->FirstName . ' ' . $this->LastName),
            'email' => $this->Email,
            'phone' => $this->Phone,
            'gender' => $this->genderDetail?->Description,
            'imageId' => $this->ImageId,
            'thirdPartyId' => $this->ThirdPartyId,
            'isActive' => (bool) $this->IsActive,
            'emailVerified' => !is_null($this->EmailVerifiedOn),
            'emailVerifiedOn' => $this->EmailVerifiedOn?->toDateTimeString(),
            'isSupplier' => (bool) $this->isSupplier(),
            'isTenant' => (bool) $this->isTenant(),
            'isCustomer' => (bool) $this->isCustomer(),
            'createdOn' => $this->CreatedOn?->toDateTimeString(),
            'modifiedOn' => $this->ModifiedOn?->toDateTimeString(),
            'thirdParty' => ThirdPartyResource::make($this->whenLoaded('thirdParty')),
        ];
    }
}
