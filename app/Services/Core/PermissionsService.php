<?php

namespace App\Services\Core;

use App\Enums\Core\RoleEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Interfaces\SpecialPermissionContract;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Core\SpecialPermission;
use App\Services\CRMEmailService;
use App\Services\PartyService;
use App\Traits\Model\SpecialPermissionTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class PermissionsService
{

    public static function copyPermissions(SpecialPermissionContract $source, SpecialPermissionContract $destination, User $actor): bool
    {
        /*if (!self::checkImplementation($destination)) {
            throw new RuntimeException("Source does not use Special Permission Trait");
        }

        if (!self::checkImplementation($source)) {
            throw new RuntimeException("Destination does not use Special Permission Trait");
        }*/
        $date = now();
        $permissions = $source->permissions->map(function ($permission) use ($actor, $destination, $date) {
            return [
                'Permission' => $permission->Permission,
                'Party' => $permission->Party,
                'PartyID' => $permission->PartyID,
                'Model' => $destination->getMorphClass(),
                'ModelID' => $destination->{$destination->getKeyName()},
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => $date,
                'ModifiedOn' => $date,
            ];
        });
        if ($permissions->count() > 0) {
            return self::bulkInsert($permissions);
        }
        return true;
    }

    private static function checkImplementation(SpecialPermissionContract $class): bool
    {
        $traits = class_uses($class);
        if (is_array($traits)) {
            return in_array(SpecialPermissionTrait::class, $traits, true);
        }
        return false;
    }

    private static function bulkInsert(Collection $permissions): bool
    {
        $result = true;
        foreach ($permissions->chunk(210) as $chunk) {//MSSQL 2100/10
            $result = $result && DB::table("t_SpecialPermissions")->insert($chunk->toArray());
        }
        return $result;
    }

    protected static function userPermissions(SpecialPermissionContract $destination, array $permissions, RoleEnum $role, User $actor): bool
    {
        /* if (!self::checkImplementation($destination)) {
             throw new RuntimeException("Destination does not use Special Permission Trait");
         }*/

        $users = User::query()->lock('WITH(NOLOCK)')
            ->hasPermission($permissions)->get(["Id", "UserID", "Name", "Email"]);
        $date = now();
        $roles = $users->map(function ($user) use ($destination, $actor, $date, $role) {
            return [
                'Permission' => $role->value,
                'Party' => User::getPrimaryKey(),
                'PartyID' => $user->Id,
                'Model' => $destination->getMorphClass(),
                'ModelID' => $destination->{$destination->getKeyName()},
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => $date,
                'ModifiedOn' => $date,
            ];
        });

        return self::bulkInsert($roles);
    }

    /**
     * @throws ErroredException
     */
    protected function _addPermissions(SpecialPermissionContract $destination, User|Team $assignee, RoleEnum $role, User $actor, bool $notify = true): SpecialPermission
    {
        if (!$this->_checkPermissions($destination, $actor, $role)) {
            throw new ErroredException('You do not have permission to add this permission.');
        }
        if ($assignee instanceof Team) {
            $permission = $destination->permissions()->lock('WITH(NOLOCK)')
                ->where('Party', Team::getPrimaryKey())->where('PartyID', $assignee->TeamID)->first();
            if (!$permission instanceof SpecialPermission) {
                $permission = new SpecialPermission();
                $permission->fill([
                    'Model' => $destination->getMorphClass(),
                    'ModelID' => $destination->{$destination->getKeyName()},
                    'Party' => Team::getPrimaryKey(),
                    'PartyID' => $assignee->TeamID,
                    'CreatedBy' => $actor->Id,
                ]);
            }
            $permission->fill([
                'Permission' => $role->value,
                'ModifiedBy' => $actor->Id,
            ])->save();

            if ($notify) {
                $users = $assignee->users()->lock('WITH(NOLOCK)')->select(['Email', 'Name'])->lock('WITH(NOLOCK)')->inRandomOrder()->limit(15)->get(['Email', 'Name']);
                $cc = $users->map(function ($user) {
                    return [$user->Name => $user->Email];
                });

                CRMEmailService::createTeam(
                    team: $assignee,
                    subject: Str::of($destination->getShareEmailSubject())->replace('#permission', $role->name)->toString(),
                    body: '<p>The ' . $destination->getSharedName() . ' has been shared with you and your team.</p>
                        <p>Please feel free to review it.</p>',
                    actor: SystemHelper::user(),
                    cc: $cc->toArray()
                );
            }

            return $permission;
        }

        $permission = $destination->permissions()->lock('WITH(NOLOCK)')
            ->where('Party', User::getPrimaryKey())->where('PartyID', $assignee->Id)->first();
        if (!$permission instanceof SpecialPermission) {
            $permission = new SpecialPermission();
            $permission->fill([
                'Model' => $destination->getMorphClass(),
                'ModelID' => $destination->{$destination->getKeyName()},
                'Party' => User::getPrimaryKey(),
                'PartyID' => $assignee->Id,
                'CreatedBy' => $actor->Id,
            ]);
        }
        $permission->fill([
            'Permission' => $role->value,
            'ModifiedBy' => $actor->Id,
        ])->save();

        if ($notify) {
            CRMEmailService::createUser(
                user: $assignee,
                subject: Str::of($destination->getShareEmailSubject())->replace('#permission', $role->name)->toString(),
                body: '<p>The ' . $destination->getSharedName() . ' has been shared with you.</p>
                        <p>Please feel free to review it.</p>',
                actor: SystemHelper::user()
            );
        }
        return $permission;
    }

    protected function _checkPermissions(SpecialPermissionContract $destination, User $actor, RoleEnum $role = null): bool
    {
        if (SystemHelper::isSystem($actor)) {
            return true;
        }

        if (!$role instanceof RoleEnum) {
            return $destination->permissions()->lock('WITH(NOLOCK)')
                ->where('Party', User::getPrimaryKey())->where('PartyID', $actor->Id)
                ->whereIn('Permission', [RoleEnum::Share->value, RoleEnum::Admin->value])
                ->exists();
        }
        $permission = $destination->permissions()->lock('WITH(NOLOCK)')
            ->where('Party', User::getPrimaryKey())->where('PartyID', $actor->Id)->first();

        if (!$permission instanceof SpecialPermission) {
            return false;
        }

        if (!in_array($permission->Permission->value, [RoleEnum::Admin->value, RoleEnum::Share->value], true)) {
            return false;
        }
        if ($permission->Permission->value !== RoleEnum::Admin->value && $role->value === RoleEnum::Admin->value) {
            return false;
        }

        return true;
    }

    /**
     * @throws ErroredException
     */
    protected function _trashPermissions($destination, SpecialPermission $permission, User $actor)
    {
        if (($permission->Model !== $destination->getMorphClass()) || (bccomp($permission->ModelID, $destination->{$destination->getKeyName()}) !== 0)) {
            throw new ErroredException('This permission is not part of this item.');
        }

        if (!$this->_checkPermissions($destination, $actor)) {
            throw new ErroredException('You do not have permission to modify this permission.');
        }

        $service = new PartyService($permission->party);
        activity()->causedBy($actor)->performedOn($destination)->event('delete')->log('Removed ' . $service->getName() . ' ' . $permission->Permission->name . ' permission from ' . $destination->getSharedName());

        $permission->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ])->save();

        $service->sendEmail(
            'Notification: ' . $destination->getSharedName() . ' permission revoked',
            '<p>Your permission (' . $permission->Permission->name . ') for ' . $destination->getSharedName() . ' has been revoked, you cannot access it.</p>
                   <p>Thank you for your continued support and collaboration.</p>'
        );
        return $destination;
    }

    //dynammically add workflowStage permission
public static function createWorkflowStagePermission(int $stageId, string $stageName, string $workflowSource, int $createdBy = 1)
{
    // Optional: fetch module ID if your permissions are linked to modules
    $moduleId = DB::table('t_ModuleSources')
        ->where('DocumentType', $workflowSource)
        ->value('ModuleID');

    // Permission name can be standardized
    $permissionName = 'workflow_stage_' . $stageId;

    // Insert or update permission
    DB::table('t_Permissions')->updateOrInsert(
        ['PermissionName' => $permissionName],
        [
            'DisplayName' => $stageName,
            'ModuleID' => $moduleId,
            'CreatedBy' => $createdBy,
            'CreatedOn' => now(),
        ]
    );

    return $permissionName;
}
}
