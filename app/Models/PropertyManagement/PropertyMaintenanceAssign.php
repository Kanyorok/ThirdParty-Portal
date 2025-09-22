<?php

namespace App\Models\PropertyManagement;

use App\Enums\Core\PostingEnum;
use App\Models\Core\CodeDetail;
use App\Models\HRM\Employee;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;

class PropertyMaintenanceAssign extends Model
{
    use SoftDeletes, UserActorTrait;
    protected $table = 't_AssignRequest';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RequestNumber',
        'AssignmentDate',
        'AssignmentType',
        'InternalTechnician',
        'PrequalifiedVendor',
        'ExpectedStartDate',
        'ExpectedCompletion',
        'PriorityLevel',
        'InstructionNotes',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'AssignRequestId';
    }
    protected $casts = [
        'Status' => PostingEnum::class,
    ];
    public function request()
    {
        return $this->belongsTo(PropertyMaintenanceRequest::class, 'RequestNumber', 'Id');
    }
    public function assignmentType()
    {
        return $this->belongsTo(CodeDetail::class, 'AssignmentType', 'Id');
    }
    public function internalTechnician()
    {
        return $this->belongsTo(Employee::class, 'InternalTechnician', 'Id');
    }
    public function prequalifiedVendor()
    {
        return $this->belongsTo(Supplier::class, 'PrequalifiedVendor', 'Id');
    }
    public function priorityLevel()
    {
        return $this->belongsTo(CodeDetail::class, 'PriorityLevel', 'Id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }
}
