<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class SalaryAdjustment extends Model
{
    protected $table = 't_HRSalaryAdjustments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID','Type','Amount','EffectiveDate','Reason','Status',
        'RequestedBy','RequestedOn','ApprovedBy','ApprovedOn',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn',
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'EffectiveDate' => 'date',
        'RequestedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
