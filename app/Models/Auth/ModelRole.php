<?php

namespace App\Models\Auth;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelRole extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_ModelRoles';
    protected $primaryKey = 'ModelRoleId';

    protected $fillable = [
        'model_id', 'model_type', 'role_id', 'BranchId',
    ];

    public static function getPrimaryKey(): string
    {
        return 'ModelRoleId';
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'Id');
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
