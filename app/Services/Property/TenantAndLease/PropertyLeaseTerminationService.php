<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Property\PropertyNewLeaseEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewLease;
use Illuminate\Support\Facades\DB;

class PropertyLeaseTerminationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PropertyLeaseTermination $propertyLeaseTermination)
    {
    }

    public static function create(
        PropertyNewLease $LeaseID,
        string     $TerminationDate,
        CodeDetail $TerminationReason,
        string $Remarks,
        User   $user
    ): self
    {
        DB::beginTransaction();

        try {
            // Create termination record
            $termination = PropertyLeaseTermination::create([
                'LeaseID' => $LeaseID->Id,
                'TerminationDate' => $TerminationDate,
                'TerminationReason' => $TerminationReason->ID,
                'Remarks' => $Remarks,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            // Deactivate the main lease
            $LeaseID->IsActive = false;
            $LeaseID->Status = PropertyNewLeaseEnum::Terminate;
            $LeaseID->ModifiedBy = $user->Id;
            $LeaseID->save();

            // Deactivate all related schedules
            PropertyLeaseSchedule::where('LeaseNumber', $LeaseID->Id)
                ->update([
                    'IsActive' => false,
                    'ModifiedBy' => $user->Id,
                ]);

            // Deactivate all related renewals
            PropertyLeaseRenewal::where('LeaseNumber', $LeaseID->Id)
                ->update([
                    'IsActive' => false,
                    'ModifiedBy' => $user->Id,
                ]);

            // Log activity
            activity()
                ->causedBy($user->Id)
                ->performedOn($termination)
                ->event('create')
                ->log("Terminated Lease ID {$LeaseID->Id}");

            DB::commit();

            return new self($termination);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
