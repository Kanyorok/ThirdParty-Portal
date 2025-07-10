<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Property\PropertyNewLeaseEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewLease;
use Illuminate\Support\Facades\DB;

class PropertyLeaseRenewalService
{
    /**
     * Create a new lease renewal if one does not already exist for the given lease.
     *
     * @throws \Exception if the lease renewal already exists or creation fails.
     */
    public static function create(
    int $leaseId,
    int $paymentFrequencyId,
    string $EndDateCurrentLease,
    string $NewStartDate,
    string $NewEndDate,
    int $NewMonthlyRent,
    float $ServiceCharge,
    float $ParkingFee,
    float $OtherCharges,
    string $Remarks,
    User $user
): PropertyLeaseRenewal {
    DB::beginTransaction();

    try {
        //Prevent duplicate renewal
        if (PropertyLeaseRenewal::where('LeaseNumber', $leaseId)->exists()) {
            throw new \Exception('This lease is already renewed.');
        }

        //Create LeaseRenewal record
        $leaseRenewal = PropertyLeaseRenewal::create([
            'LeaseNumber'         => $leaseId,
            'PaymentFrequency'    => $paymentFrequencyId,
            'EndDateCurrentLease' => $EndDateCurrentLease,
            'NewStartDate'        => $NewStartDate,
            'NewEndDate'          => $NewEndDate,
            'NewMonthlyRent'      => $NewMonthlyRent,
            'ServiceCharge'       => $ServiceCharge,
            'ParkingFee'          => $ParkingFee,
            'OtherCharges'        => $OtherCharges,
            'Remarks'             => $Remarks,
            'CreatedBy'           => $user->Id,
            'ModifiedBy'          => $user->Id,
        ]);

        //Update original lease status to "Renewed"
        $oldLease = PropertyNewLease::findOrFail($leaseId);
        $oldLease->update([
            'Status' => PropertyNewLeaseEnum::Renew->value
        ]);

        //Deactivate old lease schedule
        PropertyLeaseSchedule::where('LeaseNumber', $leaseId)
            ->update(['IsActive' => false]);

        // Create new lease schedule (active)
        $newSchedule = PropertyLeaseSchedule::create([
            'LeaseNumber'      => $leaseId,
            'PaymentFrequency' => $paymentFrequencyId,
            'StartDate'        => $NewStartDate,
            'EndDate'          => $NewEndDate,
            'BaseRent'         => $NewMonthlyRent,
            'ServiceCharge'    => $ServiceCharge,
            'ParkingFee'       => $ParkingFee,
            'OtherCharges'     => $OtherCharges,
            'IsActive'         => true,
            'CreatedBy'        => $user->Id,
            'ModifiedBy'       => $user->Id,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($leaseRenewal)
            ->withProperties(['LeaseId' => $leaseId])
            ->log("Lease Renewed. Old Lease ID {$leaseId}, New Lease ID {$leaseRenewal->Id}");

        DB::commit();

        return $leaseRenewal;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}


    /**
     * Update an existing lease renewal.
     *
     * @throws \Exception if the update fails
     */
    public static function update(
        PropertyLeaseRenewal $leaseRenewal,
        int $leaseId,
        int $paymentFrequencyId,
        string $EndDateCurrentLease,
        string $NewStartDate,
        string $NewEndDate,
        int $NewMonthlyRent,
        float $ServiceCharge,
        float $ParkingFee,
        float $OtherCharges,
        string $Remarks,
        User $user
    ): void {
        DB::beginTransaction();

        try {
            // Update lease renewal record
            $leaseRenewal->update([
                'LeaseNumber'         => $leaseId,
                'PaymentFrequency'    => $paymentFrequencyId,
                'EndDateCurrentLease' => $EndDateCurrentLease,
                'NewStartDate'        => $NewStartDate,
                'NewEndDate'          => $NewEndDate,
                'NewMonthlyRent'      => $NewMonthlyRent,
                'ServiceCharge'       => $ServiceCharge,
                'ParkingFee'          => $ParkingFee,
                'OtherCharges'        => $OtherCharges,
                'Remarks'             => $Remarks,
                'ModifiedBy'          => $user->Id,
            ]);

            // Update existing schedule (if only one exists)
            $schedule = PropertyLeaseSchedule::where('LeaseNumber', $leaseId)->latest()->first();

            if ($schedule) {
                $schedule->update([
                    'PaymentFrequency' => $paymentFrequencyId,
                    'StartDate'        => $NewStartDate,
                    'EndDate'          => $NewEndDate,
                    'BaseRent'         => $NewMonthlyRent,
                    'ServiceCharge'    => $ServiceCharge,
                    'ParkingFee'       => $ParkingFee,
                    'OtherCharges'     => $OtherCharges,
                    'ModifiedBy'       => $user->Id,
                    'IsActive'         => true,
                ]);
            }

            activity()
                ->performedOn($leaseRenewal)
                ->causedBy($user)
                ->withProperties(['action' => 'update'])
                ->log('Updated Lease Renewal and Schedule');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function delete(
        PropertyLeaseRenewal $leaseRenewal,
        User $user
    ): void {
        DB::beginTransaction();

        try {
            $leaseId = $leaseRenewal->LeaseNumber;

            // Soft delete associated schedules (and set DeletedBy)
            PropertyLeaseSchedule::where('LeaseNumber', $leaseId)->get()->each(function ($schedule) use ($user) {
                $schedule->DeletedBy = $user->Id;
                $schedule->save(); 
                $schedule->delete();
            });

            
            $leaseRenewal->DeletedBy = $user->Id;
            $leaseRenewal->save();
            $leaseRenewal->delete();

            activity()
                ->performedOn($leaseRenewal)
                ->causedBy($user)
                ->log("Soft deleted Lease Renewal and associated schedules for Lease ID {$leaseId}");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



}
