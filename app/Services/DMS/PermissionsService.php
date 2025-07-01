<?php

namespace App\Services\DMS;

use App\Enums\Core\RoleEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Core\SpecialPermission;
use App\Models\DMS\Document;
use App\Models\DMS\Repository;
use App\Services\CRMEmailService;
use App\Services\PartyService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class PermissionsService
{
    protected static function copyRepoPermissions(Repository $repository, Repository|Document $child, User $actor): bool
    {
        $date = now();
        $permissions = $repository->permissions->map(function ($permission) use ($actor, $child, $date) {
            return [
                'Permission' => $permission->Permission,
                'Party' => $permission->Party,
                'PartyID' => $permission->PartyID,
                'Model' => $child->getMorphClass(),
                'ModelID' => $child->Id,
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

    private static function bulkInsert(Collection $permissions): bool
    {
        $result = true;
        foreach ($permissions->chunk(210) as $chunk) {//MSSQL 2100/10
            $result = $result && DB::table("t_SpecialPermissions")->insert($chunk->toArray());
        }
        return $result;
    }

    protected static function userPermissions(Repository|Document $child, array $permissions, RoleEnum $role, User $actor): bool
    {
        $users = User::query()->lock('WITH(NOLOCK)')
            ->hasPermission($permissions)->get(["Id", "UserID", "Name", "Email"]);
        $date = now();
        $roles = $users->map(function ($user) use ($child, $actor, $date, $role) {
            return [
                'Permission' => $role->value,
                'Party' => User::getPrimaryKey(),
                'PartyID' => $user->Id,
                'Model' => $child->getMorphClass(),
                'ModelID' => $child->Id,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => $date,
                'ModifiedOn' => $date,
            ];
        });

        return self::bulkInsert($roles);
    }

    protected function _addPermissions(Repository|Document $child, User|Team $assignee, RoleEnum $role, User $actor, bool $notify = true): SpecialPermission
    {
        if ($assignee instanceof Team) {
            $permission = $child->permissions()->lock('WITH(NOLOCK)')
                ->where('Party', Team::getPrimaryKey())->where('PartyID', $assignee->TeamID)->first();
            if (!$permission instanceof SpecialPermission) {
                $permission = new SpecialPermission();
                $permission->fill([
                    'Model' => $child->getMorphClass(),
                    'ModelID' => $child->Id,
                    'Party' => Team::getPrimaryKey(),
                    'PartyID' => $assignee->TeamID,
                    'CreatedBy' => $actor->Id,
                    'CreatedOn' => now(),
                ]);
            }
            $permission->fill([
                'Permission' => $role->value,
                'ModifiedBy' => $actor->Id,
                'ModifiedOn' => now(),
            ])->save();

            if ($notify) {
                $users = $assignee->users()->lock('WITH(NOLOCK)')->select(['Email', 'Name'])->lock('WITH(NOLOCK)')->inRandomOrder()->limit(15)->get(['Email', 'Name']);
                $cc = $users->map(function ($user) {
                    return [$user->Name => $user->Email];
                });

                $name = ($child instanceof Document) ? 'Document ' . $child->Name : 'Repository ' . $child->Name;
                CRMEmailService::createTeam(
                    $assignee,
                    'Notification: ' . $role->name . ' permission to ' . $name,
                    '<p>The ' . $name . ' has been shared with you and your team.</p>
                        <p>Please feel free to review it.</p>',
                    SystemHelper::user(),
                    $cc->toArray()
                );
            }

            return $permission;
        }


        $permission = $child->permissions()->lock('WITH(NOLOCK)')
            ->where('Party', User::getPrimaryKey())->where('PartyID', $assignee->Id)->first();
        if (!$permission instanceof SpecialPermission) {
            $permission = new SpecialPermission();
            $permission->fill([
                'Model' => $child->getMorphClass(),
                'ModelID' => $child->Id,
                'Party' => User::getPrimaryKey(),
                'PartyID' => $assignee->Id,
                'CreatedBy' => $actor->Id,
                'CreatedOn' => now(),
            ]);
        }
        $permission->fill([
            'Permission' => $role->value,
            'ModifiedBy' => $actor->Id,
            'ModifiedOn' => now(),
        ])->save();

        if ($notify) {
            $name = ($child instanceof Document) ? 'Document ' . $child->Name : 'Repository ' . $child->Name;
            CRMEmailService::createUser(
                user: $assignee,
                subject: 'Notification: ' . $role->name . ' permission to ' . $name,
                body: '<p>The ' . $name . ' has been shared with you.</p>
                        <p>Please feel free to review it.</p>',
                actor: SystemHelper::user()
            );
        }
        return $permission;
    }

    /**
     * @throws ErroredException
     */
    protected function _trashPermissions(Repository|Document $child, SpecialPermission $permission, User $actor): Document|Repository
    {

        if (bccomp($permission->ModelID, $child->Id) !== 0 || $permission->Model !== Repository::getPrimaryKey()) {
            throw new ErroredException('This permission is not part of this item.');
        }
        $service = new PartyService($permission->party);
        activity()->causedBy($actor)->performedOn($child)->event('delete')->log('Removed ' . $service->getName() . ' ' . $permission->Permission->name . ' permission from ' . $child->Name);

        $permission->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ])->save();

        $name = ($child instanceof Document) ? 'Document ' . $child->Name : 'Repository ' . $child->Name;
        $service->sendEmail(
            'Notification: ' . $name . ' permission revoked',
            '<p>Your permission (' . $permission->Permission->name . ') for ' . $name . ' has been revoked, you can nologer view it.</p>
                   <p>Thank you for your continued support and collaboration.</p>'
        );
        return $child;
    }
}
