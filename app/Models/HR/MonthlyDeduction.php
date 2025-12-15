<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class MonthlyDeduction extends Model
{
    protected $table = 't_HRMonthlyDeductions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID','Name','Amount','Month','Year','Status',
        'CreatedBy','CreatedOn','ApprovedBy','ApprovedOn','ModifiedBy','ModifiedOn'
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
