<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class MonthlyAllowance extends Model
{
    protected $table = 't_HRMonthlyAllowances';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID','AllowanceID','Name','Amount','Month','Year','IsTaxable','IsRecurring','Status',
        'CreatedBy','CreatedOn','ApprovedBy','ApprovedOn','ModifiedBy','ModifiedOn',
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'IsTaxable' => 'boolean',
        'IsRecurring' => 'boolean',
        'CreatedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }

    public function allowance()
    {
        return $this->belongsTo(PayrollAllowance::class, 'AllowanceID', 'Id');
    }
}
