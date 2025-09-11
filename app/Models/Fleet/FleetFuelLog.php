<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetFuelLog extends Model
{
    protected $table = 't_FleetFuelLogs';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID', 'TripID', 'LogDate',
        'OdometerStart', 'OdometerEnd',
        'FuelAmount', 'FuelUnit', 'FuelType', 'Vendor',
        'Efficiency', 'Notes', 'CreatedBy', 'CreatedOn'
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }

    public function trip()
    {
        return $this->belongsTo(FleetTripLog::class, 'TripID');
    }
}
