<?php

namespace App\Policies\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Inventory\Store;
use Illuminate\Auth\Access\Response;

class TransactionTransferPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferView->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferView->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferUpdate->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->can(PermissionEnum::TransactionTransferDestroy->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function edit(User $user, TransactionTransfer $transactionTransfer): bool
    {
        return $user->can(PermissionEnum::TransactionTransferRestore->value);
    }
   //  public function approve(User $user, TransactionTransfer $transfer): bool
   // {
        // Don't allow approving if already approved or rejected
       // if (
          //  $transfer->Status === Transfers::Approved->value ||
          //  $transfer->Status === Transfers::Rejected->value
        //) {
           // return false;
       // }

        // Don't allow approving your own requisition
        // if ($requisition->CreatedBy === $user->Id) {
        //    return false;
        // }

        // Must have the approval permission
       // return $user->can(PermissionEnum::TransactionTransferApproval->value);
    //}

}
