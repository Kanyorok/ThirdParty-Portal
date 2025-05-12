<?php

namespace App\Models;

use App\Enums\Employee\GenderEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use UserActorTrait, SoftDeletes;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $table = 't_Employees';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'EmployeeID', 'FirstName', 'LastName', 'MiddleName', 'Email', 'Phone', 'Address', 'DateOfBirth', 'JoinDate', 'DepartmentId', 'BranchId', 'JobTitle', 'Gender', 'MaritalStatus',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'DateOfBirth' => 'date',
        'JoinDate' => 'date',
        'Gender' => GenderEnum::class,
    ];
}
