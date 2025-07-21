<?php

namespace App\Models\Auth;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

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
        return $this->belongsTo(Branch::class, 'BranchId', 'Id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

}
