<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Id,
            'name' => $this->Name,
            'code' => $this->CountryCode,
            'iso3' => $this->Iso3,
            'phoneCode' => $this->PhoneCode,
            'flag' => $this->Flag,
            'isActive' => $this->IsActive,
            'sortOrder' => $this->SortOrder,
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->Id,
                    'name' => $this->currency->Name,
                    'code' => $this->currency->Code,
                    'symbol' => $this->currency->Symbol,
                ];
            }),
        ];
    }
}