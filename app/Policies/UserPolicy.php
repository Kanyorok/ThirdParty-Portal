<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\User;
use App\Services\UserService;

class UserPolicy
{
    public function marketingManager(User $user): bool
    {
        return ((new UserService($user))->isMarketingManager());
    }

    public function ceo(User $user): bool
    {
        return $user->can(PermissionEnum::Ceo->value);
    }

    public function feedback(User $user): bool
    {
        return $user->canAny([PermissionEnum::SurveyRead->value, PermissionEnum::ReviewsView->value]);
    }

    public function thirdParties(User $user): bool
    {
        return $user->canAny([PermissionEnum::LeadRead->value, PermissionEnum::Members->value,  PermissionEnum::BoardManage->value]);
    }

    public function settings(User $user): bool
    {
        return $user->canAny([PermissionEnum::Users->value, PermissionEnum::Roles->value, PermissionEnum::Teams->value, PermissionEnum::ListsView->value]);
    }

    public function debt(User $user): bool
    {
        return $user->canAny([PermissionEnum::DebtCollectionView->value, PermissionEnum::DebtCollectionView->value]);
    }

    public function marketing(User $user): bool
    {
        return $user->canAny([PermissionEnum::MarketingListRead->value, PermissionEnum::MarketingPlannerRead->value, PermissionEnum::SocialRead->value, PermissionEnum::CampaignRead->value, PermissionEnum::Competitor->value]);
    }

    public function meetings(User $user): bool
    {
        return $user->can(PermissionEnum::UsersMeeting->value);
    }

    public function messaging(User $user): bool
    {
        return $user->can(PermissionEnum::UsersMessaging->value);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::Users->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::Users->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::Users->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::Users->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::Users->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::Users->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::Users->value);
    }
}
