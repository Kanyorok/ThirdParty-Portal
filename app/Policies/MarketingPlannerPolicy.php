<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Enums\Marketing\PlannerStatus;
use App\Models\Auth\User;
use App\Models\CRM\MarketingPlanner;

class MarketingPlannerPolicy
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
    public function view(User $user, MarketingPlanner $marketingPlanner): bool
    {
        if ($marketingPlanner->OwnerId === $user->Id) {
            return true;
        }

        if ($marketingPlanner->Status->value === PlannerStatus::Draft) {
            return false;
        }

        //check if draf
        return ($user->can(PermissionEnum::MarketingPlannerRead->value));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MarketingPlannerWrite->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MarketingPlanner $marketingPlanner): bool
    {
        if ($marketingPlanner->OwnerId === $user->Id) {
            return true;
        }
        return $user->can(PermissionEnum::MarketingPlannerUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MarketingPlanner $marketingPlanner): bool
    {
        return $user->can(PermissionEnum::MarketingPlannerDelete->value);
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function approve(User $user, MarketingPlanner $marketingPlanner): bool
    {
        return $user->can(PermissionEnum::MarketingPlannerApproval->value);
    }


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MarketingPlanner $marketingPlanner): bool
    {
        return $user->can(PermissionEnum::MarketingPlannerDelete->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MarketingPlanner $marketingPlanner): bool
    {
        return false;
    }
}
