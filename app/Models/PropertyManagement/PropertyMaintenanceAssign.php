<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CodeDetail;
use App\Models\HRM\Employee;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;

class PropertyMaintenanceAssign extends Model
{
    //
    protected $table = 't_AssignRequest';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RequestNumber',
        'Property',
        'Block',
        'Floor',
        'Unit',
        'AssignmentDate',
        'AssignmentType',
        'InternalTechnician',
        'PrequalifiedVendor',
        'ExpectedStartDate',
        'ExpectedCompletion',
        'PriorityLevel',
        'InstructionNotes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'AssignRequestId';
    }
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
}
