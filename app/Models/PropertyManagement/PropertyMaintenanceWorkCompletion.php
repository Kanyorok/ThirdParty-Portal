<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyMaintenanceWorkCompletion extends Model
{
    //
    protected $table = 't_WorkCompletion';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Property',
        'Block',
        'Floor',
        'Unit',
        'IssueDescription',
        'CompletionDate',
        'WorkDoneSummary',
        'PartsUsed',
        'Cost',
        'FinalStatus',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

}
