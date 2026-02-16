<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $table = 't_HRAttendanceLogs';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'LogType',
        'LogTime',
        'Channel',
        'DeviceID',
        'Latitude',
        'Longitude',
        'IsProcessed',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
    ];

    protected $casts = [
        'LogTime' => 'datetime',
        'Latitude' => 'decimal:7',
        'Longitude' => 'decimal:7',
        'CreatedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
