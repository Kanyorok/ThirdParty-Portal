<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ProcurementPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProcurementPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementPlanRead->value);
    }

    public function view(User $user, ProcurementPlan $procurementPlan)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementPlanRead->value);
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementPlanWrite->value);
    }

    public function update(User $user, ProcurementPlan $procurementPlan)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementPlanUpdate->value);
    }

    public function delete(User $user, ProcurementPlan $procurementPlan)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementPlanDelete->value);
    }

    public function approve(User $user, ProcurementPlan $procurementPlan)
    {
        return $user->hasPermissionTo(PermissionEnum::ProcurementPlanApprove->value);
    }
}
