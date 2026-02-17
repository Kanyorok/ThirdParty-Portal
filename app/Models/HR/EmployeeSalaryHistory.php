<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryHistory extends Model
{
    protected $table = 't_HREmployeeSalaryHistory';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'BasicSalary',
        'EffectiveFrom',
        'Notes',
        'CreatedBy',
        'CreatedOn',
    ];

    protected $casts = [
        'BasicSalary' => 'decimal:2',
        'EffectiveFrom' => 'date',
        'CreatedOn' => 'datetime',
    ];
}
