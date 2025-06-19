<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyMaintenanceRequest extends Model
{
    //
    protected $table = 't_MaintenanceRequest';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Property',
        'Block',
        'Floor',
        'Unit',
        'ReportedBy',
        'IssueType',
        'Priority',
        'IssueDescription',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

}
