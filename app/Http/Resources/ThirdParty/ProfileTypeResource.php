<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type_code' => $this->Code,
            'type_description' => $this->Description,
            'party_type' => $this->pivot->PartyType,
            'party_id' => $this->pivot->PartyID,
        ];
    }
}
