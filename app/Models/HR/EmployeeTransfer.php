<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    protected $table = 't_HREmployeeTransfers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'FromBranchID',
        'ToBranchID',
        'FromDepartmentID',
        'ToDepartmentID',
        'FromRoleID',
        'ToRoleID',
        'EffectiveDate',
        'Reason',
        'Status',
        'RequestedBy',
        'RequestedOn',
        'ApprovedBy',
        'ApprovedOn',
        'ApprovalComment',
        'CreatedBy',
        'CreatedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'EffectiveDate' => 'date',
        'RequestedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
