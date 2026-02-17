<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 't_HRLeaveRequests';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'LeaveTypeID',
        'StartDate',
        'EndDate',
        'TotalDays',
        'RelieverID',
        'Reason',
        'Status',
        'RequestedBy',
        'RequestedOn',
        'ApprovedBy',
        'ApprovedOn',
        'ApprovalComment',
        'CancelledBy',
        'CancelledOn',
        'CreatedBy',
        'CreatedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'StartDate' => 'date',
        'EndDate' => 'date',
        'TotalDays' => 'decimal:2',
        'RequestedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'CancelledOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function type()
    {
        return $this->belongsTo(\App\Models\HR\LeaveType::class, 'LeaveTypeID');
    }

    public function reliever()
    {
        return $this->belongsTo(Employee::class, 'RelieverID');
    }
}
