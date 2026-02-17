<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceCommissionRule;

class CommissionRulePolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::CommissionRuleView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::CommissionRuleCreate->value);
    }

    public function view(User $user, BancassuranceCommissionRule $bancassurancecommissionRule): bool
    {
        return $user->can(PermissionEnum::CommissionRuleView->value);
    }

    public function update(User $user, BancassuranceCommissionRule $bancassurancecommissionRule): bool
    {
        return $user->can(PermissionEnum::CommissionRuleUpdate->value);
    }

    public function destroy(User $user, BancassuranceCommissionRule $bancassurancecommissionRule): bool
    {
        return $user->can(PermissionEnum::CommissionRuleDelete->value);
    }
}
