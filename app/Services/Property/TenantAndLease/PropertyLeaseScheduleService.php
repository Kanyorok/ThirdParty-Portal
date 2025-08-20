<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use Exception;

class PropertyLeaseScheduleService
{
    /**
     * Create a new lease schedule if one does not already exist for the given lease.
     *
     * @throws Exception if the lease schedule already exists or creation fails.
     */
    public static function create(
        int $leaseId,
        int $paymentFrequencyId,
        string $startDate,
        string $endDate,
        float $baseRent,
        float $serviceCharge,
        float $parkingFee,
        float $otherCharges,
        User  $user
    ): PropertyLeaseSchedule
    {

        // Check if a schedule already exists for the lease
        $exists = PropertyLeaseSchedule::where('LeaseNumber', $leaseId)->exists();

        if ($exists) {
            throw new Exception('This lease is already scheduled.');
        }

        // Attempt to create the schedule
        $leaseSchedule = PropertyLeaseSchedule::create([
            'LeaseNumber' => $leaseId,
            'PaymentFrequency' => $paymentFrequencyId,
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'BaseRent' => $baseRent,
            'ServiceCharge' => $serviceCharge,
            'ParkingFee' => $parkingFee,
            'OtherCharges' => $otherCharges,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($leaseSchedule)
            ->withProperties(['LeaseId' => $leaseId])
            ->log("Added Lease Schedule for Lease ID {$leaseId}.");

        return $leaseSchedule;
    }
}
