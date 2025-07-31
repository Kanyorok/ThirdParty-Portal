<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetMaintenanceSchedule extends Model
{
    protected $table = 't_FleetMaintenanceSchedules';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID', 'MaintenanceType', 'ScheduledDate',
        'ScheduledMileage', 'Location', 'Notes', 'Status',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn'
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
}
