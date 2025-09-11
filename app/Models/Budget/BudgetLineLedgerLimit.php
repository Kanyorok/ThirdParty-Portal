<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetLineLedgerLimit extends Model
{
    protected $table = 't_BudgetLineLedgerLimits';
    public $timestamps = false;

    protected $fillable = [
        'BudgetLineID','LedgerID','ERPLedgerID','ReallocationID','LimitType','LimitAmount',
        'EffectiveFrom','EffectiveTo','CreatedBy','CreatedOn',
        'ModifiedBy','ModifiedOn'
    ];

    public function budgetLine() { return $this->belongsTo(BudgetLine::class, 'BudgetLineID'); }
}
