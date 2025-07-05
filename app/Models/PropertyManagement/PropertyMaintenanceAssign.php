<?php

namespace App\Models\PropertyManagement;

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
        'Property',
        'Block',
        'Floor',
        'Unit',
        'IssueDescription',
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

}
