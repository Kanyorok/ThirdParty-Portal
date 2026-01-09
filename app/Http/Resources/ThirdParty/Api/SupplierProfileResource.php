<?php

namespace App\Http\Resources\ThirdParty\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Id,
            'supplierId' => $this->SupplierID,
            'thirdPartyId' => $this->ThirdPartyID,

            'approvalStatus' => $this->ApprovalStatus,
            // 'statusLabel' => $this->status?->Description ?? 'Pending',
            'isPrequalified' => (bool)$this->IsPrequalified,
            'profileCompletion' => (int)($this->ProfileCompletion ?? 0),

            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn($cat) => [
                    'id' => $cat->Id,
                    'name' => $cat->Name,
                    'code' => $cat->Code
                ]);
            }),

            'createdOn' => $this->CreatedOn?->format('Y-m-d H:i:s'),
            'modifiedOn' => $this->ModifiedOn?->format('Y-m-d H:i:s'),

            'businessInfo' => [
                'name' => $this->thirdParty?->Name,
                'registrationNumber' => $this->thirdParty?->RegistrationNumber,
                'taxPIN' => $this->thirdParty?->TaxPIN,
            ]
        ];
    }
}
