<?php

namespace App\Policies\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Procurement\BidSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenderSubmissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::BidSubmissionRead->value);
    }

    public function view(User $user, BidSubmission $submission): bool
    {
        return $user->can(PermissionEnum::BidSubmissionRead->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::BidSubmissionWrite->value);
    }

    public function update(User $user, BidSubmission $submission): bool
    {
        return $user->can(PermissionEnum::BidSubmissionUpdate->value);
    }

    public function delete(User $user, BidSubmission $submission): bool
    {
        return $user->can(PermissionEnum::BidSubmissionDelete->value);
    }
}
