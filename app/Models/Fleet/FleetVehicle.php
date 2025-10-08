<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\DMS\Image;

use App\Models\Core\Branch;
use App\Models\Fleet\FuelType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Core\CodeDetail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Fleet\FleetTripLog;
use App\Models\Auth\User;
use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Models\Fleet\FleetRepairLog;


class FleetVehicle extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    protected $table = 't_FleetVehicles';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'RegistrationNo',
        'VehicleType',
        'Make',
        'Model',
        'YearOfManufacture',
        'ChassisNo',
        'EngineNo',
        'FuelType',
        'Capacity',
        'OdometerReading',
        'Status',
        'AssignedBranch',
        'MaxPassengers',
        'MaxLoad',
        'VehicleStatus',
        'Color',
        'ImageId',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];


    public static function getPrimaryKey(): string
    {
        return 'VehicleId';
    }

     public function image()
{
    return $this->belongsTo(\App\Models\DMS\Image::class, 'ImageId', 'ImageID');
}


    public function vehicleType()
    {
        return $this->belongsTo(CodeDetail::class, 'VehicleType', 'ID');
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
