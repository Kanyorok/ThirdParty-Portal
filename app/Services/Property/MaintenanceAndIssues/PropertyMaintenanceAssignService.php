<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\HRM\Employee;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\ThirdParies\Supplier;
use DateTime;

class PropertyMaintenanceAssignService
{
    private $assignment;

    /**
     * Create a new class instance.
     */
    public function __construct(PropertyMaintenanceAssign $propertyMaintenanceAssign)
    {
        $this->assignment = $propertyMaintenanceAssign;
    }

    public static function create(
        PropertyMaintenanceRequest $requestNumber,
        string $property,
        string $block,
        string $floor,
        string $unit,
        DateTime $assignmentDate,
        CodeDetail $assignmentType,
        Employee $internalTechnician,
        Supplier $prequalifiedVendor,
        DateTime $expectedStartDate,
        DateTime $expectedCompletion,
        CodeDetail $priorityLevel,
        string $instructionNotes,
        User $user
    ): self {
        $assignment = PropertyMaintenanceAssign::create([
            'RequestNumber' => $requestNumber->Id,
            'Property' => $property,
            'Block' => $block,
            'Floor' => $floor,
            'Unit' => $unit,
            'AssignmentDate' => $assignmentDate,
            'AssignmentType' => $assignmentType->Id,
            'InternalTechnician' => $internalTechnician?->Id,
            'PrequalifiedVendor' => $prequalifiedVendor?->Id,
            'ExpectedStartDate' => $expectedStartDate,
            'ExpectedCompletion' => $expectedCompletion,
            'PriorityLevel' => $priorityLevel->Id,
            'InstructionNotes' => $instructionNotes,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($assignment)
            ->event('create')
            ->log("Added Property Assignment {$assignment->Id}.");

        return new self($assignment);
    }
}
