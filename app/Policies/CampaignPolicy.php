<?php

namespace App\Policies;

use App\Enums\CampaignStatusEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\BR\DebtProduct;
use App\Models\CRM\Campaign;

class CampaignPolicy
{
    public function debt(User $user): bool
    {
        return $user->can(PermissionEnum::DebtNotificationSend->value);
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::CampaignRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Campaign $campaign): bool
    {
        if ($campaign->Status->value === CampaignStatusEnum::Draft->value) {
            return ($campaign->CreatedBy === $user->Id);
        }

        return $user->can(PermissionEnum::CampaignRead->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::CampaignWrite->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Campaign $campaign): bool
    {
        if ($campaign->Status->value === CampaignStatusEnum::Draft->value) {
            return ($campaign->CreatedBy === $user->Id);
        }

        if ($campaign->list->Source === DebtProduct::getPrimaryKey()) {
            return $this->debt($user);
        }

        if ($campaign->Status->value === CampaignStatusEnum::Sent->value) {
            return false;
        }

        return $user->can(PermissionEnum::CampaignUpdate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function approve(User $user, Campaign $campaign): bool
    {
        if ($campaign->Status->value === CampaignStatusEnum::Sent->value) {
            return false;
        }

        if ($campaign->CreatedBy === $user->Id) {
            return false;
        }

        return $user->can(PermissionEnum::CampaignApproval->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Campaign $campaign): bool
    {
        if ($campaign->Status->value === CampaignStatusEnum::Sent->value) {
            return false;
        }

        if ($campaign->Status->value === CampaignStatusEnum::Draft->value) {
            return ($campaign->CreatedBy === $user->Id);
        }
        return $user->can(PermissionEnum::CampaignDelete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Campaign $campaign): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Campaign $campaign): bool
    {
        return false;
    }
}
