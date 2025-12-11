<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 't_HRLeaveTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'AnnualEntitlementDays',
        'AllowCarryForward',
        'MaxCarryForwardDays',
        'RequiresAttachment',
        'IsPaid',
        'IsActive',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'ApprovedBy',
        'ApprovedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'AllowCarryForward'  => 'boolean',
        'RequiresAttachment' => 'boolean',
        'IsPaid'             => 'boolean',
        'IsActive'           => 'boolean',
        'CreatedOn'          => 'datetime',
        'ModifiedOn'         => 'datetime',
        'ApprovedOn'         => 'datetime',
        'DeletedOn'          => 'datetime',
    ];
}
