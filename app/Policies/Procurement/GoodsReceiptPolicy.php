<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\GoodsReceipt;
use Illuminate\Auth\Access\HandlesAuthorization;

class GoodsReceiptPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::GoodsReceiptRead->value);
    }

    public function view(User $user, GoodsReceipt $grn): bool
    {
        return $user->can(PermissionEnum::GoodsReceiptRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::GoodsReceiptWrite->value);
    }

    public function update(User $user, GoodsReceipt $grn): bool
    {
        return $user->can(PermissionEnum::GoodsReceiptUpdate->value);
    }

    public function delete(User $user, GoodsReceipt $grn): bool
    {
        return $user->can(PermissionEnum::GoodsReceiptDelete->value);
    }

    public function approve(User $user, GoodsReceipt $grn): bool
    {
        return $user->can(PermissionEnum::GoodsReceiptApprove->value);
    }

    public function post(User $user): bool
    {
        return $user->can(PermissionEnum::GoodsReceiptApprove->value);
    }
}
