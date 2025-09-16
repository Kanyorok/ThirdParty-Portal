<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetLineLedgerLimit extends Model
{
    protected $table = 't_BudgetLineLedgerLimits';
    public $timestamps = false;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetLineLedgerLimitId';
    }
    protected $fillable = [
        'BudgetID',
        'ReallocationID',
        'BudgetLineID',
        'ERPLedgerID',
        'LedgerID',
        'BranchID',
        'LimitType',
        'LimitAmount',
        'EffectiveFrom',
        'EffectiveTo',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class, 'BudgetLineID');
    }

}
