<?php

namespace App\Http\Resources\ThirdParty;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supplierCategoryId' => $this->SupplierCategoryID,
            'supplierCategoryName' => $this->SupplierCategoryName,
            'description' => $this->Description,
            'isActive' => (bool) $this->IsActive,
            'createdOn' => $this->CreatedOn,
            'modifiedOn' => $this->ModifiedOn,
        ];
    }
}
