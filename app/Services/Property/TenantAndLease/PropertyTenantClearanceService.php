<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Property\TenantClearanceEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyTenantClearance;

class PropertyTenantClearanceService
{
    protected PropertyTenantClearance $clearance;

    /**
     * Create a new class instance.
     */
    public function __construct(PropertyTenantClearance $propertyTenantClearance)
    {
        $this->clearance = $propertyTenantClearance;
    }

    public static function create(
        PropertyNewTenant $Tenant,
        string $ExitDate,
        bool $FinalInspection,
        bool $AllDuesPaid,
        bool $KeysReturned,
        CodeDetail $DepositRefunded,
        string $AdditionalNotes,
        TenantClearanceEnum $Status,
        User $user
    ): self {
        $clearance = PropertyTenantClearance::create([
            'Tenant' => $Tenant->Id,
            'ExitDate' => $ExitDate,
            'FinalInspection' => $FinalInspection,
            'AllDuesPaid' => $AllDuesPaid,
            'KeysReturned' => $KeysReturned,
            'DepositRefunded' => $DepositRefunded->ID,
            'AdditionalNotes' => $AdditionalNotes,
            'Status' => $Status->value,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)
            ->performedOn($clearance)
            ->event('create')
            ->log("Added Tenant Clearance for Tenant ID {$Tenant->Id}.");

        return new self($clearance);
    }

    public function update(
        string $ExitDate,
        bool $FinalInspection,
        bool $AllDuesPaid,
        bool $KeysReturned,
        CodeDetail $DepositRefunded,
        string $AdditionalNotes,
        TenantClearanceEnum $Status,
        User $user
    ): PropertyTenantClearance {
        $this->clearance->update([
            'ExitDate' => $ExitDate,
            'FinalInspection' => $FinalInspection,
            'AllDuesPaid' => $AllDuesPaid,
            'KeysReturned' => $KeysReturned,
            'DepositRefunded' => $DepositRefunded->ID,
            'AdditionalNotes' => $AdditionalNotes,
            'Status' => $Status->value,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)
            ->performedOn($this->clearance)
            ->event('update')
            ->log("Updated Tenant Clearance for Tenant ID {$this->clearance->Tenant}.");

        return $this->clearance;
    }
}
