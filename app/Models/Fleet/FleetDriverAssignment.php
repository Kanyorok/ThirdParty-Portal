<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetDriverAssignment extends Model
{
    protected $table = 't_FleetDriverAssignments';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID',
        'DriverID',
        'AssignmentDate',
        'UnassignmentDate',
        'Purpose',
        'Notes',
        'AssignedBy',
        'CreatedOn',
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }

    public function driver()
    {
        return $this->belongsTo(FleetDriver::class, 'DriverID'); // Make sure class is FleetDriver
    }

    public function assignedByUser()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'AssignedBy');
    }
}