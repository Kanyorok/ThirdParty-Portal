<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class WorkingDaySetting extends Model
{
    protected $table = 't_HRWorkingDays';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'DayOfWeek',
        'IsWorking',
        'DayFraction',
        'StartTime',
        'EndTime',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsWorking' => 'boolean',
        'DayFraction' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn'=> 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
