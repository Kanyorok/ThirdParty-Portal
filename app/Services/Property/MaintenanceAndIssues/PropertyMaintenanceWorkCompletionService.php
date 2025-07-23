<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;
use Date;

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
        string $property,
        string $block,
        string $floor,
        string $unit,
        string $completionDate,
        string $workDoneSummary,
        string $partsUsed,
        int $cost,
        CodeDetail $finalstatus,
        User $user
    ): self {
        $workCompletion = PropertyMaintenanceWorkCompletion::create([
            'RequestNumber' => $requestNumber->Id,
            'Property' => $property,
            'Block' => $block,
            'Floor' => $floor,
            'Unit' => $unit,
            'CompletionDate' => $completionDate,
            'WorkDoneSummary' => $workDoneSummary,
            'PartsUsed' => $partsUsed,
            'Cost' => $cost,
            'FinalStatus' => $finalstatus->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($workCompletion)

            ->event('create')
            ->log("Added Property Assignment {$workCompletion->Id}.");

        return new self($workCompletion);
    }
}
