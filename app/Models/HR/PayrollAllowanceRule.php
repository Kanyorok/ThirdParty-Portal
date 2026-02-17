<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollAllowanceRule extends Model
{
    protected $table = 't_HRPayrollAllowanceRules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'AllowanceID',
        'CalcMethod',
        'Rate',
        'Amount',
        'IncomeFrom',
        'IncomeTo',
        'MinAmount',
        'MaxAmount',
        'FormulaText',
        'EffectiveFrom',
        'EffectiveTo',
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
        'IsActive' => 'boolean',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function allowance()
    {
        return $this->belongsTo(PayrollAllowance::class, 'AllowanceID');
    }
}
