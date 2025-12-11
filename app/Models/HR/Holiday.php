<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 't_HRHolidays';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'HolidayDate',
        'Region',
        'IsRecurring',
        'IsActive',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'ApprovedBy',
        'ApprovedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'HolidayDate' => 'date',
        'IsRecurring' => 'boolean',
        'IsActive'    => 'boolean',
        'CreatedOn'   => 'datetime',
        'ModifiedOn'  => 'datetime',
        'ApprovedOn'  => 'datetime',
        'DeletedOn'   => 'datetime',
    ];
}
