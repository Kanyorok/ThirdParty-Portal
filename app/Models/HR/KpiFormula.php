<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiFormula extends Model
{
    protected $table = 't_HRKPIFormulas';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Expression',
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
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
