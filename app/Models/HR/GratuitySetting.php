<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class GratuitySetting extends Model
{
    protected $table = 't_HRGratuitySettings';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ApplyFor',
        'RatePercent',
        'CalcBasis',
        'EffectiveFrom',
        'EffectiveTo',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'RatePercent' => 'decimal:4',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];
}
