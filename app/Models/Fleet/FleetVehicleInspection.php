<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FuelType;
use App\Models\Fleet\FleetDriver;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;
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
        'InspectionID', 'ParentInspectionID', 'InspectionTypeID', 'VehicleID', 'FuelType', 'DriverID', 'InspectionDate', 'Mileage', 'EngineOil', 'Fuel',
        'Speedometer', 'Coolant', 'Reflector', 'FireExtinguisher', 'FirstAidKit', 'SpareTyre', 'Spanner', 'Jack', '4XFloorMats',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
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

    public function driver()
    {
        return $this->belongsTo(FleetDriver::class, 'DriverID', 'Id');
    }

    public function fuel()
    {
        return $this->belongsTo(FuelType::class, 'FuelType', 'Id');
    }

    public function inspectionType()
    {
        return $this->belongsTo(CodeDetail::class, 'InspectionTypeID', 'Id');
    }


}
