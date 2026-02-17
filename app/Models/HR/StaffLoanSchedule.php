<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class StaffLoanSchedule extends Model
{
    protected $table = 't_HRStaffLoanSchedules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'StaffLoanID','InstallmentNo','DueDate','PrincipalComponent','InterestComponent','TotalDue',
        'Status','PaidBy','PaidOn',
    ];

    protected $casts = [
        'PrincipalComponent' => 'decimal:2',
        'InterestComponent' => 'decimal:2',
        'TotalDue' => 'decimal:2',
        'DueDate' => 'date',
        'PaidOn' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(StaffLoan::class, 'StaffLoanID', 'Id');
    }
}
