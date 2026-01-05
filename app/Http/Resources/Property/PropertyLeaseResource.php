<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyLeaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->Id,
            'lease_number'  => $this->LeaseNumber,
            'status'        => $this->Status?->value,
            'approval'      => $this->ApprovalStatus,
            'is_active'     => (bool) $this->IsActive,

            'dates' => [
                'start' => $this->StartDate,
                'end'   => $this->EndDate,
                'due_day' => $this->DueDay,
            ],

            'financials' => [
                'currency'        => $this->currency?->Code,
                'monthly_rent'    => (float) $this->MonthlyRent,
                'deposit'         => (float) $this->Deposit,
                'service_charge'  => (float) $this->ServiceCharge,
                'parking_fee'     => (float) $this->ParkingFee,
                'other_charges'   => (float) $this->OtherCharges,
            ],

            'tenant' => [
                'id'   => $this->tenant?->Id,
                'name' => $this->tenant?->TenantName ?? null,
            ],

            'property' => [
                'id'   => $this->property?->Id,
                'name' => $this->property?->PropertyName,
            ],

            'block' => [
                'id'   => $this->block?->Id,
                'name' => $this->block?->BlockName,
            ],

            'floor' => [
                'id'    => $this->floor?->Id,
                'label' => $this->floor?->FloorLabel,
            ],

            'unit' => [
                'id'   => $this->unit?->Id,
                'code' => $this->unit?->UnitCode,
                'size' => $this->unit?->UnitSize,
            ],

            'payment_frequency' => $this->code?->Description,

            'created_by' => $this->createdByUser?->name,
            'created_on' => $this->CreatedOn,
        ];
    }
}
