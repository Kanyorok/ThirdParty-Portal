<?php

namespace App\Models\HRM;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_Departments';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'DepartmentID', 'Description', 'HeadId', 'DeputyHeadId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'HeadId', 'Id')->withTrashed();
    }

    public function deputy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'DeputyHeadId', 'Id')->withTrashed();
    }

    public static function getPrimaryKey(): string
    {
        return 'DepartmentID';
    }

    public function getRouteKeyName(): string
    {
        return 'DepartmentID';
    }
}
