<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class StaffLoan extends Model
{
    protected $table = 't_HRStaffLoans';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID','LoanRef','Name','Principal','InterestRate','TenureMonths','InstallmentAmount','Balance',
        'StartDate','EndDate','Status','CreatedBy','CreatedOn','ApprovedBy','ApprovedOn','ModifiedBy','ModifiedOn'
    ];

    protected $casts = [
        'Principal' => 'decimal:2',
        'InterestRate' => 'decimal:4',
        'InstallmentAmount' => 'decimal:2',
        'Balance' => 'decimal:2',
        'StartDate' => 'date',
        'EndDate' => 'date',
        'CreatedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function schedules()
    {
        return $this->hasMany(StaffLoanSchedule::class, 'StaffLoanID', 'Id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
