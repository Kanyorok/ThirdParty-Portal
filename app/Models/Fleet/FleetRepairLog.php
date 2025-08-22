<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetRepairLog extends Model
{
    protected $table = 't_FleetRepairLogs';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID', 'RepairType', 'RepairDate', 'Vendor', 'Cost',
        'Description', 'Notes', 'CreatedBy', 'CreatedOn'
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
    
    public function schedule()
{
    return $this->belongsTo(\App\Models\Fleet\FleetMaintenanceSchedule::class, 'ScheduleID');
}
}
