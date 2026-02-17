<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class AttendanceDevice extends Model
{
    protected $table = 't_HRAttendanceDevices';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'DeviceCode',
        'Name',
        'Channel',
        'AllowedIPs',
        'AllowedLocations',
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
