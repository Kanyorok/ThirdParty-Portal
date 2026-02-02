<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;

class ProcurementPlanMaintainPolicy
{
    public function viewAny(User $user)
    {
        return $user->can(PermissionEnum::PlanMaintenanceRead->value);
    }

    public function view(User $user)
    {
        return $user->can(PermissionEnum::PlanMaintenanceRead->value);
    }

    public function create(User $user)
    {
        return $user->can(PermissionEnum::PlanMaintenanceWrite->value);
    }

    public function store(User $user)
    {
        return $user->can(PermissionEnum::PlanMaintenanceWrite->value);
    }

    public function editDraft(User $user, ConsolidatedProcurementPlan $plan)
    {
        return $user->can(PermissionEnum::PlanMaintenanceWrite->value)
            && $plan->Status === \App\Enums\ProcurementPlanStatusEnum::Draft;
    }
}
