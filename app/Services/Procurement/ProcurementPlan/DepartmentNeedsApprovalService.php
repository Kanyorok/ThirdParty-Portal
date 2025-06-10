<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeeds;

class DepartmentNeedsApprovalService
{
    public function __construct(public DepartmentNeeds $departmentNeeds)
    {

    }

    public function workflowApprove(User $actor): static
    {
        $this->departmentNeeds->pendingWorkflows()->where('Stage', DepartmentNeedsEnum::Approved)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        $this->departmentNeeds->forceFill([
            'Status' => DepartmentNeedsEnum::Approved->value,])->save(['timestamps' => false]);

        $this->departmentNeeds->workflows()->create([
                                              'Stage'      => DepartmentNeedsEnum::Approved->name,
                                              'Status'     => WorkflowStatus::Accepted->value,
            'Notes' => 'Department Need Approved',
                                              'CreatedBy'  => $actor->Id,
                                              'ModifiedBy' => $actor->Id,
                                             ]);

        activity()->causedBy($actor)->performedOn($this->departmentNeeds)->event('approved')->log('Approved Department Needs ' . $this->departmentNeeds->NeedID);

        return $this;
    }

    public function workflowReject(User $actor, string $reason): static
    {
        // Update pending workflows
        $this->departmentNeeds->pendingWorkflows()->where('Stage', DepartmentNeedsEnum::Rejected)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        // Update status using enum value, disable timestamps
        $this->departmentNeeds->forceFill([
            'Status' => DepartmentNeedsEnum::Rejected->value,
        ])->save(['timestamps' => false]);

        // Create workflow record
        $this->departmentNeeds->workflows()->create([
            'Stage' => DepartmentNeedsEnum::Rejected->name,
            'Status' => WorkflowStatus::RejectReturn->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->departmentNeeds)->event('reject')->log('Rejected Department Needs ' . $this->departmentNeeds->NeedID);

        return $this;
    }


    public function submit(User $actor): static
    {
        $this->departmentNeeds->forceFill([
            'Status' => DepartmentNeedsEnum::Approved->value,
        ])->save(['timestamps' => false]);

        //add workflow
        $this->departmentNeeds->workflows()->create([
            'Stage' => DepartmentNeedsEnum::Pending->name,
            'Status' => WorkflowStatus::Submitted->value,
            'Notes' => 'User Submitted',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->departmentNeeds)->event('submit')->log('Submitted ' . $this->departmentNeeds->NeedID . ' for approval.');

        return $this;
    }
}
