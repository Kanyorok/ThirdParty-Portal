<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollRunLine extends Model
{
    protected $table = 't_HRPayrollRunLines';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PayrollRunID','EmployeeID','BasicSalary','TotalAllowances','TotalDeductions','StatutoryDeductions','LoanDeductions',
        'Overtime','AttendanceAdjustments','LeaveAdjustments','GrossPay','NetPay','Currency',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'
    ];

    protected $casts = [
        'BasicSalary' => 'decimal:2',
        'TotalAllowances' => 'decimal:2',
        'TotalDeductions' => 'decimal:2',
        'StatutoryDeductions' => 'decimal:2',
        'LoanDeductions' => 'decimal:2',
        'Overtime' => 'decimal:2',
        'AttendanceAdjustments' => 'decimal:2',
        'LeaveAdjustments' => 'decimal:2',
        'GrossPay' => 'decimal:2',
        'NetPay' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function run()
    {
        return $this->belongsTo(PayrollRun::class, 'PayrollRunID', 'Id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
