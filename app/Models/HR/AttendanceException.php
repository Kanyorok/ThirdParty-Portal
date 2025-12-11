<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class AttendanceException extends Model
{
    protected $table = 't_HRAttendanceExceptions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'AttendanceDailyID',
        'EmployeeID',
        'WorkDate',
        'Type',
        'Status',
        'Resolution',
        'ResolvedBy',
        'ResolvedOn',
        'CreatedBy',
        'CreatedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'WorkDate' => 'date',
        'ResolvedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
