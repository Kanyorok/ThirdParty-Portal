<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;

class DepartmentNeedsApprovalService
{
    public function __construct(public DepartmentNeed $departmentNeeds)
    {

    }

       public function submit(User $actor): static
    {
          $remarks = 'User Submitted';
        (new DepartmentNeedsWorkflowService())->submit($this->departmentNeeds, $actor, $remarks);
        activity()->causedBy($actor)->performedOn($this->departmentNeeds)->event('submit')->log('Submitted ' . $this->departmentNeeds->NeedID . ' for approval.');
        return $this;
    }
    public function workflowApprove(User $actor): static
    {
        $permissionId = $actor->Id;
         $remarks = 'Department Need Approved';  // Or get from elsewhere
        (new DepartmentNeedsWorkflowService())->approve($this->departmentNeeds, $actor, $remarks);
        // Additional logging 
        activity()->causedBy($actor)->performedOn($this->departmentNeeds)->event('approved')->log('Approved Department Needs ' . $this->departmentNeeds->NeedID);
        return $this;
    }

    public function workflowReject(User $actor, string $reason): static
    {
        (new DepartmentNeedsWorkflowService())->reject($this->departmentNeeds, $actor, $reason);
        activity()->causedBy($actor)->performedOn($this->departmentNeeds)->event('reject')->log('Rejected Department Needs ' . $this->departmentNeeds->NeedID);
        return $this;
    }


 
}
