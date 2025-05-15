<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Enums\Feedback\SurveyStatusEnum;
use App\Models\Auth\User;
use App\Models\CRM\Survey;

class SurveyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::SurveyRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Survey $survey): bool
    {
        if ($survey->Status->value === SurveyStatusEnum::Draft->value) {
            return ($user->Id === $survey->CreatedBy);
        }
        return $user->can(PermissionEnum::SurveyRead->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::SurveyWrite->value);
    }

    /**
     * Used only to add Questions.
     */
    public function update(User $user, Survey $survey): bool
    {
        return ($survey->Status->value === SurveyStatusEnum::Draft->value && $user->Id === $survey->CreatedBy);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Survey $survey): bool
    {
        return $user->can(PermissionEnum::SurveyDelete->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function approve(User $user, Survey $survey): bool
    {
        return $user->can(PermissionEnum::SurveyApproval->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Survey $survey): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Survey $survey): bool
    {
        return false;
    }
}
