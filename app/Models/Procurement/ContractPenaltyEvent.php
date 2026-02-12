<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Finance\FinanceInvoiceEntry;
use Illuminate\Database\Eloquent\Model;

class ContractPenaltyEvent extends Model
{
    protected $table = 't_ContractPenaltyEvents';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'FinanceInvoiceID',
        'MilestoneID',
        'PenaltyRuleID',
        'ComputedAmount',
        'AppliedAmount',
        'Status',
        'ActionBy',
        'ActionOn',
        'Reason',
    ];

    protected $casts = [
        'ComputedAmount' => 'float',
        'AppliedAmount' => 'float',
        'ActionOn' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(FinanceInvoiceEntry::class, 'FinanceInvoiceID', 'Id');
    }

    public function milestone()
    {
        return $this->belongsTo(ContractMilestone::class, 'MilestoneID', 'Id');
    }

    public function penaltyRule()
    {
        return $this->belongsTo(ContractPenaltyRule::class, 'PenaltyRuleID', 'Id');
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'ActionBy', 'Id');
    }
}

