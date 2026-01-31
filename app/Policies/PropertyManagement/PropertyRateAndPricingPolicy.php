<?php

namespace App\Policies\PropertyManagement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyRateAndPricing;

class PropertyRateAndPricingPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyRateAndPricingView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::PropertyRateAndPricingCreate->value);
    }

    public function view(User $user, PropertyRateAndPricing $propertyRateAndPricing): bool
    {
        return $user->can(PermissionEnum::PropertyRateAndPricingView->value);
    }

    public function update(User $user, PropertyRateAndPricing $propertyRateAndPricing): bool
    {
        return $user->can(PermissionEnum::PropertyRateAndPricingUpdate->value);
    }

    public function destroy(User $user, PropertyRateAndPricing $propertyRateAndPricing): bool
    {
        return $user->can(PermissionEnum::PropertyRateAndPricingDelete->value);
    }
}
