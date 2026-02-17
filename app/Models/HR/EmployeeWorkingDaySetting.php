<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeWorkingDaySetting extends Model
{
    protected $table = 't_HREmployeeWorkingDays';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
