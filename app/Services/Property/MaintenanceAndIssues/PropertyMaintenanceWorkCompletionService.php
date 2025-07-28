<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Core\PostingEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;
use Illuminate\Http\UploadedFile;

class PropertyMaintenanceWorkCompletionService
{
   private $assignment;

    /**
     * Create a new class instance.
     */
    public function __construct(PropertyMaintenanceWorkCompletion $propertyMaintenanceWorkCompletion)
    {
        $this->assignment = $propertyMaintenanceWorkCompletion;
    }

    public static function create(
        PropertyMaintenanceAssign $requestNumber,
        string $completionDate,
        string $workDoneSummary,
        string $partsUsed,
        int $cost,
        CodeDetail $finalstatus,
        User $user,
        UploadedFile $document = null
    ): self {
        $workCompletion = PropertyMaintenanceWorkCompletion::create([
            'RequestNumber' => $requestNumber->Id,
            'CompletionDate' => $completionDate,
            'WorkDoneSummary' => $workDoneSummary,
            'PartsUsed' => $partsUsed,
            'Cost' => $cost,
            'FinalStatus' => $finalstatus->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

 
        if (strtolower($finalstatus->Description) === 'completed') {
            $requestNumber->update([
                'Status' => PostingEnum::Completed->value
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
        PropertyMaintenanceWorkCompletion $requestNumber,
        string $completionDate,
        string $workDoneSummary,
        string $partsUsed,
        int $cost,
        CodeDetail $finalstatus,
        User $user,
        UploadedFile $document = null
        ): self {
        $requestNumber->update([
            'RequestNumber' => $requestNumber->RequestNumber,
            'CompletionDate' => $completionDate,
            'WorkDoneSummary' => $workDoneSummary,
            'PartsUsed' => $partsUsed,
            'Cost' => $cost,
            'FinalStatus' => $finalstatus->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);


        if (strtolower($finalstatus->Description) === 'completed') {
            $requestNumber->update([
                'Status' => PostingEnum::Completed->value
            ]);
        }


        if ($document) {
            $requestNumber->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyMaintenanceWorkCompletionView->value],
                $user
                );
        }

        activity()
            ->causedBy($user)
            ->performedOn($requestNumber->withoutRelations())
            ->event('update')
            ->log("Updated Work Completion {$requestNumber->Id}");



        return new self($requestNumber);
    }
}
