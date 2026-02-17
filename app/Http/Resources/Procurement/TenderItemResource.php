<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class TenderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'Id' => $this->Id,
            'TenderID' => $this->TenderID,
            'SourceType' => $this->SourceType,
            'ItemID' => $this->ItemID,
            'PlanItemID' => $this->PlanItemID,
            'ManualItemDescription' => $this->ManualItemDescription,
            'PlannedQty' => $this->PlannedQty,
            'QtyToTender' => $this->QtyToTender,
            'ItemCategory' => $this->ItemCategory,
            'Remarks' => $this->Remarks,
            'RelatedPRID' => $this->RelatedPRID,
            'CreatedBy' => $this->CreatedBy,
            'CreatedOn' => $this->CreatedOn,
            'ModifiedBy' => $this->ModifiedBy,
            'ModifiedOn' => $this->ModifiedOn,
            'item' => $this->whenLoaded('item'),
            'category' => $this->whenLoaded('category'),
            'planLineItem' => $this->whenLoaded('planLineItem'),
        ];
    }
}
