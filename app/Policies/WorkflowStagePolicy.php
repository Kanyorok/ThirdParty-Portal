<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Core\Approval\WorkflowStage;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowStagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can approve the workflow stage.
     *
     * @param  \App\Models\Auth\User  $user
     * @param  \App\Models\Core\Approval\WorkflowStage  $stage
     * @return mixed
     */
    public function approve(User $user, WorkflowStage $stage)
    {
        // Check if the user has the specific permission for this stage
        // The permission name is stored in the related Permission model
        // or constructed as 'workflowstage_' + StageName (if we follow SP logic)

        // Best practice: Use the relationship if available
        if ($stage->permission) {
            return $user->hasPermissionTo($stage->permission->name);
        }

        // Fallback: Construct expected permission name (legacy/backup)
        $permissionName = 'workflowstage_' . str_replace(' ', '', $stage->StageName);
        return $user->hasPermissionTo($permissionName);
    }
}
