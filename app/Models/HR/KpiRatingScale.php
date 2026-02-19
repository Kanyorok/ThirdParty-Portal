<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiRatingScale extends Model
{
    protected $table = 't_HRKPIRatingScales';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Code',
        'MinScore',
        'MaxScore',
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
        'MinScore' => 'decimal:2',
        'MaxScore' => 'decimal:2',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
