<?php

namespace App\Policies\Procurement;

use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeeds;

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
}
