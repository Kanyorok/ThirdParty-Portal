<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $table = 't_HRWorkSchedules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'WorkDate',
        'ShiftID',
        'IsOffDay',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'WorkDate'  => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn'=> 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'ShiftID');
    }
}
