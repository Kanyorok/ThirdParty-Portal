<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ThirdParty\SupplierCategoryResource;

class ThirdPartyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userInfo = [
            'firstName' => $this->FirstName,
            'lastName' => $this->LastName,
            'fullName' => $this->FirstName . ' ' . $this->LastName,
            'email' => $this->Email,
            'phone' => $this->Phone,
        ];

        $thirdPartyInfo = [
            'thirdPartyName' => $this->ThirdPartyName,
            'tradingName' => $this->TradingName,
            'businessType' => $this->BusinessType?->label(),
            'registrationNumber' => $this->RegistrationNumber,
            'taxPIN' => $this->TaxPIN,
            'vatNumber' => $this->VATNumber,
            'physicalAddress' => $this->PhysicalAddress,
            'website' => $this->Website,
            'countryId' => $this->CountryId,
            'countryInfo' => $this->whenLoaded('country', function () {
                if (class_exists(CountryResource::class)) {
                    return new CountryResource($this->country);
                }
                return [
                    'id' => $this->country->Id,
                    'name' => $this->country->Name,
                    'code' => $this->country->CountryCode,
                    'phoneCode' => $this->country->PhoneCode,
                    'flag' => $this->country->Flag,
                ];
            }),
        ];

        return [
            'id' => $this->Id,
            'thirdPartyUser' => $userInfo,
            'approvalStatusCode' => $this->ApprovalStatus?->value,
            'status' => $this->Status?->label(),
            'thirdPartyDetails' => $thirdPartyInfo,
            'approvalStatus' => $this->ApprovalStatus?->label(),
            'statusCode' => $this->Status?->value,
            'isPrequalified' => (bool)$this->IsPrequalified,
            'thirdPartyTypeCode' => $this->ThirdPartyType?->value,
            'types' => $this->whenLoaded('types', function () {
                return $this->types->map(fn($t) => [
                    'id' => $t->Id,
                    'code' => $t->Code,
                    'typeCategoryId' => $t->Type,
                    'label' => $t->Code,
                ]);
            }),
            'categories' => SupplierCategoryResource::collection($this->whenLoaded('categories')),
            'createdOn' => optional($this->CreatedOn)->format('Y-m-d H:i:s'),
            'modifiedOn' => optional($this->ModifiedOn)->format('Y-m-d H:i:s'),
            'createdBy' => $this->CreatedBy,
        ];
    }
}
