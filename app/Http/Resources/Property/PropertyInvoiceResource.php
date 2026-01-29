<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->Id,
            'invoice_number' => $this->InvoiceNumber,
            'billing_month' => $this->BillingMonth,
            'invoice_date' => $this->InvoiceDate,
            'status' => $this->Status?->value,
            'description' => $this->Description,
            'notes' => $this->InvoiceNotes,

            'amounts' => [
                'rent' => (float) $this->RentAmount,
                'service_charge' => (float) $this->ServicesCharge,
                'other_charges' => (float) $this->OtherCharges,
                'parking_fee' => (float) $this->ParkingFee,
            ],

            'currency' => [
                'id' => $this->currency?->Id,
                'code' => $this->currency?->Code,
            ],

            'tax' => [
                'id' => $this->tax?->Id,
                'name' => $this->tax?->Name,
                'rate' => (float) $this->tax?->Rate,
            ],

            'lease' => [
                'id' => $this->lease?->Id,
                'lease_number' => $this->lease?->LeaseNumber,
            ],

            'created_by' => $this->createdByUser?->name,
            'created_on' => $this->CreatedOn,
            'modified_by' => $this->modifiedByUser?->name,
            'modified_on' => $this->ModifiedOn,
        ];
    }
}
