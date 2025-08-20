<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetServiceAlert extends Model
{
    protected $table = 't_FleetServiceAlerts';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID', 'AlertType', 'Description',
        'TriggerMileage', 'TriggerDate',
        'IsAcknowledged', 'AcknowledgedOn', 'AcknowledgedBy',
        'CreatedOn', 'CreatedBy'
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
}
