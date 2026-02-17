<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ContractPenaltyRule extends Model
{
    protected $table = 't_ContractPenaltyRules';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ContractSourceType',
        'ContractSourceID',
        'MilestoneID',
        'PenaltyType',
        'Rate',
        'GraceDays',
        'CapAmount',
        'CapPercent',
        'ApplyMethod',
        'RequiresApprovalToApply',
        'RequiresApprovalToWaive',
        'IsActive',
    ];

    protected $casts = [
        'Rate' => 'float',
        'GraceDays' => 'integer',
        'CapAmount' => 'float',
        'CapPercent' => 'float',
        'RequiresApprovalToApply' => 'boolean',
        'RequiresApprovalToWaive' => 'boolean',
        'IsActive' => 'boolean',
    ];

    public function milestone()
    {
        return $this->belongsTo(ContractMilestone::class, 'MilestoneID', 'Id');
    }
}

