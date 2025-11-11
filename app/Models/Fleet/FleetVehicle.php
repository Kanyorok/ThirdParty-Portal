<?php

namespace App\Models\Fleet;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use App\Models\Core\GPSCoordinate;
use App\Models\DMS\Image;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class FleetVehicle extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    public $timestamps = false;
    protected $table = 't_FleetVehicles';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'RegistrationNo', 'VehicleType', 'Make', 'Model', 'YearOfManufacture', 'ChassisNo', 'EngineNo', 'FuelType', 'Capacity',
        'OdometerReading', 'Status', 'AssignedBranch', 'MaxPassengers', 'MaxLoad', 'VehicleStatus', 'Color', 'ImageId',
        'TrackerNo', 'CreatedBy', 'ModifiedBy', 'CreatedOn', 'ModifiedOn', 'DeletedBy', 'DeletedOn'
    ];


    public static function getPrimaryKey(): string
    {
        return 'VehicleId';
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'ImageId', 'ImageID');
    }


    public function vehicleType()
    {
        return $this->belongsTo(CodeDetail::class, 'VehicleType', 'ID');
    }

    public function coordinates(): MorphMany
    {
        return $this->morphMany(GPSCoordinate::class, 'source', "Source", "SourceID", $this->primaryKey);
    }

    public function tripLogs()
    {
        return $this->hasMany(FleetTripLog::class, 'VehicleID', 'Id');
    }


    public function maintenanceSchedules()
    {
        return $this->hasMany(FleetMaintenanceSchedule::class, 'VehicleID', 'Id');
    }

    public function repairLogs()
    {
        return $this->hasMany(FleetRepairLog::class, 'VehicleID', 'Id');
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'FuelType', 'Id');
    }

    public function brand()
    {
        return $this->belongsTo(FleetMake::class, 'Make', 'Id');
    }

    public function model()
    {
        return $this->belongsTo(FleetModel::class, 'Model', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'AssignedBranch');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'AssignedToUserID', 'Id');
    }


    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }

    public function vehicleStatus()
    {
        return $this->belongsTo(CodeDetail::class, 'VehicleStatus', 'ID');
    }
}
