<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;

class ExitRedundancy extends Model
{
    protected $table = 't_HRExitRedundancies';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'RefNo',
        'Reason',
        'Criteria',
        'SelectionMethod',
        'UnionNotified',
        'UnionNotifiedOn',
        'LabourOfficeNotified',
        'LabourOfficeNotifiedOn',
        'Notes',
        'Status',
        'ApprovedBy',
        'ApprovedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'UnionNotified' => 'boolean',
        'LabourOfficeNotified' => 'boolean',
        'UnionNotifiedOn' => 'date',
        'LabourOfficeNotifiedOn' => 'date',
        'ApprovedOn' => 'datetime',
    ];
}
