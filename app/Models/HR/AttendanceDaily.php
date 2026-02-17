<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class AttendanceDaily extends Model
{
    protected $table = 't_HRAttendanceDaily';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'WorkDate',
        'ShiftID',
        'FirstInTime',
        'LastOutTime',
        'TotalHours',
        'OvertimeHours',
        'Status',
        'LateMinutes',
        'EarlyExitMinutes',
        'IsManualAdjusted',
        'AdjustmentReason',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'WorkDate'      => 'date',
        'FirstInTime'   => 'datetime',
        'LastOutTime'   => 'datetime',
        'TotalHours'    => 'decimal:2',
        'OvertimeHours' => 'decimal:2',
        'CreatedOn'     => 'datetime',
        'ModifiedOn'    => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'ShiftID');
    }
}
