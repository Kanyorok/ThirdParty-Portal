<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class LeaveAccrual extends Model
{
    protected $table = 't_HRLeaveAccruals';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'LeaveTypeID',
        'AccruedDays',
        'Period',
        'CreatedBy',
        'CreatedOn',
    ];

    protected $casts = [
        'AccruedDays' => 'decimal:2',
        'CreatedOn' => 'datetime',
    ];
}
