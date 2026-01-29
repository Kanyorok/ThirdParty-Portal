<?php

namespace App\Policies\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\ThirdParty\ThirdPartyUser;

class SupplierPolicy
{
    public function view(ThirdPartyUser $user, SupplierMaster $supplier): bool
    {
        return $user->ThirdPartyId === $supplier->ThirdPartyId;
    }

    public function update(ThirdPartyUser $user, SupplierMaster $supplier): bool
    {
        // Can only update if pending or rejected
        return $user->ThirdPartyId === $supplier->ThirdPartyId
            && $user->isActive()
            && in_array($supplier->ApprovalStatus, [
                ThirdPartyApprovalStatusEnum::Pending,
                ThirdPartyApprovalStatusEnum::Rejected,
            ]);
    }

    public function submitForApproval(ThirdPartyUser $user, SupplierMaster $supplier): bool
    {
        return $user->ThirdPartyId === $supplier->ThirdPartyId
            && $user->isActive()
            && $supplier->ApprovalStatus === ThirdPartyApprovalStatusEnum::Pending;
    }

    public function updateCategories(ThirdPartyUser $user, SupplierMaster $supplier): bool
    {
        // Allow updating categories only if not approved
        return $user->ThirdPartyId === $supplier->ThirdPartyId
            && $user->isActive()
            && $supplier->ApprovalStatus !== ThirdPartyApprovalStatusEnum::Approved;
    }

    public function participate(ThirdPartyUser $user, SupplierMaster $supplier): bool
    {
        // Can participate in procurement if approved and prequalified
        return $user->ThirdPartyId === $supplier->ThirdPartyId
            && $user->isActive()
            && $supplier->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved
            && $supplier->IsPrequalified === true;
    }
}
