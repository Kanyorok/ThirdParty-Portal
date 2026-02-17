<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiAppraisal extends Model
{
    protected $table = 't_HRKPIAppraisals';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'GoalID',
        'EmployeeID',
        'PeriodID',
        'Status',
        'TotalScore',
        'OverallRatingID',
        'AppraisedBy',
        'AppraisedOn',
        'Comments',
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
        'TotalScore' => 'decimal:2',
        'AppraisedOn' => 'datetime',
        'SubmittedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'RejectedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function goal()
    {
        return $this->belongsTo(KpiGoal::class, 'GoalID', 'Id');
    }

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
        return $this->hasMany(KpiAppraisalItem::class, 'AppraisalID', 'Id');
    }
}
