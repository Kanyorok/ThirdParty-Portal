<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Enums\Core\PostingEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\HR\Employee;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\ThirdParty\SupplierMaster;
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
        DateTime $assignmentDate,
        CodeDetail $assignmentType,
        ?Employee $internalTechnician,
        ?SupplierMaster $prequalifiedVendor,
        DateTime $expectedStartDate,
        DateTime $expectedCompletion,
        CodeDetail $priorityLevel,
        string $instructionNotes,
        User $user
    ): self {
        $assignment = PropertyMaintenanceAssign::create([
            'RequestNumber' => $requestNumber->Id,
            'AssignmentDate' => $assignmentDate,
            'AssignmentType' => $assignmentType->ID,
            'InternalTechnician' => $internalTechnician?->Id,
            'PrequalifiedVendor' => $prequalifiedVendor?->Id,
            'ExpectedStartDate' => $expectedStartDate,
            'ExpectedCompletion' => $expectedCompletion,
            'PriorityLevel' => $priorityLevel->ID,
            'InstructionNotes' => $instructionNotes,
            'Status' => PostingEnum::Pending->value,
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

    public function update(
        DateTime $assignmentDate,
        CodeDetail $assignmentType,
        ?Employee $internalTechnician,
        ?SupplierMaster $prequalifiedVendor,
        DateTime $expectedStartDate,
        DateTime $expectedCompletion,
        CodeDetail $priorityLevel,
        string $instructionNotes,
        User $user
    ): void {
        $this->assignment->update([
            'AssignmentDate' => $assignmentDate,
            'AssignmentType' => $assignmentType->ID,
            'InternalTechnician' => $internalTechnician?->Id,
            'PrequalifiedVendor' => $prequalifiedVendor?->Id,
            'ExpectedStartDate' => $expectedStartDate,
            'ExpectedCompletion' => $expectedCompletion,
            'PriorityLevel' => $priorityLevel->ID,
            'InstructionNotes' => $instructionNotes,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($this->assignment)
            ->event('update')
            ->log("Updated Property Assignment {$this->assignment->Id}.");
    }
}
