<?php

namespace App\Models\HR;

use App\Models\Finance\FinanceGLAccounts;
use Illuminate\Database\Eloquent\Model;

class PayrollDeduction extends Model
{
    protected $table = 't_HRPayrollDeductions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Description',
        'DebitGLAccountID',
        'CreditGLAccountID',
        'EmployerContributionEnabled',
        'EmployerCalcMethod',
        'EmployerRate',
        'EmployerAmount',
        'EmployerDebitGLAccountID',
        'EmployerCreditGLAccountID',
        'IsMandatory',
        'ShowInPayslip',
        'IsTaxAllowable',
        'ApplyFor',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'IsMandatory' => 'boolean',
        'ShowInPayslip' => 'boolean',
        'IsTaxAllowable' => 'boolean',
        'EmployerContributionEnabled' => 'boolean',
        'EmployerRate' => 'decimal:4',
        'EmployerAmount' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function rules()
    {
        return $this->hasMany(PayrollDeductionRule::class, 'DeductionID');
    }

    public function debitGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'DebitGLAccountID', 'Id');
    }

    public function creditGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'CreditGLAccountID', 'Id');
    }
}
