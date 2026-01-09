<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiGoal extends Model
{
    protected $table = 't_HRKPIGoals';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'PeriodID',
        'PeriodYear',
        'PeriodSegment',
        'Status',
        'TotalWeight',
        'Notes',
        'SubmittedBy',
        'SubmittedOn',
        'ApprovedBy',
        'ApprovedOn',
        'RejectedBy',
        'RejectedOn',
        'RejectionReason',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'TotalWeight' => 'decimal:2',
        'SubmittedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'RejectedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'PeriodYear' => 'integer',
        'PeriodSegment' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'PeriodID', 'Id');
    }

    public function items()
    {
        return $this->hasMany(KpiGoalItem::class, 'GoalID', 'Id');
    }
}
