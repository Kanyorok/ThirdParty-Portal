<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use Exception;

class PropertyLeaseRenewalService
{
    /**
     * Create a new lease renewal if one does not already exist for the given lease.
     *
     * @throws Exception if the lease renewal already exists or creation fails.
     */
    public static function create(
        int  $leaseId,
        int  $tenantId,
        int  $propertyId,
        int  $paymentFrequencyId,
        string $EndDateCurrentLease,
        string $NewStartDate,
        string $NewEndDate,
        int  $NewMonthlyRent,
        string $Remarks,
        User $user
    ): PropertyLeaseRenewal
    {

        // Check if a renewal already exists for the lease
        $exists = PropertyLeaseRenewal::where('LeaseNumber', $leaseId)->exists();

        if ($exists) {
            throw new Exception('This lease is already renewed.');
        }

        // Attempt to create the renewal
        $leaseRenewal = PropertyLeaseRenewal::create([
            'LeaseNumber' => $leaseId,
            'TenantId' => $tenantId,
            'PropertyId' => $propertyId,
            'PaymentFrequency' => $paymentFrequencyId,
            'EndDateCurrentLease' => $EndDateCurrentLease,
            'NewStartDate' => $NewStartDate,
            'NewEndDate' => $NewEndDate,
            'NewMonthlyRent' => $NewMonthlyRent,
            'Remarks' => $Remarks,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        // Log the activity
        activity()
            ->causedBy($user)
            ->performedOn($leaseRenewal)
            ->withProperties(['LeaseId' => $leaseId])
            ->log("Added Lease Renewal for Lease ID {$leaseId}.");

        return $leaseRenewal;
    }
}
