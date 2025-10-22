<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\MedicalFund;

class MedicalFundPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MedicalFundView->value);
    }

    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::MedicalFundCreate->value);
    }
    public function view(User $user, MedicalFund $medicalFund): bool
    {
        return $user->can(PermissionEnum::MedicalFundView->value);
    }
    public function update(User $user, MedicalFund $medicalFund): bool
    {
        return $user->can(PermissionEnum::MedicalFundUpdate->value);
    }
    public function destroy(User $user, MedicalFund $medicalFund): bool
    {
        return $user->can(PermissionEnum::MedicalFundDelete->value);
    }
}
