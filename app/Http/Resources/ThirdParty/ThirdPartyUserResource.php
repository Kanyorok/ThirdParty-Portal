<?php

namespace App\Http\Resources\ThirdParty;

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
            'fullName' => $this->FirstName . ' ' . $this->LastName,
            'email' => $this->Email,
            'phone' => $this->Phone,
            'imageId' => $this->ImageId,
            'gender' => $this->Gender?->value,
            'thirdPartyId' => $this->ThirdPartyId,
            'isActive' => (bool)$this->IsActive,
            'isApproved' => $this->isApproved(),
            'isPrequalified' => $this->whenLoaded('thirdParty', fn() => (bool)($this->thirdParty ? $this->thirdParty->IsPrequalified : false)),
            'isSupplier' => $this->isSupplier(),
            'isTenant' => $this->isTenant(),
            'isCustomer' => $this->isCustomer(),
            'emailVerifiedOn' => optional($this->EmailVerifiedOn)->format('Y-m-d H:i:s'),
            'createdOn' => optional($this->CreatedOn)->format('Y-m-d H:i:s'),
            'modifiedOn' => optional($this->ModifiedOn)->format('Y-m-d H:i:s'),
            'thirdParty' => new ThirdPartyResource($this->whenLoaded('thirdParty')),
        ];
    }
}
