<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiWeightingRule extends Model
{
    protected $table = 't_HRKPIWeightingRules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Code',
        'GradeID',
        'RoleID',
        'TotalWeight',
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
        'TotalWeight' => 'integer',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
