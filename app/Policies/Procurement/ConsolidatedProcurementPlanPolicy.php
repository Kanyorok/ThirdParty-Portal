<?php

namespace App\Policies\Procurement;

use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\DepartmentNeeds;
use App\Enums\Core\PermissionEnum;
use App\Enums\ProcurementPlanStatusEnum;

class ConsolidatedProcurementPlanPolicy
{
    public function viewAny(User $user)
    {

        return $user->can(PermissionEnum::PlanConsolidationRead->value);
    }

    public function view(User $user, DepartmentNeeds $need)
    {
        return $user->can(PermissionEnum::PlanConsolidationRead->value);
    }

    public function create(User $user)
    {
        return $user->can(PermissionEnum::PlanConsolidationWrite->value);
    }

    public function approve(User $user, ConsolidatedProcurementPlan $consolidatedProcurementPlan): bool
    {
        //dd($departmentNeeds);
        if ($consolidatedProcurementPlan->Status->value === ProcurementPlanStatusEnum::Approved->value) {
            return false;
        }

        if ($consolidatedProcurementPlan->CreatedBy === $user->Id) {
            return false;
        }

        return $user->can(PermissionEnum::PlanMaintenanceApproval->value);
    }
}
