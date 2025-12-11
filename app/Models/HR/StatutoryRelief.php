<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class StatutoryRelief extends Model
{
    protected $table = 't_HRStatutoryReliefs';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Amount',
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
        'Amount' => 'decimal:2',
        'IsActive' => 'boolean',
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
