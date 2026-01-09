<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsolidatedProcurementPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Allow if user has access to Consolidation OR Maintenance
        return $user->can(PermissionEnum::PlanConsolidationRead->value) ||
            $user->can(PermissionEnum::PlanMaintenanceRead->value);
    }

    public function view(User $user, ConsolidatedProcurementPlan $plan): bool
    {
        return $user->can(PermissionEnum::PlanConsolidationRead->value) ||
            $user->can(PermissionEnum::PlanMaintenanceRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::PlanConsolidationWrite->value) ||
            $user->can(PermissionEnum::PlanMaintenanceWrite->value);
    }

    public function store(User $user): bool
    {
        return $this->create($user);
    }

    public function update(User $user, ConsolidatedProcurementPlan $plan): bool
    {
        return $user->can(PermissionEnum::PlanConsolidationUpdate->value) ||
            $user->can(PermissionEnum::PlanMaintenanceUpdate->value);
    }

    public function editDraft(User $user): bool
    {
        return $this->create($user); // Assuming editDraft is similar to write access
    }

    public function delete(User $user, ConsolidatedProcurementPlan $plan): bool
    {
        return $user->can(PermissionEnum::PlanConsolidationDelete->value) ||
            $user->can(PermissionEnum::PlanMaintenanceDelete->value);
    }

    public function submit(User $user, ConsolidatedProcurementPlan $plan): bool
    {
        return $user->can(PermissionEnum::ProcurementPlanSubmit->value);
    }
}
