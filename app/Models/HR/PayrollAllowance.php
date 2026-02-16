<?php

namespace App\Models\HR;

use App\Models\Finance\FinanceGLAccounts;
use Illuminate\Database\Eloquent\Model;

class PayrollAllowance extends Model
{
    protected $table = 't_HRPayrollAllowances';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Description',
        'DebitGLAccountID',
        'CreditGLAccountID',
        'IsTaxable',
        'IsPensionable',
        'IsMandatory',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsTaxable' => 'boolean',
        'IsPensionable' => 'boolean',
        'IsMandatory' => 'boolean',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function rules()
    {
        return $this->hasMany(PayrollAllowanceRule::class, 'AllowanceID');
    }

    public function grades()
    {
        return $this->belongsToMany(JobGrade::class, 't_HRPayrollAllowanceGrades', 'AllowanceID', 'GradeID');
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
