<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class RFQResource extends JsonResource
{
    /**
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'Id' => $this->Id,
            'rfqNumber' => $this->RFQNumber,
            'comments' => $this->Comments,
            'status' => $this->Status,
            'statusDescription' => $this->statusDescription,
            'submissionDeadline' => $this->SubmissionDeadline,
            'remarks' => $this->Remarks,
            'createdOn' => $this->CreatedOn,
            'modifiedOn' => $this->ModifiedOn,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->Id ?? null,
                    'name' => $this->category->Name ?? null,
                ];
            }),

            'requisition' => $this->whenLoaded('requisition', function () {
                return [
                    'id' => $this->requisition->Id ?? null,
                    'description' => $this->requisition->Title ?? $this->requisition->Description ?? null,
                ];
            }),

            'rfqLines' => $this->whenLoaded('rfqLines', function () {
                return $this->rfqLines->map(function ($line) {
                    return [
                        'id' => $line->Id,
                        'itemId' => $line->ItemId,
                        'description' => $line->Description,
                        'quantity' => $line->Quantity,
                        'uom' => $line->UOM ?? $line->uom->Name ?? null,
                    ];
                });
            }),

            'suppliers' => $this->whenLoaded('suppliers', function () {
                return $this->suppliers->map(function ($supplier) {

                    $name = $supplier->supplierMaster->party->ThirdPartyName
                        ?? $supplier->supplierMaster->party->TradingName
                        ?? $supplier->Name
                        ?? null;

                    return [
                        'id' => $supplier->Id,
                        'name' => $name,
                        'email' => $supplier->supplierMaster->party->Email ?? $supplier->Email ?? null,
                        'status' => $supplier->pivot->Status ?? null,
                    ];
                });
            }),

            'sections' => $this->whenLoaded('sections'),
            'criteria' => $this->whenLoaded('criteria'),
            'committeeMembers' => $this->whenLoaded('committeeMembers'),
            'createdBy' => $this->CreatedBy,
            'modifiedBy' => $this->ModifiedBy,
        ];
    }
}
