<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\ThirdParty\SupplierMaster;
use App\Services\Core\PermissionResolver;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return PermissionResolver::can($user, 'supplier', 'read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SupplierMaster $supplierMaster): bool
    {
        return PermissionResolver::can($user, 'supplier', 'read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return PermissionResolver::can($user, 'supplier', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupplierMaster $supplierMaster): bool
    {
        return PermissionResolver::can($user, 'supplier', 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupplierMaster $supplierMaster): bool
    {
        return PermissionResolver::can($user, 'supplier', 'delete');
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, SupplierMaster $supplierMaster): bool
    {
        return PermissionResolver::can($user, 'supplier', 'approve');
    }
}
