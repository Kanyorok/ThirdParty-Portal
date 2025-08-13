<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetInspectionSchedule extends Model
{
    protected $table = 't_FleetInspectionSchedule';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID',
        'InspectionType',
        'InspectionDate',
        'DueDate',
        'Status',
        'Inspector',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'VehicleID');
    }
}
