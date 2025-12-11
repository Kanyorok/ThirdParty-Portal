<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollDeductionRule extends Model
{
    protected $table = 't_HRPayrollDeductionRules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'DeductionID',
        'CalcMethod',
        'Rate',
        'Amount',
        'IncomeFrom',
        'IncomeTo',
        'MinAmount',
        'MaxAmount',
        'HasRelief',
        'ReliefType',
        'ReliefRate',
        'ReliefAmount',
        'EffectiveFrom',
        'EffectiveTo',
        'FormulaText',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'Rate' => 'decimal:4',
        'Amount' => 'decimal:2',
        'IncomeFrom' => 'decimal:2',
        'IncomeTo' => 'decimal:2',
        'MinAmount' => 'decimal:2',
        'MaxAmount' => 'decimal:2',
        'HasRelief' => 'boolean',
        'ReliefRate' => 'decimal:4',
        'ReliefAmount' => 'decimal:2',
        'IsActive' => 'boolean',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function deduction()
    {
        return $this->belongsTo(PayrollDeduction::class, 'DeductionID');
    }
}
