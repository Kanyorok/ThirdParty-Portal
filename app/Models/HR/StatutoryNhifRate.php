<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class StatutoryNhifRate extends Model
{
    protected $table = 't_HRStatutoryNHIFRates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'BandName',
        'IncomeFrom',
        'IncomeTo',
        'EmployeeRate',
        'EmployerRate',
        'IsPercentage',
        'EffectiveFrom',
        'EffectiveTo',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IncomeFrom' => 'decimal:2',
        'IncomeTo' => 'decimal:2',
        'EmployeeRate' => 'decimal:4',
        'EmployerRate' => 'decimal:4',
        'IsPercentage' => 'boolean',
        'IsActive' => 'boolean',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
