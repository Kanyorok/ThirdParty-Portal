<?php

namespace App\Models\Fleet;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetTripLog extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_TripLogs';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'TripNo',
        'ParentTripID',
        'TripType',
        'TripCode',
        'VehicleType',
        'LoadType',
        'TripStartDate',
        'StartTime',
        'TripEndDate',
        'EndTime',
        'StartLocation',
        'EndLocation',
        'DistanceCovered',
        'Purpose',
        'Notes',
        'Status',
        'CreatedBy',
        'CreatedOn',

    ];

    public static function getPrimaryKey(): string
    {
        return 'TripId';
    }

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Fleet\FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function vehicleAssignments()
    {
        return $this->hasMany(FleetVehicleAssignment::class, 'TripNo', 'TripNo');
    }

    public function statusDetail()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }

    public function parentTripType()
    {
        return $this->belongsTo(CodeDetail::class, 'TripType', 'ID');
    }

    public function parentLoadType()
    {
        return $this->belongsTo(CodeDetail::class, 'LoadType', 'ID');
    }

    public function parentVehicleType()
    {
        return $this->belongsTo(CodeDetail::class, 'VehicleType', 'ID');
    }

    public function parentTrip()
    {
        return $this->belongsTo(FleetTripLog::class, 'ParentTripID', 'Id');
    }

    public function childTrips()
    {
        return $this->hasMany(FleetTripLog::class, 'ParentTripID', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
    }
}
