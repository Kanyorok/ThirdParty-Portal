<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyTenantClearance;

class PropertyTenantClearanceService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PropertyTenantClearance $propertyTenantClearance)
    {
        //
    }

    public static function create(
        PropertyNewTenant $Tenant,
        string            $ExitDate,
        bool              $FinalInspection,
        bool              $AllDuesPaid,
        bool              $KeysReturned,
        CodeDetail        $DepositRefunded,
        string            $AdditionalNotes,
        User              $user
    ): self
    {
        $clearance = PropertyTenantClearance::create([
            'Tenant' => $Tenant->Id,
            'ExitDate' => $ExitDate,
            'FinalInspection' => $FinalInspection,
            'AllDuesPaid' => $AllDuesPaid,
            'KeysReturned' => $KeysReturned,
            'DepositRefunded' => $DepositRefunded->ID,
            'AdditionalNotes' => $AdditionalNotes,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)
            ->performedOn($clearance)
            ->event('create')
            ->log("Added Tenant Clearance for Tenant ID {$Tenant}.");

        return new self($clearance);
    }
}
