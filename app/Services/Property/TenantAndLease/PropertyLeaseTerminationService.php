<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewLease;

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
        string $TerminationDate,
        CodeDetail $TerminationReason,
        string $Remarks,
        User $user
    ): Self {
        $leasetermination = PropertyLeaseTermination::create([
            'LeaseID' => $LeaseID->Id,
            'TerminationDate' => $TerminationDate,
            'TerminationReason' => $TerminationReason->ID,
            'Remarks' => $Remarks,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);


        activity()->causedBy($user->Id)->performedOn($leasetermination)->event('create')
        ->log("Added New Lease {$leasetermination->Id}.");
        
        return new self($leasetermination);
    }
}
