<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\Branch;
use Spatie\Permission\Models\Role;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelRole extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ModelRoles';
    protected $fillable = ['model_id', 'model_type', 'role_id', 'BranchId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'];

    public static function getPrimaryKey(): string
    {
        return 'ModelRoleId';
    }
    
    public function branch()
    {
        return $this->belongsTo(\App\Models\Core\Branch::class, 'BranchId', 'Id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id', 'id');
    }

}
