<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use DateTime;

class PropertyNewLeaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PropertyNewLease $propertyNewLease)
    {
    }

    public static function create(
        PropertyNewTenant $Tenant,
        PropertyRegistry $PropertyID,
        PropertyBlock    $BlockID,
        PropertyFloor    $FloorID,
        PropertyUnit     $Unit,
        DateTime         $StartDate,
        DateTime         $EndDate,
        CodeDetail       $PaymentFrequency,
        float            $MonthlyRent,
        float            $Deposit,
        int              $DueDay,
        string           $SpecialTerms,
        User             $user
    ): self
    {

        $lastLease = PropertyNewLease::orderByDesc('Id')->first();
        $nextNumber = $lastLease ? ((int)filter_var($lastLease->LeaseNumber, FILTER_SANITIZE_NUMBER_INT)) + 1 : 1;
        $leaseNumber = 'LEASE-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);


        $newlease = PropertyNewLease::create([
            'LeaseNumber' => $leaseNumber,
            'Tenant' => $Tenant->Id,
            'PropertyID' => $PropertyID->Id,
            'BlockID' => $BlockID->Id,
            'FloorID' => $FloorID->Id,
            'Unit' => $Unit->Id,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'PaymentFrequency' => $PaymentFrequency->ID,
            'MonthlyRent' => $MonthlyRent,
            'Deposit' => $Deposit,
            'DueDay' => $DueDay,
            'SpecialTerms' => $SpecialTerms,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)
            ->performedOn($newlease)
            ->event('create')
            ->log("Added New Lease {$newlease->Id}.");

        return new self($newlease);
    }
}
