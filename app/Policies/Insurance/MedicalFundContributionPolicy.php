<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\MedicalFundContribution;

class MedicalFundContributionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MedicalFundContributionView->value);
    }
    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::MedicalFundContributionCreate->value);
    }
    public function view(User $user, MedicalFundContribution $medicalFundcontribution): bool
    {
        return $user->can(PermissionEnum::MedicalFundContributionView->value);
    }
    public function update(User $user, MedicalFundContribution $medicalFundcontribution): bool
    {
        return $user->can(PermissionEnum::MedicalFundContributionUpdate->value);
    }
    public function destroy(User $user, MedicalFundContribution $medicalFundcontribution): bool
    {
        return $user->can(PermissionEnum::MedicalFundContributionDelete->value);
    }
}
