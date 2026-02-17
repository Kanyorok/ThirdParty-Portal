<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use App\Models\Finance\FinanceGLAccounts;

class PayrollGLSetting extends Model
{
    protected $table = 't_HRPayrollGLSettings';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PayrollControlGLAccountID',
        'BasicSalaryExpenseGLAccountID',
        'SalaryBankGLAccountID',
        'SalaryCBSSettlementGLAccountID',
        'GratuityExpenseGLAccountID',
        'GratuityLiabilityGLAccountID',
        'CurrencyID',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public function payrollControlGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'PayrollControlGLAccountID', 'Id');
    }

    public function basicSalaryExpenseGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'BasicSalaryExpenseGLAccountID', 'Id');
    }

    public function salaryBankGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'SalaryBankGLAccountID', 'Id');
    }

    public function salaryCBSSettlementGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'SalaryCBSSettlementGLAccountID', 'Id');
    }

    public function gratuityExpenseGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'GratuityExpenseGLAccountID', 'Id');
    }

    public function gratuityLiabilityGL()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'GratuityLiabilityGLAccountID', 'Id');
    }
}
