<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class MonthlyDeduction extends Model
{
    protected $table = 't_HRMonthlyDeductions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID','DeductionID','StaffLoanID','Name','Amount','Month','Year','IsRecurring','IsAutoCalculated','Status',
        'CreatedBy','CreatedOn','ApprovedBy','ApprovedOn','ModifiedBy','ModifiedOn',
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'IsRecurring' => 'boolean',
        'IsAutoCalculated' => 'boolean',
        'CreatedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
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
