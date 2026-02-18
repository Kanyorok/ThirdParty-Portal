<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiGoalItem extends Model
{
    protected $table = 't_HRKPIGoalItems';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'GoalID',
        'KpiItemID',
        'AnnualTarget',
        'PeriodTarget',
        'TargetValue',
        'Weight',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'AnnualTarget' => 'decimal:2',
        'PeriodTarget' => 'decimal:2',
        'TargetValue' => 'decimal:2',
        'Weight' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function goal()
    {
        return $this->belongsTo(KpiGoal::class, 'GoalID', 'Id');
    }

    public function kpiItem()
    {
        return $this->belongsTo(KpiItem::class, 'KpiItemID', 'Id');
    }
}
