<?php

namespace App\Services\DMS;

use App\Models\DMS\Document;
use App\Models\DMS\Repository;
use Illuminate\Support\Facades\DB;

abstract class PermissionsService
{
    protected static function copyRepoPermissions(Repository $repository, Repository|Document $child): bool
    {
        $permissions = collect();
        $date = now();
        $repository->permissions->each(function ($permission) use ($repository, $permissions, $child, $date) {
            $permissions->push([
                'Permission' => $permission->Permission,
                'Party' => $permission->Party,
                'PartyID' => $permission->PartyID,
                'Model' => $child->getMorphClass(),
                'ModelID' => $child->Id,
                'CreatedBy' => $permission->CreatedBy,
                'ModifiedBy' => $permission->ModifiedBy,
                'CreatedOn' => $date,
                'ModifiedOn' => $date,
            ]);
        });
        if ($permissions->count() > 0) {
            return DB::table("t_SpecialPermissions")->insert($permissions->toArray());
        }
        return true;
    }
}
