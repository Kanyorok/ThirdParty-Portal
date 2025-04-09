<?php

namespace App\Policies;

use App\Enums\Core\PermissionEnum;
use App\Models\MeetingRoom;
use App\Models\User;

class MeetingRoomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MeetingRooms->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MeetingRoom $meetingRoom): bool
    {
        return $user->can(PermissionEnum::MeetingRooms->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MeetingRooms->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MeetingRoom $meetingRoom): bool
    {
        return $user->can(PermissionEnum::MeetingRooms->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MeetingRoom $meetingRoom): bool
    {
        return $user->can(PermissionEnum::MeetingRooms->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MeetingRoom $meetingRoom): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MeetingRoom $meetingRoom): bool
    {
        return false;
    }
}
