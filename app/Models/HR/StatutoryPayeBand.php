<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class StatutoryPayeBand extends Model
{
    protected $table = 't_HRStatutoryPAYEBands';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'LowerLimit',
        'UpperLimit',
        'Rate',
        'FixedAmount',
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
        'LowerLimit' => 'decimal:2',
        'UpperLimit' => 'decimal:2',
        'Rate' => 'decimal:4',
        'FixedAmount' => 'decimal:2',
        'IsActive' => 'boolean',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
