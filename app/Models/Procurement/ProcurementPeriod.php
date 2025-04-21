<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ProcurementPeriod extends Model
{
    protected $table = 't_ProcurementPeriods';

    protected $fillable = [
        'ProcurementPeriodNumber',
        'Title',
        'StartDate',
        'EndDate',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $primaryKey = 'Id';
}
