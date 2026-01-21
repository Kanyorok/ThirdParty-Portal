<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\Criteria;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluationCriteriaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::EvaluationCriteriaRead->value);
    }

    public function view(User $user, Criteria $criteria)
    {
        return $user->hasPermissionTo(PermissionEnum::EvaluationCriteriaRead->value);
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo(PermissionEnum::EvaluationCriteriaWrite->value);
    }

    public function update(User $user, Criteria $criteria)
    {
        return $user->hasPermissionTo(PermissionEnum::EvaluationCriteriaUpdate->value);
    }

    public function delete(User $user, Criteria $criteria)
    {
        return $user->hasPermissionTo(PermissionEnum::EvaluationCriteriaDelete->value);
    }
}
