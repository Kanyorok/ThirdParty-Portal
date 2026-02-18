<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class GratuityAccrual extends Model
{
    protected $table = 't_HRGratuityAccruals';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'PayrollRunID',
        'EmployeeID',
        'Year',
        'Month',
        'RatePercent',
        'CalcBasis',
        'BaseAmount',
        'Amount',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'RatePercent' => 'decimal:4',
        'BaseAmount' => 'decimal:2',
        'Amount' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
