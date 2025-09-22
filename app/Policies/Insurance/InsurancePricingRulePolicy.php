<?php

namespace App\Policies\Insurance;

use App\Models\Auth\User;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\InsurancePricingRule;

class InsurancePricingRulePolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::InsurancePricingRuleView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::InsurancePricingRuleCreate->value);
    }

    public function view(User $user, InsurancePricingRule $insurancepricingrule): bool
    {
        return $user->can(PermissionEnum::InsurancePricingRuleView->value);
    }

    public function update(User $user, InsurancePricingRule $insurancepricingrule): bool
    {
        return $user->can(PermissionEnum::InsurancePricingRuleUpdate->value);
    }

    public function destroy(User $user, InsurancePricingRule $insurancepricingrule): bool
    {
        return $user->can(PermissionEnum::InsurancePricingRuleDelete->value);
    }

}
