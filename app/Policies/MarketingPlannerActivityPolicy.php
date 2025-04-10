<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Enums\Marketing\PlannerStatus;
use App\Models\MarketingPlanner;
use App\Models\MarketingPlannerActivity;
use App\Models\User;

class MarketingPlannerActivityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MarketingPlannerRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MarketingPlannerActivity $marketingPlannerActivity): bool
    {
        return $user->can(PermissionEnum::MarketingPlannerRead->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, MarketingPlanner $planner): bool
    {
        return ($planner->OwnerId === $user->Id && $planner->Status->value === PlannerStatus::Draft->value);
        /* return $user->can(PermissionEnum::MarketingPlannerWrite->value);*/
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingPlannerActivity $marketingPlannerActivity): bool
    {
        return false;
        //return $user->can(PermissionEnum::MarketingPlannerUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingPlanner $planner, MarketingPlannerActivity $marketingPlannerActivity): bool
    {
        return ($planner->OwnerId === $user->Id && $planner->Status->value === PlannerStatus::Draft->value);
        //return $user->can(PermissionEnum::MarketingPlannerDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketingPlannerActivity $marketingPlannerActivity): bool
    {
        return false;
        //return $user->can(PermissionEnum::MarketingPlannerDelete->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketingPlannerActivity $marketingPlannerActivity): bool
    {
        return false;
    }
}
