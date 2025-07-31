<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetContractedDriverAssignment extends Model
{
    protected $table = 't_FleetContractedDriverAssignments';
    public $timestamps = false;

    protected $fillable = [
        'DriverID',
        'VehicleID',
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
        return $this->belongsTo(FleetContractedDriver::class, 'DriverID');
    }
}
