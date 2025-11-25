<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\ContractedDriver;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Traits\Model\DocumentsTrait;
use App\Models\Core\CodeDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Models\HRM\Employee;

class FleetVehicleInspection extends Model
{
    use UserActorTrait, SoftDeletes, DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetVehicleInspections';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InspectionID', 'ParentInspectionID', 'InspectionTypeID', 'VehicleID', 'FuelType', 'DriverID', 'ContractedDriverID',
        'InspectionDate', 'Mileage', 'EngineOil', 'Fuel', 'Coolant', 'Reflector', 'FireExtinguisher', 'FirstAidKit',
        'SpareTyre', 'Spanner', 'Jack', '4XFloorMats',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'InspId';
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function postTrips()
    {
        return $this->hasMany(FleetVehicleInspection::class, 'ParentInspectionID');
    }

    public function parentInspection()
    {
        return $this->belongsTo(FleetVehicleInspection::class, 'ParentInspectionID');
    }

    public function fuel()
    {
        return $this->belongsTo(CodeDetail::class, 'Fuel', 'ID');
    }

    public function engineOil()
    {
        return $this->belongsTo(CodeDetail::class, 'EngineOil', 'ID');
    }

    public function coolant()
    {
        return $this->belongsTo(CodeDetail::class, 'Coolant', 'ID');
    }

    /**
     * Regular fleet driver
     */
    public function driver()
    {
        return $this->belongsTo(FleetDriver::class, 'DriverID', 'Id');
    }

    /**
     * Contracted driver
     */
    public function contractedDriver()
    {
        return $this->belongsTo(ContractedDriver::class, 'ContractedDriverID', 'Id');
    }

    public function inspectionType()
    {
        return $this->belongsTo(CodeDetail::class, 'InspectionTypeID', 'ID');
    }

    /**
     * Helper to get the assigned driver (regular or contracted)
     */
    public function assignedDriver()
    {
        return $this->driver ?? $this->contractedDriver;
    }
}
