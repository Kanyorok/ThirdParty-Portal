<?php

namespace App\Models\HRM;

use App\Models\Auth\User;
use App\Models\HR\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_Departments';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'DepartmentID', 'Description', 'HeadId', 'DeputyHeadId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'HeadId', 'Id')->withTrashed();
    }

    public function deputy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'DeputyHeadId', 'Id')->withTrashed();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'DepartmentID', 'Id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'DepartmentId', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'DepartmentID';
    }

    public function getRouteKeyName(): string
    {
        return 'DepartmentID';
    }

    public function getFullNameAttribute()
    {
        return $this->Name;
    }
}
