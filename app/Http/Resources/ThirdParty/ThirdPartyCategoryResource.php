<?php

namespace App\Http\Resources\ThirdParty;

use App\Http\Resources\CodeDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyCategoryResource extends JsonResource
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
            'thirdPartyId' => $this->ThirdPartyID, // PascalCase from model, camelCase in output
            'categoryId' => $this->CategoryID,     // PascalCase from model, camelCase in output
            'categoryDetail' => CodeDetailResource::make($this->whenLoaded('category')), // Use 'category' relationship name
            'createdOn' => $this->CreatedOn,
            'modifiedOn' => $this->ModifiedOn,
            'createdBy' => $this->CreatedBy,
            'modifiedBy' => $this->ModifiedBy,
            'deletedOn' => $this->DeletedOn,
            'deletedBy' => $this->DeletedBy,
        ];
    }
}
