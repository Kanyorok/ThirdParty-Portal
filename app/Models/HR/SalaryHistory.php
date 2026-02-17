<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class SalaryHistory extends Model
{
    protected $table = 't_HRSalaryHistory';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID','EffectiveDate','BasicSalary','Reason','Status',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'
    ];

    protected $casts = [
        'EffectiveDate' => 'date',
        'BasicSalary' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
