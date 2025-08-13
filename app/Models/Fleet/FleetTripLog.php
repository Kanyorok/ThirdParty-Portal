<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;

class FleetTripLog extends Model
{
    protected $table = 't_TripLogs';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID',
        'DriverType',
        'DriverID',
        'TripDate',
        'StartTime',
        'EndTime',
        'StartLocation',
        'EndLocation',
        'DistanceCovered',
        'Purpose',
        'Notes',
        'CreatedBy',
        'CreatedOn',
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }

    public function driver()
    {
        if ($this->DriverType === 'Contracted') {
            return $this->belongsTo(\App\Models\Fleet\ContractedDriver::class, 'DriverID');
        }

        return $this->belongsTo(\App\Models\Fleet\Driver::class, 'DriverID');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'CreatedBy');
    }
}
