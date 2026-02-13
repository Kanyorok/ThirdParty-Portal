<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Property\PropertyNewLeaseEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Workflow\ApprovalWorkflow;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
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
        string $TerminationDate,
        CodeDetail $TerminationReason,
        string $Remarks = null,
        string $Status,
        User $user,
        UploadedFile $document = null
    ): self {
        DB::beginTransaction();

        try {
            $termination = PropertyLeaseTermination::create([
                'LeaseID' => $LeaseID->Id,
                'TerminationDate' => $TerminationDate,
                'TerminationReason' => $TerminationReason->ID,
                'Remarks' => $Remarks,
                'Status' => $Status,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            if ($document) {
                $termination->newDocument(
                    ModulesEnum::Property,
                    $document,
                    [PermissionEnum::PropertyLeaseTerminationView->value],
                    $user
                );
            }

            $pdf = Pdf::loadView(
                'property.tenantmanagement.leasemanagement.leasetermination.TerminationLetter',
                compact('termination')
            )->output();

            $termination->newDocumentFromContent(
                module: ModulesEnum::Property,
                extension: ExtensionsEnum::Pdf,
                fileName: "Lease_Termination_{$LeaseID->LeaseNumber}.pdf",
                content: $pdf,
                actor: $user,
                permissions: [PermissionEnum::PropertyLeaseTerminationView->value]
            );

            $terminationflow = new ApprovalWorkflow('ApprovalStatus', 'Status');
            $terminationflow->submit(
                $termination,
                $user,
                ApprovalEnum::Pending,
                'Lease Termination Submitted for Approval'
            );

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
