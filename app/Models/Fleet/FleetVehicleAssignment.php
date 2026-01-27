<?php

namespace App\Models\Fleet;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetVehicleAssignment extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';


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
