<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\Branch;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleInspection;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetDriver;
use App\Models\Auth\User;
use App\Models\HR\Employee;
use App\Models\Core\Approval\CodeDetail;


class FleetVehicleAssignment extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    protected $table = 't_FleetVehicleAssignments';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [

        'AssignmentID',
        'TripNo',
        'VehicleType',
        'VehicleID',
        'DriverID',
        'LastInspectionDate',
        'AssignmentDate',
        'Purpose',
        'Notes',
        'AssignedBy',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedOn',
        'DeletedBy',
    ];

    // Relationships

    public static function getPrimaryKey(): string
    {
        return 'AssgId';
    }
    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function fleetVehicleType()
    {
        return $this->belongsTo(CodeDetail::class, 'VehicleType', 'ID');
    }

    public function driver()
    {
        return $this->belongsTo(FleetDriver::class, 'DriverID', 'Id');
    }


    // public function branch()
    // {
    //     return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    // }


    public function assigner()
    {
        return $this->belongsTo(Employee::class, 'AssignedBy', 'Id');
    }

    public function trip()
    {
        return $this->belongsTo(FleetTripLog::class, 'TripNo', 'Id');
    }
    

    public function inspectionDate()
    {
        return $this->belongsTo(FleetVehicleInspection::class, 'LastInspectionDate', 'Id');
    }
}
