<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeActingAssignment extends Model
{
    protected $table = 't_HREmployeeActingAssignments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'ActingRoleID',
        'ActingDepartmentID',
        'ActingBranchID',
        'StartDate',
        'EndDate',
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
        'StartDate' => 'date',
        'EndDate' => 'date',
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
