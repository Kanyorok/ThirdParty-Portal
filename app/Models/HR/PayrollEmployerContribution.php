<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollEmployerContribution extends Model
{
    protected $table = 't_HRPayrollEmployerContributions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PayrollRunID',
        'EmployeeID',
        'DeductionID',
        'Month',
        'Year',
        'BaseAmount',
        'Rate',
        'CalcMethod',
        'Amount',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'BaseAmount' => 'decimal:2',
        'Rate' => 'decimal:4',
        'Amount' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }

    public function deduction()
    {
        return $this->belongsTo(PayrollDeduction::class, 'DeductionID', 'Id');
    }
}
