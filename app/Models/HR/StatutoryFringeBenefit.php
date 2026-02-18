<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class StatutoryFringeBenefit extends Model
{
    protected $table = 't_HRStatutoryFringeBenefits';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'RateType',
        'Rate',
        'CapAmount',
        'EffectiveFrom',
        'EffectiveTo',
        'Description',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'Rate' => 'decimal:4',
        'CapAmount' => 'decimal:2',
        'IsActive' => 'boolean',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
