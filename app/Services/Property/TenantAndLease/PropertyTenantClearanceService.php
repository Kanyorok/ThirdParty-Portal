<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Property\TenantClearanceEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyTenantClearance;
use DateTime;
use Illuminate\Http\UploadedFile;

class PropertyTenantClearanceService
{
    protected PropertyTenantClearance $clearance;

    /**
     * Create a new class instance.
     */
    public function __construct(PropertyTenantClearance $propertyTenantClearance) {}

    public static function create(
        PropertyLeaseTermination $Lease,
        DateTime                 $ExitDate,
        bool                     $FinalInspection,
        bool                     $AllDuesPaid,
        bool                     $KeysReturned,
        CodeDetail $DepositRefunded,
        string $AdditionalNotes = null,
        TenantClearanceEnum $Status,
        User $user,
        UploadedFile $document = null
    ): self {
        $clearance = PropertyTenantClearance::create([
            'LeaseId' => $Lease->LeaseID,
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


        if ($document) {
            $clearance->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::TenantClearanceView->value],
                $user
            );
        }

        activity()->causedBy($user->Id)
            ->performedOn($clearance)
            ->event('create')
            ->log("Added Tenant Clearance for Lease ID {$Lease->Id}.");

        return new self($clearance);
    }

    public function update(
        PropertyTenantClearance $LeaseId,
        DateTime $ExitDate,
        bool $FinalInspection,
        bool $AllDuesPaid,
        bool $KeysReturned,
        CodeDetail $DepositRefunded,
        string $AdditionalNotes,
        TenantClearanceEnum $Status,
        User $user,
        UploadedFile $document = null
    ): self {
        $LeaseId->update([
            'ExitDate' => $ExitDate,
            'FinalInspection' => $FinalInspection,
            'AllDuesPaid' => $AllDuesPaid,
            'KeysReturned' => $KeysReturned,
            'DepositRefunded' => $DepositRefunded->ID,
            'AdditionalNotes' => $AdditionalNotes,
            'Status' => $Status->value,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
            $LeaseId->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::TenantClearanceView->value],
                $user
            );
        }

        activity()->causedBy($user->Id)
            ->performedOn($LeaseId)
            ->event('update')
            ->log("Updated Lease Clearance for Lease ID {$LeaseId->Lease}.");

        return $this;
    }
}
