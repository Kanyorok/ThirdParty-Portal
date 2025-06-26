<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseSchedule;

class PropertyLeaseScheduleService
{
    public static function create(
        string $LeaseNumber,
        int $TenantId,
        int $PropertyId,
        string $PaymentFrequency,
        string $StartDate,
        string $EndDate,
        float $BaseRent,
        float $ServiceCharge,
        float $ParkingFee,
        float $OtherCharges,
        User $user
    ): PropertyLeaseSchedule {
        $leaseSchedule = PropertyLeaseSchedule::create([
            'LeaseNumber' => $LeaseNumber,
            'TenantId' => $TenantId,
            'PropertyId' => $PropertyId,
            'PaymentFrequency' => $PaymentFrequency,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'BaseRent' => $BaseRent,
            'ServiceCharge' => $ServiceCharge,
            'ParkingFee' => $ParkingFee,
            'OtherCharges' => $OtherCharges,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user->Id)
            ->performedOn($leaseSchedule)
            ->event('create')
            ->log("Added Lease Schedule {$LeaseNumber}.");

        return $leaseSchedule;
    }
}