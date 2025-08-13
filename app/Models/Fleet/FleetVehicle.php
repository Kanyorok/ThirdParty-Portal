<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetVehicle extends Model
{
    protected $table = 't_FleetVehicles';
    protected $primaryKey = 'VehicleID';
    public $timestamps = false;

    protected $fillable = [
        'RegistrationNumber',
        'VehicleTypeID',
        'Make',
        'Model',
        'YearOfManufacture',
        'ChassisNumber',
        'EngineNumber',
        'FuelTypeID',
        'Capacity',
        'OdometerReading',
        'Status',
        'AssignedBranchID',
        'AssignedToUserID',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    // Relationships
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'VehicleTypeID');
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'FuelTypeID');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Fleet\Branch::class, 'AssignedBranchID');
    }
}
