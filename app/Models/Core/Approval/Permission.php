<?php

namespace App\Models\Core\Approval;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $table = 't_Permissions';

    protected $fillable = [
        'name',
        'guard_name',
        'ModuleId',
    ];
}
