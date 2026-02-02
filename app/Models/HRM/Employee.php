<?php

namespace App\Models\HRM;

use App\Enums\Employee\GenderEnum;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\DMS\Image;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use UserActorTrait;
    use SoftDeletes;
    use ImageTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Employees';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'EmployeeID', 'FirstName', 'LastName', 'MiddleName', 'Email', 'Phone', 'Address', 'DateOfBirth', 'JoinDate', 'DepartmentId', 'BranchId', 'JobTitle', 'Gender', 'MaritalStatus',
        'ImageId', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'DateOfBirth' => 'date',
        'JoinDate' => 'date',
        'Gender' => GenderEnum::class,
    ];

    public function getFullNameAttribute(): string
    {
        return $this->LastName . ' ' . $this->FirstName;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchId', 'Id')->withTrashed();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DepartmentId', 'Id')->withTrashed();
    }

    public function user(): HasOne
    {
        // Correct FK mapping: t_Users.EmployeeId -> t_Employees.Id
        return $this->hasOne(User::class, 'EmployeeId', 'Id')->withTrashed();
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'EmployeeID';
    }

    protected function getImageName(): string
    {
        return $this->full_name;
    }

    public function committees(): BelongsToMany
    {
        return $this->belongsToMany(Committee::class, 't_Committee_Employee', 'EmployeeId', 'CommitteeId')
            ->withPivot(['CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn']);
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'ImageId', 'ImageID');
    }
}
