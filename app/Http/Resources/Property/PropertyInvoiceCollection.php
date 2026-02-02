<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyInvoiceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection->map(function ($invoice) {
                return [
                    'id' => $invoice->Id,
                    'invoice_number' => $invoice->InvoiceNumber,
                    'billing_month' => $invoice->BillingMonth,
                    'invoice_date' => $invoice->InvoiceDate,
                    'status' => $invoice->Status?->value,
                    'amounts' => [
                        'rent' => (float) $invoice->RentAmount,
                        'service_charge' => (float) $invoice->ServicesCharge,
                        'other_charges' => (float) $invoice->OtherCharges,
                        'parking_fee' => (float) $invoice->ParkingFee,
                    ],
                    'currency' => $invoice->currency?->Code,
                    'lease_number' => $invoice->lease?->LeaseNumber,
                    'created_on' => $invoice->CreatedOn,
                ];
            }),
        ];
    }
}
