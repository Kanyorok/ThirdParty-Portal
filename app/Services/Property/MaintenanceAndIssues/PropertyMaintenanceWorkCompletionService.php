<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Core\PostingEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;
use Illuminate\Http\UploadedFile;

class PropertyMaintenanceWorkCompletionService
{
   private $assignment;

    public function __construct(PropertyMaintenanceWorkCompletion $propertyMaintenanceWorkCompletion)
    {
    }

    public static function create(
        PropertyMaintenanceAssign $requestNumber,
        string $completionDate,
        string $workDoneSummary,
        ?string $partsUsed = null,
        ?int    $cost = null,
        CodeDetail $finalstatus,
        User $user,
        UploadedFile $document = null
    ): self {
        $workCompletion = PropertyMaintenanceWorkCompletion::create([
            'RequestNumber' => $requestNumber->Id,
            'CompletionDate' => $completionDate,
            'WorkDoneSummary' => $workDoneSummary,
            'PartsUsed' => $partsUsed ?? null,
            'Cost' => $cost ?? null,
            'FinalStatus' => $finalstatus->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);


        if (strtolower($finalstatus->Description) === 'completed') {
            $requestNumber->update([
                'Status' => PostingEnum::Completed->value,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now()
            ]);
        }

        if ($document) {
            $workCompletion->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyMaintenanceAssignView->value],
                $user
                );
            }

        activity()
            ->causedBy($user)
            ->performedOn($workCompletion)

            ->event('create')
            ->log("Added Property Assignment {$workCompletion->Id}.");

        return new self($workCompletion);
    }


    public static function update(
        PropertyMaintenanceWorkCompletion $workCompletion,
        string $completionDate,
        string $workDoneSummary,
        ?string $partsUsed,
        ?int $cost,
        CodeDetail $finalstatus,
        User $user,
        UploadedFile $document = null
    ): self {

        $workCompletion->update([
            'CompletionDate' => $completionDate,
            'WorkDoneSummary' => $workDoneSummary,
            'PartsUsed' => $partsUsed,
            'Cost' => $cost,
            'FinalStatus' => $finalstatus->ID,
            'ModifiedBy' => $user->Id,
        ]);

        $assignment = PropertyMaintenanceAssign::find($workCompletion->RequestNumber);

        if ($assignment) {
            $assignment->update([
                'Status' => strtolower($finalstatus->Description) === 'completed'
                    ? PostingEnum::Completed->value
                    : PostingEnum::Pending->value,
                'ModifiedBy' => $user->Id,
                'ModifiedOn' => now(),
            ]);
        }

        if ($document) {
            $workCompletion->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyMaintenanceWorkCompletionView->value],
                $user
            );
        }

        activity()
            ->causedBy($user)
            ->performedOn($workCompletion)
            ->event('update')
            ->log("Updated Work Completion {$workCompletion->Id}");

        return new self($workCompletion);
    }
}
