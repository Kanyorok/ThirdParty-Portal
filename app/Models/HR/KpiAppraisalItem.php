<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiAppraisalItem extends Model
{
    protected $table = 't_HRKPIAppraisalItems';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'AppraisalID',
        'GoalItemID',
        'ActualValue',
        'SelfRatingScaleID',
        'SelfRatingValue',
        'SelfScore',
        'SupervisorRatingScaleID',
        'SupervisorRatingValue',
        'FinalScore',
        'Score',
        'RatingScaleID',
        'Comments',
        'AppraiseeComments',
        'AppraiserComments',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'ActualValue' => 'decimal:2',
        'SelfRatingValue' => 'decimal:2',
        'SelfScore' => 'decimal:2',
        'SupervisorRatingValue' => 'decimal:2',
        'FinalScore' => 'decimal:2',
        'Score' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function appraisal()
    {
        return $this->belongsTo(KpiAppraisal::class, 'AppraisalID', 'Id');
    }

    public function goalItem()
    {
        return $this->belongsTo(KpiGoalItem::class, 'GoalItemID', 'Id');
    }
}
