<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetRoutePlan extends Model
{
    protected $table = 't_FleetRoutePlans';
    public $timestamps = false;

    protected $fillable = [
        'TripNo',
        'VehicleID',
        'Waypoints',
        'CreatedBy',
        'CreatedOn',
    ];

    protected $casts = [
        'Waypoints' => 'array', // JSON cast for convenience
    ];

    public static function getPrimaryKey(): string
    {
        return 'RouteId';
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'CreatedBy');
    }
}
