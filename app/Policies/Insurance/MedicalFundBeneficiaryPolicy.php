<?php

namespace App\Policies\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Insurance\MedicalFundBeneficiary;

class MedicalFundBeneficiaryPolicy
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
        return $user->can(PermissionEnum::MedicalFundBeneficiaryView->value);
    }
    public function store(User $user): bool
    {
        return $user->can(PermissionEnum::MedicalFundBeneficiaryCreate->value);
    }
    public function view(User $user, MedicalFundBeneficiary $medicalfundbeneficiary): bool
    {
        return $user->can(PermissionEnum::MedicalFundBeneficiaryView->value);
    }
    public function update(User $user, MedicalFundBeneficiary $medicalfundbeneficiary): bool
    {
        return $user->can(PermissionEnum::MedicalFundBeneficiaryUpdate->value);
    }
    public function destroy(User $user, MedicalFundBeneficiary $medicalfundbeneficiary): bool
    {
        return $user->can(PermissionEnum::MedicalFundBeneficiaryDelete->value);
    }
}
